<?php

namespace AppBuild\Bundle\ApplicationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Hautelook\AliceBundle\Alice\DataFixtures\Loader;

/**
 * Fixtures loader for AppBundle.
 *
 * @see @AppBundle/Resources/fixtures/applications.yml
 */
class ApplicationFixturesLoader extends Loader implements OrderedFixtureInterface
{
    /**
     * Returns an array of file paths to fixtures.
     *
     * @return array<string>
     */
    protected function getFixtures()
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
