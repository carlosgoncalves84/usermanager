<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Tests\Iterator\InnerNameIterator;


/**
 * Class CreateUserCommand
 * @package App\Command
 * @author carlos <carlos.santos.goncalves@websitebutler.de>
 */
class CreateUserCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-user';

    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this
            ->setDescription('Creates a new User')
            ->setHelp('This command allows you to create a user')
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username of the user.'),
                new InputArgument('password', InputArgument::REQUIRED, 'User password'),
            ])
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
           'User Creator',
           '============',
           '',
        ]);

        $output->writeln('Username: '.$input->getArgument('username'));
        $output->writeln('Passowrd: '.$input->getArgument('password'));
    }
}
