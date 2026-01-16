<?php

namespace App\Command;

use App\JobManagement\Domain\Entity\JobOffer;
use App\JobManagement\Infrastructure\Repository\DoctrineJobOfferRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:create-job',
    description: 'Creates a new job offer',
)]
class CreateJobCommand extends Command
{
    public function __construct(
        private DoctrineJobOfferRepository $jobOfferRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::REQUIRED, 'The title of the job')
            ->addArgument('description', InputArgument::REQUIRED, 'The description of the job')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = $input->getArgument('title');
        $description = $input->getArgument('description');

        $jobOffer = new JobOffer($title, $description);
        
        $this->jobOfferRepository->save($jobOffer);

        $output->writeln('Job offer created successfully.');

        return Command::SUCCESS;
    }
}
