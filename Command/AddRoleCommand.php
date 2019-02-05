<?php

namespace NyroDev\NyroCmsBundle\Command;

use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class AddRoleCommand extends Command
{
    protected $db;

    public function __construct(DbAbstractService $db)
    {
        $this->db = $db;

        parent::__construct();
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('nyrocms:addRole')
            ->setDescription('Create DB user role according to configuration')
            ->addArgument('name', InputArgument::OPTIONAL, 'Role name', null)
            ->addArgument('roleName', InputArgument::OPTIONAL, 'Internal role name', null)
            ->addArgument('internal', InputArgument::OPTIONAL, 'Internal', null);
    }

    /**
     * Executes the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $roleName = $input->getArgument('roleName');
        $internal = $input->getArgument('internal');

        $helper = $this->getHelper('question');
        if (!$name) {
            $question = new Question('Please enter the name of the user role: ', 'Admin');
            $name = $helper->ask($input, $output, $question);
        }
        if (!$roleName) {
            $question = new Question('Please enter the name of the internal role name, if needed: ');
            $roleName = $helper->ask($input, $output, $question);
        }
        if (is_null($internal)) {
            $question = new ChoiceQuestion(
                'Is this role internal?',
                array('false', 'true'),
                0);
            $internal = $helper->ask($input, $output, $question);
        }

        $newRole = $this->db->getNew('user_role');

        /* @var $newRole \NyroDev\NyroCmsBundle\Model\UserRole */

        $newRole->setName($name);
        $newRole->setRoleName($roleName);
        $newRole->setInternal('true' === $internal);

        $this->db->flush();

        $output->writeln('New role "'.$name.'" added with ID: '.$newRole->getId());
    }
}
