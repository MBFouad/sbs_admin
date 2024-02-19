<?php
// src/Command/ImportDataCommand.php
namespace App\Services;


use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportCustomerService
{
    private $entityManager;
    private $kernel;

    public function __construct(EntityManagerInterface $entityManager, KernelInterface $kernel)
    {
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;

    }

    public function readData($inputFileName, $parentPath = '/var/files/')
    {
        $inserted = $updated = $skipped = 0;
        $projectDir = $this->kernel->getProjectDir();
        $file_path = $projectDir . $parentPath . $inputFileName;
        $spreadsheet = IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file_path);
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();
        $sheet_data = $worksheet->toArray();
        // get header row
        $header = $sheet_data[0] ?? [];
        if (!$this->validateHeader($header)) {
            throw new \RuntimeException('Missing header columns (title, lastname, firstname, postal_code, city, email) is missing');
        }

        $header = array_flip($header);
        unset($sheet_data[0]);
        foreach ($sheet_data as $row) {
            if (!$row[$header['email']] || !$row[$header['title']] || !$row[$header['lastname']] || !$row[$header['firstname']]) {
                $skipped++;
                continue;
            }
            $customerCheck = $this->entityManager->getRepository(Customer::class)->findOneBy(['email' => $row[$header['email']]]);
            if ($customerCheck) {
                $updated++;
                continue;
            }
            $customer = new Customer();
            $customer->setTitle($row[$header['title']]);
            $customer->setLastname($row[$header['lastname']]);
            $customer->setFirstname($row[$header['firstname']]);
            $customer->setPostalCode($row[$header['postal_code']] ?? null);
            $customer->setCity($row[$header['city']] ?? null);
            $customer->setEmail($row[$header['email']]);
            $this->entityManager->persist($customer);
            $inserted++;
        }
        $this->entityManager->flush();
        return 'Inserted: ' . $inserted . ' Updated: ' . $updated . ' Skipped: ' . $skipped;
    }

    private function validateHeader($row): bool
    {
        $rules = [
            'title' => 'title',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'postal_code' => 'postal_code',
            'city' => 'city',
            'email' => 'email'
        ];
        foreach ($row as $value) {
            if (in_array($value, $rules)) {
                unset($rules[$value]);
            }
        }
        return !$rules;
    }
}