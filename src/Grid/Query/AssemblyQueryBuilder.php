<?php
/**
 * 2007-2018 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace EO\Assembly\Grid\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use PrestaShop\PrestaShop\Adapter\Configuration;
use PrestaShop\PrestaShop\Core\Grid\Query\AbstractDoctrineQueryBuilder;
use PrestaShop\PrestaShop\Core\Grid\Query\DoctrineSearchCriteriaApplicatorInterface;
use PrestaShop\PrestaShop\Core\Grid\Search\SearchCriteriaInterface;

/**
 * Class AssemblyQueryBuilder.
 */
final class AssemblyQueryBuilder extends AbstractDoctrineQueryBuilder
{
    /**
     * @var DoctrineSearchCriteriaApplicatorInterface
     */
    private $searchCriteriaApplicator;

    /**
     * @var int
     */
    private $contextLanguageId;

    /**
     * @var int
     */
    private $contextShopId;

    /**
     * @var int
     */
    private $contextShopGroupId;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var string
     */
    private $currentGrid;

    private const CASE_BOTH_FIELDS_EXIST = 1;
    private const CASE_ONLY_MIN_FIELD_EXISTS = 2;
    private const CASE_ONLY_MAX_FIELD_EXISTS = 3;

    public function __construct(
        Connection $connection,
        string $dbPrefix,
        DoctrineSearchCriteriaApplicatorInterface $searchCriteriaApplicator,
        int $contextLanguageId,
        int $contextShopId,
        int $contextShopGroupId,
        Configuration $configuration
    ) {
        parent::__construct($connection, $dbPrefix);
        $this->searchCriteriaApplicator = $searchCriteriaApplicator;
        $this->contextLanguageId = $contextLanguageId;
        $this->contextShopId = $contextShopId;
        $this->contextShopGroupId = $contextShopGroupId;
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $this->currentGrid = $searchCriteria->getFilterId();
        $qb = $this->getQueryBuilder($searchCriteria->getFilters());

        switch ($this->currentGrid) {
            case 'category_grid':
                $qb
                    ->select('c.id_category as id')
                    ->addSelect('cl.name')
                    ->addSelect('c.assembly_time')
                    ->addSelect('c.crate')
                ;
                break;

            case 'product_grid':
                $qb
                    ->select('p.id_product as id')
                    ->addSelect('CONCAT_WS(\' \', pl.name, al.name) as name')
                    ->addSelect('p.assembly_time')
                    ->addSelect('p.assembly_time_custom')
                    ->addSelect('p.crate')
                    ->addSelect('cl.name as category_name')
                    ->addSelect('p.reference')
                    ->addSelect('CAST(ps.price as float) as price')
                ;
                break;

            case 'feature_grid':
                $qb
                    ->select('fv.id_feature_value as id')
                    ->addSelect('fv.id_feature')
                    ->addSelect('fl.name')
                    ->addSelect('fv.assembly_time')
                    ->addSelect('fvl.value')
                ;
                break;
        }

        $this->searchCriteriaApplicator
            ->applyPagination($searchCriteria, $qb)
            ->applySorting($searchCriteria, $qb)
        ;

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getCountQueryBuilder(SearchCriteriaInterface $searchCriteria): QueryBuilder
    {
        $qb = $this->getQueryBuilder($searchCriteria->getFilters());

        switch ($this->currentGrid) {
            case 'category_grid':
                $qb->select('COUNT(c.id_category)');
                break;

            case 'product_grid':
                $qb->select('COUNT(p.id_product)');
                break;

            case 'feature_grid':
                $qb->select('COUNT(fv.id_feature_value)')
                    ->where('custom = 0 OR custom IS NULL');

                break;
        }

        return $qb;
    }

    /**
     * Gets query builder.
     *
     * @param array $filterValues
     *
     * @return QueryBuilder
     */
    private function getQueryBuilder(array $filterValues): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->setParameter('id_shop', $this->contextShopId);
        $qb->setParameter('id_lang', $this->contextLanguageId);

        switch ($this->currentGrid) {
            case 'category_grid':
                $qb
                    ->from($this->dbPrefix . 'category', 'c')
                    ->leftJoin(
                        'c',
                        $this->dbPrefix . 'category_lang',
                        'cl',
                        'c.id_category = cl.id_category AND cl.id_shop = :id_shop'
                    );
                break;

            case 'product_grid':
                $qb
                    ->from($this->dbPrefix . 'product', 'p')
                    ->innerJoin(
                        'p',
                        $this->dbPrefix . 'product_shop',
                        'ps',
                        'p.id_product = ps.id_product AND ps.id_shop = :id_shop'
                    )
                    ->leftJoin(
                        'p',
                        $this->dbPrefix . 'product_lang',
                        'pl',
                        'p.id_product = pl.id_product AND pl.id_shop = :id_shop'
                    )
                    ->leftJoin(
                        'p',
                        $this->dbPrefix . 'category_lang',
                        'cl',
                        'p.id_category_default = cl.id_category AND cl.id_shop = :id_shop'
                    )
                    ->leftJoin(
                        'p',
                        $this->dbPrefix . 'attribute_lang',
                        'al',
                        'p.id_attribute = al.id_attribute'
                    );
                break;

            case 'feature_grid':
                $qb
                    ->from($this->dbPrefix . 'feature_value', 'fv')
                    ->leftJoin(
                        'fv',
                        $this->dbPrefix . 'feature_lang',
                        'fl',
                        'fv.id_feature = fl.id_feature AND fl.id_lang = :id_lang'
                    )
                    ->leftJoin(
                        'fv',
                        $this->dbPrefix . 'feature_value_lang',
                        'fvl',
                        'fv.id_feature_value = fvl.id_feature_value AND fvl.id_lang = :id_lang'
                    )
                    ->where('custom = 0 OR custom IS NULL');
                break;
        }

        foreach ($filterValues as $filterName => $filter) {
            if ('id' === $filterName) {
                switch ($this->currentGrid) {
                    case 'category_grid':
                        $qb->andWhere('c.id_category = :id_category');
                        $qb->setParameter('id_category', $filter);
                        break;

                    case 'product_grid':
                        $qb->andWhere('p.id_product = :id_product');
                        $qb->setParameter('id_product', $filter);
                        break;

                    case 'feature_grid':
                        $qb->andWhere('fv.id_feature_value = :id_feature_value');
                        $qb->setParameter('id_feature_value', $filter);
                        break;
                }
                continue;
            }

            if ('name' === $filterName) {
                switch ($this->currentGrid) {
                    case 'category_grid':
                        $qb->andWhere('cl.name LIKE :name');
                        $qb->setParameter('name', '%' . $filter . '%');
                        break;

                    case 'product_grid':
                        $qb->andWhere('pl.name LIKE :name');
                        $qb->setParameter('name', '%' . $filter . '%');
                        break;

                    case 'feature_grid':
                        $qb->andWhere('fl.name LIKE :name');
                        $qb->setParameter('name', '%' . $filter . '%');
                        break;
                }
                continue;
            }


            if ('assembly_time' === $filterName) {
                switch ($this->currentGrid) {
                    case 'category_grid':
                        $qb->andWhere('c.assembly_time = :assembly_time');
                        $qb->setParameter('assembly_time', $filter);
                        break;

                    case 'product_grid':
                        $qb->andWhere('p.assembly_time = :assembly_time');
                        $qb->setParameter('assembly_time', $filter);
                        break;

                    case 'feature_grid':
                        $qb->andWhere('fv.assembly_time = :assembly_time');
                        $qb->setParameter('assembly_time', $filter);
                        break;
                }
                continue;
            }

            if ('id_feature' === $filterName) {
                $qb->andWhere('fv.id_feature = :id_feature');
                $qb->setParameter('id_feature', $filter);
                continue;
            }

            if ('assembly_time_custom' === $filterName) {
                $qb->andWhere('p.assembly_time_custom = :assembly_time_custom');
                $qb->setParameter('assembly_time_custom', $filter);
                continue;
            }

            if ('crate' === $filterName) {
                switch ($this->currentGrid) {
                    case 'category_grid':
                        $qb->andWhere('c.crate = :crate');
                        $qb->setParameter('crate', $filter);
                        break;

                    case 'product_grid':
                        $qb->andWhere('p.crate = :crate');
                        $qb->setParameter('crate', $filter);
                        break;
                }
                continue;
            }

            if ('category_name' === $filterName) {
                $qb->andWhere('cl.name = :category_name');
                $qb->setParameter('category_name', $filter);
                continue;
            }

            if ('reference' === $filterName) {
                $qb->andWhere('p.reference = :reference');
                $qb->setParameter('reference', $filter);
                continue;
            }

            if ('price' === $filterName) {
                $minFieldSqlCondition = sprintf('%s >= :%s_min', 'ps.' . $filterName, $filterName);
                $maxFieldSqlCondition = sprintf('%s <= :%s_max', 'ps.' . $filterName, $filterName);

                switch ($this->computeMinMaxCase($filter)) {
                    case self::CASE_BOTH_FIELDS_EXIST:
                        $qb->andWhere(sprintf('%s AND %s', $minFieldSqlCondition, $maxFieldSqlCondition));
                        $qb->setParameter(sprintf('%s_min', $filterName), $filter['min_field']);
                        $qb->setParameter(sprintf('%s_max', $filterName), $filter['max_field']);
                        break;
                    case self::CASE_ONLY_MIN_FIELD_EXISTS:
                        $qb->andWhere($minFieldSqlCondition);
                        $qb->setParameter(sprintf('%s_min', $filterName), $filter['min_field']);
                        break;
                    case self::CASE_ONLY_MAX_FIELD_EXISTS:
                        $qb->andWhere($maxFieldSqlCondition);
                        $qb->setParameter(sprintf('%s_max', $filterName), $filter['max_field']);
                        break;
                }
                continue;
            }
        }

        return $qb;
    }

    /**
     * @param array<string, int> $value
     *
     * @return int
     */
    private function computeMinMaxCase(array $value): int
    {
        $minFieldExists = isset($value['min_field']);
        $maxFieldExists = isset($value['max_field']);

        if ($minFieldExists && $maxFieldExists) {
            return self::CASE_BOTH_FIELDS_EXIST;
        }
        if ($minFieldExists) {
            return self::CASE_ONLY_MIN_FIELD_EXISTS;
        }

        if ($maxFieldExists) {
            return self::CASE_ONLY_MAX_FIELD_EXISTS;
        }

        throw new Exception('Min max filter wasn\'t applied correctly');
    }
}
