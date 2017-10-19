<?php

namespace LinkValue\Appbuild\ApplicationBundle\Service;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use LinkValue\Appbuild\ApplicationBundle\Entity\Build;
use LinkValue\Appbuild\ApplicationBundle\Entity\BuildToken;

/**
 * Handle purge build files tasks.
 */
class BuildTokenManager
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var int
     */
    private $tokenTtl;

    /**
     * @param EntityManager $entityManager
     * @param int           $tokenTtl
     */
    public function __construct(EntityManager $entityManager, $tokenTtl)
    {
        $this->entityManager = $entityManager;
        $this->tokenTtl = $tokenTtl;
    }

    /**
     * Generate a token for given $build and return it.
     *
     * @param Build $build
     *
     * @return BuildToken
     */
    public function generate(Build $build)
    {
        $this->entityManager->persist(
            $buildToken = (new BuildToken())
                ->setBuild($build)
                ->setToken(uniqid())
                ->setExpiredAt(new \DateTime(sprintf('+ %d seconds', $this->tokenTtl)))
        );
        $this->entityManager->flush();

        return $buildToken;
    }

    /**
     * Get the first not expired build token corresponding to $token and $build.
     *
     * @param Build  $build
     * @param string $token
     *
     * @return BuildToken|null
     */
    public function getFirstNotExpired(Build $build, $token)
    {
        $buildTokens = $build->getBuildTokens()->matching(
            Criteria::create()
                ->where(Criteria::expr()->eq('token', $token))
                ->andWhere(Criteria::expr()->gt('expiredAt', new \DateTime()))
                ->orderBy(['expiredAt' => Criteria::ASC])
                ->setMaxResults(1)
        );

        if (!$buildTokens) {
            return null;
        }

        return $buildTokens->first();
    }

    /**
     * Delete every expired build tokens.
     */
    public function purge()
    {
        $buildTokens = $this->entityManager->getRepository('AppbuildApplicationBundle:BuildToken')->matching(
            Criteria::create()
                ->where(Criteria::expr()->lte('expiredAt', new \DateTime()))
        );

        if (!$buildTokens) {
            return;
        }

        foreach ($buildTokens as $buildToken) {
            $this->entityManager->remove($buildToken);
        }

        $this->entityManager->flush();
    }
}
