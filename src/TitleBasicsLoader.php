<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

class TitleBasicsLoader extends Loader
{
    public const HEADERS = [
        'tconst',
        'titleType',
        'primaryTitle',
        'originalTitle',
        'isAdult',
        'startYear',
        'endYear',
        'runtimeMinutes',
        'genres',
    ];

    /**
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
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
            $tconst = $fields[0];
            $titleType = $fields[1];
            $primaryTitle = $fields[2];
            $originalTitle = $fields[3];
            $isAdult = ($fields[4] === '1');
            $startYear = ($fields[5] !== '\\N') ? intval($fields[5]) : null;
            $endYear = ($fields[6] !== '\\N') ? intval($fields[6]) : null;
            $runtimeMinutes = ($fields[7] !== '\\N') ? intval($fields[7]) : null;
            $genres = ($fields[8] !== '\\N') ? explode(',', $fields[8]) : [];

            if (isset($this->data[$tconst])) {
                throw new DuplicateIdException('Duplicate title ID: ' . $tconst);
            }

            $row = [
                'titleId' => $tconst,
                'titleType' => $titleType,
                'primaryTitle' => $primaryTitle,
                'originalTitle' => $originalTitle,
                'isAdult' => $isAdult,
                'startYear' => $startYear,
                'endYear' => $endYear,
                'runtimeMinutes' => $runtimeMinutes,
                'genres' => $genres,
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                if ($processRow !== null) {
                    $row = $processRow($row);
                }

                $this->data[$tconst] = $row;
            }
        }
    }
}
