<?php

namespace NyroDev\NyroCmsBundle\Command;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use NyroDev\NyroCmsBundle\Services\UserService;
use NyroDev\UtilityBundle\Services\NyrodevService;
use NyroDev\UtilityBundle\Services\Traits\LockFactoryServiceableTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\RouterInterface;

class SendDeferredWelcomeCommand extends Command
{
    use LockFactoryServiceableTrait;

    public function __construct(
        private readonly NyrodevService $nyrodev,
        private readonly DbAbstractService $db,
        private readonly UserService $user,
        private readonly RouterInterface $router,
        private readonly ParameterBagInterface $params,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('nyrocms:sendDeferredWelcome')
            ->setDescription('Send welcome email for user validated today');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lock = $this->getLockFactory()->createLock('sendDeferredWelcome.lock');

        if ($lock->acquire()) {
            $this->nyrodev->increasePhpLimits();

            $context = $this->router->getContext();
            $context->setScheme($this->params->get('nyrocms.email.router_scheme'));
            $context->setHost($this->params->get('nyrocms.email.router_host'));
            $context->setBaseUrl($this->params->get('nyrocms.email.router_base_url'));

            $users = $this->db->getUserRepository()->getForWelcomeEmails();
            $nbUsers = count($users);

            $output->writeln($nbUsers.' are activated or has as password key which ends today.');

            if ($nbUsers > 0) {
                $defaultLocale = $this->params->get('locale');
                foreach ($users as $user) {
                    $this->user->sendWelcomeEmail($user, $defaultLocale);
                }
            }

            $output->writeln('End of welcome email sending.');
            $lock->release();

            return Command::SUCCESS;
        }
        $output->writeln('sendDeferredWelcome command is locked.');

        return Command::INVALID;
    }
}
