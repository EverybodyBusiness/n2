<?php

namespace App\Notifications\System;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $errorData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $errorData)
    {
        $this->errorData = $errorData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('[긴급] 시스템 Critical Error 발생')
            ->greeting('안녕하세요, ' . $notifiable->name . '님')
            ->line('시스템에서 Critical Error가 발생했습니다.')
            ->line('**에러 정보:**')
            ->line('- **발생 시각**: ' . $this->errorData['occurred_at'])
            ->line('- **에러 타입**: ' . $this->errorData['exception_class'])
            ->line('- **에러 메시지**: ' . $this->errorData['message'])
            ->line('- **파일**: ' . $this->errorData['file'] . ':' . $this->errorData['line'])
            ->line('- **URL**: ' . $this->errorData['url'])
            ->line('- **사용자**: ' . ($this->errorData['user_name'] ?? 'Guest'))
            ->action('Telescope에서 확인', url('/telescope/exceptions/' . $this->errorData['telescope_uuid']))
            ->line('즉시 확인이 필요합니다.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'critical_error',
            'level' => 'critical',
            'title' => 'Critical Error 발생',
            'message' => $this->errorData['message'],
            'exception_class' => $this->errorData['exception_class'],
            'file' => $this->errorData['file'],
            'line' => $this->errorData['line'],
            'url' => $this->errorData['url'],
            'user_id' => $this->errorData['user_id'] ?? null,
            'telescope_uuid' => $this->errorData['telescope_uuid'],
            'occurred_at' => $this->errorData['occurred_at'],
        ];
    }
}
