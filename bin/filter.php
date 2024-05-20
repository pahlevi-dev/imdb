#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Imdb\TitleBasicsLoader;
use Imdb\TitleRatingsLoader;

$options = getopt(
    'y:t:g:r:v:as',
    ['min-year:', 'title-type:', 'genre:', 'min-rating:', 'min-votes:', 'adult', 'sort-by-votes']
);

$minYear = $options['y'] ?? ($options['min-year'] ?? null);
$titleType = $options['t'] ?? ($options['title-type'] ?? null);
$genre = $options['g'] ?? ($options['genre'] ?? null);
$minRating = $options['r'] ?? ($options['min-rating'] ?? null);
$minVotes = $options['v'] ?? ($options['min-votes'] ?? null);
$adult = isset($options['a']) || isset($options['adult']);
$sortByVotes = isset($options['s']) || isset($options['sort-by-votes']);

$titleLoader = new TitleBasicsLoader(
    __DIR__ . '/../data/title.basics.tsv.gz',
    static function ($row) use ($minYear, $titleType, $genre, $adult): bool {
        if ($minYear && $row['startYear'] < $minYear) {
            return false;
        }

        if ($titleType && $row['titleType'] !== $titleType) {
            return false;
        }

        if ($genre && is_array($row['genres']) && ! in_array($genre, $row['genres'], true)) {
            return false;
        }

        return ! ($adult && ! $row['isAdult']);
    },
    static fn($row): array => [
        'primaryTitle' => $row['primaryTitle'],
        'startYear' => $row['startYear'],
        'genres' => $row['genres'],
    ]
);
$titles = $titleLoader->getData();

$ratingLoader = new TitleRatingsLoader(
    __DIR__ . '/../data/title.ratings.tsv.gz',
    static function (array $row) use ($titles, $minRating, $minVotes): bool {
        if (! isset($titles[$row['titleId']])) {
            return false;
        }

        if ($minRating && $row['averageRating'] < $minRating) {
            return false;
        }

        return ! ($minVotes && $row['numVotes'] < $minVotes);
    },
);
$ratings = $sortByVotes ? $ratingLoader->getTopVotedTitles() : $ratingLoader->getTopRatedTitles();

foreach ($ratings as $titleId => $rating) {
    $title = $titles[$titleId];
    extract($title);
    extract($rating);
    $genreDesc = $genres ? ' (' . implode(', ', $genres) . ')' : '';
    echo sprintf('%s (%s): %s * %s%s%s', $primaryTitle, $startYear, $averageRating, $numVotes, $genreDesc, PHP_EOL);
}
