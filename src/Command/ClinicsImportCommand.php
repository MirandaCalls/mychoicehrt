<?php

namespace App\Command;

use App\DataSource\ErinReedDataSource;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:clinics:import',
    description: 'Fetches clinics from configured data sources and imports any new clinics',
)]
class ClinicsImportCommand extends Command
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // $io = new SymfonyStyle($input, $output);

        $source = new ErinReedDataSource($this->httpClient);
        $clinics = $source->fetchClinics();
        var_dump($clinics);

        return Command::SUCCESS;
    }
}
