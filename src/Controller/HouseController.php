<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\House;
use Doctrine\ORM\EntityManagerInterface;




class HouseController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }
    #[Route('/api/house', name: 'house_list', methods: ['GET'])]
    public function houseList(): JsonResponse
    {
        $houses = $this->entityManager->getRepository(House::class)->findAll();

        $data = [];
        foreach ($houses as $house) {
            $data[] = [
                'id' => $house->getId(),
                'name' => $house->getName(),
                'sleeping_capacity' => $house->getSleepingCapacity(),
                'bathrooms' => $house->getBathrooms(),
                'location' => $house->getLocation(),
                'price' => $house->getPrice(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/house/{id}', name: 'house_detail', methods: ['GET'])]
    public function getHouse(int $id): JsonResponse
    {
        $house = $this->entityManager->getRepository(House::class)->find($id);

        if (!$house) {
            return new JsonResponse(['error' => 'House not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $house->getId(),
            'name' => $house->getName(),
            'sleeping_capacity' => $house->getSleepingCapacity(),
            'bathrooms' => $house->getBathrooms(),
            'location' => $house->getLocation(),
            'price' => $house->getPrice(),
        ]);
    }

    #[Route('/api/house', name: 'house_create', methods: ['POST'])]
    public function createHouse(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        foreach (['name', 'sleeping_capacity', 'bathrooms', 'location', 'price'] as $field) {
            if (!isset($data[$field])) {
                return new JsonResponse(['error' => "Missing field: $field"], HttpResponse::HTTP_BAD_REQUEST);
            }
        }

        $house = new House();
        $house->setName($data['name']);
        $house->setSleepingCapacity((int)$data['sleeping_capacity']);
        $house->setBathrooms((int)$data['bathrooms']);
        $house->setLocation($data['location']);
        $house->setPrice((int)$data['price']);

        $this->entityManager->persist($house);
        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $house->getId(),
            'name' => $house->getName(),
            'sleeping_capacity' => $house->getSleepingCapacity(),
            'bathrooms' => $house->getBathrooms(),
            'location' => $house->getLocation(),
            'price' => $house->getPrice(),
        ], HttpResponse::HTTP_CREATED);
    }

    #[Route('/api/house/{id}', name: 'house_update', methods: ['PUT'])]
    public function updateHouse(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $house = $this->entityManager->getRepository(House::class)->find($id);

        if (!$house) {
            return new JsonResponse(['error' => 'House not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        $house->setName($data['name'] ?? $house->getName());
        $house->setSleepingCapacity((int)($data['sleeping_capacity'] ?? $house->getSleepingCapacity()));
        $house->setBathrooms((int)($data['bathrooms'] ?? $house->getBathrooms()));
        $house->setLocation($data['location'] ?? $house->getLocation());
        $house->setPrice((int)($data['price'] ?? $house->getPrice()));

        $this->entityManager->flush();

        return new JsonResponse([
            'id' => $house->getId(),
            'name' => $house->getName(),
            'sleeping_capacity' => $house->getSleepingCapacity(),
            'bathrooms' => $house->getBathrooms(),
            'location' => $house->getLocation(),
            'price' => $house->getPrice(),
        ], HttpResponse::HTTP_OK);
    }
}
