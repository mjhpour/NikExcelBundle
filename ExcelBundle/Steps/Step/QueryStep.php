<?php

namespace Nik\ExcelBundle\Steps\Step;

use Doctrine\ORM\QueryBuilder;
use Nik\ExcelBundle\Filter\QueryFilterInterface;

/**
 * QueryStep class provide a way to easy register AND based query for export excel.
 *
 * Class QueryStep
 * @package Nik\ExcelBundle\Steps\Step
 */
class QueryStep extends FilterStep
{
    const IDENTIFIER = 'i';

    const IN = 1;
    const EQ = 2;
    const GT = 3;
    const GTE = 4;
    const LT = 5;
    const LTE = 6;
    const LIKE = 7;

    /**
     * @param QueryFilterInterface $filter
     * @param integer  $priority
     *
     * @return $this
     */
    public function add(QueryFilterInterface $filter, $priority = null)
    {
        $this->filters->insert($filter, $priority);
        return $this;
    }

    /**
     * Calculate all registered query and return result.
     *
     * {@inheritdoc}
     */
    public function process($className)
    {
        $queryBuilder = $this->em->createQueryBuilder();
        $queryBuilder->select(self::IDENTIFIER)->from($className, self::IDENTIFIER);

        /** @var QueryFilterInterface $nativeQuery */
        foreach (clone $this->filters as $index => $nativeQuery) {
            if ($nativeQuery->getIsEnabled()) {
                $queryBuilder->andWhere(
                    $this->getExpr(
                        $nativeQuery->getExpr(),
                        $queryBuilder,
                        $nativeQuery->getFieldName(),
                        $index
                    )
                );
                $queryBuilder->setParameter(':'.$nativeQuery->getFieldName().$index, $nativeQuery->getValues());
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get expected expression.
     *
     * @param $identifier
     * @param QueryBuilder $builder
     * @param $x
     * @param $index
     * @return \Doctrine\ORM\Query\Expr\Comparison|\Doctrine\ORM\Query\Expr\Func
     */
    protected function getExpr($identifier, QueryBuilder $builder, $x, $index)
    {
        switch ($identifier) {
            case self::IN:
                return $builder->expr()->in(self::IDENTIFIER.'.'.$x, ':'.$x.$index);
            case self::GTE:
                return $builder->expr()->gte(self::IDENTIFIER.'.'.$x, ':'.$x.$index);
            case self::LTE:
                return $builder->expr()->lte(self::IDENTIFIER.'.'.$x, ':'.$x.$index);
            case self::LIKE:
                return $builder->expr()->like(self::IDENTIFIER.'.'.$x, ':'.$x.$index);
            default:
                return $builder->expr()->in(self::IDENTIFIER.'.'.$x, ':'.$x.$index);
        }
    }
}