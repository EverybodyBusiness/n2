---
description: 
globs: 
alwaysApply: true
---
# Orchestrator 레이어 규칙 (handle 통합형 기준)

## 1. 역할 (Role)
- 비즈니스 흐름을 조정하는 책임을 가진다.
- 트랜잭션, 예외 처리, 복수 서비스 호출, 복잡한 프로세스 관리를 담당한다.
- Service 레이어에 단일 책임을 유지시키기 위해 흐름 제어를 분리한다.
- **여러 Service 메소드를 조합하여 완전한 비즈니스 기능을 제공한다.**
- **다른 도메인 간 통신을 조율한다.**
- **도메인 이벤트 발행 및 필요 시 작업 큐 사용을 담당한다.**
- **BaseOrchestrator는 EventDispatcher를 내장 제공하여 모든 자식 Orchestrator에서 이벤트 발행 기능을 사용할 수 있다.**

## 2. 책임 (Responsibility)
- Controller로부터 전달된 요청을 받아 지정된 비즈니스 액션을 실행한다.
- 액션별로 `execute{Action}(array $data): Result` 메소드를 제공하고, 내부에서 `flow()`로 트랜잭션과 예외를 통제한다.
- 모든 실제 비즈니스 로직은 `flow()` 안에서 직접 처리한다.
- Service 메소드를 호출하여 필요한 데이터를 가공하거나 상태를 변경한다.
- 중복 호출, 상태 누락 등을 방지하기 위해 요청 흐름을 명확히 통제한다.
- **비즈니스 로직의 순서와 조건을 결정하는 흐름 제어를 담당한다.**
- **여러 도메인의 서비스들을 조합하여 완전한 기능을 제공한다.**
- **비즈니스 로직 실행 후 도메인 이벤트를 발행하여 후속 처리를 시작한다.**

## 3. 규칙 (Rules)

### 3.1 메소드 규칙
- 'app/Base' 아래의 BaseOrchestrator의 기존 메소드 수정금지, 필요시 추가 가능
- 모든 비즈니스 액션은 `execute{Action}(array $data): Result` 형태로 정의한다.
- `execute{Action}` 내부에서는 반드시 `flow(fn() => {로직})` 형태로 흐름을 실행한다.
- `flow()` 내부는 단일 Closure로 작성하고, 검증 → 처리 → 후처리를 자연스럽게 이어간다.
- **단순히 Service 메서드를 호출하기만 하는 '패스스루(Pass-through)' 메서드는 금지한다.**
- **모든 Orchestrator 메서드는 최소 두 개 이상의 논리적 단계를 포함해야 한다.**

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치: `app/Orchestrators/{EntityName}Orchestrator.php`
- 파일명: `{EntityName}Orchestrator.php` (EntityName으로 파일명 사용)
- Base 클래스 상속: `App\Base\BaseOrchestrator`
- 네임스페이스: `App\Orchestrators`
- 의존성 주입:
```php
// 서비스 의존성 주입
use App\Services\Interfaces\{EntityName}ServiceInterface;

// 다른 도메인의 서비스 의존성 주입
use App\Services\Interfaces\{OtherEntity}ServiceInterface;

// 이벤트 디스패처 주입
use App\Base\Events\EventDispatcher;
```

