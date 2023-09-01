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

class ListBackendsCommand extends  AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('loginpass:backend:list')
            ->setDescription('List of backends for loginpass.')
            ->setHelp('')
        ;

        parent::configure();
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->getLoginPassManager();

        $table = new Table($output);
        $table->setHeaders(array('Id', 'Name'));

        foreach ($manager->getBackends() as  $name => $backend) {

            $table->addRow(array(
                $name,
                $backend->getLabel(),
            ));
        }
        $table->render();
        return 0;
    }
}
