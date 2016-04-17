<?php

namespace Akeneo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clone the project in a temporary directory, push any translation file (only english) to Crowdin
 *
 * Note that this command will not create new files on Crowdin
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PushTranslationKeysCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nelson:push-translation-keys')
            ->setDescription('Fetch new translation keys from Github and push the updated files to Crowdin');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cloner      = $this->container->get('github.cloner');
        $updateDir   = $this->container->getParameter('crowdin.upload')['base_dir'] . '/update';
        $projectInfo = $this->container->get('crowdin.translation_files.project_info');
        $branches    = $this->container->getParameter('github.branches');

        foreach ($branches as $baseBranch) {
            $projectDir = $cloner->cloneProject($updateDir, $baseBranch);

            $files = $this->container
                ->get('akeneo.system.translation_files.provider')
                ->provideTranslations($projectDir);

            $this->container
                ->get('crowdin.translation_files.directories_creator')
                ->create($files, $projectInfo, $baseBranch);

            $this->container
                ->get('crowdin.translation_files.files_creator')
                ->create($files, $projectInfo, $baseBranch);

            $this->container->get('crowdin.translation_files.updater')->update($files, $baseBranch);
        }

        $this->container->get('akeneo.system.executor')->execute(sprintf('rm -rf %s', $updateDir));
    }
}
