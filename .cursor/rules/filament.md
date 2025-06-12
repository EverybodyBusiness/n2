---
description: 
globs: 
alwaysApply: true
---
# Filament 개발 규칙

## 1. 핵심 원칙

- **[FP1] 코드 생성 시 Filament 3.x의 모든 클래스와 메소드를 정확히 이해하고 사용할 것**
  - 사용하려는 메소드/속성의 존재 여부를 반드시 확인 후 코드 작성
  - 존재하지 않는 메소드를 호출하거나 속성을 사용하지 않기
  - `/doc/filament/source_analysis/` 디렉토리의 문서 먼저 참조하기
  - `/doc/filament/source_analysis/README.md` 의 내용 숙지, filament의 설계 철학, 동작 메커니즘을 이해한다
  - 사용자의 요청이 있지 않는 한, 자바스크립트 코드는 생성하지 않는다
  - 모든 Resource, Page 는 `HasAdminMenuInfo` 트레이트 사용
  <!-- - navigationGroup, title, navigationLabel, getHeading(), getSubHeading() 관련 코드 생성 금지 -->

- **[FP2] 버전 호환성과 API 준수**
  - Filament 3.3 버전의 API를 정확히 파악
  - 최신 버전과 이전 버전의 메소드명 혼동하지 않기
  - 메소드 체이닝 패턴 일관되게 사용하기

- **[FP3] 컴포넌트 계층 구조 이해**
  - 각 컴포넌트의 트레이트(Concerns)와 인터페이스(Contracts) 숙지
  - 상속 관계와 메소드 구현 방식 이해하기
  - 공통 기능(트레이트)과 인터페이스 활용 방법 이해하기

- **[FP4] 도메인 기반 디렉토리 구조 준수**
  - 모든 Filament 클래스는 도메인 디렉토리 아래에 생성
  - Resources, Pages, Widgets, Clusters 등 모든 타입에 도메인 구조 적용
  - 도메인 예시: System, Auth, Blog, Shop, Sample 등
  - 뷰 파일도 동일한 도메인 구조를 따라야 함

## 2. 리소스(Resources) 규칙

### 2.1 일반 규칙
- 모든 Model에 대한 리소스 페이지를 작성
- 필라멘트 공식 아이콘만 사용
- 모든 리소스의 Pages\List Page 에서 `HasResizableColumn` 트레이트 사용
- `slug` 값을 반드시 가질 것 (도메인 prefix 포함)
- 한국어 번역을 기본으로 사용

### 2.2 디렉토리 구조 (도메인 기반)
```
app/Filament/
├── Resources/
│   ├── System/                     # System 도메인
│   │   ├── UserResource.php
│   │   ├── UserResource/
│   │   │   └── Pages/
│   │   │       ├── CreateUser.php
│   │   │       ├── EditUser.php
│   │   │       └── ListUsers.php
│   │   ├── RoleResource.php
│   │   └── RoleResource/
│   │       └── Pages/
│   └── Blog/                       # Blog 도메인
│       ├── PostResource.php
│       └── PostResource/
│           └── Pages/
├── Pages/
│   ├── System/                     # System 도메인 페이지
│   │   ├── Settings.php
│   │   └── Dashboard.php
│   └── Sample/                     # Sample 도메인 페이지
│       └── ExamplePage.php
├── Widgets/
│   ├── System/                     # System 도메인 위젯
│   │   ├── UserStatsWidget.php
│   │   └── SystemOverview.php
│   └── Blog/                       # Blog 도메인 위젯
│       └── RecentPostsWidget.php
└── Clusters/
    ├── System/                     # System 도메인 클러스터
    │   └── SystemCluster.php
    └── Shop/                       # Shop 도메인 클러스터
        └── ShopCluster.php
```

### 2.3 네이밍 및 네임스페이스 규칙
- 리소스 클래스: `app/Filament/Resources/{Domain}/{EntityName}Resource.php`
- 네임스페이스: `App\Filament\Resources\{Domain}`
- 예시:
  ```php
  namespace App\Filament\Resources\System;
  
  class UserResource extends Resource
  {
      // ...
  }
  ```

