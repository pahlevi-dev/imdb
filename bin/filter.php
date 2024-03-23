#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Imdb\TitleBasicsLoader;
use Imdb\TitleRatingsLoader;

$options = getopt(
    "y:t:g:r:v:as",
    [
        "min-year:",
        "title-type:",
        "genre:",
        "min-rating:",
        "min-votes:",
        "adult",
        "sort-by-votes"
    ]
);

$minYear = isset($options['y']) ? $options['y'] : (isset($options['min-year']) ? $options['min-year'] : null);
$titleType = isset($options['t']) ? $options['t'] : (isset($options['title-type']) ? $options['title-type'] : null);
$genre = isset($options['g']) ? $options['g'] : (isset($options['genre']) ? $options['genre'] : null);
$minRating = isset($options['r']) ? $options['r'] : (isset($options['min-rating']) ? $options['min-rating'] : null);
$minVotes = isset($options['v']) ? $options['v'] : (isset($options['min-votes']) ? $options['min-votes'] : null);
$adult = isset($options['a']) || isset($options['adult']);
$sortByVotes = isset($options['s']) || isset($options['sort-by-votes']);

$titleLoader = new TitleBasicsLoader(
    __DIR__ . '/../data/title.basics.tsv.gz',
    function ($row) use ($minYear, $titleType, $genre, $adult) {
        if ($minYear && $row['startYear'] < $minYear) {
            return false;
        }
        if ($titleType && $row['titleType'] != $titleType) {
            return false;
        }
        if ($genre && is_array($row['genres']) && !in_array($genre, $row['genres'])) {
            return false;
        }
        if ($adult && !$row['isAdult']) {
            return false;
        }

        return true;
    },
    function ($row) {
        return [
            'primaryTitle' => $row['primaryTitle'],
            'startYear' => $row['startYear'],
            'genres' => $row['genres']
        ];
    }
);
$titles = $titleLoader->getData();

$ratingLoader = new TitleRatingsLoader(
    __DIR__ . '/../data/title.ratings.tsv.gz',
    function ($row) use ($titles, $minRating, $minVotes) {
        if (!isset($titles[$row['titleId']])) {
            return false;
        }

        if ($minRating && $row['averageRating'] < $minRating) {
            return false;
        }
        if ($minVotes && $row['numVotes'] < $minVotes) {
            return false;
        }

        return true;
    },
);
if ($sortByVotes) {
    $ratings = $ratingLoader->getTopVotedTitles();
} else {
    $ratings = $ratingLoader->getTopRatedTitles();
}

foreach ($ratings as $titleId => $rating) {
    $title = $titles[$titleId];
    extract($title);
    extract($rating);
    $genreDesc = $genres ? ' (' . implode(', ', $genres) . ')' : '';
    echo "$primaryTitle ($startYear): $averageRating * $numVotes$genreDesc\n";
}
