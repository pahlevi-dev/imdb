<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Utility\Exceptions\Data\DataException;
use DouglasGreen\Utility\Exceptions\Data\ValueException;

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

    public const VALID_GENRES = [
        'Action',
        'Adult',
        'Adventure',
        'Animation',
        'Biography',
        'Comedy',
        'Crime',
        'Documentary',
        'Drama',
        'Family',
        'Fantasy',
        'Film-Noir',
        'Game-Show',
        'History',
        'Horror',
        'Music',
        'Musical',
        'Mystery',
        'News',
        'Reality-TV',
        'Romance',
        'Sci-Fi',
        'Short',
        'Sport',
        'Talk-Show',
        'Thriller',
        'War',
        'Western',
    ];

    public const VALID_TITLE_TYPES = [
        'movie',
        'short',
        'tvEpisode',
        'tvMiniSeries',
        'tvMovie',
        'tvPilot',
        'tvSeries',
        'tvShort',
        'tvSpecial',
        'video',
        'videoGame',
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
            $tconst = $fields[0];
            $titleType = $fields[1];
            $primaryTitle = $fields[2];
            $originalTitle = $fields[3];
            $isAdult = $fields[4] === '1';
            $startYear = $fields[5] !== '\\N' ? intval($fields[5]) : null;
            $endYear = $fields[6] !== '\\N' ? intval($fields[6]) : null;
            $runtimeMinutes = $fields[7] !== '\\N' ? intval($fields[7]) : null;
            $genres = $fields[8] !== '\\N' ? explode(',', $fields[8]) : [];

            if (isset($this->data[$tconst])) {
                throw new ValueException('Duplicate title ID: ' . $tconst);
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
