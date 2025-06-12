<x-filament-panels::page>
    <div class="rounded-lg bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            <iframe 
                src="/horizon" 
                class="w-full h-screen rounded-lg"
                style="min-height: 800px;"
            ></iframe>
        </div>
    </div>
    
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <x-filament::card>
            <h3 class="text-lg font-medium">큐 사용법</h3>
            <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <p><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">dispatch(new YourJob())</code></p>
                <p>기본 큐로 작업 전송</p>
                
                <p class="mt-3"><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">dispatch(new YourJob())->onQueue('high')</code></p>
                <p>우선순위 큐 지정</p>
                
                <p class="mt-3"><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">dispatch(new YourJob())->delay(60)</code></p>
                <p>60초 후 실행</p>
            </div>
        </x-filament::card>
        
        <x-filament::card>
            <h3 class="text-lg font-medium">큐 우선순위</h3>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-red-600 font-medium">high</span>
                    <span class="text-gray-600 dark:text-gray-400">긴급 작업</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-yellow-600 font-medium">default</span>
                    <span class="text-gray-600 dark:text-gray-400">일반 작업</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-green-600 font-medium">low</span>
                    <span class="text-gray-600 dark:text-gray-400">우선순위 낮음</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-blue-600 font-medium">notifications</span>
                    <span class="text-gray-600 dark:text-gray-400">알림 전용</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-purple-600 font-medium">reports</span>
                    <span class="text-gray-600 dark:text-gray-400">리포트 생성</span>
                </div>
            </div>
        </x-filament::card>
        
        <x-filament::card>
            <h3 class="text-lg font-medium">Horizon 명령어</h3>
            <div class="mt-4 space-y-2 text-sm text-gray-600 dark:text-gray-400">
                <p><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan horizon</code></p>
                <p>Horizon 시작</p>
                
                <p class="mt-3"><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan horizon:pause</code></p>
                <p>작업 처리 일시중지</p>
                
                <p class="mt-3"><code class="text-xs bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">php artisan horizon:terminate</code></p>
                <p>Horizon 종료</p>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page> 