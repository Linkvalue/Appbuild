<?php

namespace Majora\OTAStore\ApplicationBundle\Entity;

/**
 * BuildToken.
 */
class BuildToken
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Build
     */
    private $build;

    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $expiredAt;

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
     * @return Build
     */
    public function getBuild()
    {
        return $this->build;
    }

    /**
     * @param Build $build
     *
     * @return $this
     */
    public function setBuild(Build $build)
    {
        $this->build = $build;

        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return $this
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    /**
     * @param \DateTime $expiredAt
     *
     * @return $this
     */
    public function setExpiredAt(\DateTime $expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }
}
