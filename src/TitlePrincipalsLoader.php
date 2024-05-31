<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Exceptions\DataException;
use DouglasGreen\Exceptions\ValueException;

class TitlePrincipalsLoader extends Loader
{
    public const array HEADERS = ['tconst', 'ordering', 'nconst', 'category', 'job', 'characters'];

    /**
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

        $fields = explode("\t", trim($line, PHP_EOL));
        if ($fields !== self::HEADERS) {
            throw new DataException('Format not recognized: ' . $filename);
        }

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, PHP_EOL));
            $titleId = $fields[0];
            $ordering = intval($fields[1]);
            $personId = $fields[2];
            $category = $fields[3];
            $job = $fields[4] !== '\\N' ? $fields[4] : null;
            $characters = $fields[5] !== '\\N' ? $fields[5] : null;

            if (isset($this->data[$titleId][$ordering])) {
                throw new ValueException(sprintf('Duplicate title ID and order: %s, %d', $titleId, $ordering));
            }

            $row = [
                'titleId' => $titleId,
                'personId' => $personId,
                'category' => $category,
                'job' => $job,
                'characters' => $characters,
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                if ($processRow !== null) {
                    $row = $processRow($row);
                }

                $this->data[$titleId][$ordering] = $row;
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function getPrincipals(string $titleId): ?array
    {
        return $this->data[$titleId] ?? null;
    }

    /**
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function getPrincipalsByPersonId(string $personId): ?array
    {
        $principals = [];

        foreach ($this->data as $titleId => $titlePrincipals) {
            foreach ($titlePrincipals as $ordering => $row) {
                if ($row['personId'] === $personId) {
                    $principals[$titleId][$ordering] = $row;
                }
            }
        }

        return $principals;
    }
}
