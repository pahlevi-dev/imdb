<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Utility\Exceptions\Data\DataException;
use DouglasGreen\Utility\Exceptions\Data\ValueException;

class TitleAkaLoader extends Loader
{
    public const HEADERS = [
        'titleId',
        'ordering',
        'title',
        'region',
        'language',
        'types',
        'attributes',
        'isOriginalTitle',
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
            $ordering = intval($fields[1]);
            $title = $fields[2];
            $region = $fields[3] !== '\\N' ? $fields[3] : null;
            $language = $fields[4] !== '\\N' ? $fields[4] : null;
            $types = $fields[5] !== '\\N' ? explode(', ', $fields[5]) : null;
            $attributes = $fields[6] !== '\\N' ? explode(', ', $fields[6]) : null;
            $isOriginalTitle = $fields[7] === '1';

            if (isset($this->data[$titleId][$ordering])) {
                throw new ValueException(
                    sprintf('Duplicate title ID and order: %s, %d', $titleId, $ordering),
                );
            }

            $row = [
                'titleId' => $titleId,
                'ordering' => $ordering,
                'title' => $title,
                'region' => $region,
                'language' => $language,
                'types' => $types,
                'attributes' => $attributes,
                'isOriginalTitle' => $isOriginalTitle,
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
    public function getAkas(string $titleId): ?array
    {
        return $this->data[$titleId] ?? null;
    }
}
