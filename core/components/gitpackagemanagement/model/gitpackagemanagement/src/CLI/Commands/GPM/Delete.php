<?php
namespace GPM\CLI\Commands\GPM;

use GPM\CLI\Commands\PackageCommand;
use GPM\Config\Config;
use GPM\Config\Loader\JSON;
use GPM\Config\Parser\Parser;
use GPM\Config\Validator\ValidatorException;
use GPM\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class Delete extends PackageCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Delete GPM')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);
        $logger = new ConsoleLogger($output);

        try {
            $config = new Config($this->getApplication()->modx, $this->package->dir_name, dirname(Utils::getGPMDir()) . '/');
            $parser = new Parser($this->getApplication()->modx, $config);
            $loader = new JSON($parser);
            $loader->loadAll();

            $fs = new Filesystem();
            $fs->remove([$this->getApplication()->modx->getOption('assets_path') . 'components/' . $config->general->lowCaseName]);
            
            $deleter = new \GPM\Action\Delete($config, $this->package, $logger);
            $deleter->delete();
        } catch (ValidatorException $ve) {
            $logger->error('Config file is invalid.');
            $logger->error($ve->getMessage());


            return null;
        } catch (\Exception $e) {
            $logger->error($e->getMessage());

            return null;
        }
    }
}
