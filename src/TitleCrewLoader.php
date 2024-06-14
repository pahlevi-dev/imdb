<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Utility\Data\DataException;
use DouglasGreen\Utility\Data\ValueException;

class TitleCrewLoader extends Loader
{
    public const HEADERS = ['tconst', 'directors', 'writers'];

    /**
     * @throws DataException
     * @throws ValueException
     */
    public function __construct(
        string $filename,
        callable $filterCallback = null,
        callable $processRow = null,
    ) {
        parent::__construct($filename);

        $line = gzgets($this->file);
        if ($line === false) {
            throw new DataException('Header not found: ' . $filename);
        }

        $fields = explode("\t", trim($line, PHP_EOL));
        if ($fields !== self::HEADERS) {
            throw new DataException('Format not recognized: ' . $filename);
        }

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, PHP_EOL));
            $titleId = $fields[0];
            $directors = $fields[1] !== '\N' ? explode(',', $fields[1]) : null;
            $writers = $fields[2] !== '\N' ? explode(',', $fields[2]) : null;

            if (isset($this->data[$titleId])) {
                throw new ValueException('Duplicate title ID: ' . $titleId);
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
