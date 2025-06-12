---
description: 
globs: 
alwaysApply: true
---
# 테스트 규칙 문서

## 1. 일반 규칙
- 사용자 요청으로 테스트가 시작되면 계속해서 오류를 해결한다
- 모든 테스트는 표준 Laravel 구조에 맞게 `tests/` 디렉토리에 작성한다
- Feature 테스트는 `tests/Feature/` 디렉토리에 작성한다
- Feature 테스트를 Postman에서 테스트 할 수 있도록 `tests/Postman/` 에 Postman 용 테스트 파일을 생성한다
- Unit 테스트는 `tests/Unit/` 디렉토리에 작성한다
- 각 도메인은 자신의 테스트를 독립적으로 관리한다
- 테스트 클래스는 테스트 대상 클래스명 + Test 접미사를 사용한다 (예: UserControllerTest)


## 2. 테스트 네이밍 규칙
- 테스트 메소드는 `test_` 접두사를 사용한다
- 테스트 메소드 이름은 `test_액션명_상황_기대결과` 패턴을 따른다
  (예: `test_register_with_invalid_email_fails`, `test_login_with_valid_credentials_succeeds`)
- 네임스페이스는 `Tests\{Feature|Unit}` 형식을 따른다

## 3. 폴더 구조
```
tests/
├── Feature/
│   ├── Auth/
│   │   └── AuthControllerTest.php
│   └── Util/
│       └── PdfControllerTest.php
├── Unit/
│   ├── Auth/
│   │   └── UserServiceTest.php
│   └── Util/
│       └── PdfGeneratorTest.php
└── Postman/
    ├── Auth.json
    └── Util.json
```

## 4. 테스트 전제 (Base Assumption)
- 모든 API 응답은 Result 객체(`success`, `message`, `data`, `error`) 형태로 반환된다
- 기본 Laravel Validation Exception 등의 오류도 Result로 포장되어 리턴된다

## 5. 테스트 작성 규칙 (Testing Rules)

### ✅ Rule 1. HTTP 상태코드 검증
- **성공 여부와 관계없이** 상태코드는 반드시 명시적으로 체크해야 한다
- 단, 실패 테스트에서는 `assertStatus(200)`이 아님만 검증하는 방식 허용
- 성공 여부와 상관없이 message를 출력한다
```php
$response->assertStatus(200); // 예: 정상 요청 시
$response->assertStatus(function ($status) {
    return $status !== 200; // 예: 실패 시는 200이 아님
});
```

### ✅ Rule 2. 성공 여부만 검증 (간결 기준 적용)
```php
// 성공 시
$response->assertJson([
    'success' => true
]);

// 실패 시
$response->assertJson([
    'success' => false
]);
```

### ✅ Rule 3. 메시지/에러/데이터는 선택 검증
- 필요 시 message 또는 error.message만 추가 확인
- 기본 검증은 생략 가능하며, 구체 검증은 개별 테스트 목적에 따라 수행

## 6. 테스트 구현 예시

### 예시 1: Feature 테스트 (Controller 테스트)
```php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_requires_email()
    {
        $response = $this->postJson('/api/v1/auth/users/register', [
            'password' => 'password123'
        ]);
        $response->assertStatus(function ($status) {
            return $status !== 200;
        });

        $response->assertJson([
            'success' => false
        ]);
    }

    public function test_login_success()
    {
        // 사용자 생성
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/auth/users/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }
}
```

### 예시 2: Unit 테스트 (Service 테스트)
```php
namespace Tests\Unit\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Implementations\AuthService;
use App\Models\User;
use App\Support\Result;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    public function test_validate_credentials_with_valid_data_returns_success()
    {
        // 사용자 생성
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123')
        ]);

        $result = $this->authService->validateCredentials([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());
    }
}
```

## 7. 통합 테스트 구현 가이드

모듈 간 통합이 필요한 테스트의 경우, 통합 테스트를 위한 별도 디렉토리를 사용할 수 있습니다:

```
tests/
└── Integration/
    ├── Auth/
    │   └── AuthIntegrationTest.php
    └── Resource/
        └── ResourceIntegrationTest.php
```

## 8. 최종 정리 (Conclusion)
- 테스트는 표준 라라벨 구조를 따르며 각 도메인은 자신의 테스트를 관리한다
- 테스트는 간결함을 유지하기 위해 성공 시 `success: true`, 실패 시 `success: false`만 검증한다
- 실패 테스트는 `assertStatus !== 200`으로 간단하게 처리하며, 세부 메시지 검증은 선택사항이다
- 전체 구조는 Result 표준 포맷에 맞추되, 테스트의 목적에 따라 필요한 필드만 유연하게 검증한다
