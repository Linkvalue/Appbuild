<?php

namespace AppBuild\Bundle\ApplicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

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
     * @var ArrayCollection
     */
    private $builds;

    /**
     * @var ArrayCollection
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return bool
     */
    public function isEnabled()
    {
        return !empty($this->enabled);
    }

    /**
     * @param bool $enabled
     *
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = !empty($enabled);

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getBuilds()
    {
        return $this->builds;
    }

    /**
     * @param ArrayCollection $builds
     *
     * @return self
     */
    public function setBuilds(ArrayCollection $builds)
    {
        $this->builds = $builds;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param ArrayCollection $users
     *
     * @return self
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
     * @return self
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
     * @return self
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get available/supported application supports.
     *
     * This method is static in order to be used in validation constraints.
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
}
