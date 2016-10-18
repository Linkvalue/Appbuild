<?php

namespace Majora\OTAStore\ApplicationBundle\Service;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Handle build upload tasks.
 */
class BuildUploadHelper
{
    /**
     * @var bool
     */
    private $streamBuildsContent;

    /**
     * @var string
     */
    private $webBuildsApplicationDir;

    /**
     * @var string
     */
    private $streamBuildsApplicationDir;

    /**
     * construct.
     *
     * @param bool   $streamBuildsContent
     * @param string $webBuildsApplicationDir
     * @param string $streamBuildsApplicationDir
     */
    public function __construct($streamBuildsContent, $webBuildsApplicationDir = '', $streamBuildsApplicationDir = '')
    {
        $this->streamBuildsContent = $streamBuildsContent;
        $this->webBuildsApplicationDir = $webBuildsApplicationDir;
        $this->streamBuildsApplicationDir = $streamBuildsApplicationDir;
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
     * @param UploadedFile $uploadedFile
     * @param Application  $application
     * @param string       $filename
     */
    public function moveUploadedFile(UploadedFile $uploadedFile, Application $application, $filename)
    {
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
            '%s/%s',
            ($this->streamBuildsContent) ? $this->streamBuildsApplicationDir : $this->webBuildsApplicationDir,
            $application->getSlug()
        );
    }
}
