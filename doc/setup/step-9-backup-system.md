# Step 9: 백업 시스템 (Spatie Laravel Backup)

## 개요
엔터프라이즈급 백업 시스템을 구현하여 데이터베이스와 파일 시스템의 자동 백업, 모니터링, 복원 기능을 제공합니다.

## 구현 내용

### 1. 패키지 설치
```bash
composer require spatie/laravel-backup
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

### 2. 백업 설정 (`config/backup.php`)

#### 백업 대상 설정
```php
'source' => [
    'files' => [
        'include' => [
            base_path(),  // 전체 프로젝트 디렉토리
        ],
        'exclude' => [
            base_path('vendor'),        // Composer 패키지
            base_path('node_modules'),  // NPM 패키지
            base_path('.git'),          // Git 저장소
            base_path('.env'),          // 환경 설정 파일
            storage_path('logs'),       // 로그 파일
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('app/backup-temp'),
        ],
    ],
    'databases' => [
        env('DB_CONNECTION', 'mysql'),
    ],
],
```

#### 백업 보관 정책
```php
'cleanup' => [
    'default_strategy' => [
        'keep_all_backups_for_days' => 7,      // 7일간 모든 백업
        'keep_daily_backups_for_days' => 30,   // 30일간 일별 백업
        'keep_weekly_backups_for_weeks' => 12, // 12주간 주별 백업
        'keep_monthly_backups_for_months' => 12, // 12개월간 월별 백업
        'keep_yearly_backups_for_years' => 5,  // 5년간 연별 백업
        'delete_oldest_backups_when_using_more_megabytes_than' => 50000, // 50GB
    ],
],
```

#### 백업 기능 설정
- **압축**: Gzip 압축 (`GzipCompressor::class`)
- **암호화**: AES-256 암호화
- **재시도**: 3번 재시도, 60초 대기
- **알림**: 이메일 및 데이터베이스 알림

### 3. Horizon 백업 큐 설정

#### `config/horizon.php`에 추가된 supervisor
```php
'supervisor-backups' => [
    'connection' => 'redis',
    'queue' => ['backups'],
    'balance' => 'simple',
    'maxProcesses' => 1,      // 동시 백업 방지
    'memory' => 512,          // 512MB (프로덕션: 1GB)
    'tries' => 1,
    'timeout' => 3600,        // 1시간
],
```

### 4. 백업 Job 구현

#### `app/Jobs/System/BackupJob.php`
```php
class BackupJob implements ShouldQueue
{
    public $queue = 'backups';
    public $timeout = 3600;
    
    public function __construct(array $options = [])
    {
        $this->options = array_merge([
            'only-db' => false,
            'only-files' => false,
            'disable-notifications' => false,
        ], $options);
    }
    
    public function handle(): void
    {
        // Artisan 명령 실행
        Artisan::call('backup:run', $arguments);
    }
}
```

### 5. 자동 백업 스케줄

#### `app/Console/Kernel.php`에 추가된 스케줄
```php
// 매일 새벽 3시 전체 백업
$schedule->job(new BackupJob())->dailyAt('03:00');

// 6시간마다 DB만 백업
$schedule->job(new BackupJob(['only-db' => true]))->everySixHours();

// 백업 모니터링 (매일 오전 9시)
$schedule->command('backup:monitor')->dailyAt('09:00');

// 오래된 백업 정리 (매일 새벽 4시)
$schedule->command('backup:clean')->dailyAt('04:00');

// 주간 백업 리포트 (매주 월요일)
$schedule->command('backup:list')->weeklyOn(1, '08:00')
    ->emailOutputTo(env('BACKUP_NOTIFICATION_EMAIL'));
```

### 6. Filament 백업 관리 UI

#### `app/Filament/Pages/System/BackupManager.php`
- 백업 파일 목록 조회
- 백업 다운로드/삭제
- 수동 백업 실행 (전체/DB만)
- 파일 크기 포맷팅
- 권한: admin, super_admin만 접근

### 7. 환경 설정

#### `.env` 파일에 추가할 항목
```env
# 백업 암호화 비밀번호
BACKUP_ARCHIVE_PASSWORD=your-secure-password

# 백업 알림 이메일
BACKUP_NOTIFICATION_EMAIL=admin@example.com

# Slack 알림 (선택사항)
BACKUP_SLACK_WEBHOOK_URL=
BACKUP_SLACK_CHANNEL=

# Discord 알림 (선택사항)
BACKUP_DISCORD_WEBHOOK_URL=
```

## 사용 방법

### 수동 백업 명령어
```bash
# 전체 백업
php artisan backup:run

# DB만 백업
php artisan backup:run --only-db

# 파일만 백업
php artisan backup:run --only-files

# 백업 목록 확인
php artisan backup:list

# 백업 상태 모니터링
php artisan backup:monitor

# 오래된 백업 정리
php artisan backup:clean
```

### 백업 복원 절차
```bash
# 1. 백업 파일 압축 해제
unzip backup-file.zip

# 2. 데이터베이스 복원
mysql -u username -p database_name < db-dumps/mysql-database.sql

# 3. 파일 복원
cp -r storage/* /path/to/project/storage/
cp -r public/uploads/* /path/to/project/public/uploads/
```

## 주요 기능

### 1. 자동화
- 스케줄에 따른 자동 백업
- 오래된 백업 자동 정리
- 백업 상태 자동 모니터링

### 2. 보안
- AES-256 암호화
- 민감한 파일(.env) 제외
- 접근 권한 제어

### 3. 유연성
- 전체/DB/파일 선택적 백업
- 다양한 저장소 지원 (로컬, S3 등)
- 커스터마이징 가능한 보관 정책

### 4. 모니터링
- Horizon 대시보드에서 백업 작업 모니터링
- Filament UI에서 백업 관리
- 이메일/Slack/Discord 알림

## 프로덕션 권장사항

### 1. 외부 저장소 사용
```php
'disks' => [
    'local',
    's3',        // AWS S3
    'gcs',       // Google Cloud Storage
    'dropbox',   // Dropbox
],
```

### 2. 백업 검증
- 정기적인 복원 테스트
- 백업 무결성 검사
- 백업 크기 모니터링

### 3. 보안 강화
- 백업 파일 접근 제한
- 암호화 키 안전한 관리
- 백업 전송 시 SSL/TLS 사용

### 4. 성능 최적화
- 증분 백업 고려
- 백업 시간대 최적화
- 압축 레벨 조정

## 문제 해결

### 1. 메모리 부족
```php
// Horizon 설정에서 메모리 증가
'memory' => 1024,  // 1GB
```

### 2. 타임아웃
```php
// 타임아웃 시간 증가
'timeout' => 7200,  // 2시간
```

### 3. 디스크 공간 부족
- 백업 보관 정책 조정
- 외부 저장소 사용
- 압축 레벨 증가

## 완료된 작업
1. ✅ Spatie Laravel Backup 패키지 설치
2. ✅ 백업 설정 파일 구성
3. ✅ Horizon 백업 큐 설정
4. ✅ 백업 Job 클래스 생성
5. ✅ 자동 백업 스케줄 설정
6. ✅ Filament 백업 관리 UI 구현
7. ✅ 백업 뷰 파일 생성

## 다음 단계
- Step 10: API 문서화 및 테스팅 도구 구현 