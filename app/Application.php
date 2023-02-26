<?php

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

/**
 * Class Application
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Application extends BaseApplication
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = 'crowdin', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $this->container = new ContainerBuilder();

        $this->registerExtensions();

        $input = new ArgvInput();
        $configFilename = $input->getParameterOption(['--config_file', '-c'], getenv('CROWDIN_CONFIG') ?: 'config.yml');
        $configFilePath = sprintf(__DIR__ . '/../config/%s', $configFilename);

        if (!file_exists(sprintf($configFilePath))) {
            $output = new ConsoleOutput();
            $output->writeln(
                sprintf(
                    "\n  The file %s%s%s was not found!" .
                    "\n  You need to create your own configuration file." .
                    "\n  You can use --config_file[=CONFIG_FILE] to change default configuration file.\n",
                    __DIR__,
                    DIRECTORY_SEPARATOR,
                    $configFilePath
                )
            );
        } else {
            $loader = new YamlFileLoader($this->container, new FileLocator(__DIR__));
            $loader->load($configFilePath);
            $this->container->compile();
        }

        $this->registerCommands();
    }

    /**
     * Add commands from src to the application
     */
    protected function registerCommands()
    {
        $finder = new Finder();
        $finder->files()
            ->in(__DIR__ . '/../src/Akeneo/Command')
            ->name('*Command.php');

        foreach ($finder as $file) {
            $reflection = new ReflectionClass(
                sprintf('\\Akeneo\\Command\\%s', $file->getBasename('.php'))
            );

            // Exclude abstract layers
            if ($reflection->isAbstract()) {
                continue;
            }

            $classname = $reflection->getName();
            $this->add($this->container->get($classname));
        }
    }

    /**
     * Register extensions from src to the container
     */
    protected function registerExtensions()
    {
        $finder = new Finder();
        $finder->files()
            ->in(__DIR__ . '/../src/Akeneo/DependencyInjection/Extension')
            ->name('*Extension.php');

        foreach ($finder as $file) {
            $classname = sprintf('\\Akeneo\\DependencyInjection\\Extension\\%s', $file->getBasename('.php'));

            $this->container->registerExtension(new $classname());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        $input = parent::getDefaultInputDefinition();
        $input->addOption(
            new InputOption('--config_file', '-c', InputOption::VALUE_OPTIONAL, 'Change config.yml default')
        );

        return $input;
    }
}
