<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MokoGithub\KerberosAuth\Models\AccessRequest;
use MokoGithub\KerberosAuth\Support\Kerberos;

class AccessRequestAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AccessRequest $accessRequest,
        public ?string $adminMessage = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $roleName = data_get($this->accessRequest, 'processedBy.role.name', 'User');

        $app = config('app.name');

        $mail = (new MailMessage)
            ->subject(__('kerberos-auth::kerberos.notif.accepted.subject', ['app' => $app]))
            ->success()
            ->greeting(__('kerberos-auth::kerberos.notif.accepted.greeting'))
            ->line(__('kerberos-auth::kerberos.notif.accepted.line_role', ['role' => $roleName]))
            ->action(__('kerberos-auth::kerberos.notif.accepted.action'), route(Kerberos::loginRoute()));

        if ($this->adminMessage) {
            $mail->line(__('kerberos-auth::kerberos.notif.accepted.line_message', ['message' => $this->adminMessage]));
        }

        return $mail->salutation(__('kerberos-auth::kerberos.notif.unknown_attempt.salutation', ['app' => $app]));
    }
}
