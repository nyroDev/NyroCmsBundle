<?php
namespace NyroDev\NyroCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;

class addRoleCommand extends ContainerAwareCommand {
	
	/**
	 * Configure the command
	 */
	protected function configure() {
		$this
			->setName('nyrocms:addRole')
			->setDescription('Create DB user role according to configuration')
            ->addArgument('name', InputArgument::OPTIONAL, 'Role name', null)
            ->addArgument('internal', InputArgument::OPTIONAL, 'Internal', null);
	}
	
	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output 
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$name = $input->getArgument('name');
		$internal = $input->getArgument('internal');
		
		$helper = $this->getHelper('question');
		if (!$name) {
			$question = new Question('Please enter the name of the user role: ', 'Admin');
			$name = $helper->ask($input, $output, $question);
		}
		if (is_null($internal)) {
			$question = new ChoiceQuestion(
				'Is this role internal?',
				array('false', 'true'),
				0);
			$internal = $helper->ask($input, $output, $question);
		}
		
		$dbService = $this->getContainer()->get('nyrocms_db');
		$newRole = $dbService->getNew('user_role');
		
		/* @var $newRole \NyroDev\NyroCmsBundle\Model\UserRole */
		
		$newRole->setName($name);
		$newRole->setInternal($internal === 'true');
		
		$dbService->flush();
		
		$output->writeln('New role "'.$name.'" added with ID: '.$newRole->getId());
	}
}