### 2.4 뷰 파일 위치
- 뷰 파일도 도메인 구조를 따름
- 위치: `resources/views/filament/{domain}/{resource}/`
- 예시: `resources/views/filament/system/user/custom-view.blade.php`

### 2.5 메뉴 설정 규칙
- `navigationGroup`으로 리소스 그룹화
- `navigationGroup`은 AdminMenu의 최상위 부모 레코드 기준으로 그룹화
- `navigationSort`는 AdminMenu의 order 필드 사용
- `navigationIcon`은 AdminMenu의 icon 필드 사용
- `navigationLabel`은 AdminMenu의 name 필드 사용

#### NavigationGroup 통일 규칙
- **[NG1] 모든 NavigationGroup은 한글로 통일**
  - 영문 사용 금지 (System ❌ → 시스템 ✅)
  - 일관된 명칭 사용 (시스템관리, System Management ❌ → 시스템 ✅)
  
- **[NG2] 도메인별 NavigationGroup 표준**
  ```php
  // 표준 NavigationGroup 명칭
  protected static ?string $navigationGroup = '시스템';     // System 도메인
  protected static ?string $navigationGroup = '사용자';     // Auth/User 도메인
  protected static ?string $navigationGroup = '콘텐츠';     // Content/Blog 도메인
  protected static ?string $navigationGroup = '상점';       // Shop/Commerce 도메인
  protected static ?string $navigationGroup = '보고서';     // Report 도메인
  protected static ?string $navigationGroup = '설정';       // Settings 도메인
  ```

- **[NG3] NavigationGroup 우선순위**
  ```php
  // navigationSort로 그룹 순서 지정
  protected static ?int $navigationSort = 1;  // 시스템
  protected static ?int $navigationSort = 2;  // 사용자
  protected static ?int $navigationSort = 3;  // 콘텐츠
  protected static ?int $navigationSort = 4;  // 상점
  protected static ?int $navigationSort = 5;  // 보고서
  protected static ?int $navigationSort = 9;  // 설정
  ```

- **[NG4] 같은 도메인 리소스는 반드시 같은 NavigationGroup 사용**
  ```php
  // System 도메인의 모든 리소스
  class RoleResource { protected static ?string $navigationGroup = '시스템'; }
  class AuditResource { protected static ?string $navigationGroup = '시스템'; }
  class MediaResource { protected static ?string $navigationGroup = '시스템'; }
  class ScheduledTaskResource { protected static ?string $navigationGroup = '시스템'; }
  ```

- **[NG5] NavigationLabel 명명 규칙**
  ```php
  // 단수형 사용, 관리/목록 등의 접미사 제거
  protected static ?string $navigationLabel = '역할';        // ✅ (역할 관리 ❌)
  protected static ?string $navigationLabel = '감사 로그';    // ✅ (감사 로그 목록 ❌)
  protected static ?string $navigationLabel = '미디어';       // ✅ (미디어 파일 ❌)
  protected static ?string $navigationLabel = '예약 작업';    // ✅ (스케줄 작업 ❌)
  ```

### 2.6 Slug 규칙

#### Slug 명명 규칙
- **[SL1] 모든 Resource, Page, Cluster의 slug는 도메인 prefix 필수**
  ```php
  // Resource slug 예시
  protected static ?string $slug = 'system/roles';          // ✅
  protected static ?string $slug = 'system/audits';         // ✅
  protected static ?string $slug = 'system/media';          // ✅
  protected static ?string $slug = 'roles';                  // ❌ (도메인 없음)
  
  // Page slug 예시
  protected static string $slug = 'system/horizon';          // ✅
  protected static string $slug = 'system/backup';           // ✅
  protected static string $slug = 'settings/general';        // ✅
  ```

- **[SL2] Slug 형식 규칙**
  ```php
  // 형식: {domain}/{resource-name}
  // - 도메인: 소문자, 단수형 (system, auth, blog, shop)
  // - 리소스명: 소문자, 복수형, 하이픈 구분
  
  protected static ?string $slug = 'system/scheduled-tasks'; // ✅
  protected static ?string $slug = 'blog/post-categories';   // ✅
  protected static ?string $slug = 'System/ScheduledTasks';  // ❌ (대문자)
  protected static ?string $slug = 'system/scheduled_tasks'; // ❌ (언더스코어)
  ```

