<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass\Command;

use Jelix\Authentication\Core\AuthSession\AuthUser;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class CreateUserCommand extends  AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('loginpass:user:create')
            ->setDescription('Create a user in a backend of loginpass. Generates a random password or you can use options to set a password')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'login of the user'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'the email of the user'
            )
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'firstname/lastname of the user'
            )
            ->addOption(
                'backend',
                'b',
                InputOption::VALUE_REQUIRED,
                'The backend name. By default the first one in the list of configured backends.'
            )
            ->addOption(
                'ask-pass',
                'a',
                InputOption::VALUE_NONE,
                'Asks the pass interactively in the shell'
            )
            ->addOption(
                'set-pass',
                'p',
                InputOption::VALUE_REQUIRED,
                'Indicate the password to set. WARNING: not really safe, the password can appear in the history of the shell'
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $login = $input->getArgument('login');
        $userName = $input->getArgument('name');
        $userEmail = $input->getArgument('email');
        $password = $this->getPassword($input, $output);


        $manager = $this->getManager();
        $backendName = $this->getBackendName($input, $manager);
        if (!$backendName) {
            $backendName = $manager->getFirstBackendName();
        }

        if (!$manager->createUser($login, $password, array(
            AuthUser::ATTR_NAME => $userName,
            AuthUser::ATTR_EMAIL => $userEmail
            ), $backendName)
        ) {
            throw new \Exception('The user has not been created');
        }
        return 0;
    }
}
