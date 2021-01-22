<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileUploader.
 */
class FileUploader
{
    private $publicImagesDir;

    public function __construct($publicImagesDir)
    {
        $this->publicImagesDir = $publicImagesDir;
    }

    public function upload(UploadedFile $file)
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
        $newFilename = time().'_'.$safeFilename.'.'.$file->guessExtension();

        try {
            $file->move($this->publicImagesDir, $newFilename);
        } catch (FileException $e) {
            return $e->getMessage();
        }

        return $newFilename;
    }
}
