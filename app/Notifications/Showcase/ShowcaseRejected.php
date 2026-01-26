<?php

namespace App\Notifications\Showcase;

use App\Models\Showcase\Showcase;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ShowcaseRejected extends BaseNotification
{
    public function __construct(public Showcase $showcase) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Showcase Needs Revision')
            ->greeting('Hello!')
            ->line("Your showcase \"{$this->showcase->title}\" needs revision.")
            ->line("Reason: {$this->showcase->rejection_reason}")
            ->action('Edit Showcase', route('showcase.manage.edit', $this->showcase))
            ->line('Please make the necessary changes and resubmit.');
    }

    public function toArray($notifiable): array
    {
        return [
            'showcase_id' => $this->showcase->id,
            'showcase_title' => $this->showcase->title,
            'rejection_reason' => $this->showcase->rejection_reason,
            'message' => 'Your showcase needs revision',
        ];
    }
}
