---
description: 
globs: 
alwaysApply: true
---
# 레이어별 생성 순서
1. Models
2. Repositories
3. Services
4. Orchestrators
5. Requests
6. Resources
7. Controllers
; 8. Routes (요청시)
; 9. Tests (요청시)

# 라라벨 표준 구조
- 모든 기능은 라라벨 표준 디렉토리 구조를 따릅니다
- 모든 기본 클래스는 `app/Base` 디렉토리에 위치합니다
- 클래스 간의 통신은 인터페이스를 통해 이루어집니다
- BaseOrchestrator는 EventDispatcher를 내장 제공하여 모든 자식 Orchestrator에서 이벤트 발행 기능을 사용할 수 있습니다

# 표준 폴더 구조
```
app/
├── Base/                      # 공통 베이스 클래스
│   ├── BaseModel.php
│   ├── BaseRepository.php
│   ├── BaseService.php
│   ├── BaseOrchestrator.php
│   ├── BaseController.php
│   └── Events/                # 공통 이벤트 클래스
├── Support/                   # 공유 기능
│   ├── Contracts/             # 공유 인터페이스
│   ├── Traits/                # 공유 트레이트
│   ├── Enums/                 # 열거형 정의
│   ├── Helpers/               # 전역 헬퍼 함수
│   ├── Services/              # 공통 서비스
│   ├── Result.php             # 응답 처리 클래스
│   └── Def.php                # 전역 상수 정의
├── Http/                      # HTTP 관련 클래스
│   ├── Controllers/           # 컨트롤러
│   │   ├── Api/               # API 컨트롤러
│   │   │   ├── {EntityName}Controller.php
│   │   │   └── ...
│   │   └── ...
│   ├── Middleware/            # 미들웨어
│   ├── Requests/              # 폼 요청
│   │   ├── {EntityName}/
│   │   │   ├── {Action}{EntityName}Request.php
│   │   │   └── ...
│   │   └── ...
│   └── Resources/             # API 리소스
│       ├── {EntityName}Resource.php
│       └── ...
├── Models/                    # 모델 클래스
│   ├── Blog/                  # 블로그 관련 모델
│   ├── Callbot/               # 콜봇 관련 모델
│   ├── Shop/                  # 쇼핑 관련 모델
│   ├── System/                # 시스템 관련 모델
│   └── ...
├── Orchestrators/             # 오케스트레이터
│   ├── {EntityName}Orchestrator.php
│   └── ...
├── Repositories/              # 레포지토리
│   ├── Interfaces/            # 인터페이스
│   │   ├── {EntityName}RepositoryInterface.php
│   │   └── ...
│   ├── Eloquent/              # 구현체
│   │   ├── {EntityName}Repository.php
│   │   └── ...
│   └── ...
├── Services/                  # 서비스 클래스
│   ├── Interfaces/            # 인터페이스
│   │   ├── {EntityName}ServiceInterface.php
│   │   └── ...
│   ├── Implementations/       # 구현체
│   │   ├── {EntityName}Service.php
│   │   └── ...
│   └── ...
├── Events/                    # 이벤트 클래스
│   ├── {EntityName}/
│   │   ├── {ActionName}Event.php
│   │   └── ...
│   └── ...
├── Enums/                     # 열거형 클래스
├── Gates/                     # 권한 게이트
├── Livewire/                  # Livewire 컴포넌트
│   ├── Chat/                  # 채팅 관련 컴포넌트
│   └── ...
├── Filament/                  # Filament 관련 클래스
│   ├── Admin/                 # 관리자 패널
│   ├── App/                   # 앱 패널
│   ├── Exports/               # 내보내기 기능
│   ├── Imports/               # 가져오기 기능
│   ├── Pages/                 # 페이지 컴포넌트
│   ├── Resources/             # 리소스 컴포넌트
│   ├── Settings/              # 설정 컴포넌트
│   └── Widgets/               # 위젯 컴포넌트
├── Exceptions/                # 예외 클래스
├── Providers/                 # 서비스 프로바이더
│   ├── Filament/              # Filament 관련 프로바이더
│   └── ...
├── Console/                   # 콘솔 명령어
│   ├── Commands/              # 커스텀 명령어
│   └── ...
├── Forms/                     # 폼 관련 클래스
│   ├── Components/            # 폼 컴포넌트
│   └── ...
└── Jobs/                      # 작업 클래스
    ├── {EntityName}/
    │   ├── {ActionName}Job.php
    │   └── ...
    └── ...
```

# 클래스 간 참조 규칙

## 같은 도메인 내 참조
- 직접 참조 가능
- 예: PptService에서 PdfService 직접 참조 가능

## 다른 도메인 간 참조
- 인터페이스를 통한 참조 필요
- 예: 다른 도메인의 서비스 참조 시, 인터페이스 사용 필수

# 네임스페이스 규칙
- Model: `App\Models` (예: User.php)
- 도메인별 Model: `App\Models\{Domain}` (예: `App\Models\Blog\Post.php`)
- Repository Interface: `App\Repositories\Interfaces` (예: UserRepositoryInterface.php)
- Repository 구현체: `App\Repositories\Eloquent` (예: UserRepository.php)
- Service Interface: `App\Services\Interfaces` (예: UserServiceInterface.php)
- Service 구현체: `App\Services\Implementations` (예: UserService.php)
- Controller: `App\Http\Controllers\Api` (예: UserController.php)
- Orchestrator: `App\Orchestrators` (예: UserOrchestrator.php)
- Request: `App\Http\Requests\{EntityName}` (예: CreateUserRequest.php)
- Events: `App\Events\{EntityName}` (예: UserCreatedEvent.php)
- Livewire: `App\Livewire` (예: Chat.php)
- Filament: `App\Filament` (하위 폴더 구조에 따라 세분화)
- Providers: `App\Providers` (예: AppServiceProvider.php)
- Gates: `App\Gates` (예: UserGate.php)
- Enums: `App\Enums` (예: UserStatus.php)

# 데이터베이스 폴더 구조
```
database/
├── migrations/                # 마이그레이션 파일
│   ├── callbot/               # 콜봇 관련 마이그레이션
│   ├── origin/                # 기본 마이그레이션
│   ├── {날짜}_create_{table_name}_table.php
│   └── ...
├── factories/                 # 팩토리 파일
│   ├── Blog/                  # 블로그 관련 팩토리
│   ├── callbot/               # 콜봇 관련 팩토리
│   ├── Concerns/              # 공통 팩토리 기능
│   ├── Shop/                  # 쇼핑 관련 팩토리
│   ├── {EntityName}Factory.php
│   └── ...
└── seeders/                   # 시더 파일
    ├── callbot/               # 콜봇 관련 시더
    ├── local_images/          # 이미지 관련 시더
    ├── {EntityName}Seeder.php
    └── ...
```

