<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">시작 시간</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $log->started_at?->format('Y-m-d H:i:s') ?? '-' }}
            </p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">종료 시간</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $log->finished_at?->format('Y-m-d H:i:s') ?? '-' }}
            </p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">실행 시간</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                @if($log->duration)
                    {{ $log->duration >= 60 ? round($log->duration / 60, 1) . '분' : $log->duration . '초' }}
                @else
                    -
                @endif
            </p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">메모리 사용량</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                @if($log->memory_usage)
                    {{ number_format($log->memory_usage / 1024 / 1024, 2) }} MB
                @else
                    -
                @endif
            </p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">실행 방식</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ match($log->triggered_by) {
                    'schedule' => '스케줄',
                    'manual' => '수동',
                    'api' => 'API',
                    default => '-'
                } }}
            </p>
        </div>
        
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">실행자</h3>
            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                {{ $log->user?->name ?? '-' }}
            </p>
        </div>
    </div>
    
    @if($log->output)
        <div>
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">실행 출력</h3>
            <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 overflow-x-auto">
                <pre class="text-xs text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $log->output }}</pre>
            </div>
        </div>
    @endif
    
    @if($log->error_message)
        <div>
            <h3 class="text-sm font-medium text-red-600 dark:text-red-400 mb-2">오류 메시지</h3>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 overflow-x-auto">
                <pre class="text-xs text-red-800 dark:text-red-200 whitespace-pre-wrap">{{ $log->error_message }}</pre>
            </div>
        </div>
    @endif
</div> 