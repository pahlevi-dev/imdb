<?php

namespace Imdb;

use Exception;

class TitleRatingsLoader extends Loader
{
    const HEADERS = [
        'tconst',
        'averageRating',
        'numVotes'
    ];

    public function __construct(
        string $filename,
        callable $filterCallback = null
    ) {
        parent::__construct($filename, $filterCallback);

        $line = gzgets($this->file);
        $fields = explode("\t", trim($line, "\n"));
        if ($fields != self::HEADERS) {
            throw new Exception("Format not recognized: $filename");
        }

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, "\n"));
            $titleId = $fields[0];
            $averageRating = floatval($fields[1]);
            $numVotes = intval($fields[2]);

            if (isset($this->data[$titleId])) {
                throw new Exception("Duplicate title ID: $titleId");
            }

            $row = [
                'averageRating' => $averageRating,
                'numVotes' => $numVotes
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                $this->data[$titleId] = $row;
            }
        }
    }

    public function getRating(string $titleId): ?array
    {
        return $this->data[$titleId] ?? null;
    }

    public function getTopRatedTitles(int $limit = 10): array
    {
        $ratings = $this->data;
        usort($ratings, function ($a, $b) {
            if ($a['averageRating'] == $b['averageRating']) {
                return $b['numVotes'] - $a['numVotes'];
            }
            return $b['averageRating'] - $a['averageRating'];
        });

        return array_slice($ratings, 0, $limit);
    }
}
