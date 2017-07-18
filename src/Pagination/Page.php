<?php

namespace Majora\OTAStore\Pagination;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;

class Page
{
    const PAGE_MAX_SIZE = 20;
    const FIRST_PAGE_NUMBER = 1;

    /**
     * @var int
     */
    private $number;

    /**
     * @var Collection
     */
    private $elements;

    /**
     * @var bool
     */
    private $isFirst;

    /**
     * @var bool
     */
    private $isLast;

    /**
     * @param int $number
     * @param int $totalCollectionElements
     */
    public function __construct($number, $totalCollectionElements)
    {
        if ($number > ($lastPage = ceil($totalCollectionElements / self::PAGE_MAX_SIZE))) {
            $number = $lastPage;
        }

        if ($number < self::FIRST_PAGE_NUMBER) {
            $number = self::FIRST_PAGE_NUMBER;
        }

        $this->number = $number;
        $this->isFirst = ($number <= self::FIRST_PAGE_NUMBER);
        $this->isLast = ($number >= $lastPage);
        $this->elements = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return Collection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param Collection $elements
     *
     * @return $this
     */
    public function setElements(Collection $elements)
    {
        $this->elements = $elements;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFirst()
    {
        return $this->isFirst;
    }

    /**
     * @return bool
     */
    public function isLast()
    {
        return $this->isLast;
    }

    /**
     * @param Criteria $criteria
     *
     * @return Criteria
     */
    public function setupCriteria(Criteria $criteria)
    {
        return $criteria
            ->setFirstResult(($this->number - self::FIRST_PAGE_NUMBER) * self::PAGE_MAX_SIZE)
            ->setMaxResults(self::PAGE_MAX_SIZE)
        ;
    }
}
