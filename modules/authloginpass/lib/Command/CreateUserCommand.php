<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass\Command;

use Jelix\Authentication\LoginPass\BackendPluginInterface;
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
                'The backend name. By default the first one in the list.'
            )
            ->addOption(
                'ask-pass',
                'a',
                InputOption::VALUE_NONE,
                ''
            )
            ->addOption(
                'set-pass',
                'p',
                InputOption::VALUE_REQUIRED,
                ''
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

        $backend = $this->getBackend($input, $manager, $login, false);

        if (!$backend->hasFeature(BackendPluginInterface::FEATURE_CREATE_USER)) {
            throw new \Exception('The backend doesn\'t support user creation');
        }

        if (!$backend->createUser($login, $password, $userEmail, $userName)) {
            throw new \Exception('The user has not been created');
        }

    }
}
