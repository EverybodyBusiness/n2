<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 스케줄 작업 정의 테이블
        Schema::create('scheduled_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('작업 이름');
            $table->string('command')->comment('실행할 명령어 또는 Job 클래스');
            $table->string('type')->default('command')->comment('작업 유형: command, job, closure');
            $table->string('expression')->comment('크론 표현식');
            $table->string('timezone')->default('Asia/Seoul')->comment('시간대');
            $table->text('description')->nullable()->comment('작업 설명');
            $table->json('parameters')->nullable()->comment('작업 매개변수');
            $table->boolean('is_active')->default(true)->comment('활성화 여부');
            $table->boolean('is_system')->default(false)->comment('시스템 작업 여부');
            $table->string('notification_email')->nullable()->comment('결과 알림 이메일');
            $table->integer('max_runtime')->nullable()->comment('최대 실행 시간(초)');
            $table->string('category')->nullable()->comment('작업 카테고리');
            $table->boolean('without_overlapping')->default(true)->comment('중복 실행 방지');
            $table->integer('run_in_background')->default(false)->comment('백그라운드 실행');
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['is_active', 'category']);
            $table->index('command');
            $table->unique(['command', 'type']);
        });

        // 스케줄 실행 로그 테이블
        Schema::create('scheduled_task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_task_id')->constrained()->onDelete('cascade');
            $table->dateTime('started_at')->comment('시작 시간');
            $table->dateTime('finished_at')->nullable()->comment('종료 시간');
            $table->integer('duration')->nullable()->comment('실행 시간(초)');
            $table->enum('status', ['pending', 'running', 'success', 'failed', 'timeout'])->default('pending')->comment('실행 상태');
            $table->text('output')->nullable()->comment('실행 출력');
            $table->text('error_message')->nullable()->comment('에러 메시지');
            $table->integer('memory_usage')->nullable()->comment('메모리 사용량(bytes)');
            $table->string('triggered_by')->nullable()->comment('실행 트리거: schedule, manual, api');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->comment('수동 실행한 사용자');
            $table->timestamps();
            
            $table->index(['scheduled_task_id', 'started_at']);
            $table->index(['status', 'started_at']);
        });

        // 스케줄 모니터링 테이블
        Schema::create('scheduled_task_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_task_id')->constrained()->onDelete('cascade');
            $table->integer('expected_runtime')->nullable()->comment('예상 실행 시간(초)');
            $table->integer('max_consecutive_failures')->default(3)->comment('최대 연속 실패 횟수');
            $table->integer('current_consecutive_failures')->default(0)->comment('현재 연속 실패 횟수');
            $table->dateTime('last_success_at')->nullable()->comment('마지막 성공 시간');
            $table->dateTime('last_failure_at')->nullable()->comment('마지막 실패 시간');
            $table->dateTime('next_run_at')->nullable()->comment('다음 실행 예정 시간');
            $table->boolean('is_healthy')->default(true)->comment('건강 상태');
            $table->text('health_check_message')->nullable()->comment('건강 상태 메시지');
            $table->timestamps();
            
            $table->unique('scheduled_task_id');
            $table->index('is_healthy');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_task_monitors');
        Schema::dropIfExists('scheduled_task_logs');
        Schema::dropIfExists('scheduled_tasks');
    }
};
