<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Repository\HouseRepository;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Booking;



class BookingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BookingRepository $bookingRepository,
        private readonly HouseRepository $houseRepository,
    ) {
    }

    #[Route('/api/booking', name: 'booking_list', methods: ['GET'])]
    public function bookingList(): JsonResponse
    {
        $bookings = $this->bookingRepository->findAll();

        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'id' => $booking->getId(),
                'phone_number' => $booking->getPhoneNumber(),
                'house_id' => $booking->getHouse()->getId(),
                'comment' => $booking->getComment(),
            ];
        }

        return new JsonResponse($data);
    }

    #[Route('/api/booking/{id}', name: 'booking_detail', methods: ['GET'])]
    public function getBooking(int $id): JsonResponse
    {
        $booking = $this->bookingRepository->find($id);

        if (!$booking) {
            return new JsonResponse(['error' => 'Booking not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $booking->getId(),
            'phone_number' => $booking->getPhoneNumber(),
            'house_id' => $booking->getHouse()->getId(),
            'comment' => $booking->getComment(),
        ];

        return new JsonResponse($data);
    }

    #[Route('/api/booking', name: 'booking_create', methods: ['POST'])]
    public function createBooking(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['phone_number'], $data['house_id'], $data['comment'])) {
            return new JsonResponse(['error' => 'Missing required fields'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $house = $this->houseRepository->find($data['house_id']);
        if (!$house) {
            return new JsonResponse(['error' => 'House not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        $booking = new Booking();
        $booking->setPhoneNumber($data['phone_number']);
        $booking->setComment($data['comment']);
        $booking->setHouse($house);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Booking created', 'id' => $booking->getId()], HttpResponse::HTTP_CREATED);
    }

    #[Route('/api/booking/{id}', name: 'booking_update', methods: ['PUT'])]
    public function updateBooking(int $id, Request $request): JsonResponse
    {
        $booking = $this->bookingRepository->find($id);
        if (!$booking) {
            return new JsonResponse(['error' => 'Booking not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new JsonResponse(['error' => 'Invalid JSON'], HttpResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['phone_number'], $data['house_id'], $data['comment'])) {
            return new JsonResponse(['error' => 'Missing required fields'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $house = $this->houseRepository->find($data['house_id']);
        if (!$house) {
            return new JsonResponse(['error' => 'House not found'], HttpResponse::HTTP_NOT_FOUND);
        }

        $booking->setPhoneNumber($data['phone_number']);
        $booking->setComment($data['comment']);
        $booking->setHouse($house);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Booking updated'], HttpResponse::HTTP_OK);
    }
}
