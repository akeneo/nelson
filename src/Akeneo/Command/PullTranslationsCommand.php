<?php

namespace Akeneo\Command;

use Github\Exception\ValidationFailedException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone the project in a temporary directory, download translations from Crowdin, clean the translation files, open
 * a pull request on Github
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PullTranslationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nelson:pull-translations')
            ->setDescription('Fetch new translations from Crowdin and create pull requests to the Github repository');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $logger              = $this->container->get('logger');
        $cloner              = $this->container->get('github.cloner');
        $pullRequestCreator  = $this->container->get('github.pull_request_creator');
        $downloader          = $this->container->get('crowdin.packages.downloader');
        $status              = $this->container->get('crowdin.translated_progress.selector');
        $extractor           = $this->container->get('crowdin.packages.extractor');
        $translationsCleaner = $this->container->get('akeneo.system.translation_files.cleaner');
        $systemExecutor      = $this->container->get('akeneo.system.executor');

        $branches      = $this->container->getParameter('github.branches');
        $options       = $this->container->getParameter('crowdin.download');
        $updateDir     = $options['base_dir'] . '/update';
        $cleanerDir    = $options['base_dir'] . '/clean';
        $packages      = array_keys($status->packages());
        $patternSuffix = $this->container->getParameter('system.pattern_suffix');

        if (count($packages) <= 0) {
            $output->writeln(sprintf(
                "There is no packages with minimal translation of %s%%. Nothing to do.",
                $options['min_translated_progress']
            ));
            return 0;
        }

        foreach ($branches as $baseBranch) {
            $projectDir = $cloner->cloneProject($updateDir, $baseBranch);
            $downloader->download($packages, $options['base_dir'], $baseBranch);
            $extractor->extract($packages, $options['base_dir'], $cleanerDir);
            $translationsCleaner->cleanFiles($options['locale_map'], $cleanerDir);
            $systemExecutor->execute(sprintf(
                'cp -r %s%s%s%s* %s%s',
                $cleanerDir,
                DIRECTORY_SEPARATOR,
                $patternSuffix,
                DIRECTORY_SEPARATOR,
                $projectDir,
                DIRECTORY_SEPARATOR,
                $cleanerDir
            ));
            $systemExecutor->execute(sprintf('rm -rf %s', $cleanerDir));

            try {
                $pullRequestCreator->create($baseBranch, $options['base_dir'], $projectDir);
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

        $systemExecutor->execute(sprintf('rm -rf %s', $options['base_dir'] . '/update'));
    }
}
