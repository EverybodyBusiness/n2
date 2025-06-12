# Step 7: Laravel Horizon 작업 큐 시스템

## 개요
Laravel Horizon은 Redis 기반의 큐 시스템을 위한 대시보드와 설정 시스템을 제공합니다.

## 1. 설치 완료 사항
```bash
composer require laravel/horizon
php artisan horizon:install
```

## 2. 환경 설정 (.env)

```env
# Queue Configuration
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis
```

## 3. 큐 우선순위 구성

### 3.1 정의된 큐
- **high**: 긴급/중요 작업
- **default**: 일반 작업
- **low**: 우선순위 낮은 작업
- **notifications**: 알림 전용
- **reports**: 리포트 생성 전용

### 3.2 Supervisor 구성
```php
// config/horizon.php
'defaults' => [
    'supervisor-1' => [
        'queue' => ['high', 'default', 'low'],
        'balance' => 'auto',
        'maxProcesses' => 3,
    ],
    'supervisor-notifications' => [
        'queue' => ['notifications'],
        'maxProcesses' => 2,
    ],
    'supervisor-reports' => [
        'queue' => ['reports'],
        'maxProcesses' => 1,
        'timeout' => 300,
    ],
],
```

## 4. Job 클래스 사용법

### 4.1 기본 Job 생성
```bash
php artisan make:job ProcessDataJob
```

### 4.2 Job 클래스 예시
```php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        // 작업 처리 로직
    }

    public function failed(\Throwable $exception)
    {
        // 실패 시 처리
    }
}
```

### 4.3 Job 디스패치
```php
// 기본 큐로 전송
dispatch(new ProcessDataJob($data));

// 특정 큐 지정
dispatch(new ProcessDataJob($data))->onQueue('high');

// 지연 실행 (60초 후)
dispatch(new ProcessDataJob($data))->delay(60);

// 조건부 실행
dispatch(new ProcessDataJob($data))
    ->onQueue('low')
    ->delay(now()->addMinutes(5));
```

## 5. Horizon 실행

### 5.1 개발 환경
```bash
php artisan horizon
```

### 5.2 프로덕션 환경 (Supervisor 설정)
```ini
[program:horizon]
process_name=%(program_name)s
command=php /path/to/artisan horizon
autostart=true
autorestart=true
user=forge
redirect_stderr=true
stdout_logfile=/path/to/horizon.log
stopwaitsecs=3600
```

## 6. Horizon 대시보드

### 6.1 접근 URL
- 기본: `http://your-app.com/horizon`
- Filament 통합: Admin Panel > 시스템 > Horizon 대시보드

### 6.2 주요 기능
- **대시보드**: 전체 큐 상태 모니터링
- **Pending Jobs**: 대기 중인 작업 목록
- **Completed Jobs**: 완료된 작업 이력
- **Failed Jobs**: 실패한 작업 관리
- **Metrics**: 처리량, 런타임 통계

## 7. 실패한 작업 처리

### 7.1 실패한 작업 확인
```bash
php artisan queue:failed
```

### 7.2 재시도
```bash
# 특정 작업 재시도
php artisan queue:retry 5

# 모든 실패 작업 재시도
php artisan queue:retry all
```

### 7.3 실패 작업 삭제
```bash
# 특정 작업 삭제
php artisan queue:forget 5

# 모든 실패 작업 삭제
php artisan queue:flush
```

## 8. 모니터링 및 알림

### 8.1 긴 대기 시간 감지
```php
// config/horizon.php
'waits' => [
    'redis:default' => 60, // 60초 이상 대기 시 알림
],
```

### 8.2 메트릭 스냅샷
```bash
# 수동 실행
php artisan horizon:snapshot

# 스케줄러 자동 실행 (5분마다)
$schedule->command('horizon:snapshot')->everyFiveMinutes();
```

## 9. 스케일링

### 9.1 자동 스케일링
```php
'defaults' => [
    'supervisor-1' => [
        'balance' => 'auto',
        'autoScalingStrategy' => 'time',
        'maxProcesses' => 10,
        'balanceMaxShift' => 1,
        'balanceCooldown' => 3,
    ],
],
```

### 9.2 수동 스케일링
```bash
# 일시 중지
php artisan horizon:pause

# 재개
php artisan horizon:continue

# 종료
php artisan horizon:terminate
```

## 10. 베스트 프랙티스

### 10.1 Job 설계
- 작은 단위로 분할
- 멱등성 보장
- 적절한 타임아웃 설정
- 실패 처리 로직 구현

### 10.2 큐 선택
- 중요도에 따른 큐 분리
- 처리 시간에 따른 분리
- 리소스 사용량 고려

### 10.3 모니터링
- 정기적인 메트릭 확인
- 실패율 모니터링
- 처리 시간 추적

## 다음 단계
- Step 8: 미디어 관리 시스템 구축 