### 3.2.1 생성자 구현 규칙
- 모든 Orchestrator는 반드시 생성자에서 부모 클래스(BaseOrchestrator)의 생성자를 호출하여 EventDispatcher를 전달해야 한다
```php
/**
 * {EntityName}Orchestrator 생성자
 *
 * @param {EntityName}ServiceInterface $service
 * @param EventDispatcher $eventDispatcher
 */
public function __construct(
    {EntityName}ServiceInterface $service,
    EventDispatcher $eventDispatcher
) {
    parent::__construct($eventDispatcher);
    $this->service = $service;
}
```
- 여러 서비스를 사용하는 경우에도 동일하게 EventDispatcher를 부모 생성자에 전달한다
```php
/**
 * {EntityName}Orchestrator 생성자
 *
 * @param {EntityName}ServiceInterface $primaryService
 * @param {OtherEntity}ServiceInterface $otherService
 * @param EventDispatcher $eventDispatcher
 */
public function __construct(
    {EntityName}ServiceInterface $primaryService,
    {OtherEntity}ServiceInterface $otherService,
    EventDispatcher $eventDispatcher
) {
    parent::__construct($eventDispatcher);
    $this->primaryService = $primaryService;
    $this->otherService = $otherService;
}

### 3.3 흐름 제어 규칙
- 이메일 중복 체크, 권한 검증 등의 "상태 검증"은 흐름 초반에 수행한다.
- 처리 로직은 Service 레이어에 위임하되, 호출은 Orchestrator가 직접 관리한다.
- 후처리가 필요한 경우(예: 알림 발송)는 flow() 내부 마지막에 처리한다.
- 흐름 중간에 실패 시, `Result::fail()`로 즉시 중단하고 반환한다.
- **모든 비즈니스 흐름은 다음 3단계 구조를 명시적으로 따라야 한다:**
  ```
  1. 검증 단계: 전제 조건 확인, 중복 검사, 권한 확인 등
  2. 실행 단계: Service 메서드 호출을 통한 핵심 작업 수행
  3. 후처리 단계(선택적): 알림, 로깅, 추가 상태 업데이트 등이 요구
  ```
- **단순 데이터 조회의 경우에도 권한 검증이나 데이터 가공 로직을 추가해야 한다.**
- **다른 도메인과의 통합이 필요한 경우, Orchestrator가 이를 조율한다.**
- **도메인 이벤트는 핵심 비즈니스 로직이 성공적으로 완료된 후에 발행한다.**

### 3.4 트랜잭션 및 예외 처리 규칙
- `flow()`는 항상 트랜잭션을 시작하고, 결과에 따라 commit/rollback을 자동으로 처리한다.
- Throwable 예외 발생 시 자동 rollback 및 실패 Result 반환한다.
- Orchestrator 코드 안에서는 try-catch를 직접 작성하지 않는다 (flow가 담당).
- **모든 Service 호출 결과는 성공 여부를 명시적으로 확인해야 한다.**
- **특히 여러 Service 메서드가 연속 호출될 때는 중간 결과 검증이 필수적이다.**

### 3.5 이벤트 및 작업 패턴 사용 규칙
- **이벤트 발행 책임**: 도메인 이벤트 발행은 Orchestrator에서 담당한다.
- **이벤트 발행 시점**: 비즈니스 로직이 성공적으로 완료된 후 이벤트를 발행한다.
- **작업 큐 사용 시점**: 자원 집약적이거나 시간이 오래 걸리는 작업은 큐를 통해 처리한다.
- **이벤트 객체 생성**: `new {Action}Event(데이터)` 형식으로 이벤트 객체를 생성한다.
- **이벤트 발행 방법**: `$this->eventDispatcher->dispatch(이벤트객체)` 형식으로 발행한다.
- **작업 큐 사용 방법**: `{Action}Job::dispatch(데이터)` 형식으로 작업을 큐에 추가한다.
- **패턴 선택 기준**:
  - 상태 변화 알림 → 이벤트 패턴
  - 무거운 처리 작업 → 작업 큐 패턴

## 4. 금지 항목 (Prohibited Items)
- `before{Action}`, `process{Action}`, `after{Action}` 메소드 사용 금지 (handle 통합 원칙 위배)
- 트랜잭션을 수동으로 시작하거나 commit/rollback 직접 작성 금지
- Service를 우회하여 직접 Repository 호출 금지 (Service 레이어 거쳐야 함)
- 복수 Service 호출 시 검증 없이 상태 전이 금지 (항상 중간 결과 검증할 것)
- 흐름 중간에 Result 대신 예외 throw 금지 (오직 Result 반환)
- **단순 패스스루(Service 메서드 직접 반환) 패턴 금지**
- **단일 Service 메서드만 호출하는 흐름 구현 금지**
- **Service 내에서 이벤트 발행 금지 (Orchestrator의 책임으로 이동)**
- **이벤트 핸들러에서 다른 이벤트 발행 금지 (순환 의존성 방지)**

## 5. 올바른 Orchestrator 예시

```php
// 올바른 예시: 검증 → 처리 → 후처리 단계가 명확함
public function executeRegister(array $data): Result
{
    return $this->flow(function () use ($data) {
        // 1. 검증 단계
        if ($this->userService->isEmailExists($data['email'])) {
            return Result::fail('이미 등록된 이메일입니다.');
        }

        // 2. 실행 단계
        $result = $this->userService->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        if (!$result->isSuccess()) {
            return $result;
        }

        // 3. 후처리 단계(선택적) - 도메인 이벤트 발행
        $user = $result->getData();
        $this->eventDispatcher->dispatch(
            new UserRegisteredEvent([
                'user_id' => $user['id'],
                'email' => $user['email']
            ])
        );

        return Result::success('회원가입 완료', $result->getData());
    });
}

