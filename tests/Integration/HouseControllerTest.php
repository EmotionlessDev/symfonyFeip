<?php

namespace App\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use App\Tests\Fixture\HouseFixture;
use RuntimeException;
use Exception;

class HouseControllerTest extends WebTestCase
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
            // Need to purge the database before running tests
            // Autoincrement IDs should be reset
            $connection = $this->entityManager->getConnection();
            $connection->executeStatement('TRUNCATE house RESTART IDENTITY CASCADE');

            $loader = new Loader();
            $loader->addFixture(new HouseFixture());

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

    public function testHouseList(): void
    {
        $this->client->request('GET', '/api/house');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $this->assertEquals('Test House', $data[0]['name']);
    }

    public function testGetHouse(): void
    {
        $this->client->request('GET', '/api/house/1');
        $response = $this->client->getResponse();

        $this->assertResponseIsSuccessful();
        $data = json_decode($response->getContent(), true);

        $this->assertEquals('Test House', $data['name']);
    }

    public function testCreateHouse(): void
    {
        $this->client->request('POST', '/api/house', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'New House',
            'sleeping_capacity' => 3,
            'bathrooms' => 1,
            'location' => 'Test Street',
            'price' => 200,
        ]));

        $this->assertResponseStatusCodeSame(201);
    }

    public function testUpdateHouse(): void
    {
        $this->client->request('PUT', '/api/house/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Updated House',
            'sleeping_capacity' => 5,
        ]));

        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/api/house/1');
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Updated House', $data['name']);
        $this->assertEquals(5, $data['sleeping_capacity']);
    }
}
