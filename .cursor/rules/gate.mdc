---
description: 
globs: 
alwaysApply: true
---
# 모델 생성 시 Gate 및 권한 통합 규칙

## 1. Gate 생성 규칙
- 모든 Model에 대응하는 Gate 클래스가 반드시 생성되어야 함
- 위치: `app/Modules/{ModuleName}/Gates/{ModelName}Gate.php`
- 기본 권한: view, viewAny, create, update, delete

## 2. 서비스 프로바이더 등록 규칙
- 모든 Gate는 `app/Modules/System/Providers/GateServiceProvider.php`에 자동 등록
- 모든 권한은 `app/Modules/System/Providers/AuthServiceProvider.php`에 등록

## 3. 권한 데이터 규칙
- 모델별 기본 권한은 `Permission` 테이블에 자동 등록
- `app/Modules/System/Database/Seeders/PermissionSeeder.php`에 추가

## 4. 구현 프로세스 자동화
모델 생성 시 다음 단계가 자동으로 수행되어야 합니다:

1. `{ModelName}Gate` 클래스 생성
2. `GateServiceProvider`에 Gate 등록
3. `AuthServiceProvider`에 정책 매핑 추가
4. `Permission` 테이블에 모델별 권한 추가
5. `PermissionSeeder`에 모델 권한 자동 추가

## 5. Gate 클래스 구조 템플릿

```php
namespace App\Modules\{ModuleName}\Gates;

use App\Modules\Base\BaseGate;
use App\Modules\System\Models\User;
use App\Modules\{ModuleName}\Models\{ModelName};

class {ModelName}Gate extends BaseGate
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('{model_name}.view_any');
    }

    public function view(User $user, {ModelName} $model): bool
    {
        return $user->hasPermission('{model_name}.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('{model_name}.create');
    }

    public function update(User $user, {ModelName} $model): bool
    {
        return $user->hasPermission('{model_name}.update');
    }

    public function delete(User $user, {ModelName} $model): bool
    {
        return $user->hasPermission('{model_name}.delete');
    }
}
```

## 6. GateServiceProvider 등록 템플릿

```php
// app/Modules/System/Providers/GateServiceProvider.php

public function boot()
{
    // 기존 코드...
    
    // {ModelName} Gate 등록
    Gate::define('{model_name}.view_any', [{ModelName}Gate::class, 'viewAny']);
    Gate::define('{model_name}.view', [{ModelName}Gate::class, 'view']);
    Gate::define('{model_name}.create', [{ModelName}Gate::class, 'create']);
    Gate::define('{model_name}.update', [{ModelName}Gate::class, 'update']);
    Gate::define('{model_name}.delete', [{ModelName}Gate::class, 'delete']);
}
```

## 7. AuthServiceProvider 등록 템플릿

```php
// app/Modules/System/Providers/AuthServiceProvider.php

protected $policies = [
    // 기존 코드...
    App\Modules\{ModuleName}\Models\{ModelName}::class => App\Modules\{ModuleName}\Gates\{ModelName}Gate::class,
];
```

## 8. PermissionSeeder 등록 템플릿

```php
// app/Modules/System/Database/Seeders/PermissionSeeder.php

public function run()
{
    $permissions = [
        // 기존 권한...
        
        // {ModelName} 권한
        [
            'name' => '{model_name}.view_any',
            'description' => '{ModelName} 목록 조회 권한',
            'module' => '{ModuleName}'
        ],
        [
            'name' => '{model_name}.view',
            'description' => '{ModelName} 상세 조회 권한',
            'module' => '{ModuleName}'
        ],
        [
            'name' => '{model_name}.create',
            'description' => '{ModelName} 생성 권한',
            'module' => '{ModuleName}'
        ],
        [
            'name' => '{model_name}.update',
            'description' => '{ModelName} 수정 권한',
            'module' => '{ModuleName}'
        ],
        [
            'name' => '{model_name}.delete',
            'description' => '{ModelName} 삭제 권한',
            'module' => '{ModuleName}'
        ]
    ];
    
    foreach ($permissions as $permission) {
        Permission::firstOrCreate(
            ['name' => $permission['name']],
            $permission
        );
    }
}
```

## 9. 모델 생성 시 자동화 절차

모델 생성 시 다음 작업이 자동화되어야 합니다:

1. `{ModelName}Gate` 클래스 자동 생성
2. `GateServiceProvider`에 Gate 정의 자동 추가
3. `AuthServiceProvider`에 정책 매핑 자동 추가
4. `PermissionSeeder`에 권한 데이터 자동 추가
5. 권한 데이터 자동 마이그레이션 실행 (개발 환경에 한함)

## 10. Gate 사용 예시

컨트롤러에서 Gate 사용 시:

```php
// app/Modules/{ModuleName}/Controllers/{ModelName}Controller.php

public function show(int $id)
{
    $model = $this->{modelName}Service->findById($id);
    
    // Gate 검사
    if (Gate::denies('{model_name}.view', $model)) {
        return Result::fail('권한이 없습니다.')->toResponse();
    }
    
    return $this->executeOrchestrator($request, 'show');
}
```
