<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;



class HouseController extends AbstractController
{

    #[Route('/api/house', name: 'house_list', methods: ['GET'])]
    public function houseList(): JsonResponse
    {
        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/houses.csv';
        $csvFile = file($csvPath);
        $houses = [];
        foreach ($csvFile as $line) {
            $data = str_getcsv($line);
            $houses[] = [
                'id' => (int)$data[0],
                'name' => $data[1],
                'sleeping_capacity' => (int)$data[2],
                'bathrooms' => (int)$data[3],
                'location' => $data[4],
                'price' => (float)$data[5],
            ];
        }
        return $this->json($houses);
    }

    #[Route('/api/house/{id}', name: 'house_detail', methods: ['GET'])]
    public function getHouse(int $id): JsonResponse
    {
        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/houses.csv';
        $csvFile = file($csvPath);
        $data = null;

        foreach ($csvFile as $line) {
            $lineData = str_getcsv($line);
            if ((int)$lineData[0] === $id) {
                $data = [
                    'id' => (int)$lineData[0],
                    'name' => $lineData[1],
                    'sleeping_capacity' => (int)$lineData[2],
                    'bathrooms' => (int)$lineData[3],
                    'location' => $lineData[4],
                    'price' => (float)$lineData[5],
                ];
                break;
            }
        }

        if ($data === null) {
            return new JsonResponse(['error' => 'House not found'], HttpResponse::HTTP_NOT_FOUND);
        }
        return new JsonResponse($data);
    }

    #[Route('/api/house', name: 'house_create', methods: ['POST'])]
    public function createHouse(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/houses.csv';
        $csvFile = fopen($csvPath, 'a');
        fputcsv($csvFile, [
            $data['id'],
            $data['name'],
            $data['sleeping_capacity'],
            $data['bathrooms'],
            $data['location'],
            $data['price'],
        ]);
        fclose($csvFile);

        return new JsonResponse(['message' => 'House created successfully'], HttpResponse::HTTP_CREATED);
    }

    #[Route('/api/house/{id}', name: 'house_update', methods: ['PUT'])]
    public function updateHouse(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/houses.csv';
        $csvFile = file($csvPath);
        $updatedData = [];

        foreach ($csvFile as $line) {
            $lineData = str_getcsv($line);
            if ((int)$lineData[0] === $id) {
                $updatedData[] = [
                    $id,
                    $data['name'],
                    $data['sleeping_capacity'],
                    $data['bathrooms'],
                    $data['location'],
                    $data['price'],
                ];
            } else {
                $updatedData[] = $lineData;
            }
        }

        file_put_contents($csvPath, '');
        foreach ($updatedData as $line) {
            file_put_contents($csvPath, implode(',', $line) . PHP_EOL, FILE_APPEND);
        }

        return new JsonResponse(['message' => 'House updated successfully']);
    }

}
