<?php

namespace AppBundle\Service;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class FileHelper 
{
    /**
     * Unlink file
     * @param  [string] $absolutePath 
     * @return [type]               
     */
    public function unlinkFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
            return true;
        }
        throw new FileNotFoundException('File not found: '.$filePath);
    }
}