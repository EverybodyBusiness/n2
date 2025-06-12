# Step 8: 미디어 관리 시스템 (Spatie Media Library)

## 개요
Spatie Media Library를 사용하여 파일 업로드, 이미지 변환, 최적화 기능을 구현합니다.

## 1. 설치 완료 사항
```bash
composer require spatie/laravel-medialibrary
composer require intervention/image-laravel
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider"
```

## 2. 주요 기능

### 2.1 파일 업로드 및 관리
- 다양한 파일 타입 지원 (이미지, 비디오, 문서)
- 파일 크기 제한 (기본 50MB)
- 자동 파일명 생성 및 중복 방지

### 2.2 이미지 변환
- 썸네일 자동 생성
- 반응형 이미지 생성
- WebP, AVIF 포맷 지원

### 2.3 최적화
- 자동 이미지 압축
- 메타데이터 제거
- 큐를 통한 비동기 처리

## 3. 모델에 미디어 기능 추가

### 3.1 기본 설정
```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        // 단일 파일 컬렉션
        $this->addMediaCollection('thumbnail')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);
            
        // 다중 파일 컬렉션
        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
            
        // 문서 컬렉션
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ]);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // 썸네일 생성
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->optimize()
            ->nonQueued(); // 즉시 생성
            
        // 미리보기 이미지
        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->quality(90)
            ->performOnCollections('gallery');
            
        // WebP 변환
        $this->addMediaConversion('webp')
            ->width(1200)
            ->format('webp')
            ->quality(80);
    }
}
```

## 4. 파일 업로드 사용법

### 4.1 단일 파일 업로드
```php
// 컨트롤러에서
$product = Product::find(1);

// Request에서 파일 업로드
$product->addMediaFromRequest('image')
    ->toMediaCollection('thumbnail');

// 파일 경로에서 업로드
$product->addMedia('/path/to/file.jpg')
    ->toMediaCollection('gallery');

// URL에서 다운로드하여 업로드
$product->addMediaFromUrl('https://example.com/image.jpg')
    ->toMediaCollection('gallery');
```

### 4.2 다중 파일 업로드
```php
foreach ($request->file('images') as $image) {
    $product->addMedia($image)
        ->withCustomProperties([
            'alt' => $request->alt_text,
            'uploaded_by' => auth()->id(),
        ])
        ->usingName($request->image_name)
        ->usingFileName(Str::slug($request->image_name) . '.jpg')
        ->toMediaCollection('gallery');
}
```

### 4.3 파일 조회
```php
// 첫 번째 미디어 가져오기
$media = $product->getFirstMedia('thumbnail');
$url = $product->getFirstMediaUrl('thumbnail');

// 모든 미디어 가져오기
$allMedia = $product->getMedia('gallery');

// 변환된 이미지 URL
$thumbUrl = $product->getFirstMediaUrl('gallery', 'thumb');
```

## 5. Filament 통합

### 5.1 FileUpload 컴포넌트
```php
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

// Filament Resource Form
Forms\Components\SpatieMediaLibraryFileUpload::make('thumbnail')
    ->collection('thumbnail')
    ->label('썸네일 이미지')
    ->image()
    ->imageEditor()
    ->maxSize(5120) // 5MB
    ->optimize('webp')
    ->resize(50)
    ->required(),

Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
    ->collection('gallery')
    ->label('갤러리 이미지')
    ->multiple()
    ->reorderable()
    ->image()
    ->maxFiles(10)
    ->panelLayout('grid'),
```

### 5.2 테이블에서 이미지 표시
```php
Tables\Columns\SpatieMediaLibraryImageColumn::make('thumbnail')
    ->collection('thumbnail')
    ->label('썸네일')
    ->circular()
    ->size(40),
```

## 6. 이미지 최적화 Job

### 6.1 자동 최적화
```php
// 이미지 업로드 시 자동으로 최적화 Job 실행
$media = $product->addMediaFromRequest('image')
    ->toMediaCollection('gallery');

dispatch(new OptimizeImageJob($media))->onQueue('media');
```

### 6.2 일괄 최적화
```php
// Artisan 명령어로 모든 이미지 최적화
php artisan media:optimize

// 특정 컬렉션만 최적화
php artisan media:optimize --collection=gallery
```

## 7. S3 스토리지 설정

### 7.1 환경 설정 (.env)
```env
MEDIA_DISK=s3

AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ap-northeast-2
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com
```

### 7.2 S3 설정 (config/filesystems.php)
```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'visibility' => 'public',
],
```

## 8. 보안 고려사항

### 8.1 파일 타입 제한
```php
$this->addMediaCollection('documents')
    ->acceptsMimeTypes([
        'application/pdf',
        'image/jpeg',
        'image/png'
    ])
    ->useDisk('private')
    ->useFallbackUrl('/images/no-document.jpg');
```

### 8.2 파일 크기 제한
```php
// config/media-library.php
'max_file_size' => 1024 * 1024 * 50, // 50MB
```

### 8.3 접근 권한 제어
```php
// 프라이빗 파일 다운로드
Route::get('/media/{media}/download', function (Media $media) {
    // 권한 체크
    if (!auth()->user()->can('download', $media)) {
        abort(403);
    }
    
    return $media;
})->middleware('auth');
```

## 9. 성능 최적화

### 9.1 Lazy Loading
```php
// N+1 문제 방지
$products = Product::with('media')->get();
```

### 9.2 캐싱
```php
// URL 캐싱
$url = Cache::remember("media.{$media->id}.url", 3600, function () use ($media) {
    return $media->getUrl();
});
```

### 9.3 CDN 사용
```php
// config/media-library.php
'url_generator' => App\Services\CdnUrlGenerator::class,
```

## 10. 유용한 명령어

```bash
# 미사용 미디어 정리
php artisan media:clean

# 미디어 통계 확인
php artisan media:stats

# 특정 모델의 미디어 재생성
php artisan media:regenerate --model=Product
```

## 다음 단계
- Step 9: 백업 시스템 구축 