<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2023 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\Account\Command;

use Jelix\Authentication\Account\Manager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class AccountIdpListCommand extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('account:idp:list')
            ->setDescription('List authentication providers used by the given account')
            ->setHelp('')
            ->addArgument(
                'account',
                InputArgument::REQUIRED,
                'account name'
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accountName = $input->getArgument('account');

        $account = Manager::getAccount($accountName);
        if (!$account) {
            throw new \Exception('The account '.$accountName.' does not exist');
        }

        $list = Manager::searchIdpUsedByAccount($account->getAccountId());

        $table = new Table($output);
        $table->setHeaders(array('Idp', 'user id', 'user email', 'enabled',
            'First used', 'Last used', 'usage count'));
        foreach($list as $idpAccount)
        {
            $table->addRow(array(
                $idpAccount->idp_id,
                $idpAccount->idp_user_id,
                $idpAccount->idp_user_email,
                ($idpAccount->enabled?'yes':'no'),
                $idpAccount->first_used,
                $idpAccount->last_used,
                $idpAccount->usage_count,
            ));
        }
        $table->render();

        return 0;
    }
}
