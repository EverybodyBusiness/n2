---
description: 
globs: 
alwaysApply: true
---
# Service 레이어 문서

## 1. 역할 (Role)
- 실제 비즈니스 로직을 구현하는 핵심 레이어이다.
- Service는 **핵심 비즈니스 로직**을 구현한다.
- 하나 이상의 Repository를 조합하여 데이터 처리 및 계산을 수행한다.
- 단일 책임 원칙(SRP)에 따라 각 메서드는 하나의 작업만 수행해야 한다.
- 도메인 간 통신은 서비스 인터페이스를 통해 이루어진다.

## 2. 책임 (Responsibility)
- 트랜잭션을 제외한 도메인 로직의 중심 처리
- Repository를 호출하여 데이터를 저장, 수정, 조회, 삭제
- 외부 시스템 연동, 조건 처리, 비즈니스 규칙 판단
- Orchestrator에서 요청받은 작업 실행 및 결과 반환
- **각 메서드는 CRUD 작업 중 하나만 담당하거나 단순 상태 확인만 수행해야 함**

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 단일 책임 원칙(SRP)을 준수하여 메소드는 하나의 책임만 가져야 한다
- 'app/Base' 아래의 BaseService, BaseServiceInterface 의 기존 메소드 수정금지, 필요시 추가 가능
- 메소드 라인별로 주석을 추가한다(격식체 사용)
- 메소드 내 try catch 를 사용하지 않는다. BaseService 의 runWithPolicy 에 try catch 를 사용 중이다
- 직접 DB 접근 금지, Repository 사용 시 반드시 Repository를 통해 처리한다
- 모든 비즈니스 로직은 Service에서만 처리하며 Controller나 Orchestrator에는 작성 금지
- 에러 처리는 runWithPolicy를 통해 통일하며, Result 객체로 결과 반환
- 지역 변수는 snake_case로 명명하고 상단에 주석으로 용도를 명확히 기술한다
- **복합 비즈니스 로직이나 비즈니스 흐름 제어는 금지(Orchestrator의 책임)**
- **다른 도메인의 Service 직접 호출 금지 (인터페이스를 통해 통신)**
- **도메인 이벤트 발행 금지(Orchestrator의 책임으로 위임)**

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치:
  - 인터페이스: `app/Services/Interfaces/{EntityName}ServiceInterface.php`
  - 구현체: `app/Services/Implementations/{EntityName}Service.php`
- 도메인별 구분이 필요한 경우:
  - 인터페이스: `app/Services/Interfaces/{Domain}/{EntityName}ServiceInterface.php`
  - 구현체: `app/Services/Implementations/{Domain}/{EntityName}Service.php`
- 파일명: 
  - 인터페이스: `{EntityName}ServiceInterface.php` (EntityName을 파일명으로 사용)
  - 구현체: `{EntityName}Service.php` (EntityName을 파일명으로 사용)
- Base 클래스 상속: `App\Base\BaseService`
- 인터페이스 구현: `App\Services\Interfaces\{EntityName}ServiceInterface` 또는 `App\Services\Interfaces\{Domain}\{EntityName}ServiceInterface`
- 네임스페이스:
  - 인터페이스: `App\Services\Interfaces` 또는 `App\Services\Interfaces\{Domain}`
  - 구현체: `App\Services\Implementations` 또는 `App\Services\Implementations\{Domain}`

### 3.2.1 의존성 주입 규칙
- 기본 사항:
```php
// 리포지토리 의존성 주입
use App\Repositories\Interfaces\{EntityName}RepositoryInterface;
// 또는 도메인 리포지토리인 경우
use App\Repositories\Interfaces\{Domain}\{EntityName}RepositoryInterface;

// 다른 서비스 인터페이스 의존성 주입
use App\Services\Interfaces\{OtherEntity}ServiceInterface;
// 또는 도메인 서비스인 경우
use App\Services\Interfaces\{Domain}\{OtherEntity}ServiceInterface;

// 다른 도메인의 서비스 인터페이스 의존성 주입
use App\Services\Interfaces\{AnotherDomain}\{AnotherEntity}ServiceInterface;
```

