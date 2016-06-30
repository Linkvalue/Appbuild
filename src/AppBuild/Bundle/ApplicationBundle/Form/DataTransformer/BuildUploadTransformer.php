<?php

namespace AppBuild\Bundle\ApplicationBundle\Form\DataTransformer;

use AppBuild\Bundle\ApplicationBundle\Entity\Build;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class BuildUploadTransformer.
 *
 * Handle build upload.
 */
class BuildUploadTransformer implements DataTransformerInterface
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
     * @param Build $build
     */
    public function transform($build)
    {
        return;
    }

    /**
     * Upload file in build application directory.
     *
     * @param UploadedFile $builtFile
     *
     * @return string
     */
    public function reverseTransform($builtFile)
    {
        if (!is_object($builtFile) || !$builtFile instanceof UploadedFile) {
            return $this->currentFilePath;
        }
        $filename = preg_replace('/[^a-z0-9\-\.]/i', '_',
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
