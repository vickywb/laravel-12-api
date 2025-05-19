<?php

namespace App\Repository;

use App\Models\File;

class FileRepository
{
    private $file;

    public function __construct(File $file) {
        $this->file = $file;
    }

    public function store(File $file)
    {
        $file->save();

        return $file;
    }
}