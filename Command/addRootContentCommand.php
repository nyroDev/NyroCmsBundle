<?php
namespace NyroDev\NyroCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class addRootContentCommand extends ContainerAwareCommand {
	
	/**
	 * Configure the command
	 */
	protected function configure() {
		$this
			->setName('nyrocms:addRootContent')
			->setDescription('Create DB root content role according to configuration')
            ->addArgument('title', InputArgument::OPTIONAL, 'Content title', null)
            ->addArgument('handler', InputArgument::OPTIONAL, 'Content handler', null)
            ->addArgument('theme', InputArgument::OPTIONAL, 'Content theme', null);
	}
	
	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output 
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$title = $input->getArgument('title');
		$handler = $input->getArgument('handler');
		$theme = $input->getArgument('theme');
		
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
		
		$dbService = $this->getContainer()->get('nyrocms_db');
		$newContent = $dbService->getNew('content');
		
		/* @var $newContent \NyroDev\NyroCmsBundle\Model\Content */
		
		$newContent->setTitle($title);
		$newContent->setUrl('/');
		$newContent->setHandler($handler);
		$newContent->setTheme($theme);
		
		$dbService->flush();
		
		$output->writeln('New content "'.$title.'" added with ID: '.$newContent->getId());
	}
}