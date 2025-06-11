# Step 2: Filament 관리자 패널 설치

## 개요

Filament은 Laravel을 위한 풀스택 관리자 패널 프레임워크입니다. Livewire 기반으로 구축되어 빠르고 반응성이 뛰어난 관리자 인터페이스를 제공합니다.

## 주요 기능

- ✅ 현대적이고 반응형 UI
- ✅ 리소스 관리 (CRUD)
- ✅ 대시보드 및 위젯
- ✅ 다국어 지원
- ✅ 다크 모드 지원
- ✅ 커스텀 페이지
- ✅ 액션 및 대량 작업
- ✅ 필터 및 검색

## 설치 과정

### 2.1 Filament 패키지 설치

```bash
composer require filament/filament:"^3.3" -W
```

**설치되는 주요 패키지:**
- `filament/filament` v3.3.21
- `filament/forms` - 폼 빌더
- `filament/tables` - 테이블 빌더
- `filament/actions` - 액션 컴포넌트
- `filament/notifications` - 알림 시스템
- `filament/infolists` - 정보 표시
- `filament/widgets` - 위젯 시스템
- `blade-ui-kit/blade-heroicons` - 아이콘
- `livewire/livewire` v3.x

### 2.2 Filament 초기 설정

```bash
php artisan filament:install --panels
```

**설치 중 프롬프트:**
```
What is the ID? [admin]
> admin (엔터)
```

**생성되는 파일:**
- `app/Providers/Filament/AdminPanelProvider.php` - 패널 설정
- `public/js/filament/` - JavaScript 에셋
- `public/css/filament/` - CSS 에셋

### 2.3 AdminPanelProvider 구성

**파일:** `app/Providers/Filament/AdminPanelProvider.php`

```php
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
// ... 기타 imports

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
```

### 2.4 Provider 등록

AdminPanelProvider는 자동으로 `bootstrap/providers.php`에 등록됩니다:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class, // 자동 추가됨
    App\Providers\FortifyServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
];
```

### 2.5 User 모델 업데이트

**파일:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
// ... 기타 imports

class User extends Authenticatable implements FilamentUser
{
    // ... 기존 traits

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // 현재는 모든 사용자가 접근 가능
        // Step 3에서 권한 관리로 변경됨
        return true;
    }
}
```

### 2.6 관리자 사용자 생성

관리자 사용자를 생성하기 위한 커맨드 작성:

```bash
php artisan make:command CreateAdminUser
```

**파일:** `app/Console/Commands/CreateAdminUser.php`

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin-user 
                            {--email=admin@example.com} 
                            {--password=password} 
                            {--name=Admin}';

    protected $description = 'Create an admin user for the application';

    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');

        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists!');
            return;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created successfully!");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");
        $this->info("You can now login at: " . url('/admin'));
    }
}
```

### 2.7 관리자 계정 생성

```bash
php artisan app:create-admin-user
```

**결과:**
```
Admin user created successfully!
Email: admin@example.com
Password: password
You can now login at: http://localhost/admin
```

## 패널 설정 옵션

### 기본 설정

```php
$panel
    ->id('admin')                    // 패널 식별자
    ->path('admin')                  // URL 경로
    ->domain('admin.example.com')    // 서브도메인 (선택사항)
    ->login()                        // 로그인 페이지 활성화
    ->profile()                      // 프로필 페이지 활성화
    ->registration()                 // 회원가입 활성화
    ->passwordReset()                // 비밀번호 재설정 활성화
    ->emailVerification()            // 이메일 인증 활성화
```

### 색상 테마

```php
->colors([
    'primary' => Color::Amber,
    'danger' => Color::Rose,
    'gray' => Color::Gray,
    'info' => Color::Blue,
    'success' => Color::Emerald,
    'warning' => Color::Orange,
])
```

### 네비게이션

```php
->navigation(function (NavigationBuilder $builder): NavigationBuilder {
    return $builder->items([
        NavigationItem::make('Analytics')
            ->url('https://analytics.example.com')
            ->icon('heroicon-o-chart-pie')
            ->openUrlInNewTab(),
    ]);
})
```

### 브랜딩

```php
->brandName('My Admin Panel')
->brandLogo(asset('images/logo.svg'))
->favicon(asset('images/favicon.ico'))
```

## 디렉토리 구조

Filament은 다음과 같은 디렉토리 구조를 사용합니다:

```
app/
├── Filament/
│   ├── Resources/         # 리소스 (CRUD)
│   │   └── UserResource/
│   │       ├── Pages/
│   │       └── RelationManagers/
│   ├── Pages/            # 커스텀 페이지
│   └── Widgets/          # 대시보드 위젯
└── Providers/
    └── Filament/
        └── AdminPanelProvider.php
```

## 접근 제어

### canAccessPanel() 메서드

User 모델에서 패널 접근을 제어:

```php
public function canAccessPanel(Panel $panel): bool
{
    // 예시: 특정 이메일 도메인만 허용
    return str_ends_with($this->email, '@company.com');
    
    // 예시: 특정 역할 확인 (Step 3에서 구현)
    // return $this->hasRole(['super_admin', 'admin']);
}
```

## 미들웨어

### 기본 미들웨어

- `Authenticate` - 인증 확인
- `AuthenticateSession` - 세션 인증
- `VerifyCsrfToken` - CSRF 보호
- `SubstituteBindings` - 라우트 모델 바인딩

### 커스텀 미들웨어 추가

```php
->middleware([
    // ... 기본 미들웨어
    CustomMiddleware::class,
])
```

## 에셋 관리

### JavaScript/CSS 업데이트

```bash
php artisan filament:upgrade
```

### 캐시 클리어

```bash
php artisan filament:cache-components
php artisan view:clear
php artisan route:clear
```

## 문제 해결

### 일반적인 문제

1. **404 에러 - 관리자 패널 접근 불가**
   - 해결: `php artisan route:clear` 실행
   - Provider가 등록되었는지 확인

2. **로그인 후 리다이렉트 루프**
   - 해결: `canAccessPanel()` 메서드 확인
   - 세션 설정 확인

3. **에셋 로딩 실패**
   - 해결: `php artisan filament:upgrade` 실행
   - `public/` 디렉토리 권한 확인

4. **"Class AdminPanelProvider not found"**
   - 해결: `composer dump-autoload` 실행

## 다음 단계

Filament 관리자 패널이 설치되었으므로, [Step 3: 권한 관리 시스템](./step-3-permission-management.md)을 진행하여 역할 기반 접근 제어를 구현할 수 있습니다.

---

[← 이전: Step 1](./step-1-authentication.md) | [목차](./README.md) | [다음: Step 3 →](./step-3-permission-management.md) 