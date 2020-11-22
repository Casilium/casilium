<?php
declare(strict_types=1);

namespace Ticket\Command;

use App\Encryption\Sodium;
use App\Exception\SodiumException;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OrganisationContact\Entity\Contact;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticket\Entity\Queue;
use Ticket\Entity\Ticket;
use Ticket\Service\MailReader;
use Ticket\Service\TicketService;
use function array_key_exists;
use function count;
use function defined;
use function filter_var;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function getmypid;
use function is_array;
use function preg_match;
use function sprintf;
use function strripos;
use function substr;
use const FILTER_VALIDATE_EMAIL;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_UN;
use const PREG_OFFSET_CAPTURE;

class CreateTicketsFromEmail extends Command
{
    public const TYPE_NEW   = 0;
    public const TYPE_REPLY = 1;

    /** @var array */
    private $config;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var TicketService */
    private $ticketService;

    /** @var string */
    private $ourSenderAddress;

    /** @var resource */
    private $lockFile;

    public function __construct(
        EntityManagerInterface $entityManager,
        TicketService $ticketService,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->ticketService = $ticketService;
        $this->config        = $config;

        if (! array_key_exists('encryption', $this->config) || ! isset($this->config['encryption']['key'])) {
            throw SodiumException::forEncryptionKeyNotFoundInConfig();
        }

        if (! array_key_exists('mail', $this->config)) {
            throw new Exception('Mail service configuration not found');
        }
        if (
            ! isset($config['mail']['sender'])
            || (filter_var($config['mail']['sender'], FILTER_VALIDATE_EMAIL) === false)
        ) {
            throw new Exception('Invalid sender configuration');
        }

        $this->ourSenderAddress = $this->config['mail']['sender'];

        parent::__construct();
    }

    /**
     * Configure this command
     */
    public function configure(): void
    {
        $this->setName('ticket:create-from-mail')
            ->setDescription('Creates tickets from mail inbox');
    }

    /**
     * Create a pid file to ensure process is not already running
     *
     * @throws Exception
     */
    public function lock(): void
    {
        if (defined('ROOT') == false) {
            throw new Exception('ROOT path not defined!');
        }

        $lock_filename = ROOT . '/data/get_mail.pid';
        $lock_file     = fopen($lock_filename, 'c');
        if ($lock_file === false) {
            throw new Exception(sprintf(
                'Unable to open lock file %s',
                $lock_file
            ));
        }
        $got_lock = flock($lock_file, LOCK_EX | LOCK_NB, $wouldBlock);
        if (! $got_lock && ! $wouldBlock) {
            throw new Exception(sprintf(
                'Unexpected error locking file %s',
                $lock_filename
            ));
        } elseif (! $got_lock && $wouldBlock) {
            exit('Another instance is already running');
        }

        // lock acquired let's write our PID to te lock file for the convenience
        // of humans who may wish to terminate the script.
        ftruncate($lock_file, 0);
        fwrite($lock_file, getmypid() . "\n");
        $this->lockFile = $lock_file;
    }

    /**
     * Clear out the pid file, and release the lock
     */
    private function releaseLock(): void
    {
        ftruncate($this->lockFile, 0);
        flock($this->lockFile, LOCK_UN);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->lock();

        $output->writeln('<info>Importing messages from email</info>');

        // fetch ticket queue information
        $queues = $this->entityManager->getRepository(Queue::class)->findBy(
            ['fetch_from_mail' => true],
            ['name' => 'ASC']
        );

        if ($queues === null || ! is_array($queues) || count($queues) === 0) {
            $output->writeln('No queues found');
            return 0;
        }

        // process each queue
        /** @var Queue $queue */
        foreach ($queues as $queue) {
            if ($queue->getHost() === null || $queue->getUser() === null || $queue->getPassword() === null) {
                $output->writeln(sprintf(
                    '<warning>Not processing queue "%s", missing information</warning>',
                    $queue->getName()
                ));
                continue;
            }

            $output->writeln(sprintf(' <info>Processing Queue: %s</info>', $queue->getName()));

            // retrieve messages from queue inbox
            $messages = $this->getMessages($queue);
            $output->writeln(sprintf(' <info>Retrieved %s messages</info>', count($messages)));

            foreach ($messages as $index => $message) {
                $tracking_id = $this->getUuid($message['body']);
                if ($tracking_id !== null) {
                    $output->writeln(sprintf('  <info>Found ticket reply to %s', $tracking_id));
                    if ($this->createTicketReplyFromMessage($message, $tracking_id)) {
                        $output->writeln('  <info>Response added to ticket</info>');
                    }
                    continue;
                } elseif ($ticket_id = $this->createTicketFromMessage($message, $queue) > 0) {
                    $output->writeln(sprintf(
                        '  <info>Ticket created with id %s from %s</info>',
                        $ticket_id,
                        $message['from']
                    ));
                } elseif ($ticket_id < 0) {
                    $output->writeln(sprintf(
                        '  <comment>Ticket from %s was not created</comment> (user unknown)',
                        $message['from']
                    ));
                } else {
                    $output->writeln(sprintf(
                        '  <error>Ticket from %s was not created</error>',
                        $message['from']
                    ));
                }
            }
        }

        $this->releaseLock();
        return 0;
    }

