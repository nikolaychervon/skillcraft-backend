<?php

namespace App\Infrastructure\Notifications\Base;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

abstract class EmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    protected function buildMailMessage(array $params): MailMessage
    {
        $message = new MailMessage();
        $notificationClass = static::class;
        $baseContent = __("email.$notificationClass", $params);

        foreach ($baseContent as $key => $content) {
            switch ($key) {
                case 'subject':
                    $message->subject($content);
                    break;
                case 'greeting':
                    $message->greeting($content);
                    break;
                case str_starts_with($key, 'lines'):
                    foreach ($content as $line) {
                        $message->line($line);
                    }
                    break;
                case 'action':
                    $message->action($content['text'], $content['url']);
                    break;
            }
        }

        return $message;
    }
}
