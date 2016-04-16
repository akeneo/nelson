<?php

namespace Akeneo\Command;

use Github\Exception\ValidationFailedException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone the project in a temporary directory, download translations from Crowdin, clean the translation files, open
 * a pull request on Github
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PullTranslationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:pull-translations')
            ->setDescription('Fetch new translations from Crowdin and create pull requests to the Github repository')
            ->addArgument('username', InputArgument::REQUIRED, 'Github username')
            ->addArgument('edition', InputArgument::REQUIRED, 'PIM edition, community or enterprise');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $edition = $input->getArgument('edition');

        $logger              = $this->container->get('logger');
        $cloner              = $this->container->get('github.cloner');
        $pullRequestCreator  = $this->container->get('github.pull_request_creator');
        $downloader          = $this->container->get('crowdin.packages.downloader');
        $status              = $this->container->get('crowdin.translated_progress.selector');
        $extractor           = $this->container->get('crowdin.packages.extractor');
        $translationsCleaner = $this->container->get('akeneo.system.translation_files.cleaner');

        $options   = $this->container->getParameter('crowdin.download');
        $updateDir = $options['base_dir'] . '/update';
        $packages  = $status->packages($options['min_translated_progress']);

        if (count($packages) <= 0) {
            $output->writeln(sprintf(
                "There is no packages with minimal translation of %s%%. Nothing to do.",
                $options['min_translated_progress']
            ));
            return 0;
        }

        foreach ($options[$edition]['branches'] as $baseBranch) {
            $projectDir = $cloner->cloneProject($username, $updateDir, $edition, $baseBranch);
            $downloader->download($packages, $options['base_dir'], $baseBranch);
            $extractor->extract($packages, $options['base_dir'], $updateDir);
            $translationsCleaner->cleanFiles($options['locale_map'], $projectDir);

            try {
                $pullRequestCreator->create($baseBranch, $options['base_dir'], $projectDir, $edition, $username);
            } catch (ValidationFailedException $exception) {
                $message = sprintf(
                    'No PR created for version "%s", message "%s"',
                    $baseBranch,
                    $exception->getMessage()
                );
                $output->writeln(sprintf('<warning>%s<warning>', $message));
                $logger->addWarning($message);
            }
        }

        $this->container->get('akeneo.system.executor')->execute(sprintf('rm -rf %s', $options['base_dir'] . '/update'));
    }
}
