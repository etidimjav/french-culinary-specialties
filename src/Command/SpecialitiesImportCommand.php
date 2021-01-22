<?php

namespace App\Command;

use App\Service\SpecialitiesCsvManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SpecialitiesImportCommand extends Command
{
    protected static $defaultName = 'app:import:specialities';

    private $specialitiesCsvManager;

    public function __construct(SpecialitiesCsvManager $specialitiesCsvManager)
    {
        $this->specialitiesCsvManager = $specialitiesCsvManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('website:import-users-csv')
            ->setDescription('Users csv import from file.')
            ->addArgument('import_csv_path', InputArgument::REQUIRED, 'The absolute path for the csv file.')
            ->addArgument('import_images_path', InputArgument::REQUIRED, 'The absolute path for the images.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->specialitiesCsvManager->import($input->getArgument('import_csv_path'), $input->getArgument('import_images_path'));

        $output->writeln('Specialities successfully imported !');

        return 0;
    }
}
