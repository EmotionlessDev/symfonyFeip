<?php

namespace App\Tests\Integration;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApplicationControllerTest extends WebTestCase
{
    private string $dataDir;
    private string $csvPath;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $kernel = $this->client->getKernel();

        $this->dataDir = $kernel->getProjectDir() . '/var/data';
        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0777, true);
        }

        $filename = $kernel->getContainer()->getParameter('app.csv_bookings_filename');
        $this->csvPath = $this->dataDir . '/' . $filename;

        $rows = [
            [1, '1234567890', 10, 'first comment'],
            [2, '0987654321', 20, 'second comment'],
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

    public function testListApplications(): void
    {
        $this->client->request('GET', '/api/application');
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $data);
        $this->assertSame(1, $data[0]['id']);
        $this->assertSame('1234567890', $data[0]['phone_number']);
        $this->assertSame(10, $data[0]['house_id']);
        $this->assertSame('first comment', $data[0]['comment']);
    }

    public function testGetApplication(): void
    {
        $this->client->request('GET', '/api/application/1');
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $data['id']);
        $this->assertSame('1234567890', $data['phone_number']);
        $this->assertSame(10, $data['house_id']);
        $this->assertSame('first comment', $data['comment']);
    }

    public function testCreateApplication(): void
    {
        $payload = [
            'id' => 3,
            'phone_number' => '1112223333',
            'house_id' => 30,
            'comment' => 'third comment',
        ];
        $this->client->request(
            'POST',
            '/api/application',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );
        $this->assertResponseStatusCodeSame(201);

        $this->client->request('GET', '/api/application/3');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(3, $data['id']);
        $this->assertSame('1112223333', $data['phone_number']);
        $this->assertSame(30, $data['house_id']);
        $this->assertSame('third comment', $data['comment']);
    }

    public function testUpdateApplication(): void
    {
        $payload = [
            'id' => 1,
            'phone_number' => '9998887777',
            'house_id' => 100,
            'comment' => 'updated comment',
        ];
        $this->client->request(
            'PUT',
            '/api/application/1',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload)
        );
        $this->assertResponseIsSuccessful();

        $this->client->request('GET', '/api/application/1');
        $this->assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $data['id']);
        $this->assertSame('9998887777', $data['phone_number']);
        $this->assertSame(100, $data['house_id']);
        $this->assertSame('updated comment', $data['comment']);
    }
}