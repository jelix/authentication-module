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

class AccountListCommand extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('account:list')
            ->setDescription('List accounts')
            ->setHelp('')
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Id', 'Username', 'Email', 'First Name', 'Last name', 'Status', 'Create Date'));
        foreach (Manager::getAccountList() as $account) {
            $table->addRow(array(
                $account->account_id,
                $account->username,
                $account->email,
                $account->firstname,
                $account->lastname,
                $account->status,
                $account->create_date,
            ));
        }
        $table->render();
        return 0;
    }
}
