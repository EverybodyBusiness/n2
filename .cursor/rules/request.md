---
description: 
globs: 
alwaysApply: true
---
# Request 레이어 문서

## 1. 역할 (Role)
- HTTP 요청에서 전달된 데이터를 유효성 검사하고 필터링하는 책임을 가진다.
- Controller에 전달되기 전에 요청의 정확성을 검증하여 비즈니스 로직의 안정성을 보장한다.

## 2. 책임 (Responsibility)
- 필수 필드 여부, 데이터 형식, 범위 등의 유효성 검사 수행
- 인증된 사용자만 요청 가능한 경우 권한 로직 포함
- validated() 메소드를 통해 검증된 데이터만 반환

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 모든 API 요청은 FormRequest 클래스를 사용하여 검증한다
- rules(), authorize(), messages() 메소드를 명확히 정의한다
- 기본적으로 authorize()는 true 반환, 보안 필요 시 사용자 조건 삽입
- rules()는 PSR-12 코드 스타일로 작성하며, 필드별로 주석 첨부 가능
- 검증 실패 시 자동으로 422 상태 코드와 오류 메시지를 반환한다

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치: 
  - `app/Http/Requests/{EntityName}/{Action}{EntityName}Request.php`
- 도메인별 구분이 필요한 경우:
  - `app/Http/Requests/{Domain}/{EntityName}/{Action}{EntityName}Request.php`
- 파일명: `{Action}{EntityName}Request.php`
- 클래스 상속: `Illuminate\Foundation\Http\FormRequest`
- 네임스페이스: `App\Http\Requests\{EntityName}` 또는 `App\Http\Requests\{Domain}\{EntityName}`
- 의존성 주입 예시:
```php
// 모델 클래스
use App\Models\{EntityName};
// 또는 도메인 모델인 경우
use App\Models\{Domain}\{EntityName};

// 다른 모델 클래스
use App\Models\{OtherEntityName};
// 또는 다른 도메인 모델인 경우
use App\Models\{OtherDomain}\{OtherEntityName};
```
- 기본 포맷:
```php
namespace App\Http\Requests\{EntityName};
// 또는 도메인이 있는 경우
namespace App\Http\Requests\{Domain}\{EntityName};

use Illuminate\Foundation\Http\FormRequest;

class {Action}{EntityName}Request extends FormRequest
{
    /**
     * 요청에 대한 권한 부여 판단
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 유효성 검사 규칙 정의
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // 'field' => 'required|string',
        ];
    }
    
    /**
     * 유효성 검사 메시지 커스터마이징
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            // 'field.required' => '필드명은 필수 입력 항목입니다.',
        ];
    }
    
    /**
     * 필드명 한글화 처리
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            // 'field' => '필드명',
        ];
    }
}
```

## 4. 주요 메소드 설명

### authorize()
- 요청이 인증되었는지 검사 (기본은 true)
- 필요 시 사용자 권한 체크 가능
```php
public function authorize(): bool
{
    // 인증된 사용자만 접근 가능
    return auth()->check();
    
    // 특정 역할을 가진 사용자만 접근 가능
    return auth()->user() && auth()->user()->hasRole('admin');
}
```

### rules()
- 유효성 검사 규칙 배열 반환
- 필드명 => 'required|type' 형식으로 작성
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
        'role_id' => 'required|exists:roles,id',
    ];
}
```

### messages() (선택적)
- 사용자 정의 오류 메시지 지정
```php
public function messages(): array
{
    return [
        'email.unique' => '이미 사용 중인 이메일 주소입니다.',
        'password.confirmed' => '비밀번호 확인이 일치하지 않습니다.',
    ];
}
```

### attributes() (선택적)
- 필드명 한글화 처리
```php
public function attributes(): array
{
    return [
        'name' => '이름',
        'email' => '이메일',
        'password' => '비밀번호',
    ];
}
```

## 5. 사용 예시 (Controller 내부)
```php
public function store(CreateBookingRequest $request): JsonResponse
{
    return $this->executeOrchestrator($request, 'create');
}
```

## 6. Request 그룹화
- 각 EntityName에 대한 Request 클래스는 Requests/{EntityName} 디렉토리에 위치
- 도메인별 Request는 Requests/{Domain}/{EntityName} 디렉토리에 위치
- 파일명은 액션과 엔티티명을 반영하여 명확하게 지정 (예: CreateUserRequest, UpdateBookingRequest)
```
app/Http/
└── Requests/
    ├── User/        # 사용자 관련 Request들
    │   ├── LoginRequest.php
    │   ├── RegisterRequest.php
    │   └── UpdateProfileRequest.php
    ├── Blog/        # 블로그 도메인 Request들
    │   ├── Post/    # 블로그 포스트 관련
    │   │   ├── CreatePostRequest.php
    │   │   └── UpdatePostRequest.php
    │   └── Comment/ # 블로그 댓글 관련
    │       ├── CreateCommentRequest.php
    │       └── UpdateCommentRequest.php
    ├── Shop/        # 쇼핑 도메인 Request들
    │   ├── Product/ # 상품 관련
    │   │   ├── CreateProductRequest.php
    │   │   └── UpdateProductRequest.php
    │   └── Order/   # 주문 관련
    │       ├── CreateOrderRequest.php
    │       └── UpdateOrderRequest.php
    └── Callbot/     # 콜봇 도메인 Request들
        ├── Script/  # 스크립트 관련
        │   ├── CreateScriptRequest.php
        │   └── UpdateScriptRequest.php
        └── Call/    # 통화 관련
            ├── CreateCallRequest.php
            └── UpdateCallRequest.php
```

