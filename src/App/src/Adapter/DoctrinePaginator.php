<?php

declare(strict_types=1);

namespace App\Adapter;

use ArrayIterator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Laminas\Paginator\Adapter\AdapterInterface;

class DoctrinePaginator implements AdapterInterface
{
    protected Paginator $paginator;

    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    public function setPaginator(Paginator $paginator): self
    {
        $this->paginator = $paginator;
        return $this;
    }

    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getItems($offset, $itemCountPerPage): ArrayIterator|iterable
    {
        $this->paginator
            ->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($itemCountPerPage);

        return $this->paginator->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->paginator->count();
    }
}
