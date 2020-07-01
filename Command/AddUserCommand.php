<?php

namespace NyroDev\NyroCmsBundle\Command;

use NyroDev\NyroCmsBundle\Services\AdminService;
use NyroDev\NyroCmsBundle\Services\Db\DbAbstractService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AddUserCommand extends Command
{
    protected $db;
    protected $admin;
    protected $passwordEncoder;

    public function __construct(DbAbstractService $db, AdminService $admin, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->db = $db;
        $this->admin = $admin;
        $this->passwordEncoder = $passwordEncoder;
        parent::__construct();
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
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
     * Executes the command.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $firstname = $input->getArgument('firstname');
        $lastname = $input->getArgument('lastname');
        $password = $input->getArgument('password');
        $usertype = $input->getArgument('usertype');
        $developper = $input->getArgument('developper');
        $userroles = $input->getArgument('userroles');

        $userTypes = $this->admin->getUserTypeChoices();
        $userRolesDb = $this->admin->getUserRoles();

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
                $userTypes);
            $usertype = $helper->ask($input, $output, $question);
        }

        if (is_null($developper)) {
            $question = new ChoiceQuestion(
                'Is developper?',
                ['false', 'true'],
                0);
            $developper = $helper->ask($input, $output, $question);
        }

        if ($userroles) {
            $userroles = explode(',', $userroles);
            $tmp = [];
            $error = false;
            foreach ($userroles as $id) {
                if (isset($userRolesDb[$id])) {
                    $tmp[] = $id;
                } else {
                    $output->writeln('User role '.$id.' doesn\'t exists');
                    $error = true;
                }
            }
            if (!$error) {
                $userroles = $tmp;
            } else {
                $userroles = [];
            }
        } else {
            $userroles = [];
        }

        if (0 === count($userroles) && count($userRolesDb) > 0) {
            $userRolesDbList = [
                '0' => 'nothing',
            ];
            foreach ($userRolesDb as $id => $tmp) {
                $userRolesDbList[$id] = $id.' - '.$tmp.'';
            }
            $question = new ChoiceQuestion(
                'User roles for the user?',
                $userRolesDbList,
                '0');
            $question->setMultiselect(true);
            $tmp = $helper->ask($input, $output, $question);
            foreach ($tmp as $tmp2) {
                $tmp2 = explode(' - ', $tmp2);
                $userroles[] = $tmp2[0];
            }
        }

        $newUser = $this->db->getNew('user');
        /* @var $newUser \NyroDev\NyroCmsBundle\Model\User */

        $newUser->setEmail($email);
        $newUser->setFirstname($firstname);
        $newUser->setLastname($lastname);

        $passwordSalted = $this->passwordEncoder->encodePassword($newUser, $password);
        $newUser->setPassword($passwordSalted);

        $newUser->setUserType($usertype);
        $newUser->setDevelopper('true' === $developper);

        if (count($userroles)) {
            foreach ($userroles as $ur) {
                if (isset($userRolesDb[$ur])) {
                    $newUser->addUserRole($userRolesDb[$ur]);
                }
            }
        }

        $this->db->flush();

        $output->writeln('New user "'.$email.'" added with ID: '.$newUser->getId());
    }
}
