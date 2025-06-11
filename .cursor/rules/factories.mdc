---
description: 
globs: 
alwaysApply: true
---
# Factory 레이어 문서

## 1. 역할 (Role)
- 모델에 대한 테스트 데이터 생성을 담당한다.
- Seeder와 함께 사용되어 대량의 더미 데이터를 효율적으로 생성한다.
- 테스트 코드에서 사용할 모델 인스턴스를 생성한다.

## 2. 책임 (Responsibility)
- 모델별 더미 데이터 생성 규칙 정의
- 관계를 가진 모델 데이터 함께 생성
- 다양한 상태의 모델 인스턴스 생성 지원
- 테스트 데이터의 일관성 유지

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 각 모델은 자신만의 Factory 클래스를 가진다
- Faker 라이브러리를 사용하여 실제와 유사한 데이터 생성
- 모델 간 관계는 Factory 내에서 정의하여 연결
- 상태(state) 메소드를 통해 다양한 조건의 모델 인스턴스 생성 지원

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치: `database/factories/`
- 파일명: `{EntityName}Factory.php` (EntityName을 파일명으로 사용)
- 클래스명: `{EntityName}Factory`
- 네임스페이스: `Database\Factories`
- 의존성 주입 예시:
```php
// 모델 의존성 주입
use App\Models\{EntityName};

// 다른 모델 의존성 주입
use App\Models\{OtherEntityName};
```

## 4. 구조 (Structure)

### 폴더 구조
```
database/
└── factories/
    ├── UserFactory.php
    ├── RoleFactory.php
    ├── PdfTemplateFactory.php
    ├── ResourceFactory.php
    └── ResourceBookingFactory.php
```

## 5. 구현 예시
```php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * 팩토리와 연결된 모델 클래스 이름
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * 모델의 기본 상태 정의
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
            'status' => 'active',
        ];
    }

    /**
     * 이메일 미인증 상태 지정
     *
     * @return static
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * 관리자 권한 상태 지정
     *
     * @return static
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }
}
```

## 6. 사용 예시
```php
// 단일 인스턴스 생성
$user = User::factory()->create();

// 상태를 지정하여 생성
$admin = User::factory()->admin()->create();
$unverified = User::factory()->unverified()->create();

// 다수의 인스턴스 생성
$users = User::factory()->count(10)->create();

// 관계를 가진 모델 함께 생성
$user = User::factory()
    ->has(Post::factory()->count(3))
    ->create();
```

## 7. 모델 클래스 설정
각 모델 클래스는 Factory를 사용하기 위해 다음과 같이 설정해야 합니다:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Base\BaseModel;
use Database\Factories\UserFactory;

class User extends BaseModel
{
    use HasFactory;

    /**
     * 모델과 연결된 팩토리 클래스 지정
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
```

## 8. 주의사항
- Factory에서는 실제 비즈니스 로직을 호출하지 않고 DB에 직접 데이터를 넣는 방식 사용
- 민감한 데이터(비밀번호 등)는 가짜 데이터로 생성하되 형식은 실제와 동일하게 유지
- 관계가 있는 모델은 Factory 간 연계를 통해 일관성 있는 데이터 생성
- 테스트 환경에서 반복 가능한 결과를 위해 시드값 고정 가능(`$this->faker->seed(1234)`)
