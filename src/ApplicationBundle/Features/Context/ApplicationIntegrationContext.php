<?php

namespace Majora\OTAStore\ApplicationBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\MinkContext;
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
class ApplicationIntegrationContext implements Context
{
    /** @var MinkContext */
    private $minkContext;

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

    /** @var bool */
    private $streamBuildsContent;

    /** @var string */
    private $webRelativeBuildsApplicationDir;

    /**
     * Constructor.
     *
     * @param EntityManager         $manager
     * @param Router                $router
     * @param Session               $session
     * @param TokenStorageInterface $tokenStorage
     * @param Filesystem            $filesystem
     * @param bool                  $streamBuildsContent
     * @param string                $webRelativeBuildsApplicationDir
     */
    public function __construct(
        EntityManager $manager,
        Router $router,
        Session $session,
        TokenStorageInterface $tokenStorage,
        Filesystem $filesystem,
        $streamBuildsContent,
        $webRelativeBuildsApplicationDir
    ) {
        $this->manager = $manager;
        $this->router = $router;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
        $this->filesystem = $filesystem;
        $this->streamBuildsContent = $streamBuildsContent;
        $this->webRelativeBuildsApplicationDir = $webRelativeBuildsApplicationDir;
    }

    /**
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
        foreach ($schemaManager->listTables() as $table) {
            $connection->exec(sprintf('DELETE FROM %s', $table->getName()));
        }
    }

    /**
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        /* @var \Behat\Behat\Context\Environment\InitializedContextEnvironment $environment */
        $environment = $scope->getEnvironment();

        $this->minkContext = $environment->getContext(MinkContext::class);
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
            $application->setPackageName($data['app_package_name'] ?: null);
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
        $this->minkContext->assertElementOnPage('article.table');
        $this->minkContext->assertNumElements($nbApp, 'article.table > .table__row-body');
    }

    /**
     * @Then I should see the build with version :buildVersion for application with id :applicationId
     */
    public function iShouldSeeTheBuildWithVersion($buildVersion, $applicationId)
    {
        $this->minkContext->assertElementContainsText('article.table > .table__row-body[data-id="'.$applicationId.'"] .table__cell-last-build .label-version', $buildVersion);
    }

    /**
     * @Then I should not see the build with version :buildVersion for application with id :applicationId
     */
    public function iShouldNotSeeTheBuildWithVersion($buildVersion, $applicationId)
    {
        $this->minkContext->assertElementNotContainsText('article.table > .table__row-body[data-id="'.$applicationId.'"] .table__cell-last-build .label-version', $buildVersion);
    }

    /**
     * @When I add an application with support :support with label :label
     */
    public function iAddAnApplication($support, $label)
    {
        $url = $this->router->generate('majoraotastore_admin_application_create');
        $this->minkContext->visit($url);

        $this->minkContext->fillField('majoraotastore_application[label]', $label);
        $this->minkContext->selectOption('majoraotastore_application[support]', $support);

        // iOS support needs package identifier
        if ($support == Application::SUPPORT_IOS) {
            $this->minkContext->fillField('majoraotastore_application[packageName]', 'my package');
        }

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name="majoraotastore_application"]')->submit();
    }

    /**
     * @Then the application with label :label should have been saved
     */
    public function theApplicationWithLabelShouldHaveBeenSaved($label)
    {
        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertElementOnPage('article.table');
        $this->minkContext->assertElementContainsText('article.table', $label);
    }

    /**
     * @When I edit the application with id :id to have label :label
     */
    public function iEditTheApplicationWithIdToHaveLabel($id, $label)
    {
        $url = $this->router->generate('majoraotastore_admin_application_update', ['id' => $id]);
        $this->minkContext->visit($url);

        $this->minkContext->fillField('majoraotastore_application[label]', $label);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name="majoraotastore_application"]')->submit();
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
        $this->minkContext->assertElementOnPage('article.table');
        $this->minkContext->assertNumElements($nbBuilds, 'article.table > .table__row-body');
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

        $this->minkContext->fillField('majoraotastore_build[version]', $version);
        $this->minkContext->getSession()->getPage()->find('css', '[name="majoraotastore_build[filename]"]')->setValue($filename);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name="majoraotastore_build"]')->submit();
    }

    /**
     * @Then the build with version :version should have been saved
     */
    public function theBuildWithVersionShouldHaveBeenSaved($version)
    {
        $this->minkContext->assertResponseStatus(200);
        $this->minkContext->assertElementOnPage('article.table');
        $this->minkContext->assertElementContainsText('article.table', $version);
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

        $this->minkContext->fillField('majoraotastore_build[version]', $version);

        //follow redirection after form submission
        $this->getClient()->followRedirects(true);
        $page = $this->minkContext->getSession()->getPage();
        $page->find('css', 'form[name="majoraotastore_build"]')->submit();
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
                if ($this->streamBuildsContent) {
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
                if ($this->streamBuildsContent) {
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
                        $this->webRelativeBuildsApplicationDir,
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
        $response = json_decode($this->getClient()->getInternalResponse()->getContent(), JSON_OBJECT_AS_ARRAY);

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
        $driver = $this->minkContext->getSession()->getDriver();
        if (!$driver instanceof BrowserKitDriver) {
            throw new UnsupportedDriverActionException('This step is only supported by the BrowserKitDriver', $driver);
        }

        return $driver->getClient();
    }
}
