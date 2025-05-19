<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CsvManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ApplicationController extends AbstractController
{
    public function __construct(
        private readonly CsvManager $csvManager,
        private readonly string     $filename
    ) {

    }

    #[Route('/api/application', name: 'application_list', methods: ['GET'])]
    public function applicationList(): JsonResponse
    {
        $data = $this->csvManager->readAll($this->filename);
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
        $data = $this->csvManager->readById($this->filename, $id);
        if ($data === null) {
            return new JsonResponse(['error' => 'Application not found'],  HttpResponse::HTTP_NOT_FOUND);
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
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['id'], $data['phone_number'], $data['house_id'], $data['comment'])) {
            return new JsonResponse(['error' => 'Missing required fields'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $this->csvManager->append($this->filename, [
            $data['id'],
            $data['phone_number'],
            $data['house_id'],
            $data['comment'],
        ]);
        return new JsonResponse(['message' => 'Application created'], HttpResponse::HTTP_CREATED);
    }

    #[Route('/api/application/{id}', name: 'application_update', methods: ['PUT'])]
    public function updateApplication(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['id'], $data['phone_number'], $data['house_id'], $data['comment'])) {
            return new JsonResponse(['error' => 'Missing required fields'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $this->csvManager->overwriteRow($this->filename, $id, [
            $data['id'],
            $data['phone_number'],
            $data['house_id'],
            $data['comment'],
        ]);
        return new JsonResponse(['message' => 'Application updated'], HttpResponse::HTTP_OK);
    }
}
