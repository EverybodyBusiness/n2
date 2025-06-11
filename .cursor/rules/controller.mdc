---
description: 
globs: 
alwaysApply: true
---
# Controller 레이어 문서

## 1. 역할 (Role)
- HTTP 요청을 처리하고 응답을 반환하는 책임을 가진다.
- 요청 데이터의 유효성 검사를 수행한다.
- Orchestrator를 호출하여 비즈니스 로직을 위임 실행한다.
- 공통 실행 흐름은 `BaseController::executeOrchestrator()` 메소드를 통해 처리된다.

## 2. 책임 (Responsibility)
- 라우터로부터 전달된 요청 처리
- Request Form 클래스를 통한 Validation 처리
- Orchestrator에 요청을 위임하고 결과를 받는다
- 받은 결과를 JSON 형태로 반환한다 (Result::toResponse 사용)
- 예외 상황 발생 시 오류 응답 생성
- 공통 처리 로직(logging, validation, 변환 등)은 Orchestrator의 메소드에 위임

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- Controller 내에서는 Service 또는 Repository를 직접 호출하지 않는다
- 모든 요청은 반드시 FormRequest 클래스를 통해 유효성 검사를 거친다
- 비즈니스 로직은 절대 포함하지 않는다
- response()->json()을 직접 호출하지 않고, Result 객체의 toResponse() 메소드를 사용한다
- Web 용 Controller는 사용하지 않으며, API 전용 Controller만 사용한다
- 요청 실행은 `executeOrchestrator($request, 'actionName')` 형식으로 단순화하며 공통 흐름은 BaseController에서 처리한다

### 3.2 생성 규칙 (Creation Rules)
- Api Controller 파일 위치: `app/Http/Controllers/Api/{ControllerName}.php`
- Web Controller 파일 위치: `app/Http/Controllers/{ControllerName}.php`
- 도메인별 구분이 필요한 경우: `app/Http/Controllers/Api/{Domain}/{ControllerName}.php`
- 파일명: `{EntityName}Controller.php` (EntityName으로 파일명 사용)
- Base 클래스 상속: `App\Base\BaseController`
- Api 네임스페이스: `App\Http\Controllers\Api` 또는 `App\Http\Controllers\Api\{Domain}`
- Web 네임스페이스: `App\Http\Controllers`
- 의존성 주입 예시:
```php
// 오케스트레이터 주입
use App\Orchestrators\{EntityName}Orchestrator;
```

## 4. 메소드 템플릿
```php
/**
 * 메소드 설명
 *
 * @param App\Http\Requests\{EntityName}\{MethodName}Request $request
 * @return JsonResponse
 */
public function methodName(App\Http\Requests\{EntityName}\{MethodName}Request $request): JsonResponse
{
    return $this->executeOrchestrator($request, 'methodName');
}
```

## 5. 컨트롤러 구성
- 각 엔티티(Entity)에 대해 전용 컨트롤러를 생성한다
- 관련 API 엔드포인트는 같은 컨트롤러에 그룹화한다
- API 경로는 `/api/v1/{resource}` 구조를 따른다 (예: `/api/v1/users/login`)
- API 버전(v1, v2 등)을 명시적으로 URL에 포함한다
- 도메인별 구분이 필요한 경우 `/api/v1/{domain}/{resource}` 구조를 사용할 수 있다 (예: `/api/v1/blog/posts`)

## 6. 응답 처리
- 모든 응답은 Result 클래스를 통해 일관된 구조로 반환한다
- 성공 응답: `{ "success": true, "message": "...", "data": {...} }`
- 실패 응답: `{ "success": false, "message": "...", "error": {...} }`
- HTTP 상태 코드는 Result 객체에서 자동 처리한다
