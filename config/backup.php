<?php

return [

    'backup' => [
        /*
         * The name of this application. You can use this name to monitor
         * the backups.
         * 백업 모니터링 시 사용할 애플리케이션 이름
         */
        'name' => env('APP_NAME', 'laravel-backup'),

        'source' => [
            'files' => [
                /*
                 * The list of directories and files that will be included in the backup.
                 * 백업에 포함될 디렉토리와 파일 목록
                 */
                'include' => [
                    base_path(),  // 전체 프로젝트 디렉토리
                    // 추가로 백업할 디렉토리가 있다면 여기에 추가
                ],

                /*
                 * These directories and files will be excluded from the backup.
                 * 백업에서 제외될 디렉토리와 파일 목록
                 */
                'exclude' => [
                    base_path('vendor'),        // Composer 패키지 (재설치 가능)
                    base_path('node_modules'),  // NPM 패키지 (재설치 가능)
                    base_path('.git'),          // Git 저장소
                    base_path('.env'),          // 환경 설정 파일 (보안상 제외)
                    storage_path('logs'),       // 로그 파일
                    storage_path('framework/cache'),  // 캐시 파일
                    storage_path('framework/sessions'), // 세션 파일
                    storage_path('app/backup-temp'),   // 임시 백업 파일
                ],

                /*
                 * Determines if symlinks should be followed.
                 * 심볼릭 링크를 따라갈지 여부
                 */
                'follow_links' => false,

                /*
                 * Determines if it should avoid unreadable folders.
                 * 읽을 수 없는 폴더를 무시할지 여부
                 */
                'ignore_unreadable_directories' => true,

                /*
                 * This path is used to make directories in resulting zip-file relative
                 * 백업 파일 내 경로를 상대 경로로 만들기 위한 기준 경로
                 */
                'relative_path' => base_path(),
            ],

            /*
             * The names of the connections to the databases that should be backed up
             * 백업할 데이터베이스 연결 이름
             */
            'databases' => [
                env('DB_CONNECTION', 'mysql'),
            ],
        ],

        /*
         * The database dump can be compressed to decrease disk space usage.
         * 데이터베이스 덤프 압축 설정 (디스크 공간 절약)
         */
        'database_dump_compressor' => Spatie\DbDumper\Compressors\GzipCompressor::class,

        /*
         * If specified, the database dumped file name will contain a timestamp
         * 데이터베이스 덤프 파일명에 타임스탬프 포함
         */
        'database_dump_file_timestamp_format' => 'Y-m-d-H-i-s',

        /*
         * The base of the dump filename
         * 덤프 파일명 기준
         */
        'database_dump_filename_base' => 'database',

        /*
         * The file extension used for the database dump files.
         * 데이터베이스 덤프 파일 확장자
         */
        'database_dump_file_extension' => '',

        'destination' => [
            /*
             * The compression algorithm to be used for creating the zip archive.
             * ZIP 아카이브 생성 시 사용할 압축 알고리즘
             */
            'compression_method' => ZipArchive::CM_DEFLATE,

            /*
             * The compression level (1-9)
             * 압축 레벨 (1: 가장 빠름, 9: 가장 강력)
             */
            'compression_level' => 9,

            /*
             * The filename prefix used for the backup zip file.
             * 백업 ZIP 파일명 접두사
             */
            'filename_prefix' => date('Y-m-d-H-i-s') . '-',

            /*
             * The disk names on which the backups will be stored.
             * 백업이 저장될 디스크 이름 (config/filesystems.php 참조)
             */
            'disks' => [
                'local',  // 로컬 디스크
                // 's3',  // AWS S3 (설정 후 활성화)
                // 'backup-disk',  // 전용 백업 디스크 (설정 후 활성화)
            ],
        ],

        /*
         * The directory where the temporary files will be stored.
         * 임시 파일이 저장될 디렉토리
         */
        'temporary_directory' => storage_path('app/backup-temp'),

        /*
         * The password to be used for archive encryption.
         * 아카이브 암호화에 사용할 비밀번호
         */
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),

        /*
         * The encryption algorithm to be used for archive encryption.
         * 아카이브 암호화 알고리즘
         */
        'encryption' => 'default',

        /*
         * The number of attempts, in case the backup command encounters an exception
         * 백업 실패 시 재시도 횟수
         */
        'tries' => 3,

        /*
         * The number of seconds to wait before attempting a new backup
         * 재시도 전 대기 시간 (초)
         */
        'retry_delay' => 60,
    ],

    /*
     * You can get notified when specific events occur.
     * 백업 관련 이벤트 발생 시 알림 설정
     */
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class => ['mail', 'database'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail', 'database'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class => ['mail', 'database'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class => ['database'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class => [],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent.
         * 알림을 받을 대상 설정
         */
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFICATION_EMAIL', 'admin@example.com'),

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Backup System'),
            ],
        ],

        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', ''),
            'channel' => env('BACKUP_SLACK_CHANNEL', null),
            'username' => 'Backup Bot',
            'icon' => ':package:',
        ],

        'discord' => [
            'webhook_url' => env('BACKUP_DISCORD_WEBHOOK_URL', ''),
            'username' => 'Backup Bot',
            'avatar_url' => '',
        ],
    ],

    /*
     * Here you can specify which backups should be monitored.
     * 백업 모니터링 설정
     */
    'monitor_backups' => [
        [
            'name' => env('APP_NAME', 'laravel-backup'),
            'disks' => ['local'],
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class => 1,  // 최대 1일 이내 백업 필요
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 10000,  // 최대 10GB
            ],
        ],
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups.
         * 오래된 백업 정리 전략
         */
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            /*
             * The number of days for which backups must be kept.
             * 모든 백업을 보관할 일수
             */
            'keep_all_backups_for_days' => 7,  // 7일간 모든 백업 보관

            /*
             * After the "keep_all_backups_for_days" period is over
             * 일별 백업 보관 기간
             */
            'keep_daily_backups_for_days' => 30,  // 30일간 일별 백업 보관

            /*
             * After the "keep_daily_backups_for_days" period is over
             * 주별 백업 보관 기간
             */
            'keep_weekly_backups_for_weeks' => 12,  // 12주간 주별 백업 보관

            /*
             * After the "keep_weekly_backups_for_weeks" period is over
             * 월별 백업 보관 기간
             */
            'keep_monthly_backups_for_months' => 12,  // 12개월간 월별 백업 보관

            /*
             * After the "keep_monthly_backups_for_months" period is over
             * 연별 백업 보관 기간
             */
            'keep_yearly_backups_for_years' => 5,  // 5년간 연별 백업 보관

            /*
             * After cleaning up the backups remove the oldest backup
             * 최대 저장 용량 (MB)
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 50000,  // 50GB 초과 시 오래된 백업 삭제
        ],

        /*
         * The number of attempts, in case the cleanup command encounters an exception
         * 정리 작업 실패 시 재시도 횟수
         */
        'tries' => 3,

        /*
         * The number of seconds to wait before attempting a new cleanup
         * 재시도 전 대기 시간 (초)
         */
        'retry_delay' => 60,
    ],

];
