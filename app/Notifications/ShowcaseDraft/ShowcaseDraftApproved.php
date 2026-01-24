<?php

namespace App\Notifications\ShowcaseDraft;

use App\Models\Showcase\Showcase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShowcaseDraftApproved extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Showcase $showcase) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $showcaseUrl = route('showcase.show', $this->showcase);

        return (new MailMessage)
            ->subject('Your Showcase Changes Have Been Approved')
            ->greeting('Great news!')
            ->line("Your changes to \"{$this->showcase->title}\" have been approved and are now live.")
            ->action('View Showcase', $showcaseUrl)
            ->line('Thank you for keeping your showcase up to date!');
    }

    public function toArray($notifiable): array
    {
        return [
            'showcase_id' => $this->showcase->id,
            'showcase_title' => $this->showcase->title,
            'message' => 'Your showcase changes have been approved',
        ];
    }
}
