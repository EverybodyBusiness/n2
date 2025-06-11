# Step 4: 감사 로깅 시스템 구축 (Laravel Auditing)

## 개요

Laravel Auditing 패키지를 사용하여 데이터베이스의 모든 변경 사항을 자동으로 추적하고 기록하는 감사 로깅 시스템을 구축합니다. 이 시스템은 규정 준수, 보안 모니터링, 문제 해결에 필수적입니다.

## 주요 기능

- ✅ 모든 모델 변경 사항 자동 추적 (생성, 수정, 삭제, 복원)
- ✅ 변경 전후 값 비교
- ✅ 변경 수행자 및 IP 주소 기록
- ✅ 시간대별 변경 이력 추적
- ✅ Filament 관리자 패널에서 감사 로그 조회
- ✅ 필터링 및 검색 기능
- ✅ 민감한 정보(비밀번호 등) 자동 제외
- ✅ 상세 변경 내용 비교 뷰

## 설치 과정

### 4.1 패키지 설치

```bash
composer require owen-it/laravel-auditing -W
```

**설치되는 패키지:**
- `owen-it/laravel-auditing` v14.0.0 - 데이터 변경 추적 및 감사 로깅

### 4.2 설정 파일 발행

```bash
# 설정 파일 발행
php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider" --tag="config"

# 마이그레이션 파일 발행
php artisan vendor:publish --provider "OwenIt\Auditing\AuditingServiceProvider" --tag="migrations"
```

**생성되는 파일:**
- `config/audit.php` - 감사 로깅 설정
- `database/migrations/xxxx_xx_xx_create_audits_table.php` - 감사 로그 테이블 마이그레이션

### 4.3 마이그레이션 실행

```bash
php artisan migrate
```

**생성되는 테이블:**
- `audits` - 모든 감사 로그를 저장하는 테이블

## 구현 내용

### 4.4 User 모델에 Auditable 적용

```php
// app/Models/User.php

use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class User extends Authenticatable implements FilamentUser, AuditableContract
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, Auditable;

    /**
     * Attributes to exclude from the Audit.
     *
     * @var array
     */
    protected $auditExclude = [
        'password',
        'remember_token',
    ];
}
```

### 4.5 커스텀 Audit 모델 생성

```php
// app/Models/System/Audit.php

namespace App\Models\System;

use OwenIt\Auditing\Models\Audit as AuditModel;

class Audit extends AuditModel
{
    protected $table = 'audits';

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
        'created_at' => 'datetime',
    ];

    public function getEventNameAttribute(): string
    {
        $eventNames = [
            'created' => '생성',
            'updated' => '수정',
            'deleted' => '삭제',
            'restored' => '복구',
        ];

        return $eventNames[$this->event] ?? $this->event;
    }
}
```

### 4.6 Filament 감사 로그 리소스 생성

```php
// app/Filament/Resources/System/AuditResource.php

namespace App\Filament\Resources\System;

class AuditResource extends Resource
{
    protected static ?string $model = Audit::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = '감사 로그';
    protected static ?string $navigationGroup = '시스템 관리';
    
    // 감사 로그는 읽기 전용
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }
}
```

### 4.7 설정 파일 수정

```php
// config/audit.php

return [
    'enabled' => env('AUDITING_ENABLED', true),
    'implementation' => App\Models\System\Audit::class,
    
    'events' => [
        'created',
        'updated', 
        'deleted',
        'restored',
    ],
    
    'exclude' => [], // 전역 제외 필드
    'timestamps' => false, // timestamps 필드 감사 제외
];
```

## 사용 방법

### 모델에 Auditable 적용

```php
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;

class Product extends Model implements AuditableContract
{
    use Auditable;
    
    // 감사에서 제외할 필드
    protected $auditExclude = [
        'password',
        'api_token',
    ];
    
    // 특정 이벤트만 감사
    protected $auditEvents = [
        'created',
        'updated',
    ];
}
```

### 감사 로그 조회

```php
// 특정 모델의 감사 이력
$user = User::find(1);
$audits = $user->audits;

// 최근 변경 사항
$lastAudit = $user->audits()->latest()->first();

// 특정 사용자가 수행한 모든 변경
$userAudits = Audit::where('user_id', $userId)->get();
```

