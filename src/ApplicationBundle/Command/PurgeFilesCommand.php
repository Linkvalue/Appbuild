<?php

namespace Majora\OTAStore\ApplicationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command appbuild:application:purge-files.
 */
class PurgeFilesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('appbuild:application:purge-files')
            ->setDescription('Purge unused uploaded files.')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Immediately purge all unused files without asking for confirmation.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $buildFilesPurger = $this->getContainer()->get('appbuild.application.build_files_purger');

        if (!$input->getOption('force')) {
            // Check if there is something to purge
            $filesToPurge = $buildFilesPurger->getUnusedFiles();
            if ($filesToPurge->count() === 0) {
                $output->writeln('Nothing to purge.');

                return;
            }

            // Show all files to purge
            $output->writeln('List of unused files:');
            foreach ($filesToPurge as $file) {
                $output->writeln($file->getRealPath());
            }

            // Ask confirmation
            $questionHelper = $this->getHelper('question');
            $question = new ConfirmationQuestion('Are you sure to delete these files (y/n)? ', false);
            if (!$questionHelper->ask($input, $output, $question)) {
                return;
            }
        }

        // Purge
        $buildFilesPurger->purge();
    }
}
