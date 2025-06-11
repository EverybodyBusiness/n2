---
description: 
globs: 
alwaysApply: true
---
# Livewire 컴포넌트 레이어 규칙

## 1. 핵심 원칙

### 1.1 도메인 기반 구조 필수
- **모든 Livewire 컴포넌트는 반드시 도메인 디렉토리 아래에 생성**
- 도메인 예시: System, Auth, Blog, Shop, Sample, Chat, Resource 등
- 도메인 없이 직접 `app/Livewire/` 아래에 컴포넌트 생성 금지
- 뷰 파일도 동일한 도메인 구조를 따라야 함

## 2. 위치 및 네이밍 규칙

### 2.1 컴포넌트 파일 위치
- 기본 위치: `app/Livewire/{Domain}/{ComponentName}.php`
- 서브 도메인: `app/Livewire/{Domain}/{SubDomain}/{ComponentName}.php`
- 파일명: `{ComponentName}.php` (Pascal Case 사용)
- 네임스페이스: `App\Livewire\{Domain}` 또는 `App\Livewire\{Domain}\{SubDomain}`

### 2.2 뷰 파일 위치
- 기본 위치: `resources/views/livewire/{domain}/{component-name}.blade.php`
- 서브 도메인: `resources/views/livewire/{domain}/{subdomain}/{component-name}.blade.php`
- 파일명: `{component-name}.blade.php` (kebab-case 사용)

## 3. 도메인별 구조 예시

### 3.1 전체 디렉토리 구조
```
app/Livewire/
├── System/               # 시스템 관련 컴포넌트
│   ├── UserManager.php
│   ├── Settings/
│   │   ├── GeneralSettings.php
│   │   └── SecuritySettings.php
│   └── Dashboard/
│       ├── SystemStats.php
│       └── UserActivity.php
├── Auth/                 # 인증 관련 컴포넌트
│   ├── LoginForm.php
│   ├── RegisterForm.php
│   └── TwoFactorAuth.php
├── Chat/                 # 채팅 관련 컴포넌트
│   ├── ChatList.php
│   ├── MessageBox.php
│   └── UserStatus.php
├── Blog/                 # 블로그 관련 컴포넌트
│   ├── PostList.php
│   ├── PostEditor.php
│   └── CommentSection.php
├── Shop/                 # 쇼핑 관련 컴포넌트
│   ├── ProductCatalog.php
│   ├── ShoppingCart.php
│   └── Checkout/
│       ├── PaymentForm.php
│       └── OrderSummary.php
├── Resource/             # 리소스 관련 컴포넌트
│   ├── ResourceList.php
│   ├── BookingCalendar.php
│   └── ResourceScheduler.php
└── Sample/               # 샘플/데모 컴포넌트
    ├── ExampleForm.php
    └── DemoComponent.php
```

### 3.2 뷰 디렉토리 구조
```
resources/views/livewire/
├── system/
│   ├── user-manager.blade.php
│   ├── settings/
│   │   ├── general-settings.blade.php
│   │   └── security-settings.blade.php
│   └── dashboard/
│       ├── system-stats.blade.php
│       └── user-activity.blade.php
├── auth/
│   ├── login-form.blade.php
│   ├── register-form.blade.php
│   └── two-factor-auth.blade.php
├── chat/
│   ├── chat-list.blade.php
│   ├── message-box.blade.php
│   └── user-status.blade.php
├── blog/
│   ├── post-list.blade.php
│   ├── post-editor.blade.php
│   └── comment-section.blade.php
├── shop/
│   ├── product-catalog.blade.php
│   ├── shopping-cart.blade.php
│   └── checkout/
│       ├── payment-form.blade.php
│       └── order-summary.blade.php
├── resource/
│   ├── resource-list.blade.php
│   ├── booking-calendar.blade.php
│   └── resource-scheduler.blade.php
└── sample/
    ├── example-form.blade.php
    └── demo-component.blade.php
```

## 4. 구현 규칙

### 4.1 기본 규칙
- 각 Livewire 컴포넌트는 관련 Service를 주입받아 사용해야 합니다
- 컴포넌트 내에서 직접 DB 접근이나 Repository 직접 호출은 금지됩니다
- 비즈니스 로직은 Service 레이어에 위임하고, 컴포넌트는 UI 상호작용과 데이터 바인딩에 집중해야 합니다
- 컴포넌트 간 통신은 이벤트를 사용합니다
- 모든 Livewire 컴포넌트는 ServiceProvider에 등록되어야 합니다

