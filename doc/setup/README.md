# Laravel 12 Enterprise Solution - Setup Documentation

이 문서는 Laravel 12 기반 Enterprise 솔루션의 구축 과정을 단계별로 정리한 문서입니다.

## 📋 목차

1. [Step 1: 인증 시스템 구축 (Fortify + Sanctum)](./step-1-authentication.md)
2. [Step 2: Filament 관리자 패널 설치](./step-2-filament-admin.md)
3. [Step 3: 권한 관리 시스템 구축 (Spatie Permission + Shield)](./step-3-permission-management.md)
4. [Step 4: 감사 로깅 시스템 구축 (Laravel Auditing)](./step-4-audit-logging.md)
5. [Step 4.5: 에러 로깅 시스템 구축 (Laravel Telescope)](./step-4.5-telescope-error-logging.md)

## 🎯 프로젝트 개요

### 목표
Laravel 12를 기반으로 기업용 엔터프라이즈 솔루션에 필요한 핵심 기능들을 구현합니다.

### 주요 기능
- ✅ **인증 시스템**: 로그인, 회원가입, 2FA, 비밀번호 재설정
- ✅ **관리자 패널**: Filament 기반 현대적인 관리자 대시보드
- ✅ **권한 관리**: 역할 기반 접근 제어 (RBAC)
- ✅ **감사 로깅**: 모든 데이터 변경 추적
- ✅ **에러 로깅**: Telescope 기반 실시간 에러 모니터링
- 🔲 **실시간 통신**: WebSocket 기반 실시간 기능
- 🔲 **알림 시스템**: 다채널 알림 지원
- 🔲 **작업 큐**: 백그라운드 작업 처리
- 🔲 **백업 시스템**: 자동 백업 및 복원

## 🛠️ 기술 스택

### 백엔드
- **Laravel 12**: PHP 프레임워크
- **Livewire 3**: 동적 UI 컴포넌트
- **Filament 3.3**: 관리자 패널 프레임워크

### 주요 패키지
- **laravel/fortify**: 백엔드 인증 로직
- **laravel/sanctum**: API 토큰 인증
- **filament/filament**: 관리자 패널
- **spatie/laravel-permission**: 권한 관리
- **bezhansalleh/filament-shield**: Filament 권한 통합
- **owen-it/laravel-auditing**: 감사 로깅
- **laravel/telescope**: 디버깅 및 에러 모니터링

## 📦 시스템 요구사항

- PHP >= 8.2
- Composer >= 2.0
- MySQL >= 8.0 또는 PostgreSQL >= 12
- Node.js >= 18.0
- NPM >= 9.0

## 🚀 빠른 시작

```bash
# 1. 프로젝트 클론
git clone [repository-url]
cd n2

# 2. 의존성 설치
composer install
npm install

# 3. 환경 설정
cp .env.example .env
php artisan key:generate

# 4. 데이터베이스 설정
# .env 파일에서 DB 설정 수정 후
php artisan migrate

# 5. 관리자 계정 생성
php artisan app:create-admin-user

# 6. 서버 실행
php artisan serve
npm run dev
```

## 👤 기본 관리자 계정

- **URL**: `http://localhost:8000/admin`
- **이메일**: `admin@example.com`
- **비밀번호**: `password`

⚠️ **주의**: 프로덕션 환경에서는 반드시 비밀번호를 변경하세요!

## 📝 구현 상태

| 단계 | 기능 | 상태 | 문서 |
|------|------|------|------|
| Step 1 | 인증 시스템 | ✅ 완료 | [문서](./step-1-authentication.md) |
| Step 2 | Filament 관리자 패널 | ✅ 완료 | [문서](./step-2-filament-admin.md) |
| Step 3 | 권한 관리 시스템 | ✅ 완료 | [문서](./step-3-permission-management.md) |
| Step 4 | 감사 로깅 | ✅ 완료 | [문서](./step-4-audit-logging.md) |
| Step 4.5 | 에러 로깅 (Telescope) | ✅ 완료 | [문서](./step-4.5-telescope-error-logging.md) |
| Step 5 | 실시간 통신 | 🔲 예정 | - |
| Step 6 | 알림 시스템 | 🔲 예정 | - |
| Step 7 | 작업 큐 | 🔲 예정 | - |
| Step 8 | 미디어 관리 | 🔲 예정 | - |
| Step 9 | 백업 시스템 | 🔲 예정 | - |

## 📞 문의 및 지원

프로젝트 관련 문의사항이 있으시면 이슈를 생성해주세요.

---

*최종 업데이트: 2024년 12월* 