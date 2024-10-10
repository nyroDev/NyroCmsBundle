<?php

namespace NyroDev\NyroCmsBundle\Command;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AddRootContentCommand extends Command
{
    public function __construct(
        private readonly DbAbstractService $db,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('nyrocms:addRootContent')
            ->setDescription('Create DB root content role according to configuration')
            ->addArgument('title', InputArgument::OPTIONAL, 'Content title', null)
            ->addArgument('handler', InputArgument::OPTIONAL, 'Content handler', null)
            ->addArgument('theme', InputArgument::OPTIONAL, 'Content theme', null)
            ->addArgument('host', InputArgument::OPTIONAL, 'Host constraint', null)
            ->addArgument('locales', InputArgument::OPTIONAL, 'Locales enabled (| separated)', null)
            ->addArgument('xmlSitemap', InputArgument::OPTIONAL, 'Xml sitemap enabling', null)
            ->addArgument('userRole', InputArgument::OPTIONAL, 'Add user role', null)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = $input->getArgument('title');
        $handler = $input->getArgument('handler');
        $theme = $input->getArgument('theme');
        $host = $input->getArgument('host');
        $locales = $input->getArgument('locales');
        $xmlSitemap = $input->getArgument('xmlSitemap');
        $userRole = $input->getArgument('userRole');

        $helper = $this->getHelper('question');
        if (!$title) {
            $question = new Question('Please enter the title of the root content: ');
            $title = $helper->ask($input, $output, $question);
        }
        if (!$handler) {
            $question = new Question('Please enter the handler of the root content: ');
            $handler = $helper->ask($input, $output, $question);
        }
        if (!$theme) {
            $question = new Question('Please enter the theme of the root content: ');
            $theme = $helper->ask($input, $output, $question);
        }
        if (!$host) {
            $question = new Question('Please enter the host of the root content: ');
            $host = $helper->ask($input, $output, $question);
        }
        if (!$locales) {
            $question = new Question('Please enter the locales of the root content: ');
            $locales = $helper->ask($input, $output, $question);
        }
        if (is_null($xmlSitemap)) {
            $question = new ChoiceQuestion(
                'Is Xml sitemap enabled?',
                ['false', 'true'],
                1);
            $xmlSitemap = $helper->ask($input, $output, $question);
        }
        if (is_null($userRole)) {
            $question = new ChoiceQuestion(
                'Add user role too?',
                ['false', 'true'],
                1);
            $userRole = $helper->ask($input, $output, $question);
        }

        $newContent = $this->db->getNew('content');

        /* @var $newContent \NyroDev\NyroCmsBundle\Model\Content */

        $newContent->setTitle($title);
        $newContent->setUrl('/');
        $newContent->setHandler($handler);
        $newContent->setTheme($theme);
        $newContent->setHost($host);
        $newContent->setLocales($locales);
        $newContent->setXmlSitemap('true' === $xmlSitemap);

        if ($userRole) {
            $userRole = $this->db->getNew('userRole');
            $userRole->setName($title);
            $userRole->addContent($newContent);
        }

        $this->db->flush();

        $output->writeln('New content "'.$title.'" added with ID: '.$newContent->getId());
        if ($userRole) {
            $output->writeln('New user role "'.$title.'" added with ID: '.$userRole->getId());
        }

        return Command::SUCCESS;
    }
}
