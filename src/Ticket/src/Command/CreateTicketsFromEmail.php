<?php

declare(strict_types=1);

namespace Ticket\Command;

use App\Encryption\Sodium;
use App\Exception\SodiumException;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OrganisationContact\Entity\Contact;
use ServiceLevel\Service\CalculateBusinessHours;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ticket\Entity\Agent;
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
use function strip_tags;
use function strripos;
use function strtolower;
use function substr;

use const FILTER_VALIDATE_EMAIL;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_UN;
use const PREG_OFFSET_CAPTURE;

/**
 * Creates tickets from email messages
 */
class CreateTicketsFromEmail extends Command
{
    public const TYPE_NEW   = 0;
    public const TYPE_REPLY = 1;

    /** @var array */
    private array $config;

    private EntityManagerInterface $entityManager;

    private TicketService $ticketService;

    private string $ourSenderAddress;

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

        // we need the encryption key to decrypt the mailbox password
        if (! array_key_exists('encryption', $this->config) || ! isset($this->config['encryption']['key'])) {
            throw SodiumException::forEncryptionKeyNotFoundInConfig();
        }

        // we need mail server settings
        if (! array_key_exists('mail', $this->config)) {
            throw new Exception('Mail service configuration not found');
        }

        // get sender address from config
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
        if (defined('ROOT') === false) {
            throw new Exception('ROOT path not defined!');
        }

        $lockFilename = ROOT . '/data/mail/fetch_mail.pid';

        $lockFile = fopen($lockFilename, 'c');
        if ($lockFile === false) {
            throw new Exception(sprintf(
                'Unable to open lock file %s',
                $lockFile
            ));
        }
        $gotLock = flock($lockFile, LOCK_EX | LOCK_NB, $wouldBlock);
        if (! $gotLock && ! $wouldBlock) {
            throw new Exception(sprintf(
                'Unexpected error locking file %s',
                $lockFilename
            ));
        } elseif (! $gotLock && $wouldBlock) {
            exit('Another instance is already running');
        }

