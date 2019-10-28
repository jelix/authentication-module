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

class DeleteUserCommand extends  AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('loginpass:user:delete')
            ->setDescription('Delete a user in a backend of loginpass.')
            ->setHelp('')
            ->addArgument(
                'login',
                InputArgument::REQUIRED,
                'login of the user'
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $login = $input->getArgument('login');
        $manager = $this->getManager();
        $backend = $manager->getBackendHavingUser($login);

        if (!$backend) {
            $output->writeln('User already deleted');
            return 0;
        }

        if (!$backend->hasFeature(BackendPluginInterface::FEATURE_DELETE_USER)) {
            throw new \Exception('The backend doesn\'t support user deletion');
        }

        if (!$backend->deleteUser($login)) {
            throw new \Exception('The user has not been deleted');
        }
        return 0;
    }
}
