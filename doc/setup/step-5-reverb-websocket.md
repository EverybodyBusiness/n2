# Step 5: Laravel Reverb WebSocket 설정 가이드

## 개요
Laravel Reverb는 Laravel의 공식 WebSocket 서버로, Air-gapped 환경에서 완전한 자체 호스팅이 가능합니다.

## 1. 설치 완료 사항
```bash
composer require laravel/reverb
```

## 2. 환경 변수 설정 (.env)

`.env` 파일에 다음 설정을 추가하세요:

```env
# Broadcasting
BROADCAST_CONNECTION=reverb

# Laravel Reverb Configuration
REVERB_APP_ID=my-app-id
REVERB_APP_KEY=my-app-key
REVERB_APP_SECRET=my-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Reverb Client Configuration (for Laravel Echo)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## 3. 프론트엔드 설정

### 3.1 Laravel Echo 설치
```bash
npm install --save-dev laravel-echo pusher-js
```

### 3.2 Echo 설정 (resources/js/bootstrap.js)
```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

## 4. 이벤트 및 채널 설정

### 4.1 이벤트 생성
```bash
php artisan make:event UserMessageSent
```

### 4.2 이벤트 구현 예시
```php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $user;

    public function __construct($message, $user)
    {
        $this->message = $message;
        $this->user = $user;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->user->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
```

### 4.3 채널 권한 설정 (routes/channels.php)
```php
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
```

## 5. Reverb 서버 실행

### 5.1 개발 환경
```bash
php artisan reverb:start
```

### 5.2 프로덕션 환경 (Supervisor 설정)
```ini
[program:reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan reverb:start
autostart=true
autorestart=true
user=forge
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/reverb.log
```

## 6. 클라이언트 사용 예시

### 6.1 Vue/React 컴포넌트
```javascript
// 채널 구독
window.Echo.private(`chat.${userId}`)
    .listen('.message.sent', (e) => {
        console.log('New message:', e.message);
    });

// 이벤트 발송 (서버 측)
broadcast(new UserMessageSent($message, $user));
```

### 6.2 Livewire 컴포넌트
```php
namespace App\Livewire\Chat;

use Livewire\Component;
use Livewire\Attributes\On;

class ChatRoom extends Component
{
    public $messages = [];

    #[On('echo-private:chat.{userId},.message.sent')]
    public function messageReceived($event)
    {
        $this->messages[] = $event['message'];
    }

    public function render()
    {
        return view('livewire.chat.chat-room');
    }
}
```

## 7. 보안 고려사항

### 7.1 프로덕션 환경 설정
- `REVERB_APP_KEY`와 `REVERB_APP_SECRET`을 안전한 랜덤 값으로 변경
- HTTPS 사용 시 `REVERB_SCHEME=https`로 설정
- 방화벽에서 Reverb 포트(기본 8080) 접근 제한

### 7.2 Air-gapped 환경 장점
- 외부 서비스 의존성 없음
- 완전한 데이터 통제
- 네트워크 격리 환경에서도 작동

## 8. 모니터링

### 8.1 Telescope 연동
Reverb는 자동으로 Laravel Telescope와 연동되어 WebSocket 연결을 모니터링합니다.

### 8.2 로그 확인
```bash
tail -f storage/logs/laravel.log
```

## 9. 테스트

### 9.1 연결 테스트
브라우저 콘솔에서:
```javascript
window.Echo.connector.pusher.connection.state
// "connected" 가 출력되면 정상
```

### 9.2 이벤트 발송 테스트
```bash
php artisan tinker
>>> broadcast(new \App\Events\UserMessageSent('Hello World', User::first()));
```

## 다음 단계
- Step 6: 알림 시스템 구축
- Step 7: Laravel Horizon 작업 큐 설정 