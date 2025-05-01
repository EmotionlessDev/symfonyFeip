<?php

namespace App\Service;


use RuntimeException;

class CsvManager
{
    private string $dataDir;

    /**
     * CsvManager constructor.
     *
     * @param string $dataDir Путь к директории с CSV файлами.
     */
    public function __construct(string $dataDir)
    {
        $this->dataDir = $dataDir;
        if (!is_dir($this->dataDir)) {
            throw new RuntimeException("Directory `$this->dataDir` does not exist");
        }
    }

    /**
     * Возвращает полный путь к файлу.
     *
     * @param string $filename
     * @return string
     */
    private function getPath(string $filename): string
    {
        return $this->dataDir . '/' . $filename;
    }

    /**
     * Читает CSV файл и возвращает массив строк.
     *
     * @param string $filename
     * @return array<array<string>>
     */
    public function readAll(string $filename): array
    {
        $path = $this->getPath($filename);
        if (!file_exists($path)) {
            throw new RuntimeException("File `$filename` not found");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = str_getcsv($line, ',', '"', '\\');
        }
        return $rows;
    }

    /**
     * Читает CSV файл и возвращает массив строк.
     *
     * @param string $filename
     * @param int $id
     * @return array<array<string|int|float>>|null
     */
    public function readById(string $filename, int $id): ?array
    {
        $rows = $this->readAll($filename);
        foreach ($rows as $row) {
            if ((int)$row[0] === $id) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Добавляет одну строку (массив) в конец CSV.
     *
     * @param string $filename
     * @param array<string|int|float> $row
     */
    public function append(string $filename, array $row): void
    {
        $path = $this->getPath($filename);
        $handle = fopen($path, 'a');
        if (false === $handle) {
            throw new RuntimeException("Could not open file `$filename` for writing");
        }
        if (filesize($path) > 0) {
            fwrite($handle, PHP_EOL);
        }
        fputcsv($handle, $row, ',', '"', '\\');
        fclose($handle);
    }

    /**
     * Перезаписывает весь файл новыми данными.
     *
     * @param string $filename
     * @param array<array<string|int|float>> $rows
     */
    public function overwrite(string $filename, array $rows): void
    {
        $path = $this->getPath($filename);
        $handle = fopen($path, 'w');
        if (false === $handle) {
            throw new RuntimeException("Could not open file `$filename` for writing");
        }
        foreach ($rows as $row) {
            fputcsv($handle, $row, ',', '"', '\\');
        }
        fclose($handle);
    }

    /**
     * перезаписывает строку в CSV файле по индексу.
     *
     * @param string $filename
     * @param int $id
     * @param array<string|int|float> $newRow
     */
    public function overwriteRow(string $filename, int $id, array $newRow): void
    {
        $rows = $this->readAll($filename);

        $found = false;
        foreach ($rows as $index => $row) {
            if ((int)$row[0] === $id) {
                $rows[$index] = $newRow;
                $found = true;
                break;
            }
        }

        if (! $found) {
            throw new RuntimeException("Row with ID `$id` not found in `$filename`");
        }

        $this->overwrite($filename, $rows);
    }


}