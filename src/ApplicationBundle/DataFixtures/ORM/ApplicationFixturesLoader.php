<?php

namespace Majora\OTAStore\ApplicationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Hautelook\AliceBundle\Doctrine\DataFixtures\AbstractLoader;

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
        return array(
            __DIR__.'/../../Resources/fixtures/applications.yml',
            __DIR__.'/../../Resources/fixtures/builds.yml',
        );
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