- **[SL3] getSlug() 메소드 구현 시 도메인 prefix 포함**
  ```php
  public static function getSlug(): string
  {
      $domain = strtolower(static::getDomain()); // 'system'
      $resource = Str::plural(Str::kebab(class_basename(static::$model))); // 'users'
      return "{$domain}/{$resource}";
  }
  ```

- **[SL4] 도메인별 표준 slug prefix**
  ```php
  // 표준 도메인 slug prefix
  'system/'     // 시스템 관리
  'auth/'       // 인증 및 사용자
  'content/'    // 콘텐츠 관리
  'shop/'       // 전자상거래
  'report/'     // 보고서
  'settings/'   // 설정
  ```

### 2.7 공통 기능 규칙
- **[R1] 모든 Resource는 `Import`, `Export` 기능을 기본 포함**
  - `Spatie Laravel Excel`, `Filament Excel` 패키지 사용
  - `ListTable` 내에서 기본 액션으로 등록

- **[R2] SoftDeletes 모델은 다음 기능 반드시 포함**
  - 복구(`restore`) 기능
  - 영구 삭제(`force delete`) 기능
  - 휴지통 필터(`TrashedFilter`)
  - `use SoftDeletes` 여부로 자동 판단

- **[R3] 활성/상태 필터 필수**
  - `active_flag`, `status` 컬럼이 있는 경우 필터 또는 토글 필드 제공

### 2.8 Heading 및 Subheading 규칙

#### Resource의 Heading/Subheading
- **[HS1] Resource Pages에서 getSubheading() 메소드 구현**
  ```php
  // ListPage에서 구현
  public function getSubheading(): ?string
  {
      return '시스템에 등록된 모든 역할을 관리합니다.';
  }
  
  // CreatePage에서 구현
  public function getSubheading(): ?string
  {
      return '새로운 역할을 생성하고 권한을 설정합니다.';
  }
  
  // EditPage에서 구현
  public function getSubheading(): ?string
  {
      return '역할 정보를 수정하고 권한을 변경합니다.';
  }
  ```

- **[HS2] Subheading 작성 규칙**
  - 해당 페이지의 목적을 명확히 설명
  - 사용자가 수행할 수 있는 작업을 안내
  - 간결하고 이해하기 쉬운 문장으로 작성
  - 마침표로 종료

## 3. 폼(Forms) 규칙

### 3.1 폼 구성 규칙
- 모든 폼 필드는 Card 또는 Section 컴포넌트 내에 그룹화
- 모든 필드에 한글 레이블 지정
- 필요시 `columns` 속성을 사용하여 필드를 여러 열로 배치
- 필수 필드는 `required()` 메서드로 명시적 표시

### 3.2 필드 UI 컴포넌트 규칙
- **[F1] 단일 선택 필드는 `ToogleButton` 컴포넌트 사용**
  - 성별, 등급, 상태 등에 적용

- **[F2] 다중 선택 필드는 `CheckboxList` 사용**
  - 관계형 모델에서의 다중 선택
  - `preload()`, `searchable()` 옵션 적용

- **[F3] 다중 선택 필드에서 5개 이상 항목은 `전체 선택/해제` 기능 포함**

- **[F4] `Group` 컬럼이 있는 경우:**
  - 탭 필터(TabsFilter 또는 SelectFilter) 구성
  - 같은 그룹 레코드에 대한 부분 전체 선택 기능 구현

### 3.3 폼 검증 규칙
- 모든 중요 필드는 적절한 검증 규칙 적용
- 복잡한 검증 로직은 별도 메서드로 분리
- 사용자 친화적인 오류 메시지 제공

## 4. 테이블(Tables) 규칙

