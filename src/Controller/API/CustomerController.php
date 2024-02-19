<?php

namespace App\Controller\API;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/customers', name: 'customers.')]
class CustomerController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {

        $customerRepository = $this->entityManager->getRepository(Customer::class);
        // Fetch all customers
        $customers = $customerRepository->findAllSimple();
        return $this->json($customers);
    }

    #[Route('/{customerId<\d+>?1}/orders', name: 'orders', methods: ['GET'])]
    public function orders(Request $request, int $customerId): JsonResponse
    {
        $customerRepository = $this->entityManager->getRepository(Customer::class);
        // featch customer by id
        $customer = $customerRepository->find($customerId);

        return $this->json($customer);
    }
}