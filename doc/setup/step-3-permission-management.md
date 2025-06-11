# Step 3: 권한 관리 시스템 구축 (Spatie Permission + Filament Shield)

## 개요

Spatie Laravel Permission과 Filament Shield를 사용하여 강력한 역할 기반 접근 제어(RBAC) 시스템을 구축합니다. Shield는 Filament과 Spatie Permission을 완벽하게 통합하여 GUI 기반 권한 관리를 제공합니다.

## 주요 기능

- ✅ 역할(Role) 관리
- ✅ 권한(Permission) 관리
- ✅ 역할별 권한 할당
- ✅ 리소스별 자동 권한 생성
- ✅ Super Admin 역할
- ✅ 정책(Policy) 기반 권한 제어
- ✅ GUI 기반 권한 관리 인터페이스
- ✅ 다중 가드 지원

## 설치 과정

### 3.1 패키지 설치

```bash
composer require spatie/laravel-permission bezhansalleh/filament-shield -W
```

**설치되는 패키지:**
- `spatie/laravel-permission` v6.19.0
- `bezhansalleh/filament-shield` v3.3.6

### 3.2 Spatie Permission 설정

```bash
# 설정 파일 및 마이그레이션 발행
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 마이그레이션 실행
php artisan migrate
```

**생성되는 파일:**
- `config/permission.php` - 권한 설정
- `database/migrations/xxxx_create_permission_tables.php` - 권한 테이블

**생성되는 테이블:**
- `roles` - 역할
- `permissions` - 권한
- `model_has_roles` - 모델-역할 연결
- `model_has_permissions` - 모델-권한 연결
- `role_has_permissions` - 역할-권한 연결

### 3.3 Filament Shield 설치

```bash
# Shield를 admin 패널에 설치
php artisan shield:install admin
```

**결과:**
```
INFO  Shield plugin has been registered successfully!
INFO  Shield has been successfully configured & installed!
```

### 3.4 User 모델 업데이트

**파일:** `app/Models/User.php`

```php
<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles; // HasRoles 추가

    // ... 기존 코드

    /**
     * Determine if the user can access the Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // super_admin과 panel_user 역할을 가진 사용자만 접근 가능
        return $this->hasRole(['super_admin', 'panel_user']);
    }
}
```

### 3.5 Shield 플러그인 등록 확인

**파일:** `app/Providers/Filament/AdminPanelProvider.php`

```php
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ... 기존 설정
        ->plugins([
            FilamentShieldPlugin::make(), // 자동으로 추가됨
        ]);
}
```

### 3.6 권한 생성

```bash
# 모든 리소스에 대한 권한 생성
php artisan shield:generate --all

# 특정 리소스에 대한 권한 생성
php artisan shield:generate --resource=User

# 특정 페이지에 대한 권한 생성
php artisan shield:generate --page=Settings
```

**생성되는 권한 예시:**
- `view_role` - 역할 조회
- `view_any_role` - 역할 목록 조회
- `create_role` - 역할 생성
- `update_role` - 역할 수정
- `delete_role` - 역할 삭제
- `delete_any_role` - 역할 일괄 삭제

### 3.7 Shield 리소스 발행

```bash
php artisan shield:publish admin
```

**생성되는 파일:**
- `app/Filament/Resources/RoleResource.php` - 역할 관리 리소스
- `app/Filament/Resources/RoleResource/Pages/` - 역할 관리 페이지
- `app/Policies/RolePolicy.php` - 역할 정책

### 3.8 Super Admin 생성

```bash
# ID가 1인 사용자를 super_admin으로 설정
php artisan shield:super-admin --user=1
```

**결과:**
```
INFO  Success! admin@example.com may now log in at http://localhost/admin/login.
```

## 권한 시스템 구조

### 역할 계층

```
super_admin
├── 모든 권한 자동 보유
└── canAccessPanel() 자동 통과

panel_user (일반 관리자)
├── 할당된 권한만 보유
└── canAccessPanel() 통과

일반 사용자
└── 관리자 패널 접근 불가
```

### 권한 명명 규칙

Shield는 다음과 같은 규칙으로 권한을 생성합니다:

- **리소스**: `{action}_{resource}`
  - `view_user`, `create_user`, `update_user`, `delete_user`
- **페이지**: `page_{PageName}`
  - `page_Dashboard`, `page_Settings`
- **위젯**: `widget_{WidgetName}`
  - `widget_StatsOverview`, `widget_AccountWidget`

## 정책(Policy) 작동 방식

### 자동 생성된 Policy 예시

