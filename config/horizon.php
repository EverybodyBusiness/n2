<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Horizon will be accessible from. If this
    | setting is null, Horizon will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    | Horizon이 접근 가능한 서브도메인을 설정합니다. null인 경우 애플리케이션과
    | 동일한 도메인을 사용하고, 값이 있으면 해당 서브도메인을 사용합니다.
    | 예: 'horizon' 설정 시 horizon.example.com으로 접근
    |
    */

    'domain' => env('HORIZON_DOMAIN'),  // 환경변수 HORIZON_DOMAIN 값 사용

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Horizon will be accessible from. Feel free
    | to change this path to anything you like. Note that the URI will not
    | affect the paths of its internal API that aren't exposed to users.
    |
    | Horizon 대시보드에 접근할 URI 경로를 설정합니다.
    | 기본값은 'horizon'이며, 이 경우 /horizon으로 접근합니다.
    | 내부 API 경로에는 영향을 주지 않습니다.
    |
    */

    'path' => env('HORIZON_PATH', 'horizon'),  // 기본값: /horizon

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    |
    | This is the name of the Redis connection where Horizon will store the
    | meta information required for it to function. It includes the list
    | of supervisors, failed jobs, job metrics, and other information.
    |
    | Horizon이 메타 정보를 저장할 Redis 연결 이름을 지정합니다.
    | supervisor 목록, 실패한 작업, 작업 메트릭 등의 정보가 저장됩니다.
    | config/database.php의 redis 연결 설정 중 하나를 사용합니다.
    |
    */

    'use' => 'default',  // config/database.php의 'default' Redis 연결 사용

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    |
    | This prefix will be used when storing all Horizon data in Redis. You
    | may modify the prefix when you are running multiple installations
    | of Horizon on the same server so that they don't have problems.
    |
    | Redis에 Horizon 데이터를 저장할 때 사용할 접두사입니다.
    | 동일한 서버에서 여러 Horizon 인스턴스를 실행할 때 충돌을 방지하기 위해
    | 각각 다른 접두사를 사용해야 합니다.
    |
    */

    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_').'_horizon:'  // 예: laravel_horizon:
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will get attached onto each Horizon route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    | Horizon 라우트에 적용될 미들웨어 목록입니다.
    | 인증, 권한 확인 등의 미들웨어를 추가할 수 있습니다.
    | 예: ['web', 'auth', 'admin'] - 관리자만 접근 가능하도록 설정
    |
    */

    'middleware' => ['web'],  // 기본 웹 미들웨어만 적용

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    |
    | This option allows you to configure when the LongWaitDetected event
    | will be fired. Every connection / queue combination may have its
    | own, unique threshold (in seconds) before this event is fired.
    |
    | 큐 대기 시간 임계값을 설정합니다.
    | 작업이 지정된 시간(초) 이상 대기하면 LongWaitDetected 이벤트가 발생합니다.
    | 연결/큐 조합별로 다른 임계값을 설정할 수 있습니다.
    |
    */

    'waits' => [
        'redis:default' => 60,  // default 큐: 60초 이상 대기 시 경고
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming Times
    |--------------------------------------------------------------------------
    |
    | Here you can configure for how long (in minutes) you desire Horizon to
    | persist the recent and failed jobs. Typically, recent jobs are kept
    | for one hour while all failed jobs are stored for an entire week.
    |
    | 작업 기록 보관 시간을 분 단위로 설정합니다.
    | Horizon 대시보드에서 확인할 수 있는 작업 기록의 보관 기간입니다.
    |
    */

    'trim' => [
        'recent' => 60,          // 최근 작업: 60분(1시간) 보관
        'pending' => 60,         // 대기 중 작업: 60분 보관
        'completed' => 60,       // 완료된 작업: 60분 보관
        'recent_failed' => 10080,  // 최근 실패 작업: 10080분(7일) 보관
        'failed' => 10080,       // 실패한 작업: 10080분(7일) 보관
        'monitored' => 10080,    // 모니터링 작업: 10080분(7일) 보관
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    |
    | Silencing a job will instruct Horizon to not place the job in the list
    | of completed jobs within the Horizon dashboard. This setting may be
    | used to fully remove any noisy jobs from the completed jobs list.
    |
    | 대시보드의 완료된 작업 목록에서 숨길 작업 클래스를 지정합니다.
    | 자주 실행되는 작업이나 중요하지 않은 작업을 숨겨서 대시보드를 깔끔하게 유지할 수 있습니다.
    |
    */

    'silenced' => [
        // App\Jobs\ExampleJob::class,  // 예: 숨길 작업 클래스
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    |
    | Here you can configure how many snapshots should be kept to display in
    | the metrics graph. This will get used in combination with Horizon's
    | `horizon:snapshot` schedule to define how long to retain metrics.
    |
    | 메트릭 그래프에 표시할 스냅샷 개수를 설정합니다.
    | horizon:snapshot 스케줄과 함께 사용되어 메트릭 보관 기간을 결정합니다.
    |
    */

    'metrics' => [
        'trim_snapshots' => [
            'job' => 24,    // 작업 메트릭: 24개 스냅샷 보관 (매시간 실행 시 24시간)
            'queue' => 24,  // 큐 메트릭: 24개 스냅샷 보관
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    |
    | When this option is enabled, Horizon's "terminate" command will not
    | wait on all of the workers to terminate unless the --wait option
    | is provided. Fast termination can shorten deployment delay by
    | allowing a new instance of Horizon to start while the last
    | instance will continue to terminate each of its workers.
    |
    | 빠른 종료 옵션입니다. true로 설정하면 horizon:terminate 명령이
    | 모든 워커의 종료를 기다리지 않고 즉시 종료됩니다.
    | 배포 시간을 단축할 수 있지만, 진행 중인 작업이 중단될 수 있습니다.
    |
    */

    'fast_termination' => false,  // 안전한 종료를 위해 false 권장

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    |
    | This value describes the maximum amount of memory the Horizon master
    | supervisor may consume before it is terminated and restarted. For
    | configuring these limits on your workers, see the next section.
    |
    | Horizon 마스터 supervisor의 최대 메모리 사용량(MB)입니다.
    | 이 값을 초과하면 supervisor가 재시작됩니다.
    | 워커의 메모리 제한은 아래 supervisor 설정에서 개별 지정합니다.
    |
    */

    'memory_limit' => 64,  // 마스터 프로세스: 64MB 제한

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue worker settings used by your application
    | in all environments. These supervisors and settings handle all your
    | queued jobs and will be provisioned by Horizon during deployment.
    |
    | 큐 워커 설정을 정의합니다. 각 supervisor는 특정 큐를 처리하는
    | 워커 그룹을 관리합니다. 환경별로 다른 설정을 적용할 수 있습니다.
    |
    */

    'defaults' => [
        // 기본 supervisor: 일반적인 작업 처리
        'supervisor-1' => [
            'connection' => 'redis',              // Redis 연결 사용
            'queue' => ['high', 'default', 'low'],  // 처리할 큐 목록 (우선순위 순)
            'balance' => 'auto',                  // 자동 밸런싱 (부하에 따라 워커 수 조정)
            'autoScalingStrategy' => 'time',      // 시간 기반 자동 스케일링
            'maxProcesses' => 3,                  // 최대 워커 프로세스 수
            'maxTime' => 0,                       // 워커 실행 시간 제한 (0=무제한)
            'maxJobs' => 0,                       // 워커당 처리할 작업 수 제한 (0=무제한)
            'memory' => 128,                      // 워커당 메모리 제한 (MB)
            'tries' => 3,                         // 작업 실패 시 재시도 횟수
            'timeout' => 60,                      // 작업 타임아웃 (초)
            'nice' => 0,                          // 프로세스 우선순위 (낮을수록 높은 우선순위)
        ],
        
        // 알림 전용 supervisor: 빠른 처리가 필요한 알림 작업
        'supervisor-notifications' => [
            'connection' => 'redis',
            'queue' => ['notifications'],         // notifications 큐만 처리
            'balance' => 'simple',                // 단순 밸런싱 (고정된 워커 수)
            'maxProcesses' => 2,                  // 2개의 워커로 처리
            'memory' => 128,                      // 128MB 메모리
            'tries' => 3,                         // 3번 재시도
            'timeout' => 30,                      // 30초 타임아웃 (빠른 처리)
        ],
        
        // 리포트 전용 supervisor: 무거운 리포트 생성 작업
        'supervisor-reports' => [
            'connection' => 'redis',
            'queue' => ['reports'],               // reports 큐만 처리
            'balance' => 'simple',
            'maxProcesses' => 1,                  // 1개 워커 (리소스 집약적)
            'memory' => 256,                      // 256MB 메모리 (대용량 처리)
            'tries' => 1,                         // 1번만 시도 (재시도 없음)
            'timeout' => 300,                     // 5분 타임아웃 (긴 처리 시간)
        ],
        
        // 미디어 처리 전용 supervisor: 이미지/비디오 처리
        'supervisor-media' => [
            'connection' => 'redis',
            'queue' => ['media'],                 // media 큐만 처리
            'balance' => 'simple',
            'maxProcesses' => 2,                  // 2개 워커 (CPU 집약적)
            'memory' => 256,                      // 256MB 메모리 (이미지/비디오 처리)
            'tries' => 3,                         // 3번 재시도
            'timeout' => 120,                     // 2분 타임아웃
        ],
    ],

    // 환경별 supervisor 설정 오버라이드
    'environments' => [
        // 프로덕션 환경: 더 많은 리소스 할당
        'production' => [
            'supervisor-1' => [
                'maxProcesses' => 10,             // 10개 워커로 증가
                'balanceMaxShift' => 1,           // 한 번에 조정할 워커 수
                'balanceCooldown' => 3,           // 재조정 대기 시간 (초)
            ],
            'supervisor-notifications' => [
                'maxProcesses' => 5,              // 5개 워커로 증가
            ],
            'supervisor-reports' => [
                'maxProcesses' => 3,              // 3개 워커로 증가
            ],
            'supervisor-media' => [
                'maxProcesses' => 5,              // 5개 워커로 증가
            ],
        ],

        // 로컬 개발 환경: 최소 리소스 사용
        'local' => [
            'supervisor-1' => [
                'maxProcesses' => 3,              // 3개 워커 유지
            ],
            'supervisor-notifications' => [
                'maxProcesses' => 2,              // 2개 워커 유지
            ],
            'supervisor-reports' => [
                'maxProcesses' => 1,              // 1개 워커 유지
            ],
            'supervisor-media' => [
                'maxProcesses' => 2,              // 2개 워커 유지
            ],
        ],
    ],
];
