<?php

namespace App\Notifications\Showcase;

use App\Models\Showcase\Showcase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShowcaseApproved extends Notification implements ShouldQueue
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
        $linkedInShareUrl = 'https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($showcaseUrl);

        return (new MailMessage)
            ->subject('Your Showcase Has Been Approved')
            ->greeting('Great news!')
            ->line("Your showcase \"{$this->showcase->title}\" has been approved.")
            ->action('View Showcase', $showcaseUrl)
            ->line('Thank you for sharing your work!')
            ->line("Proud of your project? [Share it on LinkedIn]({$linkedInShareUrl}) to let your network know!");
    }

    public function toArray($notifiable): array
    {
        return [
            'showcase_id' => $this->showcase->id,
            'showcase_title' => $this->showcase->title,
            'message' => 'Your showcase has been approved',
        ];
    }
}
