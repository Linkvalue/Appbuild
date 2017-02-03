<?php

namespace Majora\OTAStore\ApplicationBundle\Features\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Doctrine\ORM\EntityManager;
use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Majora\OTAStore\UserBundle\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Defines application features from the specific context.
 */
class ApplicationIntegrationContext implements SnippetAcceptingContext
{
    use KernelDictionary;

    const MAIN_TABLE_SELECTOR = '#main-content table';

    /** @var EntityManager */
    private $manager;

    /** @var Router */
    private $router;

    /** @var Session */
    private $session;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var Filesystem */
    private $filesystem;

    /** @var \Behat\MinkExtension\Context\MinkContext */
    private $minkContext;

    /**
     * Constructor.
     *
     * @param EntityManager         $manager
     * @param Router                $router
     * @param Session               $session
     * @param TokenStorageInterface $tokenStorage
     * @param Filesystem            $filesystem
     */
    public function __construct(
        EntityManager $manager,
        Router $router,
        Session $session,
        TokenStorageInterface $tokenStorage,
        Filesystem $filesystem
    ) {
        $this->manager = $manager;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->filesystem = $filesystem;
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
        // init test_stream
        exec('php bin/console --env=test_stream doctrine:schema:drop --force');
        exec('php bin/console --env=test_stream doctrine:schema:create');

        // init test_nostream
        exec('php bin/console --env=test_nostream doctrine:schema:drop --force');
        exec('php bin/console --env=test_nostream doctrine:schema:create');
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
     * @see http://symfony.com/doc/current/cookbook/testing/simulating_authentication.html
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
        $appRepository = $this->manager->getRepository('MajoraOTAStoreApplicationBundle:Application');

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
            $build->setFilePath($this->getRealFilePath($data['build_file']));
            $application->addBuild($build);
            $this->manager->persist($application);
        }
        $this->manager->flush();
    }

    /**
     * @When I list all applications
     */
    public function iListAllApplications()
    {
        $url = $this->router->generate('majoraotastore_admin_application_list');
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
     * @Then I should not see the build with version :buildVersion for application with id :applicationId
     */
    public function iShouldNotSeeTheBuildWithVersion($buildVersion, $applicationId)
    {
        $this->minkContext->assertElementNotContainsText(self::MAIN_TABLE_SELECTOR.' tr[data-id="'.$applicationId.'"] td.latest-build', $buildVersion);
    }

    /**
     * @When I add an application with support :support with label :label
     */
    public function iAddAnApplication($support, $label)
    {
        $url = $this->router->generate('majoraotastore_admin_application_create');
        $this->minkContext->visit($url);

        $this->minkContext->fillField('majoraotastore_application_label', $label);
        $this->minkContext->fillField('majoraotastore_application_support', $support);

        // iOS support needs package identifier
        if ($support == Application::SUPPORT_IOS) {
            $this->minkContext->fillField('majoraotastore_application_packageName', 'my package');
        }

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=majoraotastore_application]')->submit();
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
        $url = $this->router->generate('majoraotastore_admin_application_update', ['id' => $id]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('majoraotastore_application_label', $label);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=majoraotastore_application]')->submit();
    }

    /**
     * @When I list all builds of application with id :id
     */
    public function iListAllBuildsOfApplicationsWithId($id)
    {
        $url = $this->router->generate('majoraotastore_admin_build_list', ['application_id' => $id]);
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
        // Perform upload before visiting the form (to workaround AJAX limitations)
        $filename = $this->uploadBuildFileForApplicationWithId($file, $id);

        $url = $this->router->generate('majoraotastore_admin_build_create', ['application_id' => $id]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('majoraotastore_build_version', $version);
        $this->minkContext->fillField('majoraotastore_build_filename', $filename);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=majoraotastore_build]')->submit();
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
        $url = $this->router->generate('majoraotastore_admin_build_update', [
            'application_id' => $this->getApplicationIdForBuildId($id),
            'id' => $id,
        ]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('majoraotastore_build_version', $version);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name=majoraotastore_build]')->submit();
    }

    /**
     * @When I download the latest build
     */
    public function iDownloadTheLatestBuild()
    {
        $buildRepository = $this->manager->getRepository('MajoraOTAStoreApplicationBundle:Build');
        if (!$build = $buildRepository->findOneBy([], ['id' => 'DESC'])) {
            throw new \RuntimeException('Latest build not found');
        }

        $url = $this->router->generate('majoraotastore_admin_build_download', [
            'application_id' => $build->getApplication()->getId(),
            'id' => $build->getId(),
        ]);

        // Follow redirects?
        switch ($build->getApplication()->getSupport()) {
            case Application::SUPPORT_IOS:
                // we're not able to follow iOS protocol redirection
                $this->getClient()->followRedirects(false);
                break;

            default:
                if ($this->getContainer()->getParameter('stream_builds_content')) {
                    // we can follow build file streaming route
                    $this->getClient()->followRedirects(true);
                } else {
                    // we're not able to follow web relative paths to physical file
                    $this->getClient()->followRedirects(false);
                }
                break;
        }
        $this->minkContext->visit($url);
    }

    /**
     * @Then I receive the latest build
     */
    public function iReceiveTheLatestBuild()
    {
        $buildRepository = $this->manager->getRepository('MajoraOTAStoreApplicationBundle:Build');
        if (!$build = $buildRepository->findOneBy([], ['id' => 'DESC'])) {
            throw new \RuntimeException('Latest build not found');
        }

        // Download assertions
        switch ($build->getApplication()->getSupport()) {
            case Application::SUPPORT_IOS:
                $this->minkContext->assertResponseStatus(302);
                if (!preg_match(
                    '@itms-services://\?action=download-manifest&url=.+application.+build.+manifest@',
                    $this->getClient()->getInternalResponse()->getHeader('Location')
                )) {
                    throw new \RuntimeException('Redirection address does not match ios protocol (itms-services...)');
                }
                $this->minkContext->assertSession()->responseHeaderContains('Content-Type', 'text/html');
                break;

            default:
                if ($this->getContainer()->getParameter('stream_builds_content')) {
                    $expectedUrl = $this->router->generate('majoraotastore_admin_build_stream_file', [
                        'application_id' => $build->getApplication()->getId(),
                        'id' => $build->getId(),
                    ]);

                    $this->minkContext->assertResponseStatus(200);
                    $this->minkContext->assertPageAddress($expectedUrl);
                    $this->minkContext->assertSession()->responseHeaderEquals('Content-Type', 'application/octet-stream');
                } else {
                    $this->minkContext->assertResponseStatus(302);
                    $this->minkContext->assertSession()->responseHeaderEquals('Location', sprintf(
                        '/%s/%s/%s',
                        $this->getContainer()->getParameter('web_relative_builds_application_dir'),
                        $build->getApplication()->getSlug(),
                        $build->getFileNameWithExtension()
                    ));
                    $this->minkContext->assertSession()->responseHeaderContains('Content-Type', 'text/html');
                }
                break;
        }
    }

    /**
     * @param string $file
     * @param int    $id
     *
     * @return string
     */
    private function uploadBuildFileForApplicationWithId($file, $id)
    {
        // Create a temporary file which will be actually uploaded
        $filePath = $this->getRealFilePath($file);
        $tmpFilePath = sprintf(
            '%s_tmp.%s',
            pathinfo($filePath, PATHINFO_FILENAME),
            pathinfo($filePath, PATHINFO_EXTENSION)
        );
        $this->filesystem->copy($filePath, $tmpFilePath);

        // Upload build file
        $url = $this->router->generate('majoraotastore_admin_build_upload', ['application_id' => $id]);
        $this->getClient()->request(
            'POST',
            $url,
            [],
            ['build_file' => new UploadedFile($tmpFilePath, $file)],
            ['HTTP_X-Requested-With' => 'XMLHttpRequest']
        );
        $response = json_decode($this->getClient()->getInternalResponse()->getContent(), true);

        return $response['filename'];
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
        $buildRepository = $this->manager->getRepository('MajoraOTAStoreApplicationBundle:Build');
        if (!$build = $buildRepository->find($buildId)) {
            throw new \RuntimeException('Build not found: '.$buildId);
        }

        return $build->getApplication()->getId();
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function getRealFilePath($file)
    {
        return sprintf(
            '%s%s%s',
            rtrim(realpath($this->minkContext->getMinkParameter('files_path')), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            $file
        );
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
