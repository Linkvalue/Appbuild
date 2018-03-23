<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // symfony standard
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // vendors
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Hautelook\AliceBundle\HautelookAliceBundle(),
            new Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Lexik\Bundle\JWTAuthenticationBundle\LexikJWTAuthenticationBundle(),

            // projects
            new LinkValue\Appbuild\AppBundle\AppbuildAppBundle(),
            new LinkValue\Appbuild\UserBundle\AppbuildUserBundle(),
            new LinkValue\Appbuild\ApplicationBundle\AppbuildApplicationBundle(),
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return dirname(__DIR__).'/var/cache/'.$this->getEnvironment();
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');

        // Handle webpack-dev-server assets dynamically served
        if ($this->getEnvironment() === 'dev') {
            $loader->load(function (ContainerBuilder $container) {
                // Check if webpack dev server is up before using it
                @file_get_contents($container->getParameter('webpack_dev_server_base_url'));
                if (!isset($http_response_header)) {
                    return;
                }

                // Override "framework.assets.packages.static" configuration to use webpack dev server
                $container->loadFromExtension('framework', [
                    'assets' => [
                        'packages' => [
                            'static' => [
                                'base_path' => null,
                                'base_url' => sprintf(
                                    '%s/%s',
                                    rtrim($container->getParameter('webpack_dev_server_base_url'), '/'),
                                    $container->getParameter('static_assets_base_path')
                                ),
                            ],
                        ],
                    ],
                ]);
            });
        }
    }
}
