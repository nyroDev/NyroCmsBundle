<?php

namespace NyroDev\NyroCmsBundle\Command;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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
            $dbService = $this->getContainer()->get(DbAbstractService::class);
            $namespace = $dbService->getNamespace();

            $namespaceDir = str_replace('App\\', '', $namespace);

            $originalNamespace = 'NyroDev\NyroCmsBundle\Model\\'.$dirname;

            $srcDir = dirname($this->getContainer()->getParameter('kernel.root_dir')).'/src';

            $search = [
                $originalNamespace,
                'use '.$dbService->getNamespace().'\\Traits\\',
            ];
            $replace = [
                $namespace,
                'use NyroDev\\NyroCmsBundle\\Model\\Entity\\Traits\\',
            ];

            $finder = new Finder();
            $sources = $finder
                    ->files()
                    ->name('*.php')
                    ->in($sourceDir)
                    ->exclude('Traits');
            $fs = new Filesystem();
            foreach ($sources as $source) {
                /* @var $source SplFileInfo */
                $classname = lcfirst(substr($source->getBasename(), 0, -4));
                $src = $source->getRealPath();
                $dstClass = $dbService->getClass($classname, false);

                $dst = str_replace(array('/', '\\'), array(DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR), $srcDir.'/'.$namespaceDir.'/'.$dstClass.'.php');

                $exists = $fs->exists($dst);

                if ($force || !$exists) {
                    $output->writeln(($exists ? 'Overwriting' : 'Writing').': '.$dst);
                    $fs->dumpFile($dst, str_replace($search, $replace, file_get_contents($src)));
                } else {
                    $output->writeln('Exists: '.$dst);
                }
            }

            $output->writeln('-------------------------------');
            $output->writeln('Start copying doctrine mapping');

            $dirDestMapping = $this->getContainer()->getParameter('kernel.project_dir').'/config/nyrocms-doctrine-mapping';
            if (!$fs->exists($dirDestMapping)) {
                $fs->mkdir($dirDestMapping);
            }

            $searchMapping = [
                'entity="NyroDev\\NyroCmsBundle\\Model',
            ];
            $replaceMapping = [
                'entity="'.$namespace,
            ];

            $dirSrcMapping = realpath(__DIR__.'/../Resources/config/doctrine-mapping');
            $finder = new Finder();
            $sourcesMapping = $finder
                    ->files()
                    ->name('*.xml')
                    ->in($dirSrcMapping);
            foreach ($sourcesMapping as $sourceMapping) {
                $src = $sourceMapping->getRealPath();
                $dst = $dirDestMapping.'/'.$sourceMapping->getBaseName();
                $exists = $fs->exists($dst);
                if ($force || !$exists) {
                    $output->writeln(($exists ? 'Overwriting' : 'Writing').': '.$dst);
                    $fs->dumpFile($dst, str_replace($searchMapping, $replaceMapping, file_get_contents($src)));
                } else {
                    $output->writeln('Exists: '.$dst);
                }
            }
        } else {
            $output->writeln($db_driver.' is not supported.');
        }
    }
}
