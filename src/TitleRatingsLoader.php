<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Utility\Data\DataException;
use DouglasGreen\Utility\Data\ValueException;

class TitleRatingsLoader extends Loader
{
    public const HEADERS = ['tconst', 'averageRating', 'numVotes'];

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
            $averageRating = floatval($fields[1]);
            $numVotes = intval($fields[2]);

            if (isset($this->data[$titleId])) {
                throw new ValueException('Duplicate title ID: ' . $titleId);
            }

            $row = [
                'titleId' => $titleId,
                'averageRating' => $averageRating,
                'numVotes' => $numVotes,
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
     * @return array<string, int|float|string>
     */
    public function getRating(string $titleId): ?array
    {
        return $this->data[$titleId] ?? null;
    }

    /**
     * @return array<string, array<string, int|float|string>>
     */
    public function getTopRatedTitles(int $limit = null): array
    {
        $ratings = $this->data;

        uasort($ratings, static function (array $first, array $second): int {
            $firstRating = round($first['averageRating'] * 1000);
            $secondRating = round($second['averageRating'] * 1000);
            if ($firstRating === $secondRating) {
                // If average ratings are considered equal, sort by numVotes
                return $second['numVotes'] <=> $first['numVotes'];
            }

            // Sort by averageRating
            return $secondRating <=> $firstRating;
        });

        if ($limit !== null && $limit !== 0) {
            return array_slice($ratings, 0, $limit, true);
        }

        return $ratings;
    }

    /**
     * @return array<string, array<string, int|float|string>>
     */
    public function getTopVotedTitles(int $limit = null): array
    {
        $votes = $this->data;

        uasort($votes, static function (array $first, array $second): int {
            // Directly compare the 'numVotes' to sort by votes
            $result = $second['numVotes'] <=> $first['numVotes'];
            if ($result === 0) {
                // If 'numVotes' are equal, fallback to sorting by averageRating
                $firstRating = round($first['averageRating'] * 1000);
                $secondRating = round($second['averageRating'] * 1000);
                return $secondRating <=> $firstRating;
            }

            return $result;
        });

        if ($limit !== null && $limit !== 0) {
            return array_slice($votes, 0, $limit, true);
        }

        return $votes;
    }
}
