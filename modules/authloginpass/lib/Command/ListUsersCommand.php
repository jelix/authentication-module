<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass\Command;

use Jelix\Authentication\Core\AuthSession\AuthUser;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUsersCommand extends  AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('loginpass:users')
            ->setDescription('List users of one backend of loginpass.')
            ->setHelp('')
            ->addArgument(
                'backend',
                InputArgument::REQUIRED,
                'the backend'
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $backendName = $input->getArgument('backend');
        $manager = $this->getLoginPassManager();

        $backend = $manager->getBackendByName($backendName);
        if (!$backend) {
            throw new \InvalidArgumentException("Unknown backend");
        }

        $table = new Table($output);
        $table->setHeaders(array('Id', 'Login', 'Email', 'Username'));

        /** @var AuthUser $user */
        foreach ($backend->getUsersList() as $user) {
            $table->addRow(array(
                $user->getUserId(),
                $user->getLogin(),
                $user->getEmail(),
                $user->getName(),
            ));
        }
        $table->render();
        return 0;
    }
}
