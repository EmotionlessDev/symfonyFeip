<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApplicationController extends AbstractController
{
    #[Route('/api/application', name: 'application_list', methods: ['GET'])]
    public function applicationList(): JsonResponse
    {
        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/bookings.csv';
        $csvFile = file($csvPath);
        $applications = [];
        foreach ($csvFile as $line) {
            $data = str_getcsv($line);
            $applications[] = [
                'id' => (int)$data[0],
                'phone_number' => $data[1],
                'house_id' => (int)$data[2],
                'comment' => $data[3],
            ];
        }
        return $this->json($applications);
    }

    #[Route('/api/application/{id}', name: 'application_detail', methods: ['GET'])]
    public function getApplication(int $id): JsonResponse
    {
        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/bookings.csv';
        $csvFile = file($csvPath);
        $data = null;

        foreach ($csvFile as $line) {
            $lineData = str_getcsv($line);
            if ((int)$lineData[0] === $id) {
                $data = [
                    'id' => (int)$lineData[0],
                    'phone_number' => $lineData[1],
                    'house_id' => (int)$lineData[2],
                    'comment' => $lineData[3],
                ];
                break;
            }
        }

        if ($data === null) {
            return new JsonResponse(['error' => 'Application not found'], 404);
        }

        return $this->json($data);
    }

    #[Route('/api/application', name: 'application_create', methods: ['POST'])]
    public function createApplication(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/bookings.csv';
        $csvFile = fopen($csvPath, 'a');
        fputcsv($csvFile, [
            $data['id'],
            $data['phone_number'],
            $data['house_id'],
            $data['comment'],
        ]);
        fclose($csvFile);

        return new JsonResponse(['message' => 'Application created'], 201);
    }

    #[Route('/api/application/{id}', name: 'application_update', methods: ['PUT'])]
    public function updateApplication(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $csvPath = $this->getParameter('kernel.project_dir') . '/var/data/bookings.csv';
        $csvFile = file($csvPath);

        if ($csvFile === false) {
            return new JsonResponse(['error' => 'File not found'], 404);
        }

        $comment = $data['comment'] ?? null;

        foreach ($csvFile as $index => $line) {
            $lineData = str_getcsv($line);
            if (count($lineData) < 4) {
                continue;
            }

            if ((int)$lineData[0] === $id) {
                $lineData[3] = $comment ?? '';
                $csvFile[$index] = implode(',', $lineData) . "\n";
                file_put_contents($csvPath, implode('', $csvFile));
                break;
            }
        }


        return new JsonResponse(['message' => 'Application updated'], 200);
    }
}
