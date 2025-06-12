---
description: 
globs: 
alwaysApply: true
---
# Model 레이어 문서

## 1. 역할 (Role)
- 데이터베이스 테이블 구조를 정의하고, Eloquent ORM을 통해 데이터 조작을 담당한다.
- 다른 모델과의 관계(Relations)를 정의한다.
- 데이터의 스코프, 변환, 접근자, 변경자 등을 관리한다.

## 2. 책임 (Responsibility)
- 데이터베이스 테이블에 매핑되는 클래스 정의
- fillable, hidden, casts 속성을 통한 안전한 데이터 관리
- 관계 메소드 정의 (hasOne, hasMany, belongsTo 등)
- 데이터 조회 스코프(Scopes) 제공
- 데이터 가공을 위한 Mutators/Accessors 제공

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 모든 모델은 공통 `App\Base\BaseModel`을 상속받아야 한다
- 모델 파일 내에서 쿼리 로직 작성 금지 (Repository에서 처리)
- 모델에 직접적인 비즈니스 로직 작성 금지
- 모든 컬럼에 대해 필요한 경우 casts를 정의한다
- fillable과 hidden 배열을 명시적으로 관리한다
- Model 파일에 scopes, relations, mutators, accessors만 작성 가능
- Model 이 생성되면 Permission 테이블에 Model에 viewAll, show, create, update, delete, restore, force-delete 권한 레코드를 추가한다
- Model 생성 후, 필요시 해당 모델의 Resource를 추가한다

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치: `app/Models/{EntityName}.php`
- 파일명: `{EntityName}.php` (EntityName을 파일명으로 사용)
- Base 클래스 상속: `App\Base\BaseModel`
- 네임스페이스: `App\Models`
- 의존성 주입:
```php
// 다른 모델
use App\Models\{OtherModel};
```

## 5. 주요 요소 작성 규칙

### fillable
- 대량 할당(Mass Assignment) 가능한 필드 지정
```php
protected $fillable = ['name', 'email', 'status'];
```

### hidden
- API 응답 시 숨겨야 할 필드 지정
```php
protected $hidden = ['password', 'remember_token'];
```

### casts
- 필드 타입 자동 변환 설정
```php
protected $casts = [
    'email_verified_at' => 'datetime',
    'is_active' => 'boolean',
];
```

### 관계(Relations)
- 모델 간 관계 정의
```php
// 다른 모델과의 관계
public function roles(): HasMany
{
    return $this->hasMany(App\Models\System\Role::class);
}

// 다른 모델과의 관계 (긴 경로는 완전 경로 사용)
public function notifications(): HasMany
{
    return $this->hasMany(App\Models\Notification::class);
}
```

### 스코프(Scopes)
- 쿼리 조건 재사용을 위한 메소드
- 기본 컬럼을 제외한 대부분의 컬럼에
```php
public function scopeActive($query)
{
    return $query->where('status', 'active');
}
```

### Mutators & Accessors
- 데이터 저장/조회 시 자동 변환
```php
public function setPasswordAttribute($value)
{
    $this->attributes['password'] = bcrypt($value);
}

public function getFullNameAttribute(): string
{
    return $this->first_name . ' ' . $this->last_name;
}
```

## 6. 주의사항
- 모델에서 DB 파사드(DB::) 직접 사용 금지
- 모델에 직접 트랜잭션 제어 로직 작성 금지
- 복잡한 쿼리는 반드시 Repository로 분리해서 작성
- 모델 간 의존성이 필요할 경우 항상 명시적으로 import 사용

