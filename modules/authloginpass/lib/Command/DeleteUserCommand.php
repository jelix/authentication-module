<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass\Command;

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
        $backendName = $this->getBackendName($input, $manager);

        if (!$manager->deleteUser($login, $backendName)) {
            throw new \Exception('The user has not been deleted');
        }

        return 0;
    }
}
