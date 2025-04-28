<?php

namespace App\Tests\Service;

use App\Service\CsvManager;
use PHPUnit\Framework\TestCase;

class CsvManagerTest extends TestCase
{
    private CsvManager $csvManager;
    private string $filename;

    protected function setUp(): void
    {
        $this->csvManager = new CsvManager(__DIR__ . '/../testData');
        $this->filename = 'test.csv';
        file_put_contents(__DIR__ . "/../testData/$this->filename", "id,name,age\n1,John Doe,30\n2,Jane Smith,25");
    }

    protected function tearDown(): void
    {
        if (file_exists(__DIR__ . "/../testData/$this->filename")) {
            unlink(__DIR__ . "/../testData/$this->filename");
        }
    }

    public function testReadAll(): void
    {
        $expected = [
            ['id', 'name', 'age'],
            ['1', 'John Doe', '30'],
            ['2', 'Jane Smith', '25'],
        ];

        $result = $this->csvManager->readAll($this->filename);
        $this->assertEquals($expected, $result);
    }

    public function testReadById(): void
    {
        $expected = ['1', 'John Doe', '30'];

        $result = $this->csvManager->readById($this->filename, 1);
        $this->assertEquals($expected, $result);
    }

    public function testAppend(): void
    {
        $data = ['3', 'Alice Johnson', '28'];
        $this->csvManager->append($this->filename, $data);
        $expected = [
            ['id', 'name', 'age'],
            ['1', 'John Doe', '30'],
            ['2', 'Jane Smith', '25'],
            ['3', 'Alice Johnson', '28'],
        ];
        $result = $this->csvManager->readAll($this->filename);
        $this->assertEquals($expected, $result);
    }

    public function testOverwrite(): void
    {
        $data = [
            ['id', 'name', 'age'],
            ['4', 'Bob Brown', '35'],
        ];
        $this->csvManager->overwrite($this->filename, $data);
        $expected = [
            ['id', 'name', 'age'],
            ['4', 'Bob Brown', '35'],
        ];
        $result = $this->csvManager->readAll($this->filename);
        $this->assertEquals($expected, $result);
    }

    public function testOverwriteRow(): void
    {
        $newRow = ['1', 'John Doe', '31'];
        $this->csvManager->overwriteRow($this->filename, 1, $newRow);
        $expected = [
            ['id', 'name', 'age'],
            ['1', 'John Doe', '31'],
            ['2', 'Jane Smith', '25'],
        ];
        $result = $this->csvManager->readAll($this->filename);
        $this->assertEquals($expected, $result);
    }
}
