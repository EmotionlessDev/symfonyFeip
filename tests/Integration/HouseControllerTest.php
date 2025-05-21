<?php

namespace App\Tests\Integration;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Path;
class HouseControllerTest extends WebTestCase
{

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $kernel = $this->client->getKernel();

        $this->dataDir = Path::join($kernel->getProjectDir(), 'var', 'data');
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }

        $filename = $kernel->getContainer()->getParameter('app.csv_houses_filename');
        $this->csvPath = Path::join($this->dataDir, $filename);


        $rows = [
            [1, 'House A', 4, 2, 'Location A', 100],
            [2, 'House B', 6, 3, 'Location B', 150],
        ];
        $content = '';
        foreach ($rows as $row) {
            $content .= implode(',', $row) . "\n";
        }
        file_put_contents($this->csvPath, $content);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->csvPath)) {
            unlink($this->csvPath);
        }
        parent::tearDown();
    }

    public function testListHouses(): void
    {
        $this->client->request('GET', '/api/house');
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('House A', $data[0]['name']);
        $this->assertSame(4, $data[0]['sleeping_capacity']);
        $this->assertSame(2, $data[0]['bathrooms']);
        $this->assertSame('Location A', $data[0]['location']);
        $this->assertSame(100, $data[0]['price']);
    }

    public function testGetHouse(): void
    {
        $this->client->request('GET', '/api/house/1');
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $data['id']);
        $this->assertSame('House A', $data['name']);
        $this->assertSame(4, $data['sleeping_capacity']);
        $this->assertSame(2, $data['bathrooms']);
        $this->assertSame('Location A', $data['location']);
        $this->assertSame(100, $data['price']);
    }

    public function testCreateHouse(): void
    {
        $payload = [
            'id' => 3,
            'name' => 'House C',
            'sleeping_capacity' => 5,
            'bathrooms' => 2,
            'location' => 'Location C',
            'price' => 200,
        ];

        $this->client->request('POST', '/api/house', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(201);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('House created successfully', $data['message']);
    }

    public function testUpdateHouse(): void
    {
        $payload = [
            'id' => 1,
            'name' => 'Updated House A',
            'sleeping_capacity' => 4,
            'bathrooms' => 2,
            'location' => 'Updated Location A',
            'price' => 120,
        ];

        $this->client->request('PUT', '/api/house/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($payload));
        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('House updated successfully', $data['message']);
    }
}