// 올바른 예시: 이메일 템플릿 처리 흐름
public function executeSendEmailWithTemplate(array $data): Result
{
    return $this->flow(function () use ($data) {
        // 1. 검증 단계
        $template = $this->emailService->findTemplateByName($data['template_name']);
        if (!$template) {
            return Result::fail("템플릿을 찾을 수 없습니다: {$data['template_name']}");
        }

        if ($this->emailService->isBlockedEmail($data['to'])) {
            return Result::fail("차단된 이메일 주소입니다");
        }

        // 2. 실행 단계
        // 변수 치환 처리
        $processedTemplate = $this->emailService->processTemplateVariables(
            $template,
            $data['variables'] ?? []
        );

        // 이메일 전송
        $emailResult = $this->emailService->sendEmail(
            $data['to'],
            $processedTemplate['subject'],
            $processedTemplate['body'],
            $data['options'] ?? []
        );

        if (!$emailResult->isSuccess()) {
            return $emailResult;
        }

        // 3. 후처리 단계(선택적)
        // 이메일 전송 이벤트 발행
        $this->eventDispatcher->dispatch(
            new EmailSentEvent([
                'log_id' => $emailResult->getData('log_id'),
                'template_id' => $template->id,
                'recipient' => $data['to']
            ])
        );

        return Result::success('템플릿 이메일 전송 성공', [
            'email_id' => $emailResult->getData('log_id'),
            'template' => $template->name
        ]);
    });
}

// 올바른 예시: 여러 도메인 서비스 조합 및 큐 작업 사용
public function executeCreateResourceWithNotification(array $data): Result
{
    return $this->flow(function () use ($data) {
        // 1. 검증 단계
        if (!$this->resourceService->isValidResourceType($data['type'])) {
            return Result::fail('유효하지 않은 리소스 유형입니다.');
        }
        
        // 2. 실행 단계 - Resource 도메인 서비스 호출
        $resourceResult = $this->resourceService->createResource($data);
        
        if (!$resourceResult->isSuccess()) {
            return $resourceResult;
        }
        
        // 3. 다른 도메인 서비스 호출 - Booking 서비스
        $bookingData = [
            'resource_id' => $resourceResult->getData('id'),
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
        ];
        
        $bookingResult = $this->bookingService->initializeBookingSlots($bookingData);
        
        if (!$bookingResult->isSuccess()) {
            return $bookingResult;
        }
        
        // 4. 이벤트 발행 (상태 변화 알림)
        $this->eventDispatcher->dispatch(
            new ResourceCreatedEvent([
                'resource_id' => $resourceResult->getData('id'),
                'type' => $data['type'],
                'booking_slots' => $bookingResult->getData('slots')
            ])
        );
        
        // 5. 무거운 작업은 큐 작업으로 처리
        if (isset($data['process_attachments']) && $data['process_attachments'] === true) {
            ProcessResourceAttachmentsJob::dispatch([
                'resource_id' => $resourceResult->getData('id'),
                'attachments' => $data['attachments'] ?? []
            ]);
        }
        
        return Result::success('리소스가 생성되었습니다.', [
            'resource' => $resourceResult->getData(),
            'booking_slots' => $bookingResult->getData()
        ]);
    });
}
```

## 6. 잘못된 Orchestrator 예시 (안티패턴)

```php
// 안티패턴: 단순 패스스루
public function executeGetAllTemplates(array $data): Result
{
    return $this->flow(function () {
        // Service 메서드를 직접 반환하는 것은 Orchestrator의 역할 미흡
        return $this->emailService->getAllTemplates();
    });
}

// 안티패턴: 로직 부재
public function executeSendEmail(array $data): Result
{
    return $this->flow(function () use ($data) {
        $to = $data['to'];
        $subject = $data['subject'];
        $body = $data['body'];
        $options = $data['options'] ?? [];

        // 검증 단계 없이 바로 Service 호출
        return $this->emailService->sendEmail($to, $subject, $body, $options);
    });
}

// 안티패턴: Service에서 이벤트 발행
public function executeRegisterUser(array $data): Result
{
    return $this->flow(function () use ($data) {
        // Service에서 이벤트를 발행하면 안 됨 (SRP 위반)
        // 이벤트 발행은 Orchestrator의 책임
        $result = $this->userService->registerUserAndDispatchEvent($data);
        
        return $result;
    });
}
```

## 7. 도메인 간 상호작용 규칙

### 7.1 같은 도메인 내 서비스 간 통신
- 같은 도메인 내 여러 서비스의 메소드를 Orchestrator에서 조합하여 호출
- 예: `Auth` 도메인의 `AuthOrchestrator`는 같은 도메인의 `UserService`와 `TokenService`를 조합할 수 있음

### 7.2 다른 도메인 간 통신
- 다른 도메인의 Service는 반드시 Orchestrator 레벨에서만 조합
- 예: `Auth` 도메인의 `AuthOrchestrator`에서 `Notification` 도메인의 `EmailService` 호출 가능
- 다른 도메인의 서비스 호출 시에는 인터페이스를 통해 접근하는 것이 원칙
- 서비스 인터페이스는 `App\Services\Interfaces` 네임스페이스에 위치

