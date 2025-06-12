---
description: 
globs: 
alwaysApply: true
---
# Repository 레이어 문서

## 1. 역할 (Role)
- 데이터베이스와 직접 상호작용하여 CRUD 및 쿼리 로직을 담당한다.
- Eloquent 모델을 기반으로 데이터를 조회, 생성, 수정, 삭제하는 기능을 제공한다.
- 데이터 접근을 추상화하여 비즈니스 로직과 데이터 접근 로직을 분리한다.

## 2. 책임 (Responsibility)
- Model을 통해 데이터베이스에 접근하여 데이터를 조작한다.
- 단순한 조건 쿼리부터 복합 조건 조회, 집계 연산까지 수행한다.
- Service로부터 받은 요청에 따라 필요한 데이터를 반환한다.
- 인터페이스를 통해 데이터 접근 계층을 추상화한다.

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 'app/Base' 아래의 BaseRepository, BaseRepositoryInterface 의 기존 메소드 수정금지, 필요시 추가 가능
- 모든 쿼리는 `$this->query()` 또는 Scope를 통해 시작한다
- DB 파사드 직접 사용 금지 (트랜잭션은 Orchestrator 에서만 수행)
- Model 클래스를 직접 생성하거나 조작하지 않으며, query builder 방식 사용
- 반환 타입은 명확하게 정의한다 (예: Collection, ?Model 등)
- 모든 Repository는 인터페이스를 구현해야 한다

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치:
  - 인터페이스: `app/Repositories/Interfaces/{EntityName}RepositoryInterface.php`
  - 구현체: `app/Repositories/Eloquent/{EntityName}Repository.php`
- 도메인별 구분이 필요한 경우:
  - 인터페이스: `app/Repositories/Interfaces/{Domain}/{EntityName}RepositoryInterface.php`
  - 구현체: `app/Repositories/Eloquent/{Domain}/{EntityName}Repository.php`
- 파일명:
  - 인터페이스: `{EntityName}RepositoryInterface.php` (EntityName을 파일명으로 사용)
  - 구현체: `{EntityName}Repository.php` (EntityName을 파일명으로 사용)
- Base 클래스 상속: `App\Base\BaseRepository`
- 인터페이스 구현: `App\Repositories\Interfaces\{EntityName}RepositoryInterface`
- 네임스페이스:
  - 인터페이스: `App\Repositories\Interfaces` 또는 `App\Repositories\Interfaces\{Domain}`
  - 구현체: `App\Repositories\Eloquent` 또는 `App\Repositories\Eloquent\{Domain}`
- 의존성 주입 예시:
```php
// 모델 의존성 주입
use App\Models\{EntityName};
// 또는 도메인 내 모델인 경우
use App\Models\{Domain}\{EntityName};
```

### 3.3 쿼리 조건 작성 규칙 (Query Rules)
- 메소드 내에서 Query Builder 조건절 메서드를 사용할 수 없다
- 메소드 내에서 Model의 scope 만 사용 할 수 있다
- 해당 scope가 없으면 Model에 Local Scope 생성 후 사용한다

## 4. 메소드 템플릿 예시
```php
public function findById(int $id): ?Model
{
    return $this->query()->find($id);
}

public function findAll(): Collection
{
    return $this->query()->get();
}

public function create(array $data): Model
{
    $this->checkDataIntegrity($data);
    return $this->query()->create($data);
}

public function update(int $id, array $data): bool
{
    $this->checkDataIntegrity($data);
    return $this->query()->where('id', $id)->update($data);
}

public function delete(int $id): bool
{
    return $this->query()->where('id', $id)->delete();
}

protected function checkDataIntegrity(array $data): void
{
    // 데이터 무결성 검사 로직
}
```

## 5. 주요 메소드 유형

### 기본 CRUD
- `findById(int $id): ?Model`
- `findAll(): Collection`
- `paginate(int $perPage): LengthAwarePaginator`
- `create(array $data): Model`
- `update(int $id, array $data): bool`
- `delete(int $id): bool`

### 조회 메소드
- `findByField(string $attribute, $value): Collection`
- `findBy{Column}(...): Model`
- `findAllBy{Column}(...): Collection`
- `getBy{Condition}(...): Model`

### 집계 메소드
- `countBy{Condition}(...): int`
- `sumBy{Column}(...): int`
- `calculateBy{Logic}(...): int`

### 존재 확인 메소드
- `existsBy{Condition}(...): bool`
- `hasAny{Condition}(...): bool`

### 업데이트 관련
- `updateBy{Condition}(...): bool`
- `incrementBy{Column}(...): bool`
- `decrementBy{Column}(...): bool`

## 6. 상호작용
- 리포지토리는 자신의 도메인 내에서만 사용되며, 다른 도메인과 직접 호출하지 않는다
- 다른 도메인의 데이터가 필요한 경우 Service 레이어를 통해 접근
- 동일한 데이터 접근 로직이 필요한 경우에는 공통 인터페이스를 정의하여 공유

