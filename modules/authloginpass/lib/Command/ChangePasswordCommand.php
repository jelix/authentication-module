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
        $password = $this->getPassword($input, $output);

        $manager = $this->getManager();

        $backend = $this->getBackend($input, $manager, $login, true);

        if (!$backend->hasFeature(BackendPluginInterface::FEATURE_CHANGE_PASSWORD)) {
            throw new \Exception('The backend doesn\'t support password change');
        }

        if (!$backend->changePassword($login, $password)) {
            throw new \Exception('The password has not been changed');
        }

    }
}
