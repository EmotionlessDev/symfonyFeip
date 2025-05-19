<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



class HouseController extends AbstractController
{

    public function __construct(
        private readonly CsvManager $csvManager,
        private readonly string $filename,
    )
    {
    }
    #[Route('/api/house', name: 'house_list', methods: ['GET'])]
    public function houseList(): JsonResponse
    {
        $data = $this->csvManager->readAll($this->filename);
        $houses = [];
        foreach ($data as $line) {
            $houses[] = [
                'id' => (int)$line[0],
                'name' => $line[1],
                'sleeping_capacity' => (int)$line[2],
                'bathrooms' => (int)$line[3],
                'location' => $line[4],
                'price' => (float)$line[5],
            ];
        }
        return new JsonResponse($houses);
    }

    #[Route('/api/house/{id}', name: 'house_detail', methods: ['GET'])]
    public function getHouse(int $id): JsonResponse
    {
        $data = $this->csvManager->readAll($this->filename);
        $house = null;
        foreach ($data as $line) {
            if ((int)$line[0] === $id) {
                $house = [
                    'id' => (int)$line[0],
                    'name' => $line[1],
                    'sleeping_capacity' => (int)$line[2],
                    'bathrooms' => (int)$line[3],
                    'location' => $line[4],
                    'price' => (float)$line[5],
                ];
                break;
            }
        }
        if ($house === null) {
            return new JsonResponse(['error' => 'House not found'], HttpResponse::HTTP_NOT_FOUND);
        }
        return new JsonResponse($house);
    }

    #[Route('/api/house', name: 'house_create', methods: ['POST'])]
    public function createHouse(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['id'], $data['name'], $data['sleeping_capacity'], $data['bathrooms'], $data['location'], $data['price'])) {
            return new JsonResponse(['error' => 'Missing required fields'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $this->csvManager->append($this->filename, [
            $data['id'],
            $data['name'],
            $data['sleeping_capacity'],
            $data['bathrooms'],
            $data['location'],
            $data['price'],
        ]);
        return new JsonResponse(['message' => 'House created successfully'], HttpResponse::HTTP_CREATED);
    }

    #[Route('/api/house/{id}', name: 'house_update', methods: ['PUT'])]
    public function updateHouse(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['id'], $data['name'], $data['sleeping_capacity'], $data['bathrooms'], $data['location'], $data['price'])) {
            return new JsonResponse(['error' => 'Missing required fields'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $this->csvManager->overwriteRow($this->filename, $id, [
            $data['id'],
            $data['name'],
            $data['sleeping_capacity'],
            $data['bathrooms'],
            $data['location'],
            $data['price'],
        ]);


        return new JsonResponse(['message' => 'House updated successfully'], HttpResponse::HTTP_OK);
    }
}
