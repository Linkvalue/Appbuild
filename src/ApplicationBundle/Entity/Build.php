<?php

namespace LinkValue\Appbuild\ApplicationBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use LinkValue\Appbuild\Entity\DatedTrait;
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
     * @param ExecutionContextInterface $context
     */
    public function validateFile(ExecutionContextInterface $context)
    {
        // If the file is disabled, it can have an empty filePath
        if (!$this->enabled && !$this->filePath) {
            return;
        }

        // File must exists
        if (!file_exists($this->filePath)) {
            $context->buildViolation('build.form.must_upload_file')
                ->atPath('filename')
                ->addViolation();
        }

        // File must be of a specific type, depending on application support
        $fileExtension = pathinfo($this->filePath, PATHINFO_EXTENSION);
        switch ($this->getApplication()->getSupport()) {
            case Application::SUPPORT_IOS:
                if ($fileExtension !== 'ipa') {
                    $context->buildViolation('build.form.file_must_be_ipa')
                        ->atPath('filename')
                        ->addViolation();
                }
                break;
            case Application::SUPPORT_ANDROID:
                if ($fileExtension !== 'apk') {
                    $context->buildViolation('build.form.file_must_be_apk')
                        ->atPath('filename')
                        ->addViolation();
                }
                break;
            default:
                throw new \RuntimeException('Unsupported application type');
                break;
        }
    }
}
