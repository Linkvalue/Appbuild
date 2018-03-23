<?php

namespace LinkValue\Appbuild\ApplicationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Fixtures loader for ApplicationBundle.
 */
class ApplicationFixturesLoader extends AbstractLoader implements OrderedFixtureInterface
{
    /**
     * Returns an array of file paths to fixtures.
     *
     * @return string[]
     */
    public function getFixtures()
    {
        // copy fixture files in expected locations
        $applicationImagesDir = $this->container->getParameter('application_images_dir');
        $fs = new Filesystem();
        $fs->mkdir($applicationImagesDir);
        $fs->mirror(
            __DIR__.'/../../Resources/fixtures/files/application_images',
            $this->container->getParameter('application_images_dir'),
            null,
            ['override' => true]
        );

        return [
            __DIR__.'/../../Resources/fixtures/applications.yml',
            __DIR__.'/../../Resources/fixtures/builds.yml',
            __DIR__.'/../../Resources/fixtures/build_tokens.yml',
        ];
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 1;
    }
}
