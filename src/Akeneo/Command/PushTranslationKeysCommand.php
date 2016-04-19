<?php

namespace Akeneo\Command;

use Akeneo\Nelson\PushTranslationKeysExecutor;
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
        $updateDir   = $this->container->getParameter('crowdin.upload')['base_dir'] . '/update';
        $branches    = $this->container->getParameter('github.branches');

        /** @var PushTranslationKeysExecutor $executor */
        $executor = $this->container->get('nelson.push_translation_keys_executor');
        $executor->execute($branches, $updateDir);
    }
}
