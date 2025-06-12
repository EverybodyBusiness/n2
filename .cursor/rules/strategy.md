---
description: 
globs: 
alwaysApply: true
---
# 엔터프라이즈 플랫폼 서버 설정 및 통합 운영 전략

## 1. 플랫폼 개요

### 1.1 목적
- 엔터프라이즈 비즈니스를 위한 통합 플랫폼 구축
- Laravel 생태계의 다양한 패키지들을 유기적으로 통합
- Filament 관리자 패널을 통한 중앙 집중식 관리 및 제어
- 확장 가능하고 유지보수가 용이한 아키텍처 구현

### 1.2 핵심 원칙
- **통합성**: 모든 패키지는 서로 유기적으로 연동되어야 함
- **일관성**: 동일한 기능은 하나의 패키지로 통일하여 중복 방지
- **관리성**: 모든 기능은 Filament에서 관리, 제어, 추적, 모니터링 가능
- **확장성**: 새로운 패키지 추가 시 기존 시스템과 조화롭게 통합

## 2. 패키지 통합 전략

### 2.1 패키지 선택 기준
1. **기능 중복 검증**
   - 새 패키지 도입 전 기존 패키지의 기능 목록 검토
   - 중복 기능이 있을 경우 기존 패키지 확장 우선 고려
   - 불가피한 경우 어댑터 패턴으로 통합

2. **호환성 검증**
   - Laravel 버전 호환성 확인
   - 다른 핵심 패키지와의 충돌 여부 검증
   - 의존성 버전 충돌 사전 검토

3. **유지보수성 평가**
   - 패키지의 업데이트 주기 및 커뮤니티 활성도
   - 문서화 수준 및 지원 체계
   - 라이선스 호환성

### 2.2 핵심 패키지 구성

#### 인증 및 권한 관리
```
- Laravel Fortify: 인증 백엔드
- Laravel Sanctum: API 토큰 관리
- Spatie Laravel Permission: 역할 및 권한 관리
- Filament Shield: Filament 리소스 권한 통합
```
**통합 전략**: 
- Fortify가 인증 로직 처리, Sanctum이 토큰 발급
- Spatie Permission이 권한 체계 관리, Shield가 Filament UI 통합
- 단일 User 모델에서 모든 권한 정보 중앙 관리

#### 모니터링 및 디버깅
```
- Laravel Telescope: 애플리케이션 디버깅
- Laravel Horizon: 큐 모니터링
- Spatie Laravel Health: 시스템 헬스 체크
- Sentry Laravel: 프로덕션 에러 추적
```
**통합 전략**:
- Telescope는 개발/스테이징 환경 디버깅
- Sentry는 프로덕션 에러 추적 (중복 방지)
- Health가 모든 서비스 상태를 통합 모니터링
- Horizon이 백그라운드 작업 전담

#### 데이터 관리
```
- Spatie Laravel Backup: 백업 관리
- Owen-it Laravel Auditing: 감사 로그
- Spatie Laravel Media Library: 미디어 파일 관리
- Maatwebsite Excel: 엑셀 가져오기/내보내기
```
**통합 전략**:
- Auditing이 모든 모델 변경사항 추적
- Media Library가 모든 파일 업로드 통합 관리
- Excel이 모든 데이터 import/export 처리
- Backup이 DB와 미디어 파일 통합 백업

#### 커뮤니케이션
```
- Laravel Notification: 알림 시스템
- Laravel Broadcasting + Pusher/Soketi: 실시간 통신
- Spatie Laravel Webhook: 웹훅 관리
```
**통합 전략**:
- Notification이 이메일/SMS/푸시 통합 관리
- Broadcasting이 실시간 이벤트 전담
- Webhook이 외부 시스템 연동 통합

### 2.3 Filament 통합 규칙

#### 리소스 생성 규칙
1. **모든 모델은 Filament 리소스 필수**
   - CRUD 작업은 Filament 리소스로 통일
   - 커스텀 페이지는 도메인별로 구성

2. **위젯 통합**
   - 대시보드: 시스템 전체 상태 요약
   - 도메인별 위젯: 각 도메인의 핵심 지표

3. **액션 통합**
   - Import/Export: 모든 리소스에 기본 포함
   - Bulk Actions: 대량 작업 표준화
   - Custom Actions: 도메인 특화 작업

#### 권한 통합
```php
// 모든 Filament 리소스는 Shield 권한 체크 필수
public static function canViewAny(): bool
{
    return auth()->user()->can('view_any_' . static::getModel());
}
```

#### 모니터링 통합
- 각 리소스별 활동 로그 RelationManager
- 시스템 상태 대시보드 위젯
- 실시간 알림 통합

## 3. 시스템 아키텍처

### 3.1 서비스 레이어 통합
```php
// 모든 서비스는 공통 인터페이스 구현
interface ServiceInterface
{
    public function audit(): bool;      // 감사 로그 활성화
    public function cache(): bool;      // 캐시 활성화
    public function queue(): bool;      // 큐 사용 여부
    public function notify(): bool;     // 알림 발송 여부
}
```

### 3.2 이벤트 기반 통합
```php
// 도메인 이벤트 발생 시 자동 처리
class DomainEventSubscriber
{
    public function subscribe($events)
    {
        // 감사 로그 자동 기록
        $events->listen('eloquent.*', AuditingListener::class);
        
        // 캐시 자동 무효화
        $events->listen('model.saved', CacheInvalidator::class);
        
        // 실시간 브로드캐스팅
        $events->listen('domain.*', BroadcastListener::class);
    }
}
```

## 4. 패키지별 통합 가이드

