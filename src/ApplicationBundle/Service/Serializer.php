<?php

namespace Majora\OTAStore\ApplicationBundle\Service;

use Majora\OTAStore\ApplicationBundle\Entity\Application;

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
     * construct.
     *
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
                'download_link' => $this->buildLinkBuilder->getDownloadLink($build),
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
}
