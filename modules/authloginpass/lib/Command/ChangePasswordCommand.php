<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePasswordCommand extends  AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('loginpass:user:password')
            ->setDescription('Change the password of a user in a backend of loginpass. Generates a random password or you can use options to set a password')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'login of the user'
            )
            ->addOption(
                'backend',
                'b',
                InputOption::VALUE_REQUIRED,
                'The backend name. By default, tries to find the backend managing the user.'
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
                'Indicates the password to set. WARNING: not really safe, the password can appear in the history of the shell'
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $login = $input->getArgument('login');
        $password = $this->getPassword($input, $output);

        $manager = $this->getLoginPassManager();

        $backendName = $this->getBackendName($input, $manager);

        if (!$manager->canChangePassword($login, $backendName)) {
            throw new \Exception('The backend doesn\'t support password change');
        }

        if (!$manager->changePassword($login, $password, $backendName)) {
            throw new \Exception('The password has not been changed');
        }
        return 0;
    }
}
