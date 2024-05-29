<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

use DouglasGreen\Exceptions\FileException;

class Loader
{
    /**
     * @var resource
     */
    protected $file;

    /**
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * @throws FileException
     */
    public function __construct(string $filename)
    {
        if (! file_exists($filename)) {
            throw new FileException('File does not exist: ' . $filename);
        }

        $file = gzopen($filename, 'r');
        if ($file === false) {
            throw new FileException('Unable to open file: ' . $filename);
        }

        $this->file = $file;
    }

    public function __destruct()
    {
        gzclose($this->file);
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
