#!/usr/bin/env php
<?php

/**
 * @file Print statistics about titles and ratings.
 */

require __DIR__ . '/../vendor/autoload.php';

use Imdb\TitleBasicsLoader;
use Imdb\TitleRatingsLoader;

$titleLoader = new TitleBasicsLoader(
    __DIR__ . '/../data/title.basics.tsv.gz',
    null,
    function ($row) {
        return [
            'titleType' => $row['titleType'],
            'startYear' => $row['startYear'],
            'runtimeMinutes' => $row['runtimeMinutes'],
            'genres' => $row['genres']
        ];
    }
);
$titles = $titleLoader->getData();

$ratingLoader = new TitleRatingsLoader(__DIR__ . '/../data/title.ratings.tsv.gz');
$ratings = $ratingLoader->getData();

$counts = $rates = $votes = [];

foreach ($ratings as $titleId => $rating) {
    $type = $titles[$titleId]['titleType'];
    $counts[$type] = isset($counts[$type]) ? $counts[$type] + 1 : 1;
    $rate = $rating['averageRating'];
    $rates[$type] = isset($rates[$type]) ? $rates[$type] + $rate : $rate;
    $num = $rating['numVotes'];
    $votes[$type] = isset($votes[$type]) ? $votes[$type] + $num : $num;
}

arsort($counts);

echo <<<TXT
### Type Counts and Ratings

| Title Type | Count | Average Rating | Average Number of Votes |
|------------|-------|----------------|-------------------------|

TXT;

foreach ($counts as $type => $count) {
    $avgRate = round($rates[$type] / $count, 2);
    $avgNumVotes = round($votes[$type] / $count);

    echo "| $type | $count | $avgRate | $avgNumVotes |\n";
}
echo "\n";

// Summary table for runtimeMinutes
$runtimeCounts = [];

foreach ($titles as $title) {
    if ($title['titleType'] != 'movie') {
        continue;
    }
    $runtime = intval($title['runtimeMinutes']);
    if ($runtime > 0) {
        $roundedRuntime = round($runtime, -1);
        if ($roundedRuntime >= 300) {
            $roundedRuntime = '300+';
        }

        $runtimeCounts[$roundedRuntime] = isset($runtimeCounts[$roundedRuntime]) ? $runtimeCounts[$roundedRuntime] + 1 : 1;
    }
}

ksort($runtimeCounts);

echo "### Movie Runtimes\n\n";
echo "| Runtime (minutes) | Count |\n|-------------------|-------|\n";

foreach ($runtimeCounts as $runtime => $count) {
    echo "| $runtime | $count |\n";
}
echo "\n";

// Summary table for genres
$genreCounts = [];

foreach ($titles as $title) {
    if (!$title['genres']) {
        continue;
    }
    foreach ($title['genres'] as $genre) {
        $genreCounts[$genre] = isset($genreCounts[$genre]) ? $genreCounts[$genre] + 1 : 1;
    }
}

arsort($genreCounts);

echo "### Genre Counts\n\n";
echo "| Genre | Count |\n|-------|-------|\n";

foreach ($genreCounts as $genre => $count) {
    echo "| $genre | $count |\n";
}