**파일:** `app/Policies/RolePolicy.php`

```php
<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_role');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('view_role');
    }

    public function create(User $user): bool
    {
        return $user->can('create_role');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('update_role');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('delete_role');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_role');
    }
}
```

## 사용 예제

### 역할 생성 및 권한 할당

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// 역할 생성
$role = Role::create(['name' => 'editor']);

// 권한 할당
$role->givePermissionTo([
    'view_any_post',
    'view_post',
    'create_post',
    'update_post',
]);

// 사용자에게 역할 할당
$user->assignRole('editor');
```

### 권한 확인

```php
// 단일 권한 확인
if ($user->can('edit_post')) {
    // 권한 있음
}

// 여러 권한 확인
if ($user->hasAllPermissions(['edit_post', 'delete_post'])) {
    // 모든 권한 있음
}

// 역할 확인
if ($user->hasRole('editor')) {
    // editor 역할 보유
}

// Super Admin 확인
if ($user->hasRole('super_admin')) {
    // 모든 권한 자동 보유
}
```

### Blade 템플릿에서 사용

```blade
@can('edit_post')
    <button>Edit Post</button>
@endcan

@role('admin')
    <a href="/admin">Admin Panel</a>
@endrole
```

## Shield 설정

### config/filament-shield.php

```php
return [
    'super_admin' => [
        'enabled' => true,
        'role_name' => 'super_admin',
    ],
    
    'panel_user' => [
        'enabled' => true,
        'role_name' => 'panel_user',
    ],
    
    'permissions_generator' => [
        'option' => 'policies_and_permissions',
        'include_custom_policy' => true,
    ],
    
    'entities' => [
        'pages' => true,
        'widgets' => true,
        'resources' => true,
        'custom_permissions' => false,
    ],
];
```

## 관리자 패널에서 권한 관리

### 역할 관리 페이지

1. 관리자 패널 접속: `http://localhost:8000/admin`
2. 좌측 메뉴에서 "Shield > Roles" 클릭
3. 역할 생성/수정/삭제 가능
4. 각 역할에 권한 체크박스로 할당

### 권한 할당 UI

Shield는 다음과 같은 UI를 제공합니다:

```
┌─ Role: Editor ────────────────────────┐
│                                        │
│ Posts                                  │
│ ☑ View Any    ☑ View                 │
│ ☑ Create      ☑ Update               │
│ ☐ Delete      ☐ Delete Any           │
│                                        │
│ Users                                  │
│ ☑ View Any    ☑ View                 │
│ ☐ Create      ☐ Update               │
│ ☐ Delete      ☐ Delete Any           │
│                                        │
└────────────────────────────────────────┘
```

## 고급 기능

### 커스텀 권한 추가

```php
// 커스텀 권한 생성
Permission::create(['name' => 'export_reports']);

// Shield UI에서 관리하려면
php artisan shield:generate --option=permissions
```

### 테넌트별 권한 (멀티테넌시)

```php
// 테넌트별 역할 생성
$role = Role::create([
    'name' => 'manager',
    'team_id' => $team->id,
]);

// 테넌트 컨텍스트에서 권한 확인
setPermissionsTeamId($team->id);
if ($user->can('edit_post')) {
    // 해당 팀에서 권한 있음
}
```

## 문제 해결

### 일반적인 문제

1. **"Call to undefined method hasRole()"**
   - 해결: User 모델에 `HasRoles` trait 추가 확인

2. **권한이 작동하지 않음**
   - 해결: `php artisan permission:cache-reset` 실행
   - `config/permission.php`의 캐시 설정 확인

3. **Super Admin이 권한 없음 오류**
   - 해결: `php artisan shield:super-admin --user={id}` 재실행

4. **새 리소스의 권한이 생성되지 않음**
   - 해결: `php artisan shield:generate --resource=ResourceName`

## 보안 모범 사례

1. **최소 권한 원칙**
   - 사용자에게 필요한 최소한의 권한만 부여

2. **역할 계층화**
   - 명확한 역할 계층 구조 설계

3. **권한 감사**
   - 정기적으로 권한 할당 검토

4. **Super Admin 제한**
   - Super Admin 계정은 최소한으로 유지

## 다음 단계

권한 관리 시스템이 구축되었으므로, 다음 기능들을 추가할 수 있습니다:
- 감사 로깅 (Audit Logging)
- 실시간 알림 (Real-time Notifications)
- 작업 큐 (Job Queue)

---

[← 이전: Step 2](./step-2-filament-admin.md) | [목차](./README.md) 