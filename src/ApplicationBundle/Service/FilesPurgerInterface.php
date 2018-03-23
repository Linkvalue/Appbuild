<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use Symfony\Component\Finder\Finder;

interface FilesPurgerInterface
{
    /**
     * Remove unused build files.
     */
    public function purge();

    /**
     * Get unused files.
     *
     * @return Finder|\SplFileInfo[]
     */
    public function getUnusedFiles();

    /**
     * @param string $finderDateFilter
     *
     * @return $this
     */
    public function setUnusedFilesFinderDateFilter($finderDateFilter);
}