        // lock acquired let's write our PID to te lock file for the convenience
        // of humans who may wish to terminate the script.
        ftruncate($lockFile, 0);
        fwrite($lockFile, getmypid() . "\n");
        $this->lockFile = $lockFile;
    }

    /**
     * Clear out the pid file, and release the lock
     */
    private function releaseLock(): void
    {
        ftruncate($this->lockFile, 0);
        flock($this->lockFile, LOCK_UN);
    }

    /**
     * @param InputInterface $input console input handle
     * @param OutputInterface $output console output handle
     * @return int exit code
     * @throws SodiumException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->lock();

        $output->writeln('<info>Importing messages from email</info>');

        // fetch ticket queue information
        $queues = $this->entityManager->getRepository(Queue::class)->findBy(
            ['fetchFromMail' => true],
            ['name' => 'ASC']
        );

        // if we don't have any queues to parse, exit cleanly
        if (! is_array($queues) || count($queues) === 0) {
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

            // process mail messages
            foreach ($messages as $index => $message) {
                if (null === $message) {
                    continue;
                }

                // ignore subjects such as out of office
                if ($this->shouldIgnoreFromSubject($message['subject'])) {
                    $output->writeln('- ignore message due to subject');
                    continue;
                }

                //  if body has a tracking ID then this is a reply
                $trackingId = $this->getUuid($message['body']);
                if ($trackingId !== null) {
                    $output->writeln(sprintf('  <info>Found ticket reply to %s', $trackingId));
                    if ($this->createTicketReplyFromMessage($message, $trackingId)) {
                        $output->writeln('  <info>Response added to ticket</info>');
                    }
                    continue;
                } elseif ($ticketId = $this->createTicketFromMessage($message, $queue) > 0) {
                    // new ticket created if no tracking ID found
                    $output->writeln(sprintf(
                        '  <info>Ticket created with id %s from %s</info>',
                        $ticketId,
                        $message['from']
                    ));
                } elseif ($ticketId < 0) {
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

        // release lock on pid file
        $this->releaseLock();

        // exit cleanly
        return 0;
    }

    /**
     * Create a new ticket from email messages
     *
     * @param array $message email message
     * @param Queue $queue queue to assign ticket to
     * @return int|null id of created ticket or null if no ticket created
     * @throws Exception
     */
    private function createTicketFromMessage(array $message, Queue $queue): ?int
    {
        // see if we have an employee in the database matching the email address
        /** @var Contact $contact */
        $contact = $this->entityManager->getRepository(Contact::class)->findOneBy([
            'workEmail' => $message['from'],
        ]);
        if ($contact === null) {
            return -1;
        }

        $organisation = $contact->getOrganisation();
        if ($organisation === null) {
            return -1;
        }

        // if we have a contact and organisation we can continue
        $ticket = new Ticket();
        $ticket->setOrganisation($organisation);

        // parse date from mail (want as UTC timezone)
        $date = Carbon::parse($message['date'], 'UTC');

        $dueDate         = $date->addDays(2);
        $responseDueDate = $dueDate->subHours(4);

        // if we have a sla then we can retrieve date response and resolution is due
        if ($contact->getOrganisation()->getSla() !== null) {
            // grab the SLA
            $sla = $contact->getOrganisation()->getSla();

            // used to calculate working hours so ticket becomes due during working hours
            $businessHoursCalc = new CalculateBusinessHours($sla->getBusinessHours());

            // calculate due date
            $dueDate = $businessHoursCalc->addHoursTo(
                $dueDate,
                $ticket->getOrganisation()
                    ->getSla()
                    ->getSlaTarget($ticket->getPriority()->getId())->getResolveTime()
            );

            // calculate expected response date
            $responseDueDate = $businessHoursCalc->addHoursTo(
                $responseDueDate,
                $ticket->getOrganisation()
                    ->getSla()
                    ->getSlaTarget($ticket->getPriority()->getId())->getResolveTime()
            );
        }

        // build ticket info
        $data = [
            'createdAt'         => $date->format('Y-m-d H:i:s'),
            'agent_id'          => null,
            'organisation_id'   => $contact->getOrganisation()->getId(),
            'contact_id'        => $contact->getId(),
            'type_id'           => 1,
            'impact'            => 3,
            'urgency'           => 3,
            'site_id'           => null,
            'queue_id'          => $queue->getId(),
            'short_description' => $message['subject'],
            'long_description'  => $message['body'],
            'source'            => $ticket::SOURCE_EMAIL,
            'due_date'          => $dueDate->format('Y-m-d H:i:s'),
        ];

        // add response due date if applicable
        if ($responseDueDate !== null) {
            $data['first_response_due'] = $responseDueDate->format('Y-m-d H:i:s');
        }

        // only create ticket if we actually have a description of the problem
        if ($data['long_description'] !== null) {
            // save ticket
            $ticket = $this->ticketService->save($data);

            $this->ticketService->newTicketNotification($ticket);
            // return id of created ticket
            return $ticket->getId();
        }

        // ticket not created
        return null;
    }

    /**
     * @param array $message message to parse
     * @param string $uuid uuid of ticket to add reply to
     * @return int|null response id or null if no response created
     * @throws Exception
     */
    public function createTicketReplyFromMessage(array $message, string $uuid): ?int
    {
        /** @var Ticket $ticket */
        $ticket = $this->entityManager->getRepository(Ticket::class)->findOneBy(['uuid' => $uuid]);
        if ($ticket === null) {
            throw new Exception(sprintf('Ticket with UUID %s not found', $uuid));
        }

        // agent responding?
        $agent = null;

        /** @var Contact $contact */
        $contact = $this->entityManager->getRepository(Contact::class)->findOneBy([
            'workEmail' => $message['from'],
        ]);

        // if haven't found a contact from the "from" field, check if the reply is from an agent
        if ($contact === null) {
            // not found, reply from agent?
            $agent = $this->entityManager->getRepository(Agent::class)->findOneBy([
                'email' => $message['from'],
            ]);

            // no employee or agent found
            if ($agent === null) {
                return null;
            }
        }

        // build reply, set to public
        $data = [
            'ticket_status' => $ticket->getStatus()->getId(),
            'is_public'     => 1,
            'response'      => $this->stripSignature($message['body']),
        ];

        // assign contact if found
        if ($contact !== null) {
            $data['contact_id'] = $contact->getId();
        }

        // assign agent if found
        if ($agent !== null) {
            $data['agent_id'] = $agent->getId();
        }

        // save the response
        $response = $this->ticketService->saveResponse($ticket, $data);
        if ($response->getId() !== null) {
            $this->ticketService->newTicketReplyNotification($ticket);

            // return the response id
            return $response->getId();
        }

        // response not created
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
     * Find ticket UUID in message body
     *
     * @param string|null $content The message to parse
     * @return string|null uuid or null if not found
     */
    private function getUuid(?string $content): ?string
    {
        if (null === $content) {
            return null;
        }

        $pattern = '/Tracking ID: ([0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12})/';
        preg_match($pattern, $content, $matches);
        if (! empty($matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Strip signature
     *
     * @param string $body string to parse
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

    /**
     * Parses email subject to determine if should be skipped,
     * for example "Out of Office"
     *
     * @param string $subject email subject
     * @return bool true if should ignore or false
     */
    public function shouldIgnoreFromSubject(string $subject): bool
    {
        $patterns = $this->config['mail']['ignore_with_subject'] ?? [];

        // if no subject or patterns to check against then allow message
        if (empty($subject) || empty($patterns)) {
            return false;
        }

        // if subject is found, flag message to be skipped
        $subject = strip_tags(strtolower($subject));
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $subject)) {
                return true;
            }
        }

        // otherwise allow message
        return false;
    }
}
