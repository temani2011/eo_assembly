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

namespace EO\Assembly\Form;

use EO\Assembly\Repository\AssemblyRepository;
use PrestaShop\PrestaShop\Core\Addon\Module\ModuleRepository;
use PrestaShop\PrestaShop\Core\Form\FormDataProviderInterface;
use Product;

/**
 * Class AssemblyProductsFormDataProvider
 */
class AssemblyProductsFormDataProvider implements FormDataProviderInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var AssemblyRepository
     */
    private $repository;

    /**
     * @var ModuleRepository
     */
    private $moduleRepository;

    /**
     * AssemblyFormDataProvider constructor.
     *
     * @param AssemblyRepository $repository
     * @param ModuleRepository $moduleRepository
     */
    public function __construct(
        AssemblyRepository $repository,
        ModuleRepository $moduleRepository
    ) {
        $this->repository = $repository;
        $this->moduleRepository = $moduleRepository;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function getData()
    {
        if (null === $this->id) {
            return [];
        }

        $obj = new Product($this->id);

        return [
            'assembly' => [
                'id'                       => $obj->id,
                'name'                     => $obj->name[1] . ' ' . Product::getCurrentAttributeName($obj),
                'assembly_time'            => $obj->assembly_time,
                'assembly_time_custom'     => $obj->assembly_time_custom,
                'calculated_assembly_time' => $obj->getCalculatedAssemblyTime(),
            ]
        ];
    }

    /**
     * @param array $data
     *
     * @return array
     *
     * @throws \PrestaShop\PrestaShop\Adapter\Entity\PrestaShopDatabaseException
     */
    public function setData(array $data): array
    {
        $assemblyData = $data['assembly'];
        $errors = $this->validate($assemblyData);

        if (!empty($errors)) {
            return $errors;
        }

        if (!empty($assemblyData['id'])) {
            $id = $assemblyData['id'];
            $this->repository->updateProduct($id, $assemblyData);
        }

        return [];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     *
     * @return self
     */
    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function validate(array $data): array
    {
        $errors = [];

        // if (!isset($data['name'])) {
        //     $errors[] = [
        //         'key' => 'Missing name',
        //         'domain' => 'Admin.Catalog.Notification',
        //         'parameters' => [],
        //     ];
        // }

        return $errors;
    }
}
