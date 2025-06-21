<?php

namespace App\Tests\Integration;
use App\Tests\Fixture\BookingFixture;
use App\Tests\Fixture\HouseFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use RuntimeException;
use Exception;


class BookingControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'HTTP_ACCEPT' => 'application/json',
        ]);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeStatement('TRUNCATE booking RESTART IDENTITY CASCADE');
            $connection->executeStatement('TRUNCATE house RESTART IDENTITY CASCADE');

            $loader = new Loader();
            $loader->addFixture(new HouseFixture());
            $loader->addFixture(new BookingFixture());

            $executor = new ORMExecutor($this->entityManager);
            $executor->execute($loader->getFixtures(), append: true);
        } catch (Exception $e) {
            throw new RuntimeException('Failed to set up test environment: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
    }

    public function testBookingList(): void
    {
        $this->client->request('GET', '/api/booking');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('1234567890', $data[0]['phone_number']);
        $this->assertEquals('Test booking comment', $data[0]['comment']);
        $this->assertEquals(1, $data[0]['house_id']);
    }

    public function testGetBooking(): void
    {
        $this->client->request('GET', '/api/booking/1');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertEquals(1, $data['id']);
        $this->assertEquals('1234567890', $data['phone_number']);
        $this->assertEquals('Test booking comment', $data['comment']);
        $this->assertEquals(1, $data['house_id']);
    }

    public function testCreateBooking(): void
    {
        $this->client->request('POST', '/api/booking', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'phone_number' => '0987654321',
            'house_id' => 1,
            'comment' => 'New booking comment',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertEquals('0987654321', $data['phone_number']);
        $this->assertEquals('New booking comment', $data['comment']);
        $this->assertEquals(1, $data['house_id']);
    }

    public function testCreateBookingWithInvalidHouse(): void
    {
        $this->client->request('POST', '/api/booking', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'phone_number' => '0987654321',
            'house_id' => 999, // Non-existent house ID
            'comment' => 'New booking comment',
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(404);
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertEquals('House not found', $data['error']);
    }


    public function testCreateBookingWithMissingFields(): void
    {
        $this->client->request('POST', '/api/booking', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'phone_number' => '0987654321',
            // Missing house_id and comment
        ]));

        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertEquals('Missing required fields', $data['error']);
    }
    public function testUpdateBooking(): void
    {
        $this->client->request('PUT', '/api/booking/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'phone_number' => '1234567890',
            'comment' => 'Updated booking comment',
        ]));

        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/api/booking/1');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('1234567890', $data['phone_number']);
        $this->assertEquals('Updated booking comment', $data['comment']);
    }
}