<x-filament-panels::page>
    <div class="space-y-6">
        @if(empty($this->backups))
            <div class="text-center py-12">
                <div class="mx-auto" style="width: 256px; height: 256px;">
                    <x-heroicon-o-archive-box class="w-full h-full text-gray-400" />
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">백업 파일 없음</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">아직 생성된 백업이 없습니다.</p>
            </div>
        @else
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                백업 파일명
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                크기
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                생성일시
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">작업</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach($this->backups as $backup)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $backup['name'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $backup['size'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $backup['created_at'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <x-filament::button
                                        wire:click="downloadBackup('{{ $backup['path'] }}')"
                                        size="sm"
                                        color="gray"
                                    >
                                        <x-heroicon-m-arrow-down-tray class="h-4 w-4 mr-1" />
                                        다운로드
                                    </x-filament::button>
                                    
                                    <x-filament::button
                                        wire:click="deleteBackup('{{ $backup['path'] }}')"
                                        wire:confirm="정말로 이 백업을 삭제하시겠습니까?"
                                        size="sm"
                                        color="danger"
                                    >
                                        <x-heroicon-m-trash class="h-4 w-4 mr-1" />
                                        삭제
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page> 