<?php
/**
 * @author     Laurent Jouanneau
 * @copyright  2019 Laurent Jouanneau
 * @license   MIT
 */
namespace Jelix\Authentication\LoginPass\Command;

use Jelix\Authentication\LoginPass\BackendPluginInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Jelix\Authentication\LoginPass\Manager;

class AbstractCommand extends  \Jelix\Scripts\ModuleCommandAbstract
{

    protected function getPassword(InputInterface $input, OutputInterface $output) {
        if ($input->getOption('ask-pass')) {
            $helper = $this->getHelper('question');
            $question = new Question('Please enter the user\'s password: ', '');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $password = $helper->ask($input, $output, $question);

            $question2 = new Question('Re-enter the user\'s password to verify: ', '');
            $question2->setHidden(true);
            $question2->setHiddenFallback(false);
            $password2 = $helper->ask($input, $output, $question2);

            if ($password2 != $password) {
                throw new \Exception('Not same password');
            }
        }
        else if ($input->getOption('set-pass')) {
            $password = $input->getOption('set-pass');
        }
        else {
            $generator = (new \RandomLib\Factory)->getMediumStrengthGenerator();
            $password = $generator->generateString(rand(9,15), "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-!&%+@.#");
            $output->writeln('Generated password is: '.$password);
        }
        return $password;
    }

    /**
     * @param InputInterface $input
     * @param Manager $manager
     * @return BackendPluginInterface
     */
    protected function getBackend(InputInterface $input, $manager, $login, $userExists=true) {
        $backendName = $input->getOption('backend');
        if ($backendName) {
            $backend = $manager->getBackendByName($backendName);
            if (!$backend) {
                throw new \InvalidArgumentException("Unknown backend");
            }
            if ($userExists) {
                if (!$backend->userExists($login)) {
                    throw new \InvalidArgumentException("User does not exist in the given backend");
                }
            }
            else {
                if ($backend->userExists($login)) {
                    throw new \InvalidArgumentException("User already exists in the given backend");
                }
            }
            return $backend;
        }

        $backend = $manager->getBackendHavingUser($login);

        if ($userExists) {
            if (!$backend) {
                throw new \InvalidArgumentException("The user does not exists");
            }
        }
        else {
            if ($backend) {
                throw new \InvalidArgumentException("The user already exists");
            }
            // get the first backend
            $backends = $manager->getBackends();
            if (count($backends)) {
                $backend = reset($backends);
            }
            else {
                throw new \InvalidArgumentException("No configured backend");
            }
        }
        return $backend;
    }

    /**
     * @return Manager
     */
    protected function getManager()
    {
        /** @var \loginpassIdentityProvider $idp */
        $idp = \jAuthentication::manager()->getIdpById('loginpass');
        return $idp->getManager();
    }
}
