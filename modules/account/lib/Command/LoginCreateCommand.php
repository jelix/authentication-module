<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\Account\Command;


use Jelix\Authentication\Account\Manager;
use Jelix\Authentication\Core\AuthSession\AuthUser;
use Jelix\Authentication\LoginPass\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LoginCreateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('account:login:create')
            ->setDescription('Set a login password for an account, with the loginpass identity provider')
            ->setHelp('')
            ->addArgument(
                'account',
                InputArgument::REQUIRED,
                'account name'
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
        $accountName = $input->getArgument('account');

        $existingAccount = Manager::getAccount($accountName);
        if (!$existingAccount) {
            throw new \Exception('The account '.$accountName.' does not exists');
        }

        if (Manager::isAccountUsingIdp($existingAccount->getAccountId(), 'loginpass')) {
            throw new \Exception('The account '.$accountName.' has already a login');
        }


        $login = $existingAccount->getUserName();
        $realName = $existingAccount->getRealName();
        $userEmail = $existingAccount->getEmail();
        $password = $this->getPassword($input, $output);

        $lpManager = $this->getLoginPassManager();
        $user = $lpManager->getUser($login);
        if ($user) {
            throw new \Exception('The login already exists');
        }

        $backendName = $this->getBackendName($input, $lpManager);
        if (!$backendName) {
            $backendName = $lpManager->getFirstBackendName();
        }

        $authUser = $lpManager->createUser($login, $password, array(
            AuthUser::ATTR_NAME => $realName,
            AuthUser::ATTR_EMAIL => $userEmail
        ), $backendName);
        if (!$authUser) {
            throw new \Exception('The login has not been created');
        }

        Manager::attachAccountToIdp($existingAccount->getAccountId(), 'loginpass', $login, $userEmail);

        return 0;
    }
}
