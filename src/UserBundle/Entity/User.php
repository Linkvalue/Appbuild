<?php

namespace Majora\OTAStore\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User.
 */
class User implements AdvancedUserInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $password;

    /**
     * @var array
     */
    private $roles;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var string
     */
    private $firstname;

    /**
     * @var string
     */
    private $lastname;

    /**
     * @var ArrayCollection
     */
    private $applications;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->roles = ['ROLE_USER'];
        $this->enabled = true;
        $this->applications = new ArrayCollection();
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
     * Set email.
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get salt.
     *
     * @return string|null
     */
    public function getSalt()
    {
        return;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles)
    {
        foreach ($roles as $role) {
            if (!in_array($role, $this->getAvailableRoles())) {
                throw new \InvalidArgumentException(sprintf('Given role [%s] is not supported.'));
            }
        }

        $this->roles = $roles;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Get top level role.
     *
     * @return string
     */
    public function getRole()
    {
        foreach ($this->getAvailableRoles() as $role) {
            if (in_array($role, $this->roles)) {
                return $role;
            }
        }

        return '';
    }

    /**
     * List of available roles (from strongest to weakest).
     *
     * @return array
     */
    public function getAvailableRoles()
    {
        return ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'];
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Set enabled.
     *
     * @param bool $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = !empty($enabled);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return !empty($this->enabled);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        //Nothing to delete the password is encrypted
    }

    /**
     * Set firstname.
     *
     * @param string $firstname
     *
     * @return User
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname.
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname.
     *
     * @param string $lastname
     *
     * @return User
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname.
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * @return ArrayCollection
     */
    public function getApplications()
    {
        return $this->applications;
    }

    /**
     * @param ArrayCollection $applications
     *
     * @return self
     */
    public function setApplications(ArrayCollection $applications)
    {
        $this->applications = $applications;

        return $this;
    }
}
