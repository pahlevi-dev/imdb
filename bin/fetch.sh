#!/usr/bin/env bash
echo "Fetching name.basics.tsv"
# wget https://datasets.imdbws.com/name.basics.tsv.gz
gzip -d name.basics.tsv.gz

echo "Fetching title.akas.tsv"
# wget https://datasets.imdbws.com/title.akas.tsv.gz
gzip -d title.akas.tsv.gz

echo "Fetching title.basics.tsv"
# wget https://datasets.imdbws.com/title.basics.tsv.gz
gzip -d title.basics.tsv.gz

echo "Fetching title.crew.tsv"
# wget https://datasets.imdbws.com/title.crew.tsv.gz
gzip -d title.crew.tsv.gz

echo "Fetching title.episode.tsv"
# wget https://datasets.imdbws.com/title.episode.tsv.gz
gzip -d title.episode.tsv.gz

echo "Fetching title.principals.tsv"
# wget https://datasets.imdbws.com/title.principals.tsv.gz
gzip -d title.principals.tsv.gz

echo "Fetching title.ratings.tsv"
# wget https://datasets.imdbws.com/title.ratings.tsv.gz
gzip -d title.ratings.tsv.gz

mkdir -p ../data

mv *.tsv ../data
