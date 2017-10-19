<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use LinkValue\Appbuild\ApplicationBundle\Entity\Application;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle build upload tasks.
 */
class BuildUploadHelper extends AbstractUploadHelper
{
    /**
     * @var string
     */
    private $buildsApplicationDir;

    /**
     * @param string $buildsApplicationDir
     */
    public function __construct($buildsApplicationDir)
    {
        $this->buildsApplicationDir = $buildsApplicationDir;
    }

    /**
     * Get full file path for application and filename.
     *
     * @param Application $application
     * @param string      $filename
     *
     * @return string
     */
    public function getFilePath(Application $application, $filename)
    {
        return realpath(
            sprintf(
                '%s%s%s',
                $this->getBasePath($application),
                DIRECTORY_SEPARATOR,
                $filename
            )
        );
    }

    /**
     * Move uploaded file to its final destination.
     *
     * @param File|UploadedFile $uploadedFile
     * @param Application       $application
     * @param string            $filename
     */
    public function moveUploadedFile(File $uploadedFile, Application $application, $filename)
    {
        if (file_exists($this->getFilePath($application, $filename))) {
            unlink($this->getFilePath($application, $filename));
        }

        $uploadedFile->move($this->getBasePath($application), $filename);
    }

    /**
     * Get upload base path for application.
     *
     * @param Application $application
     *
     * @return string
     */
    private function getBasePath(Application $application)
    {
        return sprintf(
            '%s%s%s',
            rtrim($this->buildsApplicationDir, DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $application->getSlug()
        );
    }
}
