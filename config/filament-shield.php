<?php

return [
    // Shield Role 리소스 관련 설정
    'shield_resource' => [
        // Role 관리 메뉴를 네비게이션에 표시할지 여부
        'should_register_navigation' => true,
        
        // Role 리소스의 URL 경로
        'slug' => 'shield/roles',
        
        // 네비게이션 메뉴에서의 정렬 순서 (-1은 가장 위)
        'navigation_sort' => -1,
        
        // 네비게이션에 Role 개수 배지 표시 여부
        'navigation_badge' => true,
        
        // 네비게이션 그룹 사용 여부
        'navigation_group' => true,
        
        // 서브 네비게이션 위치 (null, start, end)
        'sub_navigation_position' => null,
        
        // 전역 검색에서 Role 검색 가능 여부
        'is_globally_searchable' => false,
        
        // Role 편집 시 모델 경로 표시 여부
        'show_model_path' => true,
        
        // 멀티테넌시 환경에서 테넌트별 Role 분리 여부
        'is_scoped_to_tenant' => true,
        
        // Role 리소스가 속할 클러스터 (그룹)
        'cluster' => null,
    ],

    // 멀티테넌시 사용 시 테넌트 모델 클래스
    'tenant_model' => null,

    // 인증 프로바이더 모델 설정
    'auth_provider_model' => [
        // User 모델의 전체 경로 (Fully Qualified Class Name)
        'fqcn' => 'App\\Models\\User',
    ],

    // 슈퍼 관리자 역할 설정
    'super_admin' => [
        // 슈퍼 관리자 기능 활성화 여부
        'enabled' => false,
        
        // 슈퍼 관리자 역할 이름
        'name' => 'super_admin',
        
        // Gate를 통한 권한 정의 여부
        'define_via_gate' => false,
        
        // Gate 인터셉트 시점 (before: 다른 권한 체크 전, after: 다른 권한 체크 후)
        'intercept_gate' => 'before', // after, before
    ],

    // 패널 사용자 역할 설정
    'panel_user' => [
        // 패널 사용자 기능 활성화 여부
        'enabled' => true,
        
        // 패널 사용자 역할 이름
        'name' => 'panel_user',
    ],

    // 권한 접두사 설정
    'permission_prefixes' => [
        // 리소스 관련 권한 접두사 목록
        'resource' => [
            'view',              // 개별 조회
            'view_any',          // 목록 조회
            'create',            // 생성
            'update',            // 수정
            'restore',           // 복원
            'restore_any',       // 전체 복원
            'replicate',         // 복제
            'reorder',           // 순서 변경
            'delete',            // 삭제
            'delete_any',        // 전체 삭제
            'force_delete',      // 영구 삭제
            'force_delete_any',  // 전체 영구 삭제
        ],

        // 페이지 권한 접두사
        'page' => 'page',
        
        // 위젯 권한 접두사
        'widget' => 'widget',
    ],

    // 권한 생성 대상 엔티티 설정
    'entities' => [
        // 페이지에 대한 권한 생성 여부
        'pages' => true,
        
        // 위젯에 대한 권한 생성 여부
        'widgets' => true,
        
        // 리소스에 대한 권한 생성 여부
        'resources' => true,
        
        // 커스텀 권한 생성 여부
        'custom_permissions' => false,
    ],

    // 권한 생성기 설정
    'generator' => [
        // 생성 옵션 (policies_and_permissions: 정책과 권한 모두 생성)
        'option' => 'policies_and_permissions',
        
        // 정책 파일 저장 디렉토리
        'policy_directory' => 'Policies',
        
        // 정책 클래스 네임스페이스
        'policy_namespace' => 'Policies',
    ],

    // 권한 생성 제외 설정
    'exclude' => [
        // 제외 기능 활성화 여부
        'enabled' => true,

        // 권한 생성에서 제외할 페이지 목록
        'pages' => [
            'Dashboard',  // 대시보드는 기본적으로 모든 사용자가 접근 가능
        ],

        // 권한 생성에서 제외할 위젯 목록
        'widgets' => [
            'AccountWidget',      // 계정 위젯
            'FilamentInfoWidget', // Filament 정보 위젯
        ],

        // 권한 생성에서 제외할 리소스 목록
        'resources' => [],
    ],

    // 자동 검색 설정
    'discovery' => [
        // 모든 리소스 자동 검색 여부
        'discover_all_resources' => false,
        
        // 모든 위젯 자동 검색 여부
        'discover_all_widgets' => false,
        
        // 모든 페이지 자동 검색 여부
        'discover_all_pages' => false,
    ],

    // Role 정책 등록 설정
    'register_role_policy' => [
        // Role 모델에 대한 정책 자동 등록 여부
        'enabled' => true,
    ],

];
