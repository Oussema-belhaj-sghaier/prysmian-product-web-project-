<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Alert;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Psr\Log\LoggerInterface;

class PlantMailerService
{
    public function __construct(
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    )
    {
    }

    public function sendAlert(Alert $alert, array $recipients): string
    {
        $recipients = array_values(array_filter($recipients, static fn (mixed $email): bool => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL)));
        if ($recipients === []) {
            return 'no_recipients';
        }

        if (str_starts_with($_ENV['MAILER_DSN'] ?? 'null://null', 'null://')) {
            return 'local_mode';
        }

        $cable = $alert->getCable();
        $email = (new Email())
            ->from($_ENV['MAILER_FROM'] ?? 'atelier@prysmian.tn')
            ->to(...$recipients)
            ->subject('[Prysmian] Alerte atelier - ' . $cable->getReferenceCode())
            ->html(sprintf(
                '<h2>Alerte de fabrication</h2><p><strong>%s</strong> · %s</p><p>%s</p><p>Usine : %s<br>Gravité : %s</p>',
                htmlspecialchars($cable->getReferenceCode(), ENT_QUOTES),
                htmlspecialchars($alert->getAlertType()->value, ENT_QUOTES),
                htmlspecialchars($alert->getMessage(), ENT_QUOTES),
                htmlspecialchars($cable->getFactory() ?? 'Non renseignée', ENT_QUOTES),
                htmlspecialchars($alert->getSeverity()->value, ENT_QUOTES),
            ));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Échec d’envoi de l’alerte email', ['error' => $exception->getMessage()]);
            return 'transport_error';
        }

        return 'sent';
    }

    public function sendWelcome(User $user): string
    {
        if (str_starts_with($_ENV['MAILER_DSN'] ?? 'null://null', 'null://')) {
            return 'local_mode';
        }

        $email = (new Email())
            ->from($_ENV['MAILER_FROM'] ?? 'atelier@prysmian.tn')
            ->to($user->getEmail())
            ->subject('Bienvenue sur Prysmian Manufacturing Control')
            ->html(sprintf('<h2>Bienvenue %s</h2><p>Votre compte Prysmian Manufacturing Control est prêt.</p><p>Rôle : <strong>%s</strong></p><p>Vous pouvez maintenant suivre la fabrication, le stock et la qualité.</p>', htmlspecialchars($user->getFullName(), ENT_QUOTES), htmlspecialchars($user->getRole()->value, ENT_QUOTES)));

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Échec de l’email de bienvenue', ['error' => $exception->getMessage()]);
            return 'transport_error';
        }

        return 'sent';
    }

    public function sendContactMessage(User $recipient, string $subject, string $message): string
    {
        if (str_starts_with($_ENV['MAILER_DSN'] ?? 'null://null', 'null://')) {
            return 'local_mode';
        }

        $email = (new Email())
            ->from($_ENV['MAILER_FROM'] ?? 'atelier@prysmian.tn')
            ->to($recipient->getEmail())
            ->subject($subject)
            ->text($message);

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error('Échec de l’email de contact', [
                'recipient' => $recipient->getEmail(),
                'error' => $exception->getMessage(),
            ]);
            return 'transport_error';
        }

        return 'sent';
    }
}