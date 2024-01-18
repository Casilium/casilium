<?php

declare(strict_types=1);

namespace MailService\Service;

use Laminas\Mail;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mime\Message as MimeMessage;
use Laminas\Mime\Part as MimePart;
use Mezzio\Template\TemplateRendererInterface;

use function array_merge;

class MailService
{
    /** @var array */
    protected $options;

    /** @var TemplateRendererInterface */
    protected $renderer;

    /** @var SmtpTransport */
    protected $transport;

    /** @var string */
    protected $mailFrom;

    /**
     * @param TemplateRendererInterface $renderer Template renderer
     * @param array $options config options
     */
    public function __construct(TemplateRendererInterface $renderer, array $options)
    {
        $this->renderer = $renderer;
        $this->options  = $options;
        $this->mailFrom = $options['sender'];
    }

    /**
     * @param string $template Mail template to render
     * @param array $options
     * @return string The rendered email template
     */
    public function prepareBody(string $template, $options = []): string
    {
        $options = array_merge($options, [
            'layout' => 'layout::email',
        ]);

        return $this->renderer->render($template, $options);
    }

    /**
     * @param string $to email to send to
     * @param string $subject email subject
     * @param string $body email body
     */
    public function send(string $to, string $subject, string $body): void
    {
        $html       = new MimePart($body);
        $html->type = 'text/html';

        $message = new MimeMessage();
        $message->addPart($html);

        $mail = new Mail\Message();
        $mail->setEncoding('UTF-8');
        $mail->setBody($message);
        $mail->setFrom($this->mailFrom);
        $mail->addTo($to);
        $mail->setSubject($subject);

        $this->getTransport()->send($mail);
    }

    public function getTransport(): SmtpTransport
    {
        if (null === $this->transport) {
            $smtpOptions     = new Mail\Transport\SmtpOptions($this->options['smtp_options']);
            $this->transport = new SmtpTransport($smtpOptions);
        }

        return $this->transport;
    }
}
