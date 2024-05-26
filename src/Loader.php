<?php

declare(strict_types=1);

namespace DouglasGreen\Imdb;

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

    public function __construct(string $filename)
    {
        if (! file_exists($filename)) {
            throw new FileNotFoundException('File does not exist: ' . $filename);
        }

        $file = gzopen($filename, 'r');
        if ($file === false) {
            throw new FileOpenException('Unable to open file: ' . $filename);
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
