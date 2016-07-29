<?php

namespace AppBuild\Bundle\ApplicationBundle\Features\Context;

use AppBuild\Bundle\ApplicationBundle\Entity\Application;
use AppBuild\Bundle\ApplicationBundle\Entity\Build;
use AppBuild\Bundle\UserBundle\Entity\User;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Defines application features from the specific context.
 */
class ApplicationIntegrationContext implements SnippetAcceptingContext
{
    const MAIN_TABLE_SELECTOR = '#main-content table';

    /** @var EntityManager */
    private $manager;

    /** @var Router */
    private $router;

    /** @var Session */
    private $session;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var \Behat\MinkExtension\Context\MinkContext */
    private $minkContext;

    public function __construct(
        EntityManager $manager,
        Router $router,
        Session $session,
        TokenStorageInterface $tokenStorage
    ) {
        $this->manager = $manager;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /* @var \Behat\Behat\Context\Environment\InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext('Behat\MinkExtension\Context\MinkContext');
    }

    /**
     * Bootstrap test database.
     *
     * @BeforeFeature
     */
    public static function initBdd(BeforeFeatureScope $scope)
    {
        exec('php app/console --env=test doctrine:schema:drop --force');
        exec('php app/console --env=test doctrine:schema:create');
    }

    /**
     * @BeforeScenario
     */
    public function truncateBdd(BeforeScenarioScope $scope)
    {
        $connection = $this->manager->getConnection();

        $schemaManager = $connection->getSchemaManager();
        $tables = $schemaManager->listTables();
        foreach ($tables as $table) {
            $connection->exec(sprintf('DELETE FROM %s', $table->getName()));
        }
    }

    /**
     * @Given I am authenticated with role :role
     *
     * @link http://symfony.com/doc/current/cookbook/testing/simulating_authentication.html
     */
    public function iAmAuthenticatedWithRole($role)
    {
        $client = $this->getClient();
        $session = $this->session;
        $user = $this->createUserWithRoles([$role]);

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    /**
     * @param string[] $roles
     *
     * @return User
     */
    private function createUserWithRoles(array $roles)
    {
        $user = new User();
        $user->setRoles($roles);

        $user->setEmail('my@email.com');
        $user->setPassword('my_password');
        $user->setFirstname('my firstname');
        $user->setLastname('my lastname');

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    /**
     * @Given there are these applications:
     */
    public function thereAreTheseApplications(TableNode $applications)
    {
        //id is auto-increment and should be specified only when testing
        $idProperty = new \ReflectionProperty(Application::class, 'id');
        $idProperty->setAccessible(true);

        foreach ($applications as $data) {
            $application = new Application();
            $idProperty->setValue($application, $data['app_id']);
            $application->setLabel($data['app_label']);
            $application->setSupport($data['app_support']);
            $this->manager->persist($application);
        }
        $this->manager->flush();
    }

    /**
     * @Given there are these builds:
     */
    public function thereAreTheseBuilds(TableNode $builds)
    {
        $appRepository = $this->manager->getRepository('AppBuildApplicationBundle:Application');

        //id is auto-increment and should be specified only when testing
        $idProperty = new \ReflectionProperty(Build::class, 'id');
        $idProperty->setAccessible(true);

        foreach ($builds as $data) {
            if (!$application = $appRepository->find($data['app_id'])) {
                throw new \RuntimeException('Application not found: '.$data['app_id']);
            }

            $build = new Build();
            $build->setApplication($application);
            $idProperty->setValue($build, $data['build_id']);
            $build->setVersion($data['build_version']);
            $build->setFilePath('my_path');
            $this->manager->persist($build);
        }
        $this->manager->flush();
    }

    /**
     * @When I list all applications
     */
    public function iListAllApplications()
    {
        $url = $this->router->generate('appbuild_admin_application_list');
        //avoid redirection to login page if authentication has failed
        $this->getClient()->followRedirects(false);
        $this->minkContext->visit($url);
    }

    /**
     * @Then :nbApp applications should be displayed
     */
    public function applicationsShouldBeDisplayed($nbApp)
    {
        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertElementOnPage(self::MAIN_TABLE_SELECTOR);
        $this->minkContext->assertNumElements($nbApp, self::MAIN_TABLE_SELECTOR.' > tbody > tr');
    }

    /**
     * @When I add an application with label :label
     */
    public function iAddAnApplication($label)
    {
        $url = $this->router->generate('appbuild_admin_application_create');
        $this->minkContext->visit($url);

        $this->minkContext->fillField('appbuild_application_label', $label);
        $this->minkContext->fillField('appbuild_application_support', Application::SUPPORT_IOS);
        $this->minkContext->fillField('appbuild_application_packageName', 'my package');

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=appbuild_application]')->submit();
    }

    /**
     * @Then the application with label :label should have been saved
     */
    public function theApplicationWithLabelShouldHaveBeenSaved($label)
    {
        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertElementOnPage(self::MAIN_TABLE_SELECTOR);
        $this->minkContext->assertElementContainsText(self::MAIN_TABLE_SELECTOR, $label);
    }

    /**
     * @When I edit the application with id :id to have label :label
     */
    public function iEditTheApplicationWithIdToHaveLabel($id, $label)
    {
        $url = $this->router->generate('appbuild_admin_application_update', ['id' => $id]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('appbuild_application_label', $label);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=appbuild_application]')->submit();
    }

    /**
     * @When I list all builds of application with id :id
     */
    public function iListAllBuildsOfApplicationsWithId($id)
    {
        $url = $this->router->generate('appbuild_admin_build_list', ['application_id' => $id]);
        //avoid redirection to login page if authentication has failed
        $this->getClient()->followRedirects(false);
        $this->minkContext->visit($url);
    }

    /**
     * @Then :nbBuilds builds should be displayed
     */
    public function buildsShouldBeDisplayed($nbBuilds)
    {
        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertElementOnPage(self::MAIN_TABLE_SELECTOR);
        $this->minkContext->assertNumElements($nbBuilds, self::MAIN_TABLE_SELECTOR.' > tbody > tr');
    }

    /**
     * @When I add a build for application with id :id with version :version and file :file
     */
    public function iAddABuildForApplicationWithIdWithVersion($id, $version, $file)
    {
        $url = $this->router->generate('appbuild_admin_build_create', ['application_id' => $id]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('appbuild_build_version', $version);
        $this->minkContext->attachFileToField('appbuild_build_filePath', $file);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=appbuild_build]')->submit();
    }

    /**
     * @Then the build with version :version should have been saved
     */
    public function theBuildWithVersionShouldHaveBeenSaved($version)
    {
        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertElementOnPage(self::MAIN_TABLE_SELECTOR);
        $this->minkContext->assertElementContainsText(self::MAIN_TABLE_SELECTOR, $version);
    }

    /**
     * @When I edit the build with id :id to have version :version
     */
    public function iEditTheBuildWithIdToHaveVersion($id, $version)
    {
        $url = $this->router->generate('appbuild_admin_build_update', [
            'application_id' => $this->getApplicationIdForBuildId($id),
            'id' => $id,
        ]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('appbuild_build_version', $version);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=appbuild_build]')->submit();
    }

    /**
     * @When I download the latest build
     */
    public function iDownloadTheLatestBuild()
    {
        $buildRepository = $this->manager->getRepository('AppBuildApplicationBundle:Build');
        if (!$build = $buildRepository->findOneBy([], ['id' => 'DESC'])) {
            throw new \RuntimeException('Latest build not found');
        }

        $url = $this->router->generate('appbuild_admin_build_download', [
            'application_id' => $this->getApplicationIdForBuildId($build->getApplication()->getId()),
            'id' => $build->getId(),
        ]);

        //follow correct redirection depending on the support (ios/android)
        $this->getClient()->followRedirects(true);
        $this->minkContext->visit($url);
    }

    /**
     * @Then I receive the latest android build
     */
    public function iReceiveTheLatestAndroidBuild()
    {
        $buildRepository = $this->manager->getRepository('AppBuildApplicationBundle:Build');
        if (!$build = $buildRepository->findOneBy([], ['id' => 'DESC'])) {
            throw new \RuntimeException('Latest build not found');
        }

        //for android : get the raw file
        $expectedUrl = $this->router->generate('appbuild_admin_build_get_raw_file', [
            'application_id' => $this->getApplicationIdForBuildId($build->getApplication()->getId()),
            'id' => $build->getId(),
        ]);

        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertPageAddress($expectedUrl);
        $this->minkContext->assertSession()->responseHeaderEquals('Content-Type', 'application/octet-stream');
    }

    /**
     * @param int $buildId
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    private function getApplicationIdForBuildId($buildId)
    {
        $buildRepository = $this->manager->getRepository('AppBuildApplicationBundle:Build');
        if (!$build = $buildRepository->find($buildId)) {
            throw new \RuntimeException('Build not found: '.$buildId);
        }

        return $build->getApplication()->getId();
    }

    /**
     * @return \Symfony\Component\BrowserKit\Client
     *
     * @throws UnsupportedDriverActionException
     */
    private function getClient()
    {
        /* @var BrowserKitDriver $driver */
        $driver = $this->minkContext->getSession()->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }

        return $driver->getClient();
    }
}
