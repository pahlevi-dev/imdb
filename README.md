# imdb
Process [IMDb non-commercial datasets](https://developer.imdb.com/non-commercial-datasets/).

## Scripts
* `bin/fetch.sh` - Fetch data files and put in `data/` directory.
* `bin/filter.php` - Filter the data for recent highly-rated movies.

## Statistics

Here is a summary of data from the files downloaded on March 16, 2024. It shows
that:
* There are a variety of title types.
* Movies get more votes but lower ratings than TV series.
* There are 3.3 times as many movies as TV series.
* There are 7.5 episodes in the average TV series.

| Title Type | Count | Average Rating | Average Number of Votes |
|------------|-------|----------------|-------------------------|
| tvEpisode | 704337 | 7.39 | 202 |
| movie | 306297 | 6.17 | 3635 |
| short | 156668 | 6.83 | 75 |
| tvSeries | 94056 | 6.86 | 1528 |
| video | 52702 | 6.58 | 200 |
| tvMovie | 52484 | 6.6 | 254 |
| tvMiniSeries | 16824 | 7.12 | 1225 |
| videoGame | 16043 | 6.79 | 362 |
| tvSpecial | 12183 | 6.73 | 227 |
| tvShort | 2285 | 6.81 | 161 |

