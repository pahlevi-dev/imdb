<?php

namespace Imdb;

use Exception;

class Loader
{
    protected $file;
    protected $data = [];

    public function __construct(
        string $filename,
        callable $filterCallback = null
    ) {
        if (!file_exists($filename)) {
            throw new Exception("File does not exist: $filename");
        }

        $this->file = gzopen($filename, 'r');
        if ($this->file === false) {
            throw new Exception("Unable to open file: $filename");
        }
    }

    public function __destruct()
    {
        gzclose($this->file);
    }

    public function getData(): array
    {
        return $this->data;
    }
}