### 4.1 테이블 구성 규칙
- 모든 테이블 컬럼에 한글 레이블 지정
- 검색 가능한 컬럼에는 `searchable()` 메서드 적용
- 정렬 가능한 컬럼에는 `sortable()` 메서드 적용
- 자주 사용하지 않는 컬럼은 `toggleable()` 메서드로 기본 숨김 처리

### 4.2 필터링 및 정렬 UX 규칙
- **[U1] 날짜(Date) 필드:**
  - `DateRangeFilter`, `DatePickerFilter` 사용

- **[U2] Boolean 필드는 `Toggle` 또는 `Switch` 컴포넌트 사용**
  - 공지 활성화 여부, 표시 여부 등에 적용

- **[U3] `searchable()` 필드는 사용자 친화적으로 구성**
  - 이름, 코드, 이메일 등 주요 필드 검색 기능 활성화

### 4.3 액션 및 일괄 액션 규칙
- 적절한 액션 컴포넌트 사용(`EditAction`, `ViewAction`, `DeleteAction` 등)
- 필요한 경우 커스텀 액션 구현
- 일괄 액션은 적절한 그룹화와 권한 제어 적용

### 4.4 테이블 요약 및 그룹화
- 필요시 `Summarizers`를 사용하여 집계 정보 표시
- 데이터 그룹화가 필요한 경우 `Group` 컴포넌트 활용
- 대량 데이터의 경우 페이지네이션 및 필터링 최적화

## 5. 페이지(Pages) 규칙

### 5.1 일반 규칙
- 모든 커스텀 페이지는 `Filament\Pages\Page` 클래스 상속
- 모든 페이지 클래스는 `HasAdminMenuInfo` 트레이트 사용
- 독립적인 URL 경로를 가진 단일 화면으로 구성

### 5.2 네이밍 및 디렉토리 규칙 (도메인 기반)
- 페이지 위치: `app/Filament/Pages/{Domain}/{PageName}.php`
- 네임스페이스: `App\Filament\Pages\{Domain}`
- 설정 페이지: `app/Filament/Pages/System/Settings/{SettingName}.php`
- 예시:
  ```php
  namespace App\Filament\Pages\System;
  
  class Dashboard extends Page
  {
      // ...
  }
  ```

### 5.3 메뉴 및 권한 관리
- `getNavigationLabel()`, `getNavigationIcon()`, `getNavigationGroup()` 메서드 구현
- `canAccess()` 메서드로 페이지 접근 권한 제어

### 5.4 Heading 및 Subheading 규칙

#### 페이지 제목 설정
- **[PH1] 모든 Page는 getHeading()과 getSubheading() 메소드 구현**
  ```php
  public function getHeading(): string
  {
      return '백업 관리';  // 페이지의 주 제목
  }
  
  public function getSubheading(): ?string
  {
      return '시스템 백업을 생성하고 관리합니다.';  // 보조 설명
  }
  ```

- **[PH2] Heading 작성 규칙**
  - 명사형으로 작성 (동사 사용 지양)
  - 페이지의 핵심 기능을 나타내는 2-4단어
  - 네비게이션 레이블과 일관성 유지

- **[PH3] Subheading 작성 규칙**
  - 페이지의 목적과 기능을 설명하는 완전한 문장
  - 사용자가 이 페이지에서 할 수 있는 작업 안내
  - 15-30자 내외로 간결하게 작성
  - 마침표로 종료

- **[PH4] 도메인별 Subheading 예시**
  ```php
  // 시스템 도메인
  '시스템 설정을 구성하고 관리합니다.'
  '백그라운드 작업의 상태를 모니터링합니다.'
  '시스템 로그를 조회하고 분석합니다.'
  
  // 사용자 도메인
  '사용자 계정을 생성하고 권한을 할당합니다.'
  '사용자 활동 내역을 추적하고 관리합니다.'
  
  // 콘텐츠 도메인
  '게시물을 작성하고 발행 상태를 관리합니다.'
  '카테고리를 구성하고 콘텐츠를 분류합니다.'
  ```

## 6. 위젯(Widgets) 규칙

### 6.1 일반 규칙
- 모든 위젯은 `Filament\Widgets\Widget` 클래스 상속
- 재사용 가능하고 모듈화된 방식으로 설계
- 한글 제목 및 적절한 아이콘 사용

