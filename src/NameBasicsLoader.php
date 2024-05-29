<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Exceptions\DataException;
use DouglasGreen\Exceptions\ValueException;

class NameBasicsLoader extends Loader
{
    public const HEADERS = [
        'nconst',
        'primaryName',
        'birthYear',
        'deathYear',
        'primaryProfession',
        'knownForTitles',
    ];

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @throws DataException
     * @throws ValueException
     */
    public function __construct(
        string $filename,
        callable $filterCallback = null,
        callable $processRow = null
    ) {
        parent::__construct($filename);

        $line = gzgets($this->file);
        if ($line === false) {
            throw new DataException('Header not found: ' . $filename);
        }

        $fields = explode("\t", trim($line, "\n"));
        if ($fields !== self::HEADERS) {
            throw new DataException('Format not recognized: ' . $filename);
        }

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, "\n"));
            $personId = $fields[0];
            $primaryName = $fields[1];
            $birthYear = $fields[2] !== '\\N' ? intval($fields[2]) : null;
            $deathYear = $fields[3] !== '\\N' ? intval($fields[3]) : null;
            $primaryProfession = $fields[4] !== '\\N' ? explode(',', $fields[4]) : null;
            $knownForTitles = $fields[5] !== '\\N' ? explode(',', $fields[5]) : null;

            if (isset($this->data[$personId])) {
                throw new ValueException('Duplicate person ID: ' . $personId);
            }

            $row = [
                'primaryName' => $primaryName,
                'birthYear' => $birthYear,
                'deathYear' => $deathYear,
                'primaryProfession' => $primaryProfession,
                'knownForTitles' => $knownForTitles,
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                if ($processRow !== null) {
                    $row = $processRow($row);
                }

                $this->data[$personId] = $row;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPersonById(string $personId): ?array
    {
        return $this->data[$personId] ?? null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function searchByName(string $name): array
    {
        $results = [];

        foreach ($this->data as $personId => $row) {
            if (stripos((string) $row['primaryName'], $name) !== false) {
                $results[$personId] = $row;
            }
        }

        return $results;
    }
}
