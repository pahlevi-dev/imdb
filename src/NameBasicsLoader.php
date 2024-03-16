<?php

namespace Imdb;

use Exception;

class NameBasicsLoader extends Loader
{
    public function __construct(
        string $filename,
        callable $filterCallback = null
    ) {
        parent::__construct($filename, $filterCallback);

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, "\n"));
            $personId = $fields[0];
            $primaryName = $fields[1];
            $birthYear = $fields[2] !== '\\N' ? intval($fields[2]) : null;
            $deathYear = $fields[3] !== '\\N' ? intval($fields[3]) : null;
            $primaryProfession = $fields[4] !== '\\N' ? explode(',', $fields[4]) : null;
            $knownForTitles = $fields[5] !== '\\N' ? explode(',', $fields[5]) : null;

            if (isset($this->data[$personId])) {
                throw new Exception("Duplicate person ID: $personId");
            }

            $row = [
                'primaryName' => $primaryName,
                'birthYear' => $birthYear,
                'deathYear' => $deathYear,
                'primaryProfession' => $primaryProfession,
                'knownForTitles' => $knownForTitles
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                $this->data[$personId] = $row;
            }
        }
    }

    public function getPersonById(string $personId): ?array
    {
        return $this->data[$personId] ?? null;
    }

    public function searchByName(string $name): array
    {
        $results = [];

        foreach ($this->data as $personId => $row) {
            if (stripos($row['primaryName'], $name) !== false) {
                $results[$personId] = $row;
            }
        }

        return $results;
    }
}
