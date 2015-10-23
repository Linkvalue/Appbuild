<?php

namespace AppBuild\Bundle\ApplicationBundle\Form\DataTransformer;

use AppBuild\Bundle\AppcliationBundle\Entity\Application;
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
     * Nothing to transform but needed to implement interface.
     *
     * @param Application $application
     */
    public function transform($application)
    {
        return;
    }

    /**
     * Upload file in built application dir with the code application on filename.
     *
     * @param UploadedFile $builtFile
     *
     * @return string
     */
    public function reverseTransform($builtFile)
    {
        if (!$builtFile || !$builtFile instanceof UploadedFile) {
            return $this->currentFilePath;
        }
        $filename = preg_replace("/[^a-z0-9\-\.]/i", '_',
            sprintf('%s%s',
                sha1(uniqid(mt_rand(), true)),
                $builtFile->getClientOriginalName()
            )
        );
        $file = $builtFile->move(
            $this->buildApplicationDir,
            $filename
        );

        return $file->getRealPath();
    }
}
