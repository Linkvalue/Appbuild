<?php

namespace Majora\OTAStore\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command appbuild:application:purge-downloading-tokens.
 */
class PurgeDownloadingTokensCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('appbuild:application:purge-downloading-tokens')
            ->setDescription('Purge expired downloading tokens.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('appbuild.application.build_token_manager')->purge();
    }
}
