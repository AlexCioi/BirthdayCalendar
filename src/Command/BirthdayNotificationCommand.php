<?php

namespace App\Command;

use App\Entity\Friend;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'BirthdayNotificationCommand',
    description: 'Add a short description for your command',
)]
class BirthdayNotificationCommand extends Command
{
    private $entityManager;
    private $notificationEmailService;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->entityManager->getRepository(Friend::class);
        $qb = $repo->getQb();
        $repo->get($qb);
        $friends = $qb->getQuery()->getResult();



        return Command::SUCCESS;
    }
}
