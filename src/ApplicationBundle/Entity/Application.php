<?php

namespace Majora\OTAStore\ApplicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Majora\OTAStore\UserBundle\Entity\User;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Application.
 */
class Application
{
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
    private $slug;

    /**
     * @var string
     */
    private $support;

    /**
     * @var string
     */
    private $packageName;

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
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->enabled = true;
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
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get available/supported application supports.
     *
     * @return array
     */
    public static function getAvailableSupports()
    {
        return array(
            self::SUPPORT_IOS,
            self::SUPPORT_ANDROID,
        );
    }

    /**
     * @return ArrayCollection|Build[]
     */
    public function getEnabledBuilds()
    {
        return $this->builds
            ->filter(function (Build $build) {
                return $build->isEnabled();
            });
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
     * Assert this Application is valid (depending on its support).
     *
     * This method is intended to be used in validation constraints.
     *
     * @param ExecutionContextInterface $context
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (empty($this->packageName)) {
            switch ($this->getSupport()) {
                case self::SUPPORT_IOS:
                    $context->buildViolation('application.form.must_set_package_name')
                        ->atPath('packageName')
                        ->addViolation()
                    ;
                    break;
                default:
                    break;
            }
        }
    }
}
