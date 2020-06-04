<?php


namespace App\Service;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Messenger\MessageHandler;
use Symfony\Component\Mailer\Messenger\SendEmailMessage;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $logger;

    private $params;

    private $mailer;

    public function __construct(LoggerInterface $logger, ParameterBagInterface $params)
    {
        $this->logger = $logger;

        $this->params = $params;

        $transport = new GmailSmtpTransport($this->params->get('mail.login'), $this->params->get('mail.password'));
        $handler = new MessageHandler($transport);
        $bus = new MessageBus([
            new HandleMessageMiddleware(new HandlersLocator([
                SendEmailMessage::class => [$handler],
            ])),
        ]);
        $this->mailer = new Mailer($transport, $bus);
    }

    public function sendCancelFlightMail($emails, $flight_code){
        foreach ($emails as $email){
            $mail = (new Email())
                ->from($this->params->get('mail.from'))
                ->to($email)
                ->subject($this->params->get('mail.message.flight_cancel.header'))
                ->text($this->params->get('mail.message.flight_cancel.text') . $flight_code);
            $this->mailer->send($mail);
        }
    }
}