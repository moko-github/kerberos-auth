<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MokoGithub\KerberosAuth\Models\AccessRequest;

class AccessRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AccessRequest $accessRequest,
        public string $adminMessage
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name');

        return (new MailMessage)
            ->subject(__('kerberos-auth::kerberos.notif.rejected.subject', ['app' => $app]))
            ->error()
            ->greeting(__('kerberos-auth::kerberos.notif.rejected.greeting'))
            ->line(__('kerberos-auth::kerberos.notif.rejected.line_reason', ['reason' => $this->adminMessage]))
            ->line(__('kerberos-auth::kerberos.notif.rejected.line_retry'))
            ->action(__('kerberos-auth::kerberos.notif.rejected.action'), route('access-request.create'))
            ->salutation(__('kerberos-auth::kerberos.notif.unknown_attempt.salutation', ['app' => $app]));
    }
}
