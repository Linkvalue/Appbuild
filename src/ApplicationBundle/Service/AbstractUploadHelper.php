<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class AbstractUploadHelper
{
    /**
     * Generate and return a unique filename for an uploaded file.
     *
     * @param UploadedFile $uploadedFile
     *
     * @return string
     */
    public function generateFilename(UploadedFile $uploadedFile)
    {
        return preg_replace(
            '/[^a-z0-9\-\.]/i',
            '_',
            sprintf(
                '%s.%s',
                sha1(uniqid(mt_rand(), true)),
                $uploadedFile->getClientOriginalExtension()
            )
        );
    }

    /**
     * Put binary content into a tmp file and return it into a Symfony\Component\HttpFoundation\File\File
     * This binary file has to be moved or deleted manually.
     *
     * @param $fileContent mixed The file binary content (Can be either a string, an array or a stream resource.)
     * @param $filename string The file name
     *
     * @return File The created file in temp folder
     *
     * @throws FileException if the file could not be created
     */
    public function createTempFile($fileContent, $filename)
    {
        $filePath = sprintf(
            '%s%s%s',
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR),
            DIRECTORY_SEPARATOR,
            ltrim($filename, DIRECTORY_SEPARATOR)
        );

        if (file_put_contents($filePath, $fileContent) === false) {
            throw new FileException(sprintf('Could not create temp file "%s"', $filePath));
        }

        return new File($filePath);
    }
}
