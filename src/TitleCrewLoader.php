<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

class TitleCrewLoader extends Loader
{
    public const HEADERS = ['tconst', 'directors', 'writers'];

    public function __construct(
        string $filename,
        callable $filterCallback = null,
        callable $processRow = null
    ) {
        parent::__construct($filename);

        $line = gzgets($this->file);
        if ($line === false) {
            throw new InvalidFormatException('Header not found: ' . $filename);
        }

        $fields = explode("\t", trim($line, "\n"));
        if ($fields !== self::HEADERS) {
            throw new InvalidFormatException('Format not recognized: ' . $filename);
        }

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, "\n"));
            $titleId = $fields[0];
            $directors = ($fields[1] !== '\\N') ? explode(',', $fields[1]) : null;
            $writers = ($fields[2] !== '\\N') ? explode(',', $fields[2]) : null;

            if (isset($this->data[$titleId])) {
                throw new DuplicateIdException('Duplicate title ID: ' . $titleId);
            }

            $row = [
                'titleId' => $titleId,
                'directors' => $directors,
                'writers' => $writers,
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                if ($processRow !== null) {
                    $row = $processRow($row);
                }

                $this->data[$titleId] = $row;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getCrew(string $titleId): ?array
    {
        return $this->data[$titleId] ?? null;
    }
}
