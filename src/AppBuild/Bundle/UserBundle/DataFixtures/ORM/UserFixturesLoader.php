<?php

namespace AppBuild\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Hautelook\AliceBundle\Alice\DataFixtureLoader;

/**
 * Fixtures loader for UserBundle.
 *
 * @see @AppBundle/Resources/fixtures/users.yml
 */
class UserFixturesLoader extends DataFixtureLoader implements OrderedFixtureInterface
{
    /**
     * Returns an array of file paths to fixtures.
     *
     * @return array<string>
     */
    protected function getFixtures()
    {
        return array(
            __DIR__.'/../../Resources/fixtures/users.yml',
        );
    }

    /**
     * Get the order of this fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return 2;
    }
}
