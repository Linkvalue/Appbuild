<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle application images upload tasks.
 */
class ApplicationImageUploadHelper extends AbstractUploadHelper
{
    /**
     * @var string
     */
    private $applicationImagesDir;

    /**
     * @param string $applicationImagesDir
     */
    public function __construct($applicationImagesDir)
    {
        $this->applicationImagesDir = $applicationImagesDir;
    }

    /**
     * Get full file path for filename.
     *
     * @param string $filename
     *
     * @return string
     */
    public function getFilePath($filename)
    {
        return realpath(
            sprintf(
                '%s%s%s',
                $this->getBasePath(),
                DIRECTORY_SEPARATOR,
                $filename
            )
        );
    }

    /**
     * Move uploaded file to its final destination.
     *
     * @param File|UploadedFile $uploadedFile
     * @param string            $filename
     */
    public function moveUploadedFile(File $uploadedFile, $filename)
    {
        if (file_exists($this->getFilePath($filename))) {
            unlink($this->getFilePath($filename));
        }

        $uploadedFile->move($this->getBasePath(), $filename);
    }

    /**
     * Get upload base path.
     *
     * @return string
     */
    private function getBasePath()
    {
        return rtrim($this->applicationImagesDir, DIRECTORY_SEPARATOR);
    }
}
