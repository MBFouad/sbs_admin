<?php
// src/Command/ImportDataCommand.php
namespace App\Services;


use App\Entity\Customer;
use App\Entity\Purchases;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpKernel\KernelInterface;

class ImportPurchasesService
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
            if (!$row[$header['customer_id']] || !$row[$header['product_id']] || !$row[$header['price']]) {
                $skipped++;
                continue;
            }
            $customerCheck = $this->entityManager->getRepository(Customer::class)->findOneBy(['id' => $row[$header['customer_id']]]);
            if (!$customerCheck) {
                $skipped++;
                continue;
            }
            $purchase = new Purchases();
            $purchase->setCustomer($customerCheck);
            $purchase->setProductId($row[$header['product_id']]);
            $purchase->setQuantity($row[$header['quantity']] ?? 1);
            $purchase->setPrice($row[$header['price']]);
            $purchase->setCurrency($row[$header['currency']] ?? 'dollars');
            $purchase->setDate(new \DateTime($row[$header['date']]) ?? date('Y-m-d'));
            $this->entityManager->persist($purchase);
            $inserted++;
        }
        $this->entityManager->flush();
        return 'Inserted: ' . $inserted . ' Updated: ' . $updated . ' Skipped: ' . $skipped;
    }

    private function validateHeader($row): bool
    {
        $rules = [
            'customer_id' => 'customer_id',
            'product_id' => 'product_id',
            'quantity' => 'quantity',
            'price' => 'price',
            'currency' => 'currency',
            'date' => 'date'
        ];
        foreach ($row as $value) {
            if (in_array($value, $rules)) {
                unset($rules[$value]);
            }
        }
        return !$rules;
    }
}