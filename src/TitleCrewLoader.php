<?php

namespace Imdb;

use Exception;

class TitleCrewLoader extends Loader
{
    public function __construct(
        string $filename,
        callable $filterCallback = null
    ) {
        parent::__construct($filename, $filterCallback);

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, "\n"));
            $titleId = $fields[0];
            $directors = ($fields[1] !== '\\N') ? explode(',', $fields[1]) : null;
            $writers = ($fields[2] !== '\\N') ? explode(',', $fields[2]) : null;

            if (isset($this->data[$titleId])) {
                throw new Exception("Duplicate title ID: $titleId");
            }

            $row = [
                'directors' => $directors,
                'writers' => $writers
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                $this->data[$titleId] = $row;
            }
        }
    }

    public function getCrew(string $titleId): ?array
    {
        return $this->data[$titleId] ?? null;
    }
}
