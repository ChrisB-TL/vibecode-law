<?php

namespace App\Notifications\Showcase;

use App\Models\Showcase\Showcase;
use App\Notifications\BaseNotification;
use Illuminate\Notifications\Messages\MailMessage;

class ShowcaseSubmittedForApproval extends BaseNotification
{
    public function __construct(public Showcase $showcase) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Showcase Submitted for Approval')
            ->greeting('Hello!')
            ->line("A new showcase \"{$this->showcase->title}\" has been submitted by {$this->showcase->user->first_name} {$this->showcase->user->last_name} and is awaiting approval.")
            ->action('Review Showcase', route('staff.showcase-moderation.index'))
            ->line('Please review and approve or reject this submission.');
    }

    public function toArray($notifiable): array
    {
        return [
            'showcase_id' => $this->showcase->id,
            'showcase_title' => $this->showcase->title,
            'submitted_by' => $this->showcase->user->first_name.' '.$this->showcase->user->last_name,
            'message' => 'A new showcase has been submitted for approval',
        ];
    }
}