#### 생성자 구현 규칙
- **Repository를 필요로 하는 일반 서비스**:
```php
public function __construct(
    protected {EntityName}RepositoryInterface ${entityName}Repository
) {
}
```

- **Repository를 필요로 하지 않는 유틸리티 서비스**:
```php
public function __construct() 
{
    // Repository 의존성 없음
}
```

- **복합 의존성이 필요한 서비스**:
```php
public function __construct(
    // 필요 시 Repository 의존성 (선택적)
    protected ?{EntityName}RepositoryInterface ${entityName}Repository = null,
    // 유틸리티 의존성
    protected FileManagerInterface $fileManager,
    protected LoggerInterface $logger
) {
}
```

#### 메서드 작성 규칙
- ✅ **Service → 자신의 Repository 호출**: 허용됨
  - Service는 자신의 도메인과 관련된 Repository만 직접 호출할 수 있다.
  - Repository는 순수 DB 접근 계층으로 사용되어야 하며, 비즈니스 로직은 포함하지 않는다.

- ❌ **Service → 다른 Service 직접 호출**: **제한적 허용**
  - 같은 도메인 내 다른 Service 직접 호출 허용
  - 다른 도메인의 Service는 반드시 인터페이스를 통해 호출
  - 도메인 간 통신이 필요한 경우 의존성 주입을 통해 Service 인터페이스 사용

- ❌ **Service → 이벤트 발행**: **금지**
  - 서비스 내에서 직접 이벤트를 발행하지 않는다. (SRP 위반)
  - 이벤트 발행은 Orchestrator의 책임이다.
  - 서비스는 순수하게 비즈니스 로직만 처리하고 결과를 반환한다.

### 3.2.2 외부 연동 및 조합 로직
- 도메인 간 복합적인 흐름(예: 이메일 전송 + 로그 저장)은 Service 내부에서 직접 구현하지 않는다.
- 이 경우에는 상위 계층인 **Orchestrator**에서 여러 Service를 조합하여 비즈니스 시나리오를 완성한다.

### 3.3 메서드 네이밍 표준화
- **발견**: `find{Entity}By{Condition}` - 데이터 조회
- **생성**: `create{Entity}` - 새 데이터 생성
- **수정**: `update{Entity}` - 기존 데이터 수정
- **삭제**: `delete{Entity}` - 데이터 삭제
- **검증**: `validate{Condition}`, `is{Condition}`, `has{Condition}` - 상태 확인
- **유틸리티**: `process{Action}`, `convert{From}To{To}`, `generate{Output}` - 유틸리티 동작

### 3.4 쿼리 조건 작성 규칙 (Query Rules)
- Repository 사용 시 Repository에 정의된 메소드만 호출 가능하며 직접 쿼리 작성 금지

### 3.5 유틸리티 서비스 규칙
- **독립성**: 유틸리티 서비스는 Repository 의존성 없이 동작 가능함
- **순수 기능**: 비즈니스 도메인에 의존하지 않는 순수 기능 제공
- **독립적 배포**: 다른 도메인과 독립적으로 배포 가능해야 함
- **확장 가능성**: 필요시 Repository 의존성 추가 가능하도록 설계

#### 주요 유틸리티 서비스 유형
1. **파일 처리**: 업로드, 다운로드, 변환
2. **데이터 변환**: XML/JSON 파싱, CSV 처리
3. **통신**: HTTP 클라이언트, API 연동
4. **보안**: 암호화, 해시 생성
5. **알림**: 이메일, SMS, 푸시 알림

