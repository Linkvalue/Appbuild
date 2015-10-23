<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Hautelook\AliceBundle\Alice\DataFixtureLoader;

/**
 * Fixtures loader for AppBundle.
 *
 * @see @AppBundle/Resources/fixtures/applications.yml
 */
class ApplicationFixturesLoader extends DataFixtureLoader implements OrderedFixtureInterface
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
