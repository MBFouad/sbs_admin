<?php

namespace App\Tests\services;

use App\Entity\Customer;
use App\Entity\Purchases;
use App\Services\ImportCustomerService;
use App\Services\ImportPurchasesService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportPurchasesServiceTest extends KernelTestCase
{
    private $entityManager;
    private $_kernel;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $command = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['n']);
        $this->_kernel = $kernel;
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

    }

    public function testReadData()
    {
        // Create an instance of YourService
        $importPurchasesService = new ImportPurchasesService($this->entityManager, $this->_kernel);
        // Call the method you want to test
        $result = $importPurchasesService->readData('purchases.csv');
        // Check count of customers in database

        // get customer count from database
        $purchasesCount = $this->entityManager->getRepository(Purchases::class)->count();

        $this->assertEquals(5, $purchasesCount);

    }
}