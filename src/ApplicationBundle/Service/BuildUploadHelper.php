<?php

namespace Majora\OTAStore\ApplicationBundle\Service;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle build upload tasks.
 */
class BuildUploadHelper
{
    /**
     * @var string
     */
    private $buildsApplicationDir;

    /**
     * construct.
     *
     * @param string $buildsApplicationDir
     */
    public function __construct($buildsApplicationDir)
    {
        $this->buildsApplicationDir = $buildsApplicationDir;
    }

    /**
     * Generate and return a filename for an uploaded file.
     *
     * @param UploadedFile $uploadedFile
     *
     * @return string
     */
    public function generateFilename(UploadedFile $uploadedFile)
    {
        return preg_replace(
            '/[^a-z0-9\-\.]/i',
            '_',
            sprintf(
                '%s_%s',
                sha1(uniqid(mt_rand(), true)),
                $uploadedFile->getClientOriginalName()
            )
        );
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
                '%s/%s',
                $this->getBasePath($application),
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
        $uploadedFile->move($this->getBasePath($application), $filename);
    }

    /**
     * Put binary content into a tmp file and return it into a Symfony\Component\HttpFoundation\File\File
     * This binary file has to be moved or deleted manually.
     *
     * @param $fileContent mixed The file binary content (Can be either a string, an array or a stream resource.)
     * @param $filename string The file name
     *
     * @return File The created file in temp folder
     *
     * @throws FileException if the file could not be created
     */
    public function createTempFile($fileContent, $filename)
    {
        $filePath = sprintf(
            '%s%s%s',
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            ltrim($filename, DIRECTORY_SEPARATOR)
        );

        if (file_put_contents($filePath, $fileContent) === false) {
            throw new FileException(sprintf('Could not create temp file "%s"', $filePath));
        }

        return new File($filePath);
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
            '%s/%s',
            $this->buildsApplicationDir,
            $application->getSlug()
        );
    }
}
