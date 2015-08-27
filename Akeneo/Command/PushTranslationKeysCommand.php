<?php

namespace Akeneo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone the project in a temporary directory, push any translation file (only english) to Crowdin
 *
 * Note that this command will not create new files on Crowdin
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PushTranslationKeysCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('crowdin:push-translation-keys')
            ->setDescription('Fetch new translation keys from Github and push the updated files to Crowdin')
            ->addArgument('username', InputArgument::REQUIRED, 'Github username')
            ->addArgument('edition', InputArgument::REQUIRED, 'PIM edition, community or enterprise');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $edition  = $input->getArgument('edition');

        $cloner     = $this->container->get('github.cloner');
        $updateDir  = $this->container->getParameter('crowdin.upload')['base_dir'] . '/update';
        $projectDir = $cloner->cloneProject($username, $updateDir, $edition);
        $files      = $this->container->get('akeneo.system.translation_files.provider')->provideTranslations($projectDir);

        $this->container->get('crowdin.translation_files.updater')->update($files);
        $this->container->get('akeneo.system.executor')->execute(sprintf('rm -rf %s', $updateDir));
    }
}