### 3.6 흐름 제어 규칙 (Flow Control Rules)
#### 허용되는 흐름 제어
- **입력값 검증**: 유효성 검사 후 예외 처리 가능
```php
if ($paymentRequest->amount <= 0) {
    throw new InvalidArgumentException('Invalid payment amount.');
}
```
- **데이터 변형 및 전처리**: 단일 기능 내 가공 로직 허용
```php
if ($paymentRequest->currency !== 'KRW') {
    $this->convertCurrency($paymentRequest);
}
```
- **부가 처리**: 기능 보완을 위한 선택적 로직 허용
```php
if ($user->hasCoupon()) {
    $this->applyCouponDiscount($user);
}
```

#### 금지되는 흐름 제어
- **비즈니스 플로우 결정**: 다음 단계의 기능을 선택하는 로직은 금지
```php
// 안티패턴: 여러 비즈니스 로직이 포함된 흐름 제어
if ($this->tokenService->isExpired($user)) {
    $this->tokenService->reissueToken($user);
} else {
    $this->sessionService->createSession($user);
}
```
- **여러 기능 묶기**: 분기 조건으로 여러 비즈니스 기능 호출 금지
- **복합 작업 처리**: 하나의 메서드에서 여러 작업(조회+수정+알림 등) 처리 금지
- **다른 도메인의 Service 직접 호출**: 도메인 간 직접 통신 금지 (인터페이스 사용)
- **이벤트 발행 로직**: 서비스 내에서 이벤트 발행 금지 (Orchestrator의 역할)

#### 체크리스트
- 이 조건문은 단일 기능 내의 미세 조정인가?
- 이 로직이 "무엇을 할지 결정"하고 있는가?
- 여러 Use Case를 하나의 메소드에서 처리하려고 하는가?
- **이 메서드가 단일 CRUD 작업을 벗어나는가?**
- **Orchestrator에서 처리해야 할 비즈니스 흐름이 포함되어 있는가?**
- **다른 도메인의 Service를 직접 호출하고 있는가?**
- **이벤트를 발행하는 코드가 포함되어 있는가?**

## 4. 올바른 Service 메서드 예시

### 4.1 Repository를 사용하는 일반 서비스
```php
// 올바른 예시: Repository를 사용하는 서비스
public function findUserById(int $id): ?Model
{
    return $this->userRepository->findById($id);
}

// 올바른 예시: 단일 책임(이메일 전송)만 담당
public function sendEmail(string $to, string $subject, string $body, array $options = []): Result
{
    return $this->runWithPolicy('이메일 전송', compact('to', 'subject', 'body', 'options'), function (array $data) {
        // 단일 작업 수행: 이메일 전송 로직
        $log = $this->emailLogRepository->createLog([...]);
        // 이메일 전송 로직...
        return Result::success('이메일 전송 성공', ['log_id' => $log->id]);
    });
}
```

### 4.2 Repository 없는 유틸리티 서비스
```php
// 올바른 예시: Repository 없이 순수 기능만 제공하는 유틸리티 서비스
public function generatePdf(array $data): Result
{
    return $this->runWithPolicy('PDF 생성', $data, function (array $data) {
        $html_content = $data['content'];
        $options = $data['options'] ?? [];
        
        // PDF 생성 로직
        $pdf = new TCPDF();
        $pdf->SetCreator('System');
        $pdf->AddPage();
        $pdf->writeHTML($html_content);
        
        $output = $pdf->Output('', 'S');
        
        return Result::success('PDF 생성 완료', [
            'content' => base64_encode($output),
            'size' => strlen($output)
        ]);
    });
}

// 올바른 예시: 단일 책임(변수 처리)만 담당
public function processTemplateVariables(array $template, array $variables): array
{
    $subject = $template['subject'];
    $body = $template['content'];

    foreach ($variables as $key => $value) {
        $body = str_replace("{{$key}}", $value, $body);
        $subject = str_replace("{{$key}}", $value, $subject);
    }

    return [
        'subject' => $subject,
        'body' => $body
    ];
}
```

