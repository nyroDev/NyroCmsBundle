<?php
namespace NyroDev\NyroCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ChoiceQuestion;


class addUserCommand extends ContainerAwareCommand {
	
	/**
	 * Configure the command
	 */
	protected function configure() {
		$this
			->setName('nyrocms:addUser')
			->setDescription('Create DB user according to configuration')
            ->addArgument('email', InputArgument::OPTIONAL, 'User email', null)
            ->addArgument('firstname', InputArgument::OPTIONAL, 'User firstname', null)
            ->addArgument('lastname', InputArgument::OPTIONAL, 'User lastname', null)
            ->addArgument('password', InputArgument::OPTIONAL, 'User password', null)
            ->addArgument('usertype', InputArgument::OPTIONAL, 'User type', null)
            ->addArgument('developper', InputArgument::OPTIONAL, 'developper', null)
            ->addArgument('userroles', InputArgument::OPTIONAL, 'User roles', null);
	}
	
	/**
	 * Executes the command
	 *
	 * @param InputInterface $input
	 * @param OutputInterface $output 
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$email = $input->getArgument('email');
		$firstname = $input->getArgument('firstname');
		$lastname = $input->getArgument('lastname');
		$password = $input->getArgument('password');
		$usertype = $input->getArgument('usertype');
		$developper = $input->getArgument('developper');
		$userroles = $input->getArgument('userroles');
		
		$dbService = $this->getContainer()->get('nyrocms_db');
		$userTypes = $this->getContainer()->get('nyrocms_admin')->getUserTypes();
		$userRolesDb = $this->getContainer()->get('nyrocms_admin')->getUserRoles();
		
		$helper = $this->getHelper('question');
		if (!$email) {
			$question = new Question('Please enter the email of the user: ');
			$email = $helper->ask($input, $output, $question);
		}
		if (!$firstname) {
			$question = new Question('Please enter the firstname of the user: ');
			$firstname = $helper->ask($input, $output, $question);
		}
		if (!$lastname) {
			$question = new Question('Please enter the lastname of the user: ');
			$lastname = $helper->ask($input, $output, $question);
		}
		if (!$password) {
			$question = new Question('Please enter the password of the user: ');
			$question->setHidden(true);
			$password = $helper->ask($input, $output, $question);
		}
		if ($usertype && !isset($userTypes[$usertype])) {
			$output->writeln($usertype.' doesn\'t exists');
			$usertype = null;
		}
		if (!$usertype) {
			$question = new ChoiceQuestion(
				'User type for the user?',
				$userTypes
			);
			$usertype = $helper->ask($input, $output, $question);
		}
		
		if (is_null($developper)) {
			$question = new ChoiceQuestion(
				'Is developper?',
				array('false', 'true'),
				0
			);
			$developper = $helper->ask($input, $output, $question);
		}
		
		if ($userroles) {
			$userroles = explode(',', $userroles);
			$tmp = array();
			$error = false;
			foreach($userroles as $id) {
				if (isset($userRolesDb[$id])) {
					$tmp[] = $id;
				} else {
					$output->writeln('User role '.$id.' doesn\'t exists');
					$error = true;
				}
			}
			if (!$error)
				$userroles = $tmp;
			else
				$userroles = array();
		}
		
		if (count($userroles) === 0 && count($userRolesDb) > 0) {
			$userRolesDbList = array(
				'0'=>'nothing'
			);
			foreach($userRolesDb as $id=>$tmp)
				$userRolesDbList[$id] = $id.' - '.$tmp.'';
			$question = new ChoiceQuestion(
				'User roles for the user?',
				$userRolesDbList,
				'0'
			);
			$question->setMultiselect(true);
			$tmp = $helper->ask($input, $output, $question);
			foreach($tmp as $tmp2) {
				$tmp2 = explode(' - ', $tmp2);
				$userroles[] = $tmp2[0];
			}
		}
		
		$newUser = $dbService->getNew('user');
		/* @var $newUser \NyroDev\NyroCmsBundle\Model\User */
		
		$newUser->setEmail($email);
		$newUser->setFirstname($firstname);
		$newUser->setLastname($lastname);
		
		$salt = sha1(uniqid());
		$passwordSalted = $this->getContainer()->get('security.encoder_factory')->getEncoder($newUser)->encodePassword($password, $salt);
		$newUser->setPassword($passwordSalted);
		$newUser->setSalt($salt);
		
		$newUser->setUserType($usertype);
		$newUser->setDevelopper($developper === 'true');
		
		if (count($userroles)) {
			foreach($userroles as $ur) {
				if (isset($userRolesDb[$ur])) {
					$newUser->addUserRole($userRolesDb[$ur]);
				}
			}
		}
		
		$dbService->flush();
		
		$output->writeln('New user "'.$email.'" added with ID: '.$newUser->getId());
	}
}