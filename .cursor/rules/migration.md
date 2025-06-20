---
description: 
globs: 
alwaysApply: true
---
---
description: 
globs: 
alwaysApply: true
---
# Migration 레이어 문서

## 1. 역할 (Role)
- 데이터베이스 테이블 구조를 정의하고 관리하는 책임을 가진다.
- 테이블 생성, 수정, 삭제 등의 스키마 변경 작업을 수행한다.

## 1.1 실행 금지
- 다음 명령들은 절대 실행하면 안되고, 물어보지도 말고 필요하다면 안내만 해야 한다.
1. 모든 테이블을 삭제하고 마이그레이션을 다시 실행하는 명령: php artisan migrate:fresh
2. 모든 테이블을 삭제하고 마이그레이션을 다시 실행한 후 시더까지 실행하는 명령: php artisan migrate:fresh --seed
3. 데이터베이스를 완전히 삭제하고 다시 생성하는 명령: php artisan db:wipe
4. 테이블을 롤백하는 명령 (마이그레이션 실행 이전 상태로 되돌림): php artisan migrate:rollback
5. 모든 마이그레이션을 롤백하는 명령:php artisan migrate:reset

## 2. 책임 (Responsibility)
- 테이블과 컬럼 정의
- 기본 키, 외래 키, 인덱스 설정
- 컬럼 타입 및 제약조건 설정
- SoftDeletes, timestamps 등 기본 필드 자동 추가

## 3. 규칙 (Rules)

### 3.1 일반 규칙
- 모든 테이블은 기본적으로 `id`, `created_at`, `updated_at`, `deleted_at` 컬럼을 포함한다 (SoftDeletes 적용)
- 외래 키(Foreign Key) 설정 시 `onDelete`는 'cascade' 또는 'restrict'를 명시한다
- 외래 키 `onUpdate`는 반드시 'cascade'로 설정한다
- 자주 검색되거나 Join 되는 컬럼에는 인덱스를 추가한다
- 컬럼의 속성이 enum() 인 경우, app\Enums 에 enum 클래스를 생성한다
- 마이그레이션 파일 이름은 "생성할 테이블명_생성" 또는 "수정할 테이블명_수정" 형태로 작성한다
- 테이블의 컬럼 등에 대해 변경이 필요하면, 한번 만들어진 migration 파일은 직접 수정하지 않는다. 개발자에게 수정할 것을 요청하거나, 새로운 마이그레이션을 생성하여 변경한다

### 3.2 생성 규칙 (Creation Rules)
- 파일 위치: `database/migrations/`
- 파일명:  Laravel 자동 생성 규칙(타임스탬프 + 설명)
- 클래스명: `{날짜}_create_{table_name}_table`
- 테이블명: 서비스 이름을 접두사로 사용 가능 (예, users 테이블 생성시, system_users) 
- 스키마 정의 시 Schema 파사드 사용

### 3.3 주석 규칙
- 파일 상단에 테이블의 용도, 역할에 대한 설명을 추가한다
- id, relation_id(예, user_id 등), timestamp(created_at, updated_at, deleted_at, 등)을 제외한 모든 컬럼에 ->comment('용도, 역할에 대한 주석')을 추가

## 4. 구조 (Structure)

### 폴더 구조
```
database/
└── migrations/
    ├── {날짜}_create_users_table.php
    ├── {날짜}_create_roles_table.php
    ├── {날짜}_create_pdf_templates_table.php
    ├── {날짜}_create_resources_table.php
    └── {날짜}_create_resource_bookings_table.php
```

### 네이밍 규칙
- 테이블명: 스네이크 케이스(snake_case) 사용, 복수형 (예: users, order_items).
- 관계 테이블: 두 테이블명을 알파벳 순으로 연결 (예: product_tag)
- 컬럼명: 스네이크 케이스 사용 (예: first_name, created_at)
- 외래 키: `{단수형 테이블명}_id` 형식 (예: user_id, product_id)

## 5. 컬럼 타입 가이드라인

### 문자열 타입
- 짧은 텍스트: `string(길이)` - 최대 255자
- 긴 텍스트: `text` - 65,535자까지
- 초긴 텍스트: `longText` - 4GB까지

### 숫자 타입
- 정수: `integer` - -2,147,483,648 ~ 2,147,483,647
- 작은 정수: `smallInteger` - -32,768 ~ 32,767
- 큰 정수: `bigInteger` - -9,223,372,036,854,775,808 ~ 9,223,372,036,854,775,807
- 부동소수점: `float`, `double`, `decimal(전체자릿수, 소수점자릿수)`

### 날짜/시간 타입
- 날짜: `date`
- 시간: `time`
- 날짜시간: `dateTime`
- 타임스탬프: `timestamp`

### 기타 타입
- 부울: `boolean`
- JSON: `json`
- 열거형: `enum(['옵션1', '옵션2', ...])`
- IP 주소: `ipAddress`
- MAC 주소: `macAddress`
- UUID: `uuid`

## 6. 주의사항
- `php artisan migrate:fresh` 명령은 개발 초기 외에는 사용 금지 (모든 데이터 삭제 위험)
- 마이그레이션 수정 시 되도록 기존 파일을 수정하지 않고 새로운 수정용 마이그레이션 파일 생성
- 롤백 가능성을 고려하여 up()과 down() 메소드 모두 정확히 작성
- 데이터 삽입이 필요한 경우 Seeder를 별도로 작성한다
- 생성하고자 하는 테이블이 users 테이블이면 다음 코드를 up() 의 시작에 추가한다.
```php
Schema::dropIfExists('users');
```

