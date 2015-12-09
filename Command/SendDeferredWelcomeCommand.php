<?php
namespace NyroDev\NyroCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\HttpFoundation\Request;

class SendDeferredWelcomeCommand extends ContainerAwareCommand {
	
	protected function configure() {
        $this
            ->setName('nyrocms:sendDeferredWelcome')
            ->setDescription('Send welcome email for user validated today');
    }
	
	protected function execute(InputInterface $input, OutputInterface $output) {
		$lockHandler = new LockHandler('sendDeferredWelcome.lock');
		if ($lockHandler->lock()) {
			$this->getContainer()->get('nyrodev')->increasePhpLimits();

			$this->getContainer()->enterScope('request');
			$this->getContainer()->set('request', new Request(), 'request');

			$context = $this->getContainer()->get('router')->getContext();
			$context->setScheme($this->getContainer()->getParameter('nyroCms.email.router_scheme'));
			$context->setHost($this->getContainer()->getParameter('nyroCms.email.router_host'));
			$context->setBaseUrl($this->getContainer()->getParameter('nyroCms.email.router_base_url'));

			$users = $this->getContainer()->get('nyrocms_db')->getUserRepository()->getForWelcomeEmails();
			$nbUsers = count($users);

			$output->writeln($nbUsers.' are activated or has as password key which ends today.');

			if ($nbUsers > 0) {
				foreach($users as $user) {
					$this->getContainer()->get('nyrocms_user')->sendWelcomeEmail($user);
				}
			}

			$output->writeln('End of welcome email sending.');
			$lockHandler->release();
		} else {
			$output->writeln('sendDeferredWelcome command is locked.');
		}
	}
	
}