### 4.3 복합 유틸리티 서비스
```php
// 올바른 예시: Repository와 외부 서비스를 함께 사용하는 복합 서비스
public function sendNotificationWithTracking(array $data): Result
{
    return $this->runWithPolicy('알림 발송 및 추적', $data, function (array $data) {
        // 외부 서비스로 알림 발송
        $notification_result = $this->sendNotification($data['recipient'], $data['message']);
        
        if (!$notification_result->isSuccess()) {
            return $notification_result;
        }
        
        // 추적 정보 저장 (Repository 사용 - 선택적으로 존재할 경우)
        if ($this->notificationLogRepository !== null) {
            $this->notificationLogRepository->create([
                'recipient' => $data['recipient'],
                'content' => $data['message'],
                'status' => 'sent',
                'sent_at' => now(),
                'external_id' => $notification_result->getData('id')
            ]);
        }
        
        return Result::success('알림이 발송되었습니다', $notification_result->getData());
    });
}
```

## 5. 잘못된 Service 메서드 예시 (안티패턴)

```php
// 안티패턴: 유틸리티 서비스에서 불필요한 Repository 의존성 강제
public function __construct(
    protected FileRepositoryInterface $fileRepository // 불필요한 의존성
) {
    // 파일 변환 서비스에 Repository가 필요하지 않음
}

// 안티패턴: 여러 비즈니스 로직이 혼합됨
public function sendEmailWithTemplate(string $to, string $templateName, array $variables = []): Result
{
    return $this->runWithPolicy('템플릿으로 이메일 전송', compact('to', 'templateName', 'variables'), function (array $data) {
        // 1. 템플릿 조회 로직
        $template = $this->emailTemplateRepository->findByName($data['templateName']);
        if (!$template) {
            return Result::fail("템플릿을 찾을 수 없습니다");
        }

        // 2. 변수 치환 로직
        $subject = $template->subject;
        $body = $template->content;
        foreach ($data['variables'] as $key => $value) {
            $body = str_replace("{{$key}}", $value, $body);
        }

        // 3. 이메일 전송 로직
        return $this->sendEmail($data['to'], $subject, $body);
    });
}

// 안티패턴: 다른 도메인의 Service 직접 호출
public function sendNotification(int $userId, string $message): Result
{
    return $this->runWithPolicy('알림 전송', compact('userId', 'message'), function (array $data) {
        // 다른 도메인의 Service 직접 호출 - 금지
        $emailService = new \App\Services\Implementations\EmailService();
        $smsService = new \App\Services\Implementations\SmsService();
        
        // 이메일 전송
        $emailResult = $emailService->sendEmail($user->email, '알림', $data['message']);
        
        // SMS 전송
        $smsResult = $smsService->sendSMS($user->phone, $data['message']);
        
        return Result::success('알림 전송 완료');
    });
}

// 안티패턴: 서비스 내에서 이벤트 발행
public function registerUserAndDispatchEvent(array $userData): Result
{
    return $this->runWithPolicy('사용자 등록 및 이벤트 발행', $userData, function (array $data) {
        // 사용자 생성
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
        
        // 이벤트 발행 - 서비스 내에서 직접 이벤트 발행 금지 (SRP 위반)
        event(new UserRegisteredEvent([
            'user_id' => $user->id,
            'email' => $user->email
        ]));
        
        return Result::success('사용자가 등록되었습니다', [
            'id' => $user->id
        ]);
    });
}
```

## 6. BaseService 클래스 구현 예시

```php
namespace App\Base;

use App\Support\Result;

abstract class BaseService
{
    /**
     * 정책에 따라 로직 실행
     *
     * @param string $action 실행할 액션 이름
     * @param array $data 입력 데이터
     * @param callable $callback 실행할 콜백 함수
     * @return Result 실행 결과
     */
    protected function runWithPolicy(string $action, array $data, callable $callback): Result
    {
        try {
            return $callback($data);
        } catch (\Throwable $e) {
            logger()->error("[" . class_basename($this) . "] $action 실행 중 오류 발생: " . $e->getMessage(), [
                'exception' => $e,
                'data' => $data
            ]);
            
            return Result::fail($e->getMessage());
        }
    }
}
```