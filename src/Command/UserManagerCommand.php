<?php

namespace App\Command;

use App\Entity\User;
use App\Service\UserManagerService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;


/**
 * Class UserManagerCommand
 * @package App\Command
 * @author carlos <carlos.santos.goncalves@websitebutler.de>
 */
class UserManagerCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-user';

    /**
     * @var UserManagerService $userManager
     */
    private $userManager;

    public function __construct(UserManagerService $userManager)
    {
        $this->userManager = $userManager;

        parent::__construct();
    }

    protected function configure():void
    {
        $this
            ->setDescription('User CRUD')
            ->setHelp('This command allows you to create, find, modify and delete a user');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
           'User Manager',
           '============',
           '',
        ]);

        $actions = ['Create User', 'Query User', 'Modify User', 'Delete User', 'Exit'];
        $helper = $this->getHelper('question');

        $question = new ChoiceQuestion(
            'Please select your action: ',
            $actions,
            0
        );

        $question->setErrorMessage('The action %s is invalid.');

        $action = $helper->ask($input, $output, $question);

        if ($action === 'Exit') {
            $output->writeln('Bye Bye');
            return;
        }

        $output->writeln('You have just selected ' . $action . ' action');

        switch ($action) {
            case 'Create User':
                $this->createUser($input, $output);
                break;
            case 'Query User':
                $this->queryUser($input, $output);
                break;
            case 'Modify User':
                $this->modifyUser($input, $output);
                break;
            case 'Delete User':
                $this->deleteUser($input, $output);
                break;
            default:
                return;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private  function createUser(InputInterface $input, OutputInterface $output): void
    {
        $question1 = new Question('Enter user name: ', false);
        $question1->setNormalizer(function ($name) {
            return $name ? trim($name) : '';
        });
        $question1->setValidator(function ($name) {
            if (preg_match('/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/',$name) || !preg_match('/.+/', $name)) {
                throw new \RuntimeException(
                    'This name is not valid.'
                );
            }

            $user = $this->userManager->getUserRepository()->countUsersByName($name);

            if ($user > 0) {
                throw new \RuntimeException(
                    'This name already exists.'
                );
            }

            return $name;
        });
        $question1->setMaxAttempts(3);
        $helper1 = $this->getHelper('question');
        $name = $helper1->ask($input, $output, $question1);

        $question2 = new Question('Enter password: ', false);
        $question2->setHidden(true);
        $question2->setHiddenFallback(false);
        $question2->setValidator(function ($password) {

            $newHash = sha1($password);
            $check = $this->userManager->checkPassword($password, $newHash);

            if (!$check) {
                throw new \RuntimeException('Your password is weak.');
            }

            if (is_string($check)) {
                throw new \RuntimeException($check);
            }

            return $newHash;
        });
        $question2->setMaxAttempts(3);
        $helper2 = $this->getHelper('question');
        $hashedPass = $helper2->ask($input, $output, $question2);

        $result = $this->userManager->create($name, $hashedPass);

        $this->getFinalOutput($output, $result, 'Created');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function queryUser(InputInterface $input, OutputInterface $output): void
    {
        $question = new Question('Enter user name: ', false);
        $question->setNormalizer(function ($name) {
            return $name ? trim($name) : '';
        });
        $question->setValidator(function ($name) {
            if (preg_match('/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/',$name) || !preg_match('/.+/', $name)) {
                throw new \RuntimeException(
                    'This name is not valid.'
                );
            }

            return $name;
        });
        $question->setMaxAttempts(3);
        $helper = $this->getHelper('question');
        $name = $helper->ask($input, $output, $question);

        $result = $this->userManager->query($name);

        $this->getFinalOutput($output, $result, 'Found');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function modifyUser(InputInterface $input, OutputInterface $output): void
    {
        $question0 = new Question('Enter user name: ', false);
        $question0->setNormalizer(function ($name) {
            return $name ? trim($name) : '';
        });
        $question0->setValidator(function ($name) {
            if (preg_match('/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/',$name) || !preg_match('/.+/', $name)) {
                throw new \RuntimeException(
                    'This name is not valid.'
                );
            }

            return $name;
        });
        $question0->setMaxAttempts(3);
        $helper0 = $this->getHelper('question');
        $name = $helper0->ask($input, $output, $question0);

        $question1 = new Question('Enter new user name: ', false);
        $question1->setNormalizer(function ($newName) {
            return $newName ? trim($newName) : '';
        });
        $question1->setValidator(function ($newName) {
            if (preg_match('/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/',$newName) || !preg_match('/.+/', $newName)) {
                throw new \RuntimeException(
                    'This name is not valid.'
                );
            }

            $user = $this->userManager->getUserRepository()->countUsersByName($newName);

            if ($user > 1) {
                throw new \RuntimeException(
                    'This name already exists.'
                );
            }

            return $newName;
        });
        $question1->setMaxAttempts(3);
        $helper1 = $this->getHelper('question');
        $newName = $helper1->ask($input, $output, $question1);

        $question2 = new Question('Enter new password: ', false);
        $question2->setHidden(true);
        $question2->setHiddenFallback(false);
        $question2->setValidator(function ($password) {

            $newHash = sha1($password);
            $check = $this->userManager->checkPassword($password, $newHash);

            if (!$check) {
                throw new \RuntimeException('Your password is weak.');
            }

            if (is_string($check)) {
                throw new \RuntimeException($check);
            }

            return $newHash;
        });
        $question2->setMaxAttempts(3);
        $helper2 = $this->getHelper('question');
        $hashedPass = $helper2->ask($input, $output, $question2);

        $result = $this->userManager->update($name, $newName, $hashedPass);

        $this->getFinalOutput($output, $result, 'Updated');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function deleteUser(InputInterface $input, OutputInterface $output): void
    {
        $question = new Question('Enter user name: ', false);
        $question->setNormalizer(function ($name) {
            return $name ? trim($name) : '';
        });
        $question->setValidator(function ($name) {
            if (preg_match('/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/',$name) || !preg_match('/.+/', $name)) {
                throw new \RuntimeException(
                    'This name is not valid.'
                );
            }

            return $name;
        });
        $question->setMaxAttempts(3);
        $helper = $this->getHelper('question');
        $name = $helper->ask($input, $output, $question);

        $result = $this->userManager->delete($name);

        $this->getFinalOutput($output, $result);
    }

    /**
     * @param OutputInterface $output
     * @param $result
     * @param $description
     */
    private function getFinalOutput(OutputInterface $output, $result, $description = ''): void
    {
        if($result instanceof User) {
            $output->writeln(['User ' . $result->getName() .' was '. $description, '']);
        }

        $output->writeln([$result, '',]);

        $input = new ArrayInput(['command' => 'app:create-user']);

        try {
            $this->getApplication()->run($input, $output);
        } catch (\Exception $e) {}
    }
}

