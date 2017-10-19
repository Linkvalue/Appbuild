<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use LinkValue\Appbuild\ApplicationBundle\Entity\Application;
use LinkValue\Appbuild\ApplicationBundle\Entity\Build;

/**
 * Entities serializer.
 */
class Serializer
{
    /**
     * @var BuildLinkBuilder
     */
    private $buildLinkBuilder;

    /**
     * @param BuildLinkBuilder $buildLinkBuilder
     */
    public function __construct(BuildLinkBuilder $buildLinkBuilder)
    {
        $this->buildLinkBuilder = $buildLinkBuilder;
    }

    /**
     * Serialize given application.
     *
     * @param Application $application
     *
     * @return array
     */
    public function serializeApplication(Application $application)
    {
        // Setup application builds
        $builds = [];
        foreach ($application->getEnabledBuilds() as $build) {
            $builds[] = [
                'id' => $build->getId(),
                'version' => $build->getVersion(),
                'is_latest' => ($build == $application->getLatestEnabledBuild()),
            ];
        }

        // Setup application
        return [
            'id' => $application->getId(),
            'label' => $application->getLabel(),
            'slug' => $application->getSlug(),
            'support' => $application->getSupport(),
            'package_name' => $application->getPackageName(),
            'builds' => $builds,
        ];
    }

    /**
     * Serialize given build.
     *
     * @param Build $build
     *
     * @return array
     */
    public function serializeBuild(Build $build)
    {
        $application = $build->getApplication();

        return [
            'id' => $build->getId(),
            'label' => $build->getLabel(),
            'version' => $build->getVersion(),
            'comment' => $build->getComment(),
            'is_latest' => ($build == $application->getLatestEnabledBuild()),
            'download_link' => $this->buildLinkBuilder->getDownloadLink($build),
            'application' => [
                'id' => $application->getId(),
            ],
        ];
    }

    /**
     * Serialize given build to return only information to download it.
     *
     * @param Build $build
     *
     * @return array
     */
    public function serializeBuildForDownloading(Build $build)
    {
        return [
            'id' => $build->getId(),
            'version' => $build->getVersion(),
            'comment' => $build->getComment(),
            'download_link' => $this->buildLinkBuilder->getDownloadLink($build),
        ];
    }
}
