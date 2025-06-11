# Step 1: 인증 시스템 구축 (Laravel Fortify + Sanctum)

## 개요

Laravel Fortify와 Sanctum을 사용하여 견고한 인증 시스템을 구축합니다. Fortify는 백엔드 인증 로직을, Sanctum은 API 토큰 관리를 담당합니다.

## 주요 기능

- ✅ 사용자 로그인/로그아웃
- ✅ 회원가입
- ✅ 비밀번호 재설정
- ✅ 이메일 인증
- ✅ 2단계 인증 (2FA)
- ✅ API 토큰 인증
- ✅ 프로필 정보 업데이트
- ✅ 비밀번호 변경

## 설치 과정

### 1.1 패키지 설치

```bash
composer require laravel/fortify laravel/sanctum
```

**설치되는 패키지:**
- `laravel/fortify` v1.26.0
- `laravel/sanctum` v4.1.1
- `pragmarx/google2fa` (2FA 지원)
- `bacon/bacon-qr-code` (QR 코드 생성)

### 1.2 설정 파일 발행

```bash
# Fortify 설정 파일 발행
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"

# Sanctum 설정 파일 발행
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

**생성되는 파일:**
- `config/fortify.php` - Fortify 설정
- `config/sanctum.php` - Sanctum 설정
- `app/Providers/FortifyServiceProvider.php` - Fortify 서비스 프로바이더
- `app/Actions/Fortify/` - Fortify 액션 클래스들
- `database/migrations/` - 인증 관련 마이그레이션

### 1.3 FortifyServiceProvider 등록

**파일:** `bootstrap/providers.php`

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    App\Providers\FortifyServiceProvider::class, // 추가
];
```

### 1.4 Fortify 기능 활성화

**파일:** `config/fortify.php`

```php
'features' => [
    Features::registration(),         // 회원가입
    Features::resetPasswords(),       // 비밀번호 재설정
    Features::emailVerification(),    // 이메일 인증
    Features::updateProfileInformation(), // 프로필 업데이트
    Features::updatePasswords(),      // 비밀번호 변경
    Features::twoFactorAuthentication([   // 2단계 인증
        'confirm' => true,
        'confirmPassword' => true,
    ]),
],
```

### 1.5 User 모델 업데이트

**파일:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens; // HasApiTokens 추가

    // ... 기존 코드
}
```

### 1.6 마이그레이션 실행

```bash
php artisan migrate
```

**생성되는 테이블:**
- `users` (기존) - two_factor_secret, two_factor_recovery_codes 컬럼 추가
- `personal_access_tokens` - Sanctum API 토큰

## 액션 클래스

Fortify는 다음 액션 클래스들을 제공합니다:

### CreateNewUser.php
사용자 등록 시 실행되는 로직
```php
public function create(array $input): User
{
    Validator::make($input, [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => $this->passwordRules(),
    ])->validate();

    return User::create([
        'name' => $input['name'],
        'email' => $input['email'],
        'password' => Hash::make($input['password']),
    ]);
}
```

### ResetUserPassword.php
비밀번호 재설정 로직

### UpdateUserPassword.php
비밀번호 변경 로직

### UpdateUserProfileInformation.php
프로필 정보 업데이트 로직

## 설정 옵션

### Rate Limiting

**파일:** `app/Providers/FortifyServiceProvider.php`

```php
RateLimiter::for('login', function (Request $request) {
    $throttleKey = Str::transliterate(
        Str::lower($request->input(Fortify::username())).'|'.$request->ip()
    );
    return Limit::perMinute(5)->by($throttleKey);
});

RateLimiter::for('two-factor', function (Request $request) {
    return Limit::perMinute(5)->by($request->session()->get('login.id'));
});
```

- 로그인 시도: 분당 5회 제한
- 2FA 시도: 분당 5회 제한

### 인증 필드 커스터마이징

기본적으로 `email`을 사용하지만, 변경 가능:

```php
Fortify::authenticateUsing(function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if ($user && Hash::check($request->password, $user->password)) {
        return $user;
    }
});
```

## API 인증 (Sanctum)

### API 토큰 발급

```php
$token = $user->createToken('token-name')->plainTextToken;
```

### API 라우트 보호

```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

## 보안 고려사항

1. **비밀번호 정책**
   - 최소 8자 이상
   - PasswordValidationRules trait 사용

2. **세션 보안**
   - HTTPS 사용 권장
   - `SESSION_SECURE_COOKIE=true` (프로덕션)

3. **CSRF 보호**
   - 자동으로 활성화됨
   - API 요청 시 Sanctum이 처리

## 테스트

### 인증 테스트 예제

```php
public function test_users_can_authenticate()
{
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
}
```

## 문제 해결

### 일반적인 문제

1. **"Class 'App\Providers\FortifyServiceProvider' not found"**
   - 해결: `composer dump-autoload` 실행

2. **마이그레이션 실패**
   - 해결: 데이터베이스 연결 확인, `.env` 파일 검토

3. **로그인 후 리다이렉트 문제**
   - 해결: `RouteServiceProvider::HOME` 상수 확인

## 다음 단계

인증 시스템이 구축되었으므로, [Step 2: Filament 관리자 패널](./step-2-filament-admin.md)을 진행하여 관리자 인터페이스를 구축할 수 있습니다.

---

[← 목차로 돌아가기](./README.md) | [다음: Step 2 →](./step-2-filament-admin.md) 