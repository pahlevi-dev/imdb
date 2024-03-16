#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Imdb\TitleBasicsLoader;
use Imdb\TitleRatingsLoader;

$options = getopt(
    "y:t:g:r:v:",
    [
        "min-year:",
        "title-type:",
        "genre:",
        "min-rating:",
        "min-votes:"
    ]
);

$minYear = isset($options['y']) ? $options['y'] : (isset($options['min-year']) ? $options['min-year'] : null);
$titleType = isset($options['t']) ? $options['t'] : (isset($options['title-type']) ? $options['title-type'] : null);
$genre = isset($options['g']) ? $options['g'] : (isset($options['genre']) ? $options['genre'] : null);
$minRating = isset($options['r']) ? $options['r'] : (isset($options['min-rating']) ? $options['min-rating'] : null);
$minVotes = isset($options['v']) ? $options['v'] : (isset($options['min-votes']) ? $options['min-votes'] : null);

$titleLoader = new TitleBasicsLoader(
    __DIR__ . '/../data/title.basics.tsv.gz',
    function ($row) use ($minYear, $titleType, $genre) {
        if ($minYear && $row['startYear'] < $minYear) {
            return false;
        }
        if ($titleType && $row['titleType'] != $titleType) {
            return false;
        }
        if ($genre && is_array($row['genres']) && !in_array($genre, $row['genres'])) {
            return false;
        }

        return true;
    },
    function ($row) {
        return [
            'primaryTitle' => $row['primaryTitle'],
            'startYear' => $row['startYear'],
        ];
    }
);
$titles = $titleLoader->getData();

$ratingLoader = new TitleRatingsLoader(
	__DIR__ . '/../data/title.ratings.tsv.gz',
    function ($row) use ($minRating, $minVotes) {
        if ($minRating && $row['averageRating'] < $minRating) {
            return false;
        }
        if ($minVotes && $row['numVotes'] < $minVotes) {
            return false;
        }

        return true;
    },
);
$ratings = $ratingLoader->getTopRatedTitles();

foreach ($ratings as $titleId => $rating) {
    if (!isset($titles[$titleId])) {
        continue;
    }

    $title = $titles[$titleId];
    extract($title);
    extract($rating);
    echo "$primaryTitle ($startYear): $averageRating * $numVotes\n";
}
