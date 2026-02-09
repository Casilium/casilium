<?php

declare(strict_types=1);

namespace MailServiceTest\Service;

use MailService\Service\MailService;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;

final class MailServiceTest extends TestCase
{
    private TemplateRendererInterface $renderer;

    protected function setUp(): void
    {
        $this->renderer = $this->createMock(TemplateRendererInterface::class);
    }

    public function testBuildDsnIncludesTlsOverrides(): void
    {
        $config = [
            'sender'       => 'helpdesk@example.com',
            'smtp_options' => [
                'host'              => 'smtp.example.com',
                'port'              => 587,
                'connection_config' => [
                    'username'          => 'user',
                    'password'          => 'pass',
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true,
                ],
            ],
        ];

        $service = new class ($this->renderer, $config) extends MailService {
            public function exposeBuildDsn(): string
            {
                return $this->buildDsn();
            }
        };

        $dsn = $service->exposeBuildDsn();

        $this->assertSame(
            'smtp://user:pass@smtp.example.com:587?verify_peer=0&verify_peer_name=0&allow_self_signed=1',
            $dsn
        );
    }

    public function testSendLogsTransportExceptionWithoutThrowingWhenFailOnErrorDisabled(): void
    {
        $config = [
            'sender'        => 'helpdesk@example.com',
            'fail_on_error' => false,
            'smtp_options'  => [],
        ];
        $logger = new TestLogger();

        $service = new class ($this->renderer, $config, $logger) extends MailService {
            public function setMailer(MailerInterface $mailer): void
            {
                $this->mailer = $mailer;
            }
        };

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willThrowException(new TransportException('boom'));

        $service->setMailer($mailer);

        $service->send('user@example.com', 'Subject', 'Body');

        $this->assertTrue($logger->hasErrorRecords());
    }

    public function testSendRethrowsTransportExceptionWhenFailOnErrorEnabled(): void
    {
        $config = [
            'sender'        => 'helpdesk@example.com',
            'fail_on_error' => true,
            'smtp_options'  => [],
        ];
        $logger = new TestLogger();

        $service = new class ($this->renderer, $config, $logger) extends MailService {
            public function setMailer(MailerInterface $mailer): void
            {
                $this->mailer = $mailer;
            }
        };

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())
            ->method('send')
            ->willThrowException(new TransportException('boom'));

        $service->setMailer($mailer);

        $this->expectException(TransportException::class);
        $service->send('user@example.com', 'Subject', 'Body');

        $this->assertTrue($logger->hasErrorRecords());
    }

    public function testSendIsSkippedWhenMailDisabled(): void
    {
        $config = [
            'sender'       => 'helpdesk@example.com',
            'enabled'      => false,
            'smtp_options' => [],
        ];
        $logger = new TestLogger();

        $service = new class ($this->renderer, $config, $logger) extends MailService {
            public function setMailer(MailerInterface $mailer): void
            {
                $this->mailer = $mailer;
            }
        };

        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');

        $service->setMailer($mailer);

        $service->send('user@example.com', 'Subject', 'Body');

        $this->assertTrue($logger->hasInfoRecords());
    }
}
