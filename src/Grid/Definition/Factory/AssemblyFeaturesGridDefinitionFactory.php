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

namespace EO\Assembly\Grid\Definition\Factory;

use PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;
use PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn;
use PrestaShop\PrestaShop\Core\Grid\Column\Type\DataColumn;
use PrestaShop\PrestaShop\Core\Grid\Definition\Factory\AbstractGridDefinitionFactory;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Grid\Filter\FilterCollection;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use PrestaShopBundle\Form\Admin\Type\SearchAndResetType;

/**
 * Class AssemblyFeaturesGridDefinitionFactory.
 */
final class AssemblyFeaturesGridDefinitionFactory extends AbstractGridDefinitionFactory
{
    const GRID_ID = 'feature_grid';

    /**
     * {@inheritdoc}
     */
    protected function getId()
    {
        return self::GRID_ID;
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'Настройка времени сборки характеристик';
    }

    /**
     * {@inheritdoc}
     */
    protected function getColumns()
    {
        return (new ColumnCollection())
            ->add(
                (new DataColumn('id_feature'))
                    ->setName('ID характеристики')
                    ->setOptions([
                        'field' => 'id_feature',
                    ])
            )
            ->add(
                (new DataColumn('name'))
                    ->setName('Характеристика')
                    ->setOptions([
                        'field' => 'name',
                    ])
            )
            ->add(
                (new DataColumn('id'))
                    ->setName('ID значения')
                    ->setOptions([
                        'field' => 'id',
                    ])
            )
            ->add(
                (new DataColumn('value'))
                    ->setName('Значение характеристики')
                    ->setOptions([
                        'field' => 'value',
                    ])
            )
            ->add(
                (new DataColumn('assembly_time'))
                    ->setName('Сборка')
                    ->setOptions([
                        'field' => 'assembly_time',
                    ])
            )
            ->add(
                (new ActionColumn('actions'))
                    ->setName($this->trans('Actions', [], 'Admin.Global'))
                    ->setOptions([
                        'actions' => (new RowActionCollection())
                            ->add((new LinkRowAction('edit'))
                                ->setIcon('edit')
                                ->setOptions([
                                    'route' => 'admin_assembly_features_edit',
                                    'route_param_name' => 'id',
                                    'route_param_field' => 'id',
                            ]))
                        ,
                    ])
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFilters()
    {
        return (new FilterCollection())
            ->add(
                (new Filter('id', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'Поиск по ID значению',
                        ],
                    ])
                    ->setAssociatedColumn('id')
            )
            ->add(
                (new Filter('id_feature', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'Поиск по ID характеристики',
                        ],
                    ])
                    ->setAssociatedColumn('id_feature')
            )
            ->add(
                (new Filter('name', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'Поиск по названию характеристики',
                        ],
                    ])
                    ->setAssociatedColumn('name')
            )
            ->add(
                (new Filter('value', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'Поиск по значению характеристики',
                        ],
                    ])
                    ->setAssociatedColumn('value')
            )
            ->add(
                (new Filter('assembly_time', TextType::class))
                    ->setTypeOptions([
                        'required' => false,
                        'attr' => [
                            'placeholder' => 'Поиск по времени сборки',
                        ],
                    ])
                    ->setAssociatedColumn('assembly_time')
            )
            ->add(
                (new Filter('actions', SearchAndResetType::class))
                    ->setAssociatedColumn('actions')
                    ->setTypeOptions([
                        'reset_route' => 'admin_common_reset_search_by_filter_id',
                        'reset_route_params' => [
                            'filterId' => self::GRID_ID,
                        ],
                        'redirect_route' => 'admin_assembly_features_list',
                    ])
                    ->setAssociatedColumn('actions')
            )
        ;
    }
}
