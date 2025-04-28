<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApplicationController extends AbstractController
{
    private readonly CsvManager $csvManager;
    public function __construct()
    {
        $this->csvManager = new CsvManager(__DIR__ . '/../../var/data');
    }

    #[Route('/api/application', name: 'application_list', methods: ['GET'])]
    public function applicationList(): JsonResponse
    {
        $data = $this->csvManager->readAll('bookings.csv');
        $applications = [];
        foreach ($data as $line) {
            $applications[] = [
                'id' => (int)$line[0],
                'phone_number' => $line[1],
                'house_id' => (int)$line[2],
                'comment' => $line[3],
            ];
        }
        return new JsonResponse($applications);
    }

    #[Route('/api/application/{id}', name: 'application_detail', methods: ['GET'])]
    public function getApplication(int $id): JsonResponse
    {
        $data = $this->csvManager->readById('bookings.csv', $id);
        if ($data === null) {
            return new JsonResponse(['error' => 'Application not found'], 404);
        }
        return new JsonResponse([
            'id' => (int)$data[0],
            'phone_number' => $data[1],
            'house_id' => (int)$data[2],
            'comment' => $data[3],
        ]);
    }

    #[Route('/api/application', name: 'application_create', methods: ['POST'])]
    public function createApplication(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $this->csvManager->append('bookings.csv', [
            $data['id'],
            $data['phone_number'],
            $data['house_id'],
            $data['comment'],
        ]);
        return new JsonResponse(['message' => 'Application created'], 201);
    }

    #[Route('/api/application/{id}', name: 'application_update', methods: ['PUT'])]
    public function updateApplication(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }
        $this->csvManager->overwriteRow('bookings.csv', $id, [
            $data['id'],
            $data['phone_number'],
            $data['house_id'],
            $data['comment'],
        ]);
        return new JsonResponse(['message' => 'Application updated'], 200);
    }
}
