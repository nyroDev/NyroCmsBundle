<?php

namespace NyroDev\NyroCmsBundle\Command;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\UtilityBundle\Services\Traits\KernelInterfaceServiceableTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CreateDbClassesCommand extends Command
{
    use KernelInterfaceServiceableTrait;

    protected $db;
    protected $params;

    public function __construct(DbAbstractService $db, ParameterBagInterface $params)
    {
        $this->db = $db;
        $this->params = $params;

        parent::__construct();
    }

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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');

        $db_driver = $this->params->get('nyroDev_utility.db_driver');

        $dirname = null;
        switch ($db_driver) {
            case 'orm':
                $dirname = 'Entity';
                break;
        }

        if ($dirname) {
            $sourceDir = realpath(__DIR__.'/../Model/'.$dirname);
            $namespace = $this->db->getNamespace();

            $namespaceDir = str_replace('App\\', '', $namespace);

            $originalNamespace = 'NyroDev\NyroCmsBundle\Model\\'.$dirname;

            $projectDir = $this->getKernelInterface()->getProjectDir();

            $srcDir = $projectDir.'/src';

            $search = [
                $originalNamespace,
                'use '.$this->db->getNamespace().'\\Traits\\',
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
                $dstClass = $this->db->getClass($classname, false);

                $dst = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $srcDir.'/'.$namespaceDir.'/'.$dstClass.'.php');

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

            $dirDestMapping = $projectDir.'/config/nyrocms-doctrine-mapping';
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

            return Command::SUCCESS;
        } else {
            $output->writeln($db_driver.' is not supported.');

            return Command::INVALID;
        }
    }
}
