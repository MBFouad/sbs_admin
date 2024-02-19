<?php

namespace App\Tests\services;

use App\Entity\Customer;
use App\Services\ImportCustomerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportCustomerServiceTest extends KernelTestCase
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
        $importCustomerService = new ImportCustomerService($this->entityManager, $this->_kernel);
        // Call the method you want to test
        $result = $importCustomerService->readData('customers.csv');
        // Check count of customers in database

        // get customer count from database
        $customerCount = $this->entityManager->getRepository(Customer::class)->count();

        $this->assertEquals(3, $customerCount);

    }
}