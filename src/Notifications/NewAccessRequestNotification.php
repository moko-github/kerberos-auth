<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use MokoGithub\KerberosAuth\Models\AccessRequest;

class NewAccessRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AccessRequest $accessRequest)
    {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $app = config('app.name');

        return (new MailMessage)
            ->subject(__('kerberos-auth::kerberos.notif.new_request.subject', ['app' => $app]))
            ->greeting(__('kerberos-auth::kerberos.notif.new_request.greeting'))
            ->line(__('kerberos-auth::kerberos.notif.new_request.line_details'))
            ->line("**Kerberos :** `{$this->accessRequest->kerberos}`")
            ->line("**Justification :** {$this->accessRequest->justification}")
            ->salutation(__('kerberos-auth::kerberos.notif.unknown_attempt.salutation', ['app' => $app]));
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'access_request_id' => $this->accessRequest->id,
            'kerberos' => $this->accessRequest->kerberos,
            'user_name' => data_get($this->accessRequest, 'user.name'),
            'justification' => $this->accessRequest->justification,
            'created_at' => $this->accessRequest->created_at->toISOString(),
        ];
    }
}
