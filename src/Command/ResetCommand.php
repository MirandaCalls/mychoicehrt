<?php

namespace App\Command;

use App\Entity\Clinic;
use App\Entity\DuplicateLink;
use App\Entity\FeedbackMessage;
use App\Entity\ImportHash;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:reset',
    description: 'Dev tool used to reset the application database',
)]
class ResetCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if (!$io->confirm('Are you sure you want to reset the clinic tables?', default: false)) {
            return Command::SUCCESS;
        }

        $this->deleteAllEntries(FeedbackMessage::class);
        $this->deleteAllEntries(DuplicateLink::class);
        $this->deleteAllEntries(ImportHash::class);
        $this->deleteAllEntries(Clinic::class);
        $this->entityManager->flush();

        $io->success('All clinic tables were successfully reset.');

        return Command::SUCCESS;
    }

    private function deleteAllEntries(string $entityClass)
    {
        $this->entityManager->createQueryBuilder()
            ->delete()
            ->from($entityClass, 'z')
            ->getQuery()
            ->execute()
        ;
    }
}
