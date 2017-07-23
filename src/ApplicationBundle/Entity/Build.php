<?php

namespace Majora\OTAStore\ApplicationBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Majora\OTAStore\Entity\DatedTrait;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Build.
 */
class Build
{
    use DatedTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var Application
     */
    private $application;

    /**
     * @var ArrayCollection|BuildToken[]
     */
    private $buildTokens;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->buildTokens = new ArrayCollection();
        $this->enabled = true;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return sprintf('%s [%s]',
            $this->getApplication()->getLabel(),
            $this->getVersion()
        );
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return sprintf('%s-%s',
            $this->getApplication()->getSlug(),
            (new Slugify())->slugify($this->getVersion())
        );
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @param Application $application
     *
     * @return $this
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;

        return $this;
    }

    /**
     * @return ArrayCollection|BuildToken[]
     */
    public function getBuildTokens()
    {
        return $this->buildTokens;
    }

    /**
     * @param BuildToken $buildToken
     *
     * @return $this
     */
    public function addBuildToken(BuildToken $buildToken)
    {
        $this->buildTokens->add($buildToken);

        return $this;
    }

    /**
     * @param ArrayCollection $buildTokens
     *
     * @return $this
     */
    public function setBuildTokens(ArrayCollection $buildTokens)
    {
        $this->buildTokens = $buildTokens;

        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     *
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return !empty($this->enabled);
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled)
    {
        $this->enabled = !empty($enabled);

        return $this;
    }

    /**
     * Returns the build file name with its extension.
     *
     * @return string
     */
    public function getFileNameWithExtension()
    {
        return basename($this->filePath);
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Validation method for build filePath property.
     *
     * @param ExecutionContextInterface $context
     */
    public function validateFilePath(ExecutionContextInterface $context)
    {
        // If the file is disabled, it can have an empty filePath
        if (!$this->enabled && !$this->filePath) {
            return;
        }

        if (!file_exists($this->filePath)) {
            $context->buildViolation('build.form.must_upload_file')
                ->atPath('filename')
                ->addViolation();
        }
    }
}
