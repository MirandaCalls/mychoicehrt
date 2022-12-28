<?php

namespace App\Command;

use App\Geonames\CitiesImporter;
use App\Geonames\PostalCodesImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:geonames:import',
    description: 'Imports the datasets from geonames.org',
)]
class GeonamesImportCommand extends Command
{
    private PostalCodesImporter $postalCodesImporter;
    private CitiesImporter $citiesImporter;

    public function __construct(
        PostalCodesImporter $postalCodesImporter,
        CitiesImporter $citiesImporter,
    ) {
        $this->postalCodesImporter = $postalCodesImporter;
        $this->citiesImporter = $citiesImporter;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $importers = [
            'Postal Codes' => $this->postalCodesImporter,
            'Cities' => $this->citiesImporter,
        ];
        foreach ($importers as $name => $importer) {
            $io->section($name);
            $io->text('Importing records...');
            $totalRecords = $importer->import();
            $io->text(sprintf('Imported %d records', $totalRecords));
        }

        $io->success('Successfully imported fresh geonames.org datasets.');

        return Command::SUCCESS;
    }

}
