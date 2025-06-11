# Step 4.5: 에러 로깅 시스템 구축 (Laravel Telescope)

이 문서는 Laravel Telescope를 사용하여 엔터프라이즈급 에러 로깅 시스템을 구축하는 과정을 설명합니다.

## 📋 목차

1. [Laravel Telescope란?](#1-laravel-telescope란)
2. [Telescope 설치](#2-telescope-설치)
3. [기본 설정](#3-기본-설정)
4. [보안 설정](#4-보안-설정)
5. [엔터프라이즈 기능 추가](#5-엔터프라이즈-기능-추가)
6. [Filament 통합](#6-filament-통합)
7. [운영 가이드](#7-운영-가이드)

## 1. Laravel Telescope란?

Laravel Telescope는 Laravel 애플리케이션의 디버깅 및 모니터링을 위한 도구입니다.

### 주요 기능
- 🚨 **Exception 추적**: 모든 예외 상황 기록
- 📊 **Request 모니터링**: HTTP 요청/응답 추적
- 🔍 **Query 분석**: 데이터베이스 쿼리 및 성능 분석
- 📧 **Mail 추적**: 발송된 이메일 모니터링
- 🔔 **Notification 추적**: 알림 발송 내역
- 💼 **Job 모니터링**: 큐 작업 추적
- 📝 **Log 기록**: 애플리케이션 로그 중앙화

## 2. Telescope 설치

### 2.1 패키지 설치
```bash
composer require laravel/telescope
```

### 2.2 Telescope 설치 및 마이그레이션
```bash
php artisan telescope:install
php artisan migrate
```

## 3. 기본 설정

### 3.1 설정 파일 (`config/telescope.php`)

주요 설정 항목:

```php
// 민감한 데이터 자동 마스킹
'watchers' => [
    Watchers\RequestWatcher::class => [
        'enabled' => env('TELESCOPE_REQUEST_WATCHER', true),
        'size_limit' => env('TELESCOPE_RESPONSE_SIZE_LIMIT', 64),
        'ignore' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'credit_card',
            // ... 기타 민감한 필드
        ],
    ],
],

// 데이터 보관 기간
'prune' => [
    'hours' => env('TELESCOPE_PRUNE_HOURS', 48), // 48시간 보관
    'keep_exceptions' => env('TELESCOPE_KEEP_EXCEPTIONS_DAYS', 7), // 예외는 7일간 보관
],

// 샘플링 비율 (프로덕션 환경)
'sample' => [
    'default' => env('TELESCOPE_SAMPLE_RATE', 100), // 개발: 100%, 프로덕션: 10-20% 권장
],
```

## 4. 보안 설정

### 4.1 접근 권한 제한

`app/Providers/TelescopeServiceProvider.php`:

```php
protected function gate(): void
{
    Gate::define('viewTelescope', function ($user) {
        return $user->hasRole('super_admin');
    });
}
```

### 4.2 민감한 데이터 보호

```php
protected function hideSensitiveRequestDetails(): void
{
    if ($this->app->environment('local')) {
        return;
    }

    Telescope::hideRequestParameters(['_token']);
    Telescope::hideRequestHeaders([
        'cookie',
        'x-csrf-token',
        'x-xsrf-token',
    ]);
}
```

## 5. 엔터프라이즈 기능 추가

### 5.1 Critical Error 알림 시스템

#### 알림 클래스
`app/Notifications/System/CriticalErrorNotification.php`:
- 이메일 및 데이터베이스 알림 지원
- Critical Error 발생 시 관리자에게 즉시 알림

#### 이벤트 리스너
`app/Listeners/System/TelescopeErrorListener.php`:
- Telescope 이벤트 감지
- Critical Error 판별 로직
- 관리자 알림 발송

### 5.2 자동 리포트 생성

#### 리포트 생성 커맨드
`app/Console/Commands/System/GenerateTelescopeReport.php`:

```bash
# 일일 리포트
php artisan telescope:report --period=daily

# 주간 리포트
php artisan telescope:report --period=weekly

# 월간 리포트
php artisan telescope:report --period=monthly

# 특정 이메일로 발송
php artisan telescope:report --period=daily --email=admin@example.com
```

#### 리포트 내용
- 에러 요약 통계
- Top 10 예외 유형
- 시간대별 에러 분포
- 영향받은 사용자 수
- 최근 발생 에러 목록

### 5.3 스케줄 설정

`routes/console.php`:

```php
// Telescope 데이터 정리 (매일 새벽 2시)
Schedule::command('telescope:prune')->daily()->at('02:00');

// 일일 리포트 (매일 오전 9시)
Schedule::command('telescope:report --period=daily')->dailyAt('09:00');

// 주간 리포트 (매주 월요일 오전 9시)
Schedule::command('telescope:report --period=weekly')->weeklyOn(1, '09:00');

// 월간 리포트 (매월 1일 오전 9시)
Schedule::command('telescope:report --period=monthly')->monthlyOn(1, '09:00');
```

## 6. Filament 통합

### 6.1 Telescope 데이터 조회

`app/Filament/Resources/System/TelescopeEntryResource.php`:
- Filament 관리자 패널에서 Telescope 데이터 조회
- 필터링 및 검색 기능
- 실시간 업데이트 (10초 간격)

### 6.2 주요 기능
- Type별 필터링 (Exception, Request, Query, Job, Log)
- 날짜 범위 검색
- 사용자별 조회
- Telescope 원본 페이지로 바로가기

## 7. 운영 가이드

### 7.1 환경별 설정

#### 개발 환경
```env
TELESCOPE_ENABLED=true
TELESCOPE_SAMPLE_RATE=100
TELESCOPE_PRUNE_HOURS=168
```

#### 프로덕션 환경
```env
TELESCOPE_ENABLED=true
TELESCOPE_SAMPLE_RATE=20
TELESCOPE_PRUNE_HOURS=48
```

### 7.2 모니터링 체크리스트

#### 일일 점검
- [ ] Critical Error 알림 확인
- [ ] 일일 리포트 검토
- [ ] 비정상적인 패턴 확인

#### 주간 점검
- [ ] 주간 리포트 분석
- [ ] 반복되는 에러 패턴 확인
- [ ] 성능 저하 요소 확인

#### 월간 점검
- [ ] 월간 트렌드 분석
- [ ] 시스템 개선사항 도출
- [ ] 에러 감소율 확인

### 7.3 문제 해결

#### Telescope가 작동하지 않을 때
```bash
# 캐시 정리
php artisan cache:clear
php artisan config:clear

# 재설치
php artisan telescope:install
php artisan migrate:fresh --path=database/migrations/*telescope*
```

#### 데이터가 너무 많을 때
```bash
# 수동 정리
php artisan telescope:prune --hours=24

# 특정 타입만 정리
php artisan telescope:prune --type=query
```

## 📝 요약

Laravel Telescope와 추가 보완 기능을 통해 다음을 구현했습니다:

✅ **기본 에러 로깅**: Telescope를 통한 중앙집중식 로깅
✅ **보안 강화**: 접근 권한 제한 및 민감정보 마스킹
✅ **알림 시스템**: Critical Error 실시간 알림
✅ **리포트 생성**: 일/주/월간 자동 리포트
✅ **Filament 통합**: 관리자 패널에서 직접 조회
✅ **자동화**: 스케줄을 통한 자동 정리 및 리포트

## 🔗 관련 링크

- **Telescope 대시보드**: `/telescope`
- **Filament Error Logs**: `/admin/telescope-entries`
- **공식 문서**: [Laravel Telescope Documentation](https://laravel.com/docs/telescope)

---

*최종 업데이트: 2024년 12월* 