### 4.1 새 패키지 추가 프로세스

1. **사전 검토 체크리스트**
   ```
   □ 기존 패키지와 기능 중복 확인
   □ 의존성 충돌 검증
   □ 라이선스 호환성 확인
   □ 성능 영향 평가
   □ 보안 취약점 스캔
   ```

2. **통합 구현 단계**
   ```
   1. ServiceProvider 생성 및 등록
   2. Configuration 파일 통합
   3. Filament 리소스/페이지 생성
   4. 권한 정책 설정
   5. 모니터링 지표 추가
   6. 문서화 및 테스트
   ```

3. **검증 및 배포**
   ```
   □ 단위 테스트 작성
   □ 통합 테스트 실행
   □ 성능 벤치마크
   □ 스테이징 환경 검증
   □ 롤백 계획 수립
   ```

### 4.2 주요 통합 패턴

#### 어댑터 패턴
```php
// 다양한 스토리지 서비스 통합
interface StorageAdapter
{
    public function store($file, $path);
    public function retrieve($path);
    public function delete($path);
}

class S3Adapter implements StorageAdapter { }
class LocalAdapter implements StorageAdapter { }
class MediaLibraryAdapter implements StorageAdapter { }
```

#### 파사드 패턴
```php
// 복잡한 서브시스템 단순화
class NotificationFacade
{
    public function send($user, $message, $channels = [])
    {
        // Email, SMS, Push, Slack 등 통합 처리
    }
}
```

#### 옵저버 패턴
```php
// 모델 이벤트 자동 처리
class ModelObserver
{
    public function created($model)
    {
        // 감사 로그, 캐시, 검색 인덱스 자동 업데이트
    }
}
```

## 5. 운영 전략
### 5.1 모니터링 대시보드

#### 시스템 상태 (Filament 대시보드)
- **인프라 지표**: CPU, 메모리, 디스크, 네트워크
- **애플리케이션 지표**: 응답시간, 처리량, 에러율
- **비즈니스 지표**: 사용자 수, 거래량, 수익

#### 통합 모니터링 뷰
```php
// 모든 서비스 상태를 한눈에
class SystemHealthWidget extends Widget
{
    protected function getCards(): array
    {
        return [
            Card::make('Database', $this->checkDatabase()),
            Card::make('Redis', $this->checkRedis()),
            Card::make('Queue', $this->checkQueue()),
            Card::make('Storage', $this->checkStorage()),
            Card::make('External APIs', $this->checkApis()),
        ];
    }
}
```

### 5.2 백업 및 복구 전략

#### 통합 백업 정책
```php
// 모든 데이터 소스 통합 백업
'backup' => [
    'source' => [
        'files' => [
            'include' => [
                base_path(),                    // 애플리케이션 코드
                storage_path('app/public'),     // 업로드 파일
                storage_path('media'),          // 미디어 라이브러리
            ],
        ],
        'databases' => ['mysql', 'redis'],     // 모든 데이터베이스
    ],
    'destination' => [
        'disks' => ['s3', 'local'],            // 다중 백업
    ],
];
```

### 5.3 성능 최적화

#### 캐싱 전략
```php
// 계층적 캐싱 구조
class CacheManager
{
    public function remember($key, $ttl, $callback)
    {
        // L1: APCu (로컬 메모리)
        // L2: Redis (분산 캐시)
        // L3: Database (영구 저장)
    }
}
```

#### 큐 최적화
```php
// 작업별 큐 분리
'horizon' => [
    'environments' => [
        'production' => [
            'supervisor-critical' => ['queue' => 'critical', 'processes' => 10],
            'supervisor-default' => ['queue' => 'default', 'processes' => 5],
            'supervisor-low' => ['queue' => 'low', 'processes' => 2],
            'supervisor-scheduled' => ['queue' => 'scheduled', 'processes' => 3],
        ],
    ],
];
```

## 6. 보안 통합

### 6.1 인증 및 권한
- 2FA 필수 적용 (관리자)
- API 토큰 자동 만료
- IP 화이트리스트
- 감사 로그 필수

### 6.2 데이터 보호
- 민감 데이터 암호화
- PII 자동 마스킹
- 백업 파일 암호화
- 접근 로그 기록

## 7. 확장 가이드라인

### 7.1 새로운 도메인 추가
1. 도메인 디렉토리 생성
2. 필수 서비스 인터페이스 구현
3. Filament 리소스 생성
4. 권한 정책 설정
5. 모니터링 지표 추가
6. 테스트 스위트 작성

### 7.2 패키지 업그레이드 전략
1. 변경사항 영향 분석
2. 스테이징 환경 테스트
3. 점진적 롤아웃
4. 롤백 계획 준비
5. 모니터링 강화

## 8. 문서화 요구사항

### 8.1 필수 문서
- 아키텍처 다이어그램
- API 문서 (OpenAPI)
- 통합 가이드
- 운영 매뉴얼
- 트러블슈팅 가이드

### 8.2 코드 문서화
```php
/**
 * 패키지 통합 시 필수 주석
 * 
 * @package 사용된 패키지명
 * @integration 통합된 다른 패키지들
 * @filament 관련 Filament 리소스/페이지
 * @monitoring 모니터링 지표
 */
```

## 9. 지속적 개선

### 9.1 정기 검토
- 월간: 패키지 업데이트 확인
- 분기: 성능 벤치마크
- 반기: 아키텍처 리뷰
- 연간: 기술 스택 재평가

### 9.2 피드백 루프
- 사용자 피드백 수집
- 성능 지표 분석
- 에러 패턴 분석
- 개선사항 우선순위화
