<?php

declare(strict_types=1);

namespace Ticket\Command;

use App\Encryption\Sodium;
use App\Exception\SodiumException;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Monolog\Logger;
use OrganisationContact\Entity\Contact;
use RuntimeException;
use ServiceLevel\Service\CalculateBusinessHours;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Ticket\Entity\Agent;
use Ticket\Entity\Queue;
use Ticket\Entity\Ticket;
use Ticket\Exception\MailConnectionException;
use Ticket\Service\MailReader;
use Ticket\Service\TicketService;

use function array_key_exists;
use function count;
use function defined;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function filter_var;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function getmypid;
use function in_array;
use function is_dir;
use function is_resource;
use function json_decode;
use function json_encode;
use function mkdir;
use function preg_match;
use function preg_replace;
use function sprintf;
use function strip_tags;
use function strripos;
use function strtolower;
use function substr;
use function sys_get_temp_dir;
use function time;
use function unlink;

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

    private Logger $logger;

    private string $ourSenderAddress;

    /** @var resource */
    private $lockFile;

    public function __construct(
        EntityManagerInterface $entityManager,
        TicketService $ticketService,
        Logger $logger,
        array $config
    ) {
        $this->entityManager = $entityManager;
        $this->ticketService = $ticketService;
        $this->logger        = $logger;
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

        $lockDir      = ROOT . '/data/mail';
        $lockFilename = $lockDir . '/fetch_mail.pid';

        // Create directory if it doesn't exist
        if (! is_dir($lockDir)) {
            if (! mkdir($lockDir, 0755, true)) {
                throw new Exception(sprintf('Unable to create lock directory %s', $lockDir));
            }
        }

        $lockFile = fopen($lockFilename, 'c');
        if ($lockFile === false) {
            throw new Exception(sprintf(
                'Unable to open lock file %s',
                $lockFilename
            ));
        }
        $gotLock = flock($lockFile, LOCK_EX | LOCK_NB, $wouldBlock);
        if (! $gotLock && ! $wouldBlock) {
            throw new Exception(sprintf(
                'Unexpected error locking file %s',
                $lockFilename
            ));
        } elseif (! $gotLock && $wouldBlock) {
            // Instead of exit(), throw exception so finally block runs
            throw new RuntimeException('Another instance is already running');
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
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->lock();

            $output->writeln('<info>Importing messages from email</info>');
            $this->logger->info('Starting email import process');

            return $this->processAllQueues($output);
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>Fatal error: %s</error>', $e->getMessage()));
            $this->logger->critical('Email import failed with critical error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        } finally {
            // Always release lock, even on exception
            if (isset($this->lockFile) && is_resource($this->lockFile)) {
                $this->releaseLock();
            }
        }
    }

    /**
     * Process all queues configured for mail fetching
     */
    private function processAllQueues(OutputInterface $output): int
    {
        $queues = $this->entityManager->getRepository(Queue::class)->findBy(
            ['fetchFromMail' => true],
            ['name' => 'ASC']
        );

        if (empty($queues)) {
            $output->writeln('<comment>No queues found</comment>');
            $this->logger->warning('No mail queues configured for processing');
            return Command::SUCCESS;
        }

        $totalProcessed = 0;
        $totalFailed    = 0;

        // Group queues by host for circuit breaker optimization
        $queuesByHost = $this->groupQueuesByHost($queues);

        foreach ($queues as $queue) {
            try {
                $processed       = $this->processQueue($queue, $output);
                $totalProcessed += $processed;
            } catch (MailConnectionException $e) {
                $totalFailed++;
                $output->writeln(sprintf(
                    '<error>Queue "%s" failed: %s</error>',
                    $queue->getName(),
                    $e->getMessage()
                ));
                $this->logger->error('Queue processing failed', [
                    'queue' => $queue->getName(),
                    'host'  => $queue->getHost(),
                    'error' => $e->getMessage(),
                ]);

                // Record failure for circuit breaker
                $this->recordQueueFailure($queue);

                // If same host failed for another queue, skip remaining queues on this host
                if ($this->shouldSkipHostDueToFailure($queue->getHost(), $queuesByHost)) {
                    $output->writeln(sprintf(
                        '<comment>Skipping remaining queues on host %s due to connection failure</comment>',
                        $queue->getHost()
                    ));
                    $this->logger->warning('Skipping queues on failed host', [
                        'host' => $queue->getHost(),
                    ]);
                    continue;
                }
            }
        }

        $output->writeln(sprintf(
            '<info>Processed %d messages from %d queues (%d queues failed)</info>',
            $totalProcessed,
            count($queues) - $totalFailed,
            $totalFailed
        ));

        $this->logger->info('Email import completed', [
            'messages_processed' => $totalProcessed,
            'queues_processed'   => count($queues) - $totalFailed,
            'queues_failed'      => $totalFailed,
        ]);

        return Command::SUCCESS;
    }

    /**
     * Process a single queue
     */
    private function processQueue(Queue $queue, OutputInterface $output): int
    {
        // Validate queue configuration
        if ($queue->getHost() === null || $queue->getUser() === null || $queue->getPassword() === null) {
            $output->writeln(sprintf(
                '<warning>Skipping queue "%s" - missing configuration</warning>',
                $queue->getName()
            ));
            $this->logger->warning('Queue skipped due to missing configuration', [
                'queue' => $queue->getName(),
            ]);
            return 0;
        }

        // Check circuit breaker - skip if recently failed
        if ($this->shouldSkipDueToRecentFailures($queue)) {
            $output->writeln(sprintf(
                '<comment>Skipping queue "%s" - circuit breaker active (recent failures)</comment>',
                $queue->getName()
            ));
            $this->logger->info('Queue skipped by circuit breaker', [
                'queue' => $queue->getName(),
            ]);
            return 0;
        }

        $output->writeln(sprintf('<info>Processing Queue: %s</info>', $queue->getName()));

        // Retrieve messages (can throw MailConnectionException)
        try {
            $messages = $this->getMessages($queue);
        } catch (Exception $e) {
            throw MailConnectionException::forQueueConnectionFailure(
                $queue->getName(),
                $queue->getHost() ?? 'unknown',
                $e
            );
        }

        $output->writeln(sprintf('<info>Retrieved %d messages</info>', count($messages)));
        $this->logger->info('Messages retrieved from queue', [
            'queue' => $queue->getName(),
            'count' => count($messages),
        ]);

        $processedCount = 0;

        // Process each message with isolation
        foreach ($messages as $index => $message) {
            if ($message === null) {
                continue;
            }

            try {
                if ($this->processMessage($message, $queue, $output)) {
                    $processedCount++;
                }
            } catch (Throwable $e) {
                // Log but continue processing other messages
                $output->writeln(sprintf(
                    '<error>Message %s failed: %s</error>',
                    $index,
                    $e->getMessage()
                ));
                $this->logger->error('Message processing failed', [
                    'queue'         => $queue->getName(),
                    'message_index' => $index,
                    'from'          => $message['from'] ?? 'unknown',
                    'subject'       => $message['subject'] ?? 'unknown',
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        // Reset failure counter on success
        $this->resetQueueFailureCounter($queue);

        return $processedCount;
    }

    /**
     * Process a single message
     */
    private function processMessage(array $message, Queue $queue, OutputInterface $output): bool
    {
        // Ignore subjects like "Out of Office"
        if ($this->shouldIgnoreFromSubject($message['subject'])) {
            $output->writeln('<comment>Ignoring message due to subject filter</comment>');
            return false;
        }

        // Check if this is a reply (has tracking UUID)
        $trackingId = $this->getUuid($message['body']);

        if ($trackingId !== null) {
            return $this->handleTicketReply($message, $trackingId, $output);
        } else {
            return $this->handleNewTicket($message, $queue, $output);
        }
    }

    /**
     * Handle a ticket reply message
     */
    private function handleTicketReply(array $message, string $trackingId, OutputInterface $output): bool
    {
        $output->writeln(sprintf('<info>Found ticket reply to %s</info>', $trackingId));

        try {
            $responseId = $this->createTicketReplyFromMessage($message, $trackingId);

            if ($responseId !== null) {
                $output->writeln('<info>Response added to ticket</info>');
                $this->logger->info('Ticket reply created', [
                    'tracking_id' => $trackingId,
                    'response_id' => $responseId,
                    'from'        => $message['from'],
                ]);
                return true;
            }

            return false;
        } catch (Throwable $e) {
            // Notification failures are logged but don't fail the message
            $this->logger->warning('Ticket reply notification failed', [
                'tracking_id' => $trackingId,
                'error'       => $e->getMessage(),
            ]);
            throw $e; // Re-throw for outer handler
        }
    }

    /**
     * Handle a new ticket message
     */
    private function handleNewTicket(array $message, Queue $queue, OutputInterface $output): bool
    {
        try {
            $ticketId = $this->createTicketFromMessage($message, $queue);

            if ($ticketId > 0) {
                $output->writeln(sprintf(
                    '<info>Ticket #%d created from %s</info>',
                    $ticketId,
                    $message['from']
                ));
                $this->logger->info('New ticket created', [
                    'ticket_id' => $ticketId,
                    'from'      => $message['from'],
                    'subject'   => $message['subject'],
                    'queue'     => $queue->getName(),
                ]);
                return true;
            } elseif ($ticketId === -1) {
                $output->writeln(sprintf(
                    '<comment>Ticket from %s not created - contact not found</comment>',
                    $message['from']
                ));
                $this->logger->notice('Ticket not created - unknown contact', [
                    'from'    => $message['from'],
                    'subject' => $message['subject'],
                ]);
                return false;
            } else {
                $output->writeln(sprintf(
                    '<error>Ticket from %s failed to create</error>',
                    $message['from']
                ));
                $this->logger->error('Ticket creation failed', [
                    'from'    => $message['from'],
                    'subject' => $message['subject'],
                ]);
                return false;
            }
        } catch (Throwable $e) {
            $this->logger->error('New ticket creation exception', [
                'from'  => $message['from'],
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a new ticket from email messages
     *
     * @param array $message email message
     * @param Queue $queue queue to assign ticket to
     * @return int|null id of created ticket or null if no ticket created
     * @throws Exception
     */
    protected function createTicketFromMessage(array $message, Queue $queue): ?int
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

        $impact          = Ticket::IMPACT_LOW;
        $urgency         = Ticket::URGENCY_LOW;
        $priorityId      = $impact + $urgency;
        $dueDate         = $date->copy()->addDays(2);
        $responseDueDate = $dueDate->copy()->subHours(4);

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
                    ->getSlaTarget($priorityId)->getResolveTime()
            );

            // calculate expected response date
            $responseDueDate = $businessHoursCalc->addHoursTo(
                $responseDueDate,
                $ticket->getOrganisation()
                    ->getSla()
                    ->getSlaTarget($priorityId)->getResolveTime()
            );
        }

        // build ticket info
        $data = [
            'createdAt'         => $date->format('Y-m-d H:i:s'),
            'agent_id'          => null,
            'organisation_id'   => $contact->getOrganisation()->getId(),
            'contact_id'        => $contact->getId(),
            'type_id'           => 1,
            'impact'            => $impact,
            'urgency'           => $urgency,
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

            // Try to send notification, but don't fail if it doesn't work
            try {
                $this->ticketService->newTicketNotification($ticket);
            } catch (Throwable $e) {
                $this->logger->warning('New ticket notification failed', [
                    'ticket_id' => $ticket->getId(),
                    'error'     => $e->getMessage(),
                ]);
                // Continue - ticket is saved, just notification failed
            }

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
            // Try to send notification
            try {
                $this->ticketService->newTicketReplyNotification($ticket);
            } catch (Throwable $e) {
                $this->logger->warning('Ticket reply notification failed', [
                    'ticket_id'   => $ticket->getId(),
                    'response_id' => $response->getId(),
                    'error'       => $e->getMessage(),
                ]);
                // Continue - response saved, just notification failed
            }

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
     * Check if queue should be skipped due to recent failures
     */
    private function shouldSkipDueToRecentFailures(Queue $queue): bool
    {
        $failureFile = $this->getFailureFilePath($queue);

        if (! file_exists($failureFile)) {
            return false;
        }

        $data = json_decode(file_get_contents($failureFile), true);

        if (! isset($data['failures']) || ! isset($data['last_failure'])) {
            return false;
        }

        $hourAgo = time() - 3600;

        // Skip if 3+ failures in last hour
        if ($data['failures'] >= 3 && $data['last_failure'] > $hourAgo) {
            return true;
        }

        // Clean up old failure data
        if ($data['last_failure'] < $hourAgo) {
            @unlink($failureFile);
        }

        return false;
    }

    /**
     * Record queue failure for circuit breaker
     */
    private function recordQueueFailure(Queue $queue): void
    {
        $failureFile = $this->getFailureFilePath($queue);

        $data = ['failures' => 1, 'last_failure' => time()];

        if (file_exists($failureFile)) {
            $existing = json_decode(file_get_contents($failureFile), true);
            if (isset($existing['failures'])) {
                $data['failures'] = $existing['failures'] + 1;
            }
        }

        file_put_contents($failureFile, json_encode($data));
    }

    /**
     * Reset failure counter after successful processing
     */
    private function resetQueueFailureCounter(Queue $queue): void
    {
        $failureFile = $this->getFailureFilePath($queue);

        if (file_exists($failureFile)) {
            @unlink($failureFile);
        }
    }

    /**
     * Get failure tracking file path for queue
     */
    private function getFailureFilePath(Queue $queue): string
    {
        $safeQueueName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $queue->getName());
        return sys_get_temp_dir() . "/mail_queue_failures_{$safeQueueName}.json";
    }

    /**
     * Group queues by host for optimization
     *
     * @param array $queues
     * @return array
     */
    private function groupQueuesByHost(array $queues): array
    {
        $grouped = [];
        foreach ($queues as $queue) {
            $host = $queue->getHost() ?? 'unknown';
            if (! isset($grouped[$host])) {
                $grouped[$host] = [];
            }
            $grouped[$host][] = $queue;
        }
        return $grouped;
    }

    /**
     * Check if we should skip remaining queues on this host
     */
    private function shouldSkipHostDueToFailure(string $host, array $queuesByHost): bool
    {
        // If multiple queues share the same host and one failed,
        // likely others will fail too - skip them
        if (! isset($queuesByHost[$host]) || count($queuesByHost[$host]) <= 1) {
            return false;
        }

        // Check if this is the first failure for this host
        static $failedHosts = [];

        if (in_array($host, $failedHosts, true)) {
            return true; // Already failed
        }

        $failedHosts[] = $host;
        return false; // First failure, continue processing
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
