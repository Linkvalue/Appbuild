<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use LinkValue\Appbuild\ApplicationBundle\Entity\Build;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;

/**
 * BuildLinkBuilder.
 */
class BuildLinkBuilder
{
    /**
     * @var Router
     */
    private $router;

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param Build $build
     * @param bool  $absoluteUrl
     *
     * @return string
     */
    public function getDownloadLink(Build $build, $absoluteUrl = true)
    {
        return $this->router->generate(
            'appbuild_admin_build_download',
            [
                'application_id' => $build->getApplication()->getId(),
                'id' => $build->getId(),
            ],
            ($absoluteUrl) ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
}
