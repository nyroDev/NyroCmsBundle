<?php

namespace NyroDev\NyroCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class CreateDbClassesCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('nyrocms:createDbClasses')
            ->setDescription('Create DB classes according to configuration')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Overwrite classes of existing');
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $force = $input->getOption('force');

        $db_driver = $this->getContainer()->getParameter('nyroDev_utility.db_driver');

        $dirname = null;
        switch ($db_driver) {
            case 'orm':
                $dirname = 'Entity';
                break;
        }

        if ($dirname) {
            $sourceDir = realpath(__DIR__.'/../Model/'.$dirname);
            $converter = new CamelCaseToSnakeCaseNameConverter();
            $dbService = $this->getContainer()->get('nyrocms_db');
            $namespace = $dbService->getNamespace();
            $originalNamespace = 'NyroDev\NyroCmsBundle\Model\\'.$dirname;

            $srcDir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $finder = new Finder();
            $sources = $finder
                    ->files()
                    ->name('*.php')
                    ->in($sourceDir);
            $fs = new Filesystem();
            foreach ($sources as $source) {
                /* @var $source SplFileInfo */
                $classname = lcfirst(substr($source->getBasename(), 0, -4));
                $classnameIdent = $converter->normalize($classname);
                $src = $source->getRealPath();
                $dstClass = $dbService->getClass($classnameIdent, false);

                $dst = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $srcDir.'/'.$namespace.'/'.$dstClass.'.php');

                $exists = $fs->exists($dst);

                if ($force || !$exists) {
                    $output->writeln(($exists ? 'Overwriting' : 'Writing').': '.$dst);
                    $fs->dumpFile($dst, str_replace($originalNamespace, $namespace, file_get_contents($src)));
                } else {
                    $output->writeln('Exists: '.$dst);
                }
            }
        } else {
            $output->writeln($db_driver.' is not supported.');
        }
    }
}
