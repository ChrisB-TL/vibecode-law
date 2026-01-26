<?php

namespace App\Notifications\ShowcaseDraft;

use App\Models\Showcase\Showcase;
use App\Models\Showcase\ShowcaseDraft;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ShowcaseDraftRejected extends BaseNotification
{
    public function __construct(public ShowcaseDraft $draft, public string $reason) {}

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        /** @var Showcase $showcase */
        $showcase = $this->draft->showcase;

        return (new MailMessage)
            ->subject('Your Showcase Changes Were Not Approved')
            ->greeting('Hello!')
            ->line("Your changes to \"{$showcase->title}\" were not approved.")
            ->line('Reason: '.$this->reason)
            ->action('Edit Your Draft', route('showcase.draft.edit', $this->draft))
            ->line('You can make the necessary changes and resubmit for approval.');
    }

    /**
     * @param  mixed  $notifiable
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        /** @var Showcase $showcase */
        $showcase = $this->draft->showcase;

        return [
            'draft_id' => $this->draft->id,
            'showcase_id' => $this->draft->showcase_id,
            'showcase_title' => $showcase->title,
            'rejection_reason' => $this->reason,
            'message' => 'Your showcase changes were not approved',
        ];
    }
}
