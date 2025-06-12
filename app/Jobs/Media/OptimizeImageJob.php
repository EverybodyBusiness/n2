<?php

namespace App\Jobs\Media;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\System\Media;
use Spatie\ImageOptimizer\OptimizerChainFactory;
use Illuminate\Support\Facades\Log;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * 작업이 처리될 큐
     *
     * @var string
     */
    public $queue = 'media';

    /**
     * 작업 최대 시도 횟수
     *
     * @var int
     */
    public $tries = 3;

    /**
     * 작업 타임아웃 시간 (초)
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * 미디어 모델
     *
     * @var Media
     */
    protected $media;

    /**
     * Create a new job instance.
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 이미지가 아닌 경우 스킵
        if (!$this->media->is_image) {
            return;
        }

        $path = $this->media->getPath();
        
        if (!file_exists($path)) {
            Log::warning('이미지 최적화 실패: 파일을 찾을 수 없음', [
                'media_id' => $this->media->id,
                'path' => $path,
            ]);
            return;
        }

        try {
            $originalSize = filesize($path);
            
            // 이미지 최적화 실행
            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($path);
            
            $optimizedSize = filesize($path);
            $savedBytes = $originalSize - $optimizedSize;
            $savedPercentage = ($savedBytes / $originalSize) * 100;
            
            // 미디어 모델 업데이트
            $this->media->update([
                'size' => $optimizedSize,
                'custom_properties' => array_merge($this->media->custom_properties ?? [], [
                    'optimized' => true,
                    'original_size' => $originalSize,
                    'saved_bytes' => $savedBytes,
                    'saved_percentage' => round($savedPercentage, 2),
                    'optimized_at' => now()->toDateTimeString(),
                ]),
            ]);
            
            Log::info('이미지 최적화 완료', [
                'media_id' => $this->media->id,
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'saved_percentage' => round($savedPercentage, 2) . '%',
            ]);
            
        } catch (\Exception $e) {
            Log::error('이미지 최적화 실패', [
                'media_id' => $this->media->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * 작업 실패 시 처리
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('이미지 최적화 작업 실패', [
            'media_id' => $this->media->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);
    }
}
