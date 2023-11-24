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

class CreateAccountCommand extends \Jelix\Scripts\ModuleCommandAbstract
{
    protected function configure()
    {
        $this
            ->setName('account:create')
            ->setDescription('Create an account')
            ->setHelp('')
            ->addArgument(
                'account',
                InputArgument::REQUIRED,
                'account name'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'the email of the user'
            )
            ->addArgument(
                'firstname',
                InputArgument::OPTIONAL,
                'firstname of the user',
                ''
            )
            ->addArgument(
                'lastname',
                InputArgument::OPTIONAL,
                'lastname of the user',
                ''
            )
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accountName = $input->getArgument('account');
        $firstName = $input->getArgument('firstname');
        $lastName = $input->getArgument('lastname');
        $userEmail = $input->getArgument('email');

        $existingAccount = Manager::getAccount($accountName);
        if ($existingAccount) {
            throw new \Exception('The account '.$accountName.' already exists');
        }

        $newAccount = Manager::createAccountObject($accountName, $userEmail);
        $newAccount->firstname = $firstName;
        $newAccount->lastname = $lastName;
        Manager::saveNewAccount($newAccount);

        return 0;
    }
}
