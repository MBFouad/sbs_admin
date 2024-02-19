<?php
// src/Command/ImportDataCommand.php
namespace App\Command;

use App\Services\ImportPurchasesService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Question\Question;
use App\Services\ImportCustomerService;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'ugo:orders:import', // Define the command name here
    description: 'Import customer & purchases data into the database'
)]
class ImportDataCommand extends Command
{
//    protected static $defaultName = 'ugo:orders:import';
    private $parentPath = '/var/files/';
    private $entityManager;
    private ImportCustomerService $importCustomerService;
    private ImportPurchasesService $importPurchasesService;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel, ImportCustomerService $importCustomerService, ImportPurchasesService $importPurchasesService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->importCustomerService = $importCustomerService;
        $this->importPurchasesService = $importPurchasesService;
        $this->kernel = $kernel;

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $formatter = $this->getHelper('formatter');
        $helper = $this->getHelper('question');
        // ************************************ GET Data from user ************************************ //
        $question = new Question("Please enter your customer file name which should exists in  $this->parentPath: (customers.csv): ", 'customers.csv');
        $question->setValidator(function (string $answer): string {
            if (!is_string($answer) || '.csv' !== substr($answer, -4) || !file_exists($this->kernel->getProjectDir() . $this->parentPath . $answer)) {
                throw new \RuntimeException(
                    'invalid file name, please enter a exists and valid file name with extension .csv'
                );
            }

            return $answer;
        });
        $question->setMaxAttempts(3);
        $customer_file_name = $helper->ask($input, $output, $question);
        $question = new Question("Please enter your purchases file name which should exists in $this->parentPath: (purchases.csv)", 'purchases.csv');
        $question->setValidator(function (string $answer): string {
            if (!is_string($answer) || '.csv' !== substr($answer, -4) || !file_exists($this->kernel->getProjectDir() . $this->parentPath . $answer)) {
                throw new \RuntimeException(
                    'invalid file name, please enter a exists and valid file name with extension .csv'
                );
            }

            return $answer;
        });
        $question->setMaxAttempts(3);
        $purchases_file_name = $helper->ask($input, $output, $question);

        // ************************************ Import customers ************************************ //
        $output->writeln("Importing customer data from $this->parentPath.$customer_file_name ...");
        try {
            $importCustomerResult = $this->importCustomerService->readData($customer_file_name, $this->parentPath);
        } catch (\Exception $e) {
            $formattedLine = $formatter->formatSection(
                'Error!',
                $e->getMessage(), 'error'
            );
            $output->writeln($formattedLine);
            return Command::FAILURE;
        }
        $formattedLine = $formatter->formatSection(
            'Success!',
            $importCustomerResult
        );
        $output->writeln($formattedLine);

        // ************************************ Import Purchases ************************************ //
        $output->writeln("Importing purchases data from $this->parentPath.$purchases_file_name ...");
        try {
            $importCustomerResult = $this->importPurchasesService->readData($purchases_file_name, $this->parentPath);
        } catch (\Exception $e) {
            $formattedLine = $formatter->formatSection(
                'Error!',
                $e->getMessage(), 'error'
            );
            $output->writeln($formattedLine);
            return Command::FAILURE;
        }
        $formattedLine = $formatter->formatSection(
            'Success!',
            $importCustomerResult
        );
        $output->writeln($formattedLine);
        return Command::SUCCESS;
    }
}