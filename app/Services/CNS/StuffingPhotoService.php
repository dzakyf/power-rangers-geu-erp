<?php

namespace App\Services\CNS;

use App\Services\UploadService;
use App\VesselScheduleDtl;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StuffingPhotoService
{
    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function categories(): array
    {
        return [
            'flexibag' => [
                'field' => 'Flexibag_Photos',
                'topic' => 'stuffing-flexibag-photo',
                'label' => 'Foto Pemasangan Flexibag',
                'multiple' => true,
                'max' => 7,
            ],
            'loading_start' => [
                'field' => 'Loading_Start_Photo',
                'topic' => 'stuffing-loading-start-photo',
                'label' => 'Foto Loading Start',
                'multiple' => false,
                'max' => 1,
            ],
            'loading_done' => [
                'field' => 'Loading_Done_Photo',
                'topic' => 'stuffing-loading-done-photo',
                'label' => 'Foto Loading Done',
                'multiple' => false,
                'max' => 1,
            ],
            'seal_customs' => [
                'field' => 'Seal_Customs_Photo',
                'topic' => 'stuffing-seal-customs-photo',
                'label' => 'Foto Seal Bea Cukai',
                'multiple' => false,
                'max' => 1,
            ],
            'seal_shipping' => [
                'field' => 'Seal_Shipping_Photo',
                'topic' => 'stuffing-seal-shipping-photo',
                'label' => 'Foto Seal Pelayaran',
                'multiple' => false,
                'max' => 1,
            ],
            'seal_surveyor' => [
                'field' => 'Seal_Surveyor_Photo',
                'topic' => 'stuffing-seal-surveyor-photo',
                'label' => 'Foto Seal Surveyor',
                'multiple' => false,
                'max' => 1,
            ],
            'road_letter' => [
                'field' => 'Road_Letter_Photo',
                'topic' => 'stuffing-road-letter-photo',
                'label' => 'Foto Surat Jalan',
                'multiple' => false,
                'max' => 1,
            ],
            'weighbridge' => [
                'field' => 'Weighbridge_Photo',
                'topic' => 'stuffing-container-weighbridge-photo',
                'label' => 'Foto Nota Timbang',
                'multiple' => false,
                'max' => 1,
            ],
        ];
    }

    public function photosForContainer(int $containerId): array
    {
        $photos = [];

        foreach ($this->categories() as $key => $category) {
            $photos[$key] = DB::table('meta_files')
                ->where('Topic', $category['topic'])
                ->where('Map_To', (string) $containerId)
                ->orderBy('ID')
                ->get()
                ->map(function ($photo) {
                    return [
                        'ID' => $photo->ID,
                        'filename' => $photo->Filename,
                        'url' => asset('filevendor/' . $photo->Path),
                    ];
                })
                ->values()
                ->all();
        }

        return $photos;
    }

    public function storeForContainer(VesselScheduleDtl $container, array $files): void
    {
        $containerId = (string) $container->ID;

        foreach ($this->categories() as $category) {
            $incoming = $this->normalizeFiles($files[$category['field']] ?? []);

            if (empty($incoming)) {
                continue;
            }

            if ($category['multiple']) {
                $existing = DB::table('meta_files')
                    ->where('Topic', $category['topic'])
                    ->where('Map_To', $containerId)
                    ->count();

                if ($existing + count($incoming) > $category['max']) {
                    throw ValidationException::withMessages([
                        $category['field'] => 'Maksimal ' . $category['max'] . ' foto untuk ' . $category['label'] . '.',
                    ]);
                }

                foreach ($incoming as $file) {
                    $this->storeFile($file, $category, $containerId);
                }

                continue;
            }

            $this->deleteByTopic($containerId, $category['topic']);
            $this->storeFile($incoming[0], $category, $containerId);
        }
    }

    public function deletePhoto(int $containerId, int $photoId): void
    {
        $category = null;
        $photo = null;

        foreach ($this->categories() as $candidate) {
            $photo = DB::table('meta_files')
                ->where('ID', $photoId)
                ->where('Topic', $candidate['topic'])
                ->where('Map_To', (string) $containerId)
                ->first();

            if ($photo) {
                $category = $candidate;
                break;
            }
        }

        if (!$photo || !$category) {
            throw ValidationException::withMessages([
                'photo_id' => 'Foto tidak ditemukan pada container ini.',
            ]);
        }

        $this->deleteMetaFile($photo);
    }

    private function storeFile(UploadedFile $file, array $category, string $containerId): void
    {
        $this->uploadService->storeFile(
            $file,
            'file',
            $category['topic'],
            $containerId,
            $category['label']
        );
    }

    private function deleteByTopic(string $containerId, string $topic): void
    {
        $photos = DB::table('meta_files')
            ->where('Topic', $topic)
            ->where('Map_To', $containerId)
            ->get();

        foreach ($photos as $photo) {
            $this->deleteMetaFile($photo);
        }
    }

    private function deleteMetaFile($photo): void
    {
        $path = public_path('filevendor/' . $photo->Path);

        if (is_file($path)) {
            @unlink($path);
        }

        DB::table('meta_files')->where('ID', $photo->ID)->delete();
    }

    private function normalizeFiles($files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (!is_array($files)) {
            return [];
        }

        return array_values(array_filter($files, function ($file) {
            return $file instanceof UploadedFile;
        }));
    }
}
