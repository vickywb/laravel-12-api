<?php

namespace App\Repository;

use App\Models\File;

class FileRepository
{
    private $file;

    public function __construct(File $file) {
        $this->file = $file;
    }

    public function store($data)
    {
        $file = $this->file->create($data);

        return $file;
    }
}