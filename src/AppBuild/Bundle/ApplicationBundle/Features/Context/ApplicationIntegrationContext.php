<?php

namespace AppBuild\Bundle\ApplicationBundle\Features\Context;

use AppBuild\Bundle\ApplicationBundle\Entity\Application;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Symfony2Extension\Context\KernelAwareContext;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Defines application features from the specific context.
 */
class ApplicationIntegrationContext
    implements SnippetAcceptingContext, KernelAwareContext
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * {@inheritdoc}
     */
    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Bootstrap test database.
     *
     * @BeforeFeature
     */
    public static function initBdd()
    {
        exec('php app/console --env=test doctrine:schema:drop --force');

        exec('php app/console --env=test doctrine:schema:create');

        exec('php app/console --env=test doctrine:fixtures:load --no-interaction');
    }

    /**
     * @Given there is :nbApp application(s)
     */
    public function thereIsApplications($nbApp)
    {
        for ($i = 1; $i > $nbApp; ++$i) {
            $this->kernel->getContainer()->get('doctrine.orm.entity_manager')->persist(new Application());
        }
    }

    /**
     * @When I list all applications
     */
    public function iListAllApplications()
    {
        throw new PendingException();
    }

    /**
     * @Then :arg1 applications should be displayed
     */
    public function applicationsShouldBeDisplayed($arg1)
    {
        throw new PendingException();
    }

    /**
     * @When I add a application
     */
    public function iAddAApplication()
    {
        throw new PendingException();
    }

    /**
     * @Then this application should have been saved
     */
    public function thisApplicationShouldHaveBeenSaved()
    {
        throw new PendingException();
    }

    /**
     * @Given the application with :arg1 :arg2 exists
     */
    public function theApplicationWithExists($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Given the application with :arg1 :arg2 does not exist
     */
    public function theApplicationWithDoesNotExist($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @When I edit this application to have :arg1 :arg2
     */
    public function iEditThisApplicationToHave($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @When I delete this application
     */
    public function iDeleteThisApplication()
    {
        throw new PendingException();
    }

    /**
     * @Then this application should have been removed
     */
    public function thisApplicationShouldHaveBeenRemoved()
    {
        throw new PendingException();
    }
}