### 4.2 도메인별 서비스 사용
- 각 도메인의 컴포넌트는 해당 도메인의 서비스를 우선적으로 사용
- 다른 도메인의 서비스가 필요한 경우 Orchestrator를 통해 접근
- 서비스 인터페이스를 통한 의존성 주입 필수

## 5. 예시 코드

### 5.1 System 도메인 컴포넌트
```php
namespace App\Livewire\System;

use Livewire\Component;
use App\Services\Interfaces\UserServiceInterface;

class UserManager extends Component
{
    public $users = [];
    
    public function boot(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }
    
    public function mount()
    {
        $result = $this->userService->getAllUsers();
        if ($result->isSuccess()) {
            $this->users = $result->getData();
        }
    }
    
    public function render()
    {
        return view('livewire.system.user-manager');
    }
}
```

### 5.2 Chat 도메인 컴포넌트
```php
namespace App\Livewire\Chat;

use Livewire\Component;
use App\Services\Interfaces\ChatServiceInterface;

class ChatList extends Component
{
    public $chats = [];
    
    public function boot(ChatServiceInterface $chatService)
    {
        $this->chatService = $chatService;
    }
    
    public function mount()
    {
        $result = $this->chatService->getUserChats(auth()->id());
        if ($result->isSuccess()) {
            $this->chats = $result->getData();
        }
    }
    
    public function render()
    {
        return view('livewire.chat.chat-list');
    }
}
```

### 5.3 Shop 도메인 서브도메인 컴포넌트
```php
namespace App\Livewire\Shop\Checkout;

use Livewire\Component;
use App\Services\Interfaces\Shop\CheckoutServiceInterface;

class PaymentForm extends Component
{
    public $paymentMethods = [];
    public $selectedMethod = null;
    
    public function boot(CheckoutServiceInterface $checkoutService)
    {
        $this->checkoutService = $checkoutService;
    }
    
    public function mount()
    {
        $result = $this->checkoutService->getAvailablePaymentMethods();
        if ($result->isSuccess()) {
            $this->paymentMethods = $result->getData();
        }
    }
    
    public function render()
    {
        return view('livewire.shop.checkout.payment-form');
    }
}
```

## 6. 컴포넌트 등록

### 6.1 ServiceProvider에서 도메인별 컴포넌트 등록
```php
// app/Providers/LivewireServiceProvider.php
public function boot()
{
    // System 도메인 컴포넌트
    Livewire::component('system.user-manager', \App\Livewire\System\UserManager::class);
    Livewire::component('system.settings.general', \App\Livewire\System\Settings\GeneralSettings::class);
    
    // Chat 도메인 컴포넌트
    Livewire::component('chat.list', \App\Livewire\Chat\ChatList::class);
    Livewire::component('chat.message-box', \App\Livewire\Chat\MessageBox::class);
    
    // Shop 도메인 컴포넌트
    Livewire::component('shop.product-catalog', \App\Livewire\Shop\ProductCatalog::class);
    Livewire::component('shop.checkout.payment', \App\Livewire\Shop\Checkout\PaymentForm::class);
}
```

## 7. 도메인 구조 가이드라인

### 7.1 도메인 선택 기준
- **System**: 시스템 설정, 사용자 관리, 권한 관리 등
- **Auth**: 인증, 로그인, 회원가입, 비밀번호 재설정 등
- **Chat**: 실시간 채팅, 메시징 기능
- **Blog**: 블로그 포스트, 댓글, 카테고리 관리
- **Shop**: 전자상거래, 제품, 주문, 결제
- **Resource**: 리소스 예약, 일정 관리
- **Sample**: 예제, 데모, 테스트 컴포넌트

### 7.2 새 도메인 추가 시
1. `app/Livewire/{NewDomain}/` 디렉토리 생성
2. `resources/views/livewire/{new-domain}/` 뷰 디렉토리 생성
3. ServiceProvider에 도메인 컴포넌트 등록
4. 필요시 도메인별 서비스 인터페이스 정의

## 8. 주의사항

### 8.1 절대 금지 사항
- ❌ `app/Livewire/` 직접 아래에 컴포넌트 생성
- ❌ 도메인 구조 없이 평면적인 구조 사용
- ❌ 컴포넌트에서 다른 도메인의 Repository 직접 사용
- ❌ 뷰 파일을 도메인 구조 없이 생성

### 8.2 권장 사항
- ✅ 관련 컴포넌트는 같은 도메인으로 그룹화
- ✅ 복잡한 도메인은 서브도메인으로 세분화
- ✅ 도메인별 공통 기능은 Base 컴포넌트로 추상화
- ✅ 도메인 간 통신은 이벤트 시스템 활용
