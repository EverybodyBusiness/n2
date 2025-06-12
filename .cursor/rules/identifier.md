---
description: 
globs: 
alwaysApply: true
---
# Support 클래스
- Result 클래스의 namepace : App\Modules\Support\Result
- Agent 는 Result Class 를 수정할 수 없다

# Variable(변수) 규칙
- 모든 Method 내의 지역 변수명은 snake_case 를 사용한다.
- 변수명은 의미가 명확하게 표현되어야 합니다.
- 메소드 내의 지역 변수 복수형 데이터는 `_list` 접미사를 사용한다.
- 메소드 내의 지역 변수 유효성 검사 데이터는 `_data` 접미사를 사용한다.
- 메소드 내의 새로운 지역 변수 생성 시 `new_` 접두사를 사용한다.

# Constant(상수) 규칙
- 새로운 상수 선언 시, app/Modules/Support/Def.php 에 동일한 상수가 있는지 체크한다
- 새로운 상수가 없으면 public const 로 선언하고 각 클래스에서 사용한다
