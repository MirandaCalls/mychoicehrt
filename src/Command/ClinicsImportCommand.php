<?php

namespace App\Command;

use App\DataSource\DataSourceException;
use App\DataSource\DataSourceInterface;
use App\DataSource\ErinReedDataSource;
use App\DataSource\TransInTheSouthDataSource;
use App\Entity\Clinic;
use App\Entity\ImportHash;
use App\HereMaps\Client as HereClient;
use App\Repository\ClinicRepository;
use App\Repository\ImportHashRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:clinics:import',
    description: 'Fetches clinics from configured data sources and imports any new clinics',
)]
class ClinicsImportCommand extends Command
{
    private HttpClientInterface $httpClient;
    private HereClient $hereClient;
    private ClinicRepository $clinics;
    private ImportHashRepository $imports;

    private array $dataSources = [
        ErinReedDataSource::class,
        TransInTheSouthDataSource::class,
    ];

    public function __construct(
        HttpClientInterface $httpClient,
        HereClient $hereClient,
        ClinicRepository $clinics,
        ImportHashRepository $imports,
    ) {
        $this->httpClient = $httpClient;
        $this->hereClient = $hereClient;
        $this->clinics = $clinics;
        $this->imports = $imports;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Sync started');

        $clinicsAddedCount = 0;
        foreach ($this->dataSources as $source) {
            /* @var DataSourceInterface $source */
            $source = new $source($this->httpClient, $this->hereClient);

            $io->section($source->getType());
            $io->text('Fetching clinics');
            try {
                $newClinics = $source->fetchClinics();
            } catch (DataSourceException $e) {
                $io->error($e);
                return Command::FAILURE;
            }

            $io->text(sprintf('Fetched %d clinics', count($newClinics)));
            $io->text('Processing clinics');

            /* @var Clinic $new */
            foreach ($io->progressIterate($newClinics) as $new) {
                $newHashStr = $source->hash($new);
                $existingImport = $this->imports->findByHash($newHashStr);
                if ($existingImport !== null) {
                    // This clinic has already been imported
                    continue;
                }

                $source->preImport($new);
                $this->clinics->save($new);

                $import = new ImportHash();
                $import->setDataSource($source->getType());
                $import->setHash($newHashStr);
                $this->imports->save($import, true);

                $clinicsAddedCount++;
            }
        }

        if ($clinicsAddedCount > 0) {
            $io->success(sprintf('Imported %d clinics', $clinicsAddedCount));
        } else {
            $io->success('Sync finished, no clinics imported');
        }

        return Command::SUCCESS;
    }
}