    /**
     * Create a new ticket from email messages
     *
     * @param array $message
     * @throws Exception
     */
    private function createTicketFromMessage(array $message, Queue $queue): ?int
    {
        $ticket = new Ticket();

        // see if we have an employee in the database matching the email address
        /** @var Contact $contact */
        $contact = $this->entityManager->getRepository(Contact::class)->findOneBy([
            'work_email' => $message['from'],
        ]);
        if ($contact === null) {
            return -1;
        }

        $date = Carbon::parse($message['date']);

        $data = [
            'createdAt'         => $date->format('Y-m-d H:i:s'),
            'agent_id'          => null,
            'organisation_id'   => $contact->getOrganisation()->getId(),
            'contact_id'        => $contact->getId(),
            'type_id'           => 1,
            'impact'            => 3,
            'urgency'           => 3,
            'site_id'           => 1,
            'queue_id'          => $queue->getId(),
            'short_description' => $message['subject'],
            'long_description'  => $message['body'],
            'source'            => $ticket::SOURCE_EMAIL,
        ];

        if ($data['long_description'] !== null) {
            $ticket = $this->ticketService->save($data);
            return $ticket->getId();
        }
        return null;
    }

    public function createTicketReplyFromMessage(array $message, string $uuid): ?int
    {
        /** @var Ticket $ticket */
        $ticket = $this->entityManager->getRepository(Ticket::class)->findOneBy(['uuid' => $uuid]);
        if ($ticket === null) {
            throw new Exception(sprintf('Ticket with UUID %s not found', $uuid));
        }

        /** @var Contact $employee */
        $contact = $this->entityManager->getRepository(Contact::class)->findOneBy([
            'work_email' => $message['from'],
        ]);
        if ($employee === null) {
            throw new Exception(sprintf('Employee not found with email: %s', $message['from']));
        }

        $data = [
            'contact_id'    => $employee->getId(),
            'ticket_status' => $ticket->getStatus()->getId(),
            'is_public'     => 1,
            'response'      => $this->stripSignature($message['body']),
        ];

        $response = $this->ticketService->saveResponse($ticket, $data);
        if ($response->getId() !== null) {
            return $response->getId();
        }
        return null;
    }

    /**
     * Retrieve messages from Queue mailbox
     *
     * @return array
     * @throws SodiumException
     */
    private function getMessages(Queue $queue): array
    {
        /** @var Queue $queue */
        $queue = $this->entityManager->getRepository(Queue::class)
            ->findOneBy(['name' => $queue->getName()]);

        $host = $queue->getHost();
        $user = $queue->getUser();

        // decrypt password
        $key      = $this->config['encryption']['key'];
        $password = Sodium::decrypt($queue->getPassword(), $key);

        $mailReader = new MailReader($host, $user, $password);

        return $mailReader->processMessages();
    }

    /**
     * Find ticket UUID in body content
     */
    private function getUuid(string $content): ?string
    {
        $pattern = '/Tracking ID: ([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12})/';
        preg_match($pattern, $content, $matches);
        if ($matches !== null) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Strip signature
     *
     * @param string email body
     * @return string|string[]|null
     */
    public function stripSignature(string $body): string
    {
        $signatureData = [
            '--- REPLY ABOVE THIS LINE ---',
        ];

        foreach ($signatureData as $key => $datum) {
            if (strripos($body, $datum) !== false) {
                $body = substr($body, 0, strripos($body, $datum));
            }
        }

        $pattern = "/From: {$this->ourSenderAddress}/";
        $result  = preg_match($pattern, $body, $matches, PREG_OFFSET_CAPTURE);
        if ($result !== false && $result !== 0) {
            $body = substr($body, 0, $matches[0][1]);
        }
        return $body;
    }
}
