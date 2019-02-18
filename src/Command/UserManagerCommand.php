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
    protected static $defaultName = 'app:user-manager';

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
            ->setDescription('CRUD functionality for user Entity')
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
        $nameQuestion = new Question('Enter new User name: ', false);
        $passwordQuestion = new Question('Enter new password: ', false);

        $name = $this->askName($input, $output, $nameQuestion);
        $password = $this->askPassword($input, $output, $passwordQuestion);

        $result = $this->userManager->create($name, $password);

        $this->getFinalOutput($output, $result, 'Created');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    private function queryUser(InputInterface $input, OutputInterface $output): void
    {
        $nameQuestion = new Question('Enter User name: ', false);

        $name = $this->askName($input, $output, $nameQuestion, false);

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
        $nameQuestion = new Question('Enter User name: ', false);
        $newNameQuestion = new Question('Enter new User name: ', false);
        $newPasswordQuestion = new Question('Enter new password: ', false);

        $name = $this->askName($input, $output, $nameQuestion, false);
        $newName = $this->askName($input, $output, $newNameQuestion);
        $newPassword = $this->askPassword($input, $output, $newPasswordQuestion);

        $result = $this->userManager->update($name, $newName, $newPassword);

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
        $question = new Question('Enter User name: ', false);

        $name = $this->askName($input, $output, $question, false);

        $result = $this->userManager->delete($name);

        $this->getFinalOutput($output, $result);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Question $question
     * @param bool $default
     * @return mixed
     */
    private function askName(InputInterface $input, OutputInterface $output, Question $question, $default = true)
    {
        $question->setNormalizer(function ($name) {
            return $name ? trim($name) : '';
        });
        $question->setValidator(function ($name) use($default) {
            if (preg_match('/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/',$name) || !preg_match('/.+/', $name)) {
                throw new \RuntimeException(
                    'This name is not valid.'
                );
            }

            if($default) {
                $user = $this->userManager->getUserRepository()->countUsersByName($name);

                if ($user > 0) {
                    throw new \RuntimeException(
                        'This name already exists.'
                    );
                }
            }

            return $name;
        });
        $question->setMaxAttempts(3);
        $helper = $this->getHelper('question');

        return $helper->ask($input, $output, $question);

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Question $question
     * @return mixed
     */
    private function askPassword(InputInterface $input, OutputInterface $output, Question $question)
    {
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator(function ($password) {

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
        $question->setMaxAttempts(3);
        $helper = $this->getHelper('question');

        return $helper->ask($input, $output, $question);
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

        $input = new ArrayInput(['command' => 'app:user-manager']);

        try {
            $this->getApplication()->run($input, $output);
        } catch (\Exception $e) {}
    }
}