### 프로그래밍 방식으로 감사 비활성화

```php
// 임시로 감사 비활성화
$user->disableAuditing();
$user->update(['name' => 'New Name']); // 감사되지 않음
$user->enableAuditing();

// 또는 일회성 비활성화
$user->updateQuietly(['name' => 'New Name']);
```

## 관리자 패널에서 감사 로그 확인

1. **감사 로그 목록**
   - URL: `/admin/system/audits`
   - 모든 변경 이력을 시간순으로 표시
   - 모델, 이벤트, 수행자별 필터링 가능

2. **감사 로그 상세**
   - 변경 전후 값 비교
   - 수행자 정보 (사용자, IP 주소, User Agent)
   - 변경 시각 및 URL 정보

3. **주요 기능**
   - 날짜 범위 필터
   - 모델별 필터
   - 이벤트 유형별 필터 (생성/수정/삭제/복원)
   - 수행자 검색

## 보안 고려사항

### 민감 정보 보호

```php
// 모델별 제외 필드 설정
protected $auditExclude = [
    'password',
    'remember_token',
    'api_key',
    'secret_token',
];

// 특정 필드만 감사
protected $auditInclude = [
    'name',
    'email',
    'status',
];
```

### 감사 로그 보호

- 감사 로그는 수정/삭제 불가능하도록 설정
- Super Admin만 감사 로그 조회 가능하도록 권한 설정
- 감사 로그 자체는 감사 대상에서 제외

## 성능 최적화

### 대량 작업 시 감사 비활성화

```php
// 시더나 대량 작업 시
Model::disableAuditing();
Model::insert($largeDataset);
Model::enableAuditing();
```

### 오래된 감사 로그 정리

```php
// 6개월 이상 된 로그 삭제
Audit::where('created_at', '<', now()->subMonths(6))->delete();

// 또는 artisan 명령어로
php artisan audit:clean --days=180
```

## 문제 해결

### 감사가 기록되지 않는 경우

1. **Auditable trait 확인**
   ```php
   use OwenIt\Auditing\Auditable;
   ```

2. **AuditableContract 인터페이스 구현 확인**
   ```php
   implements AuditableContract
   ```

3. **감사 활성화 확인**
   ```php
   // .env
   AUDITING_ENABLED=true
   ```

4. **이벤트 설정 확인**
   ```php
   protected $auditEvents = ['created', 'updated', 'deleted'];
   ```

### 특정 필드가 감사되지 않는 경우

```php
// auditExclude에 포함되어 있는지 확인
protected $auditExclude = ['field_name'];

// 또는 auditInclude를 사용하는 경우 포함되어 있는지 확인
protected $auditInclude = ['field_name'];
```

## 추가 기능

### 사용자 정의 감사 메시지

```php
public function transformAudit(array $data): array
{
    if ($this->isEventAuditable('created')) {
        $data['custom_message'] = '새로운 사용자가 등록되었습니다.';
    }
    
    return $data;
}
```

### 감사 이벤트 리스너

```php
// EventServiceProvider
protected $listen = [
    'OwenIt\Auditing\Events\Audited' => [
        'App\Listeners\SendAuditNotification',
    ],
];
```

### 커스텀 Resolver

```php
// 사용자 정의 IP 주소 resolver
class CustomIpAddressResolver implements \OwenIt\Auditing\Contracts\IpAddressResolver
{
    public static function resolve(): string
    {
        // Cloudflare 등 프록시 뒤의 실제 IP 가져오기
        return request()->header('CF-Connecting-IP') 
            ?? request()->ip();
    }
}
```

## 다음 단계

감사 로깅 시스템이 구축되었으므로, 다음 단계로 진행할 수 있습니다:

- **Step 5**: 실시간 통신 (Laravel Echo + Pusher/Soketi)
- **Step 6**: 알림 시스템 (다채널 알림)
- **Step 7**: 작업 큐 시스템
- **Step 8**: 미디어 관리 시스템
- **Step 9**: 백업 시스템

---

*문서 작성일: 2024년 12월* 