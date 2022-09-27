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

namespace EO\Assembly\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use PrestaShop\PrestaShop\Core\Exception\DatabaseException;
use Symfony\Component\Translation\TranslatorInterface;
use Validate;
use Category;
use FeatureValue;
use Product;
use Db;

/**
 * Class AssemblyRepository.
 */
class AssemblyRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * @var array
     */
    private $languages;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * AssemblyRepository constructor.
     *
     * @param Connection $connection
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Connection $connection,
        TranslatorInterface $translator
    ) {
        $this->connection = $connection;
        $this->translator = $translator;
    }

    /**
     * Gets category
     *
     * @param int $id
     *
     * @return object
     */
    public function getCategory(int $id): object
    {
        try {
            $obj = new Category($id);
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка получения категории', 0, $e);
        }

        if ($obj->id !== (int) $id) {
            throw new \PrestaShopException(
                sprintf('Категории с id "%s" не найдено.', $id)
            );
        }

        return $obj;
    }

    /**
     * Update category
     *
     * @param int   $id
     * @param array $data
     *
     * @return object
     */
    public function updateCategory(int $id, array $data): object
    {
        $obj = new Category((int) $data['id']);

        if (!Validate::isLoadedObject($obj)) {
            throw new \PrestaShopException('Категории несуществует', 0);
        }

        try {
            $obj->assembly_time = (int) $data['assembly_time'];
            $obj->setFieldsToUpdate(['assembly_time' => 1]);
            $obj->save();
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления категории', 0, $e);
        }

        $this->updateCategoryRelatedProductsAssemblyTime($obj, (int) $data['assembly_time']);
        $this->updateCategoryRelatedFeatures($obj, $data['features']);

        return $obj;
    }

    /**
     * Update assembly time for category related products
     *
     * @param Category $category
     * @param int $data
     *
     * @return void
     */
    public function updateCategoryRelatedProductsAssemblyTime(Category $category, int $assemblyTime): void
    {
        if (!Validate::isLoadedObject($category)) {
            throw new \PrestaShopException('Категории несуществует', 0);
        }

        $products = $category->getProductsWs();

        if (!$products) {
            return;
        }

        try {
            Db::getInstance()->update(
                'product',
                ['assembly_time' => $assemblyTime],
                'id_product IN (' . implode(',', array_column($products, 'id')) . ')'
            );
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления товаров категории', 0, $e);
        }
    }

    /**
     * Update assembly time for category related features
     *
     * @param Category $category
     * @param array $data
     *
     * @return void
     */
    public function updateCategoryRelatedFeatures(Category $category, array $features): void
    {
        if (!Validate::isLoadedObject($category)) {
            throw new \PrestaShopException('Категории несуществует', 0);
        }

        if (!$features) {
            return;
        }

        try {
            Db::getInstance()->delete('features_category_assembly', 'id_category = ' . $category->id);
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка удаления текущего времени сборки характеристик в категории', 0, $e);
        }

        $featureValuesData = [];
        foreach ($features as $feature) {
            if (!isset($feature['values']) && !$feature['values']) {
                continue;
            }

            foreach ($feature['values'] as $featureValueId => $featureValue) {
                if (!$featureValue['assembly_time']) {
                    continue;
                }

                $featureValuesData[] = [
                    'id_category'      => $category->id,
                    'id_feature_value' => $featureValueId,
                    'assembly_time'    => $featureValue['assembly_time'],
                ];
            }
        }

        if (!$featureValuesData) {
            return;
        }

        try {
            Db::getInstance()->insert('features_category_assembly', $featureValuesData, false, false);
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления времени сборки характеристик в категории', 0, $e);
        }
    }

    /**
     * Update feature
     *
     * @param int   $id
     * @param array $data
     *
     * @return object
     */
    public function updateFeature(int $id, array $data): object
    {
        $obj = new FeatureValue((int) $data['id']);

        if (!Validate::isLoadedObject($obj)) {
            throw new \PrestaShopException('Характеристики несуществует', 0);
        }

        try {
            $obj->assembly_time = (int) $data['assembly_time'];
            $obj->setFieldsToUpdate(['assembly_time' => 1]);
            $obj->save();
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления характеристики', 0, $e);
        }

        return $obj;
    }

    /**
     * Gets product
     *
     * @param int $id
     *
     * @return object
     */
    public function getProduct(int $id): object
    {
        try {
            $obj = new Product($id);
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка получения товара', 0, $e);
        }

        if ($obj->id !== (int) $id) {
            throw new \PrestaShopException(
                sprintf('Товар с id "%s" не найден.', $id)
            );
        }

        return $obj;
    }

    /**
     * Update product
     *
     * @param int   $id
     * @param array $data
     *
     * @return object
     */
    public function updateProduct(int $id, array $data): object
    {
        $obj = new Product((int) $data['id']);

        if (!Validate::isLoadedObject($obj)) {
            throw new \PrestaShopException('Товара несуществует', 0);
        }

        try {
            $obj->assembly_time_custom = (int) $data['assembly_time_custom'];
            $obj->setFieldsToUpdate(['assembly_time_custom' => 1]);
            $obj->save();
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления товара', 0, $e);
        }

        return $obj;
    }

    public function setAssemblyTimeCustom($idsArray)
    {
        try {
            $timeCustom = \Tools::getValue('TimeCustom');
            if(!preg_match('{^[0-9]+$}', $timeCustom)) return false;
            DB::getInstance()->query('UPDATE eo_product p SET p.assembly_time_custom = '.$timeCustom.' WHERE p.id_product in ('.implode(',', $idsArray).')');
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления "Сборка ручное значение"', 0, $e);
        }
    }

    /**
     * Update crate status for a given products
     *
     * @param  array $array
     * @return bool
     */
    public function updateProductsCrateStatus($productIds, $enable = true): bool
    {
        if (!$productIds || !is_array($productIds)) {
            return false;
        }

        try {
            $result = Db::getInstance()->update(
                'product',
                ['crate' => $enable],
                'id_product IN (' . implode(',', $productIds) . ')'
            );
        } catch (\PrestaShopException $e) {
            throw new \PrestaShopException('Ошибка обновления товара', 0, $e);
        }

        return $result;
    }
}
