<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\Account\Command;

use Jelix\Authentication\Account\Manager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class AccountUnsetIdpCommand extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('account:idp:unset')
            ->setDescription('Detach an account from an authentication provider')
            ->setHelp('')
            ->addArgument(
                'account',
                InputArgument::REQUIRED,
                'account name'
            )
            ->addArgument(
                'idpid',
                InputArgument::REQUIRED,
                'the identity provider from which the account should be detached'
            )
            ->addArgument(
                'userid',
                InputArgument::OPTIONAL,
                'the user identifiant. Take the account name by default'
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accountName = $input->getArgument('account');
        $idpId = $input->getArgument('idpid');
        $userId = $input->getArgument('userid');
        if (!$userId) {
            $userId = $accountName;
        }

        $account = Manager::getAccount($accountName);
        if (!$account) {
            throw new \Exception('The account '.$accountName.' does not exist');
        }

        if (!Manager::isAccountUsingIdp($account->getAccountId(), 'loginpass')) {
            $output->writeln('The account '.$accountName.' does not use the given identity provider');
            return 1;
        }

        $idpManager = \jAuthentication::manager();
        $idp = $idpManager->getIdpById($idpId);
        if (!$idp) {
            throw new \Exception('The identity provider '.$idpId.' does not exist');
        }

        Manager::detachAccountFromIdp($account->getAccountId(), $idpId, $userId);

        return 0;
    }
}
