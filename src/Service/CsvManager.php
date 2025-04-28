<?php

namespace App\Service;


class CsvManager
{
    private string $dataDir;

    /**
     * CsvManager constructor.
     *
     */
    public function __construct()
    {
        $this->dataDir = __DIR__ . '/../../var/data';
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
            throw new \RuntimeException("File `$filename` not found");
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $rows = [];
        foreach ($lines as $line) {
            $rows[] = str_getcsv($line);
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
            throw new \RuntimeException("Could not open file `$filename` for writing");
        }
        fputcsv($handle, $row);
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
            throw new \RuntimeException("Could not open file `$filename` for writing");
        }
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }

    /**
     * перезаписывает строку в CSV файле по индексу.
     *
     * @param string $filename
     * @param int $rowIndex
     * @param array<string|int|float> $newRow
     */
    public function overwriteRow(string $filename, int $rowIndex, array $newRow): void
    {
        $rows = $this->readAll($filename);
        if (isset($rows[$rowIndex])) {
            $rows[$rowIndex] = $newRow;
            $this->overwrite($filename, $rows);
        } else {
            throw new \RuntimeException("Row index `$rowIndex` out of bounds");
        }
    }


}