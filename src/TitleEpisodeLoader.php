<?php

namespace Imdb;

use Exception;

class TitleEpisodeLoader extends Loader
{
    public function __construct(
        string $filename,
        callable $filterCallback = null
    ) {
        parent::__construct($filename, $filterCallback);

        while (($line = gzgets($this->file)) !== false) {
            $fields = explode("\t", trim($line, "\n"));
            $episodeId = $fields[0];
            $parentId = $fields[1];
            $seasonNumber = ($fields[2] !== '\\N') ? intval($fields[2]) : null;
            $episodeNumber = ($fields[3] !== '\\N') ? intval($fields[3]) : null;

            if (isset($this->data[$episodeId])) {
                throw new Exception("Duplicate episode ID: $episodeId");
            }

            $row = [
                'parentId' => $parentId,
                'seasonNumber' => $seasonNumber,
                'episodeNumber' => $episodeNumber
            ];

            if ($filterCallback === null || $filterCallback($row)) {
                $this->data[$episodeId] = $row;
            }
        }
    }

    public function getEpisode(string $episodeId): ?array
    {
        return $this->data[$episodeId] ?? null;
    }

    public function getEpisodesByParentId(string $parentId): ?array
    {
        $episodes = [];

        foreach ($this->data as $episodeId => $row) {
            if ($row['parentId'] === $parentId) {
                $episodes[$episodeId] = $row;
            }
        }

        return $episodes;
    }
}
