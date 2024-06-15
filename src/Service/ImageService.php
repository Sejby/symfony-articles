<?php

namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageService
{
    private $photoDir;

    public function __construct(#[Autowire('%photo_dir%')] string $photoDir)
    {
        $this->photoDir = $photoDir;
    }

    public function processImage(UploadedFile $photo): array
    {
        $filename = uniqid() . '.' . $photo->guessExtension();
        $photo->move($this->photoDir, $filename);

        $imagine = new Imagine();
        $size = new Box(120, 90); // Nastavení požadované velikosti perexu
        $imagePath = $this->photoDir . '/' . $filename;
        $perexFilename = 'thumb_' . $filename;

        // Vytvoření perexu (zmenšeného obrázku)
        $imagine->open($imagePath)
                ->resize($size)
                ->save($this->photoDir . '/' . $perexFilename);

        return [
            'filename' => $filename,
            'perexFilename' => $perexFilename,
        ];
    }

    public function deleteImage(string $filename): void
    {
        $imagePath = $this->photoDir . '/' . $filename;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}
