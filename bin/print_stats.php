#!/usr/bin/env php
<?php

/**
 * @file Print statistics about titles and ratings.
 */

require __DIR__ . '/../vendor/autoload.php';

use Imdb\TitleBasicsLoader;
use Imdb\TitleRatingsLoader;

$titleLoader = new TitleBasicsLoader(__DIR__ . '/../data/title.basics.tsv.gz');
$titles = $titleLoader->getData();

$ratingLoader = new TitleRatingsLoader(__DIR__ . '/../data/title.ratings.tsv.gz');
$ratings = $ratingLoader->getData();

$counts = $rates = $votes = [];

foreach ($ratings as $titleId => $rating) {
    if (!isset($titles[$titleId])) {
        continue;
    }

    $type = $titles[$titleId]['titleType'];
    $counts[$type] = isset($counts[$type]) ? $counts[$type] + 1 : 1;
    $rate = $rating['averageRating'];
    $rates[$type] = isset($rates[$type]) ? $rates[$type] + $rate : $rate;
    $num = $rating['numVotes'];
    $votes[$type] = isset($votes[$type]) ? $votes[$type] + $num : $num;
}

arsort($counts);

echo <<<TXT
| Title Type | Count | Average Rating | Average Number of Votes |
|------------|-------|----------------|-------------------------|

TXT;

foreach ($counts as $type => $count) {
    $avgRate = round($rates[$type] / $count, 2);
    $avgNumVotes = round($votes[$type] / $count);

    echo "| $type | $count | $avgRate | $avgNumVotes |\n";
}
