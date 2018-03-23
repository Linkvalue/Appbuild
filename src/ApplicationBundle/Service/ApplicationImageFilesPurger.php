<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Handle purge application image files tasks.
 */
class ApplicationImageFilesPurger implements FilesPurgerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $applicationImagesDir;

    /**
     * @var string
     */
    private $unusedFilesFinderDateFilter;

    /**
     * @param Filesystem    $filesystem
     * @param EntityManager $entityManager
     * @param string        $applicationImagesDir
     */
    public function __construct(
        Filesystem $filesystem,
        EntityManager $entityManager,
        $applicationImagesDir
    ) {
        $this->filesystem = $filesystem;
        $this->entityManager = $entityManager;
        $this->applicationImagesDir = $applicationImagesDir;

        // Unused files finder date filter has a default value (!= null)
        // because we must let the time for user to submit the form after uploading the build file
        $this->unusedFilesFinderDateFilter = '< now - 12hours';
    }

    /**
     * @param $finderDateFilter
     *
     * @return $this
     */
    public function setUnusedFilesFinderDateFilter($finderDateFilter)
    {
        $this->unusedFilesFinderDateFilter = $finderDateFilter;

        return $this;
    }

    /**
     * Remove unused build files.
     */
    public function purge()
    {
        foreach ($this->getUnusedFiles() as $file) {
            $this->filesystem->remove($file);
        }
    }

    /**
     * Get unused files.
     *
     * @return Finder|\SplFileInfo[]
     */
    public function getUnusedFiles()
    {
        $usedFilesPath = $this->getUsedFilesPath();

        return (new Finder())
            ->files()
            ->in($this->applicationImagesDir)
            ->filter(function (\SplFileInfo $file) use ($usedFilesPath) {
                return !in_array($file->getRealPath(), $usedFilesPath);
            })
           ->date($this->unusedFilesFinderDateFilter)
        ;
    }

    /**
     * Get used files.
     *
     * @return string[]
     */
    private function getUsedFilesPath()
    {
        $usedFiles = [];

        $applications = $this->entityManager->getRepository('AppbuildApplicationBundle:Application')->findAll();
        foreach ($applications as $application) {
            $usedFiles[] = $application->getDisplayImageFilePath();
            $usedFiles[] = $application->getFullSizeImageFilePath();
        }

        return $usedFiles;
    }
}
