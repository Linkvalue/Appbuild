<?php

namespace LinkValue\Appbuild\ApplicationBundle\Entity;

use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use LinkValue\Appbuild\Entity\DatedTrait;
use LinkValue\Appbuild\UserBundle\Entity\User;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Application.
 */
class Application
{
    use DatedTrait;

    /**
     * Available supports.
     */
    const SUPPORT_IOS = 'ios';
    const SUPPORT_ANDROID = 'android';

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $support;

    /**
     * @var string
     */
    private $packageName;

    /**
     * @var string
     */
    private $displayImageFilePath;

    /**
     * @var string
     */
    private $fullSizeImageFilePath;

    /**
     * @var ArrayCollection|Build[]
     */
    private $builds;

    /**
     * @var ArrayCollection|User[]
     */
    private $users;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
        $this->displayImageFilePath = '';
        $this->fullSizeImageFilePath = '';
        $this->builds = new ArrayCollection();
        $this->users = new ArrayCollection();
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
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return (new Slugify())->slugify(
            sprintf(
                '%s-%s',
                $this->getLabel(),
                $this->getSupport()
            )
        );
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * @param string $support
     *
     * @return $this
     */
    public function setSupport($support)
    {
        if (!in_array($support, self::getAvailableSupports())) {
            throw new \InvalidArgumentException(sprintf(
                '[%s] is not a valid support value, supported values are [%s]',
                $support,
                implode(',', self::getAvailableSupports())
            ));
        }

        $this->support = $support;

        return $this;
    }

    /**
     * @return string
     */
    public function getPackageName()
    {
        return $this->packageName;
    }

    /**
     * @param string $packageName
     *
     * @return $this
     */
    public function setPackageName($packageName)
    {
        $this->packageName = $packageName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayImageFilePath()
    {
        return $this->displayImageFilePath;
    }

    /**
     * @param string $displayImageFilePath
     *
     * @return $this
     */
    public function setDisplayImageFilePath($displayImageFilePath)
    {
        $this->displayImageFilePath = $displayImageFilePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayImageFileName()
    {
        return basename($this->displayImageFilePath);
    }

    /**
     * @return string
     */
    public function getFullSizeImageFilePath()
    {
        return $this->fullSizeImageFilePath;
    }

    /**
     * @param string $fullSizeImageFilePath
     *
     * @return $this
     */
    public function setFullSizeImageFilePath($fullSizeImageFilePath)
    {
        $this->fullSizeImageFilePath = $fullSizeImageFilePath;

        return $this;
    }

    /**
     * @return string
     */
    public function getFullSizeImageFileName()
    {
        return basename($this->fullSizeImageFilePath);
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
     * @return ArrayCollection|Build[]
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * @param Build $build
     *
     * @return $this
     */
    public function addBuild(Build $build)
    {
        $this->builds->add($build);

        return $this;
    }

    /**
     * @param ArrayCollection $builds
     *
     * @return $this
     */
    public function setBuilds(ArrayCollection $builds)
    {
        $this->builds = $builds;

        return $this;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function addUser(User $user)
    {
        $this->users->add($user);

        return $this;
    }

    /**
     * @param ArrayCollection $users
     *
     * @return $this
     */
    public function setUsers(ArrayCollection $users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get available/supported application supports.
     *
     * @return array
     */
    public static function getAvailableSupports()
    {
        return [
            self::SUPPORT_IOS,
            self::SUPPORT_ANDROID,
        ];
    }

    /**
     * @return ArrayCollection|Build[]
     */
    public function getEnabledBuilds()
    {
        return $this->builds->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('enabled', true))
                ->orderBy(['createdAt' => Criteria::ASC])
        );
    }

    /**
     * @return Build
     */
    public function getLatestEnabledBuild()
    {
        return $this->getEnabledBuilds()->last();
    }

    /**
     * @return ArrayCollection|Build[]
     */
    public function getDisabledBuilds()
    {
        return $this->builds
            ->filter(function (Build $build) {
                return !$build->isEnabled();
            });
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validatePackageName(ExecutionContextInterface $context)
    {
        if (empty($this->packageName)) {
            switch ($this->getSupport()) {
                case self::SUPPORT_IOS:
                    $context->buildViolation('application.form.must_set_package_name')
                        ->atPath('packageName')
                        ->addViolation();
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateDisplayImage(ExecutionContextInterface $context)
    {
        if (!$this->displayImageFilePath) {
            return;
        }

        if (!file_exists($this->displayImageFilePath)) {
            $context->buildViolation('application.form.display_image_file_does_not_exist')
                ->atPath('displayImageFilename')
                ->addViolation();
        }
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function validateFullSizeImage(ExecutionContextInterface $context)
    {
        if (!$this->fullSizeImageFilePath) {
            return;
        }

        if (!file_exists($this->fullSizeImageFilePath)) {
            $context->buildViolation('application.form.full_size_image_file_does_not_exist')
                ->atPath('fullSizeImageFilename')
                ->addViolation();
        }
    }
}
