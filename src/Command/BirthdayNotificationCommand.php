<?php

namespace App\Command;

use App\Entity\Friend;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'BirthdayNotificationCommand',
    description: 'Add a short description for your command',
)]
class BirthdayNotificationCommand extends Command
{
    private $mailer;
    private $doctrine;

    public function __construct(MailerInterface $mailer, ManagerRegistry $doctrine)
    {
        parent::__construct();
        $this->mailer = $mailer;
        $this->doctrine = $doctrine;
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
        $repo = $this->doctrine->getRepository(Friend::class);
        $qb = $repo->getQb();
        $repo->getNotificationBirthdays($qb);
        $friends = $qb->getQuery()->getResult();

        foreach ($friends as $friend) {
            $message = (new Email())
                ->from('alexandrucioi@icloud.com')
                ->to($friend->getUser())
                ->subject(sprintf("Reminder: ".$friend->getFirstName().' '.$friend->getLastName()."'s birthday is coming up on %s", $friend->getBirthDate()->format('d/m/Y')))
                ->text('mail text');
            echo (sprintf("Reminder: ".$friend->getFirstName().' '.$friend->getLastName()."'s birthday is coming up on %s", $friend->getBirthDate()->format('d/m/Y')));

            try {
                $this->mailer->send($message);
            } catch (\Exception $e) {
                echo 'Caught exception: '. $e->getMessage() ."\n";
            }
        }

        return Command::SUCCESS;
    }
}
