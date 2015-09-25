<?php

namespace AppBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BuiltApplicationTransformer implements DataTransformerInterface
{
    /**
     * @var string
     */
    protected $buildApplicationDir;

    /**
     * @var string
     */
    protected $currentFilePath;

    /**
     * construct.
     *
     * @param string $buildApplicationDir
     * @param string $currentFilePath
     */
    public function __construct($buildApplicationDir, $currentFilePath)
    {
        $this->buildApplicationDir = $buildApplicationDir;
        $this->currentFilePath = $currentFilePath;
    }

    /**
     * .
     *
     * @param Application|null $application
     */
    public function transform($application)
    {
        return;
    }

    /**
     * Upload file in built application dir with the code application on filename.
     *
     * @param UploadedFile $builtFile
     */
    public function reverseTransform($builtFile)
    {
        if (!$builtFile || !$builtFile instanceof UploadedFile) {
            return $this->currentFilePath;
        }
        $clean_filename = preg_replace("/[^a-z0-9\-\.]/i", "_" , sha1(uniqid(mt_rand(), true)).$builtFile->getClientOriginalName());
        $file = $builtFile->move(
            $this->buildApplicationDir,
            $clean_filename
        );

        return $file->getRealPath();
    }
}
