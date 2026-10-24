<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendPasswordResetEmailMessage;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;

#[AsMessageHandler]
class SendPasswordResetEmailMessageHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }

    public function __invoke(SendPasswordResetEmailMessage $message): void
    {
        $this->mailer->send(
            (new TemplatedEmail())
                ->locale('ru')
                ->from(new Address($this->fromAddress, $this->fromName))
                ->to($message->email)
                ->subject($message->subject)
                ->htmlTemplate('mail/admin/reset_password/email.html.twig')
                ->textTemplate('mail/admin/reset_password/email.txt.twig')
                ->context([
                    'resetUrl' => $message->resetUrl,
                    'expirationMessage' => $message->expirationMessage,
                ])
        );
    }
}
