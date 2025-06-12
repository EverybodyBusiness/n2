---
description: 
globs: 
alwaysApply: true
---
# Seeder 레이어 문서

## 1. 역할 (Role)
- 데이터베이스에 초기 데이터를 생성하는 책임을 가진다.
- 개발 및 테스트 환경에서 필요한 샘플 데이터를 제공한다.
- 각 도메인에서 필요로 하는 기본 데이터를 삽입한다.

## 2. 책임 (Responsibility)
- 초기 마스터 데이터 생성
- 테스트 데이터 생성
- 데이터 관계 설정
- Factory 클래스를 사용한 대량 데이터 생성

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 각 도메인에 필요한 Seeder를 관리한다
- 모든 Seeder는 트랜잭션으로 실행되어야 한다
- Seeder 간 의존성이 있을 경우 명시적으로 순서를 정의한다
- 환경별(개발, 테스트, 운영)로 다른 Seeder를 실행할 수 있어야 한다
- 데이터 삽입은 Model 또는 Repository를 통해 진행한다

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치: `database/seeders/`
- 파일명: `{EntityName}Seeder.php` (EntityName을 파일명으로 사용)
- 클래스명: `{EntityName}Seeder`
- 네임스페이스: `Database\Seeders`
- 의존성 주입 예시:
```php
// 모델/리포지토리 의존성 주입
use App\Models\{EntityName};
use App\Repositories\Interfaces\{EntityName}RepositoryInterface;
```

## 4. 구조 (Structure)

### 폴더 구조
```
database/
└── seeders/
    ├── DatabaseSeeder.php
    ├── UserSeeder.php
    ├── RoleSeeder.php
    ├── PdfTemplateSeeder.php
    ├── ResourceSeeder.php
    └── ResourceBookingSeeder.php
```

## 5. 구현 예시
```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * 사용자 초기 데이터 생성
     *
     * @return void
     */
    public function run(): void
    {
        DB::transaction(function () {
            // 관리자 계정 생성
            User::create([
                'name' => '관리자',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]);

            // 테스트 사용자 생성
            User::factory()->count(10)->create();
        });
    }
}
```

## 6. Seeder 실행 명령어
```
# 특정 Seeder 실행
php artisan db:seed --class=UserSeeder

# 모든 Seeder 실행 (DatabaseSeeder가 모든 Seeder를 호출)
php artisan db:seed
```

## 7. 주의사항
- 운영 환경에서는 필수 마스터 데이터만 Seeder로 삽입
- 중복 데이터 방지를 위해 firstOrCreate 또는 updateOrCreate 메소드 활용
- 대량의 테스트 데이터는 Factory를 활용하여 생성
- Seeder 실행 순서를 고려하여 의존성 있는 데이터는 순차적으로 생성
