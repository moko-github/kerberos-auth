<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UnknownKerberosAttemptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $kerberos,
        public string $ipAddress,
        public string $userAgent,
        public CarbonInterface $attemptedAt
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name');

        return (new MailMessage)
            ->subject(__('kerberos-auth::kerberos.notif.unknown_attempt.subject', ['app' => $app]))
            ->error()
            ->greeting(__('kerberos-auth::kerberos.notif.unknown_attempt.greeting'))
            ->line(__('kerberos-auth::kerberos.notif.unknown_attempt.line_detected'))
            ->line("**Identifiant :** `{$this->kerberos}`")
            ->line("**Adresse IP :** {$this->ipAddress}")
            ->line("**Navigateur :** {$this->userAgent}")
            ->line("**Date/Heure :** {$this->attemptedAt->format('d/m/Y H:i:s')}")
            ->line(__('kerberos-auth::kerberos.notif.unknown_attempt.line_legit'))
            ->salutation(__('kerberos-auth::kerberos.notif.unknown_attempt.salutation', ['app' => $app]));
    }
}