### 6.2 네이밍 및 유형 규칙 (도메인 기반)
- 위젯 위치: `app/Filament/Widgets/{Domain}/{WidgetName}.php`
- 네임스페이스: `App\Filament\Widgets\{Domain}`
- 통계 위젯: `{EntityName}StatsWidget.php`
- 차트 위젯: `{EntityName}ChartWidget.php`
- 목록 위젯: `Recent{EntityName}Widget.php`
- 예시:
  ```php
  namespace App\Filament\Widgets\System;
  
  class UserStatsWidget extends StatsWidget
  {
      // ...
  }
  ```

### 6.3 설정 및 권한 규칙
- `getHeading()`, `getColumnSpan()`, `getPollingInterval()` 메서드 구현
- `canView()` 메서드로 위젯 표시 권한 제어

## 7. 공통 개발 패턴

### 7.1 네비게이션 및 구조 규칙
- **[N1] 모든 Resource는 `navigationGroup`, `navigationIcon` 지정**
- **[N2] 리소스 공통 구성 요소는 `BaseResource`로 추출하여 상속**

### 7.2 커스터마이징 전략
- **[X1] 자주 사용하는 컴포넌트는 `Custom Components`로 추출**
- **[X2] 테이블 컬럼 및 Form 필드는 `Field Factory` 패턴으로 분리 가능**

### 7.3 권한 관리 규칙
- 모든 리소스는 권한 체크 메서드 구현
- 필요시 Gate 클래스와 연동하여 세밀한 권한 제어 구현

## 8. 코드 검증 프로세스

### 8.1 메소드 존재 검증
- 코드 작성 전 필라멘트 API 문서(`doc/filament/source_analysis/`) 참조
- 존재하지 않는 메소드/속성 사용 금지
- 사용 전 메소드 파라미터 및 반환값 유형 확인

### 8.2 자동 검증 절차
1. 코드 작성 전 Filament API 문서 참조
2. 코드 작성 후 문법 및 API 사용 정확성 검토
3. 존재하지 않는 메소드/속성 사용 시 경고 메시지 표시

### 8.3 도메인별 구성 검증
- 도메인 특성에 맞는 Filament 컴포넌트 구성 여부 확인
- 관련 리소스, 페이지, 위젯의 일관성 검증
- 사용자 경험 관점에서의 UI/UX 검증

## 9. 클러스터(Clusters) 규칙

### 9.1 클러스터 위치 및 네이밍
- 클러스터 위치: `app/Filament/Clusters/{Domain}/{ClusterName}.php`
- 네임스페이스: `App\Filament\Clusters\{Domain}`
- 예시:
  ```php
  namespace App\Filament\Clusters\System;
  
  class SystemCluster extends Cluster
  {
      protected static ?string $navigationIcon = 'heroicon-o-cog';
      protected static ?string $navigationLabel = '시스템 관리';
  }
  ```

## 10. 패널 프로바이더 설정

### 10.1 도메인별 리소스 자동 검색
```php
public function panel(Panel $panel): Panel
{
    return $panel
        // 도메인별 리소스 검색
        ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
        ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
        ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
        ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters');
}
```

## 11. 도메인 구조 예시

### 11.1 시스템(System) 도메인
- 사용자 관리, 권한, 설정 등
- `app/Filament/Resources/System/UserResource.php`
- `app/Filament/Pages/System/Settings.php`

### 11.2 샘플(Sample) 도메인
- 예제 및 데모 컴포넌트
- `app/Filament/Resources/Sample/ExampleResource.php`
- `app/Filament/Pages/Sample/DemoPage.php`

### 11.3 블로그(Blog) 도메인
- 블로그 관련 기능
- `app/Filament/Resources/Blog/PostResource.php`
- `app/Filament/Widgets/Blog/RecentPostsWidget.php`

### 11.4 쇼핑(Shop) 도메인
- 전자상거래 관련 기능
- `app/Filament/Resources/Shop/ProductResource.php`
- `app/Filament/Clusters/Shop/ShopCluster.php`