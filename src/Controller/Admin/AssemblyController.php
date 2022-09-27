<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

declare(strict_types=1);

namespace EO\Assembly\Controller\Admin;

use Context;
use Module;
use EO\Assembly\Grid\Definition\Factory\AssemblyGridDefinitionFactory;
use EO\Assembly\Grid\Filters\AssemblyFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Grid\ResponseBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssemblyController extends FrameworkBundleAdminController
{
    public $tabName = 'Категории';

    /**
     * List assembly
     *
     * @param AssemblyFilters $filters
     *
     * @return Response
     */
    public function indexAction(AssemblyFilters $filters): Response
    {
        $gridFactory = $this->get('assembly.grid.factory.assembly');
        $grid = $gridFactory->getGrid($filters);

        return $this->render('@Modules/eo_assembly/views/templates/admin/assembly/index.html.twig', [
            'enableSidebar' => true,
            'layoutTitle' => 'Время сборки',
            'headerTabContent' => $this->getHeaderTabsContent(),
            'grid' => $this->presentGrid($grid),
        ]);
    }

    /**
     * Provides filters functionality.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function searchAction(Request $request): RedirectResponse
    {
        /** @var ResponseBuilder $responseBuilder */
        $responseBuilder = $this->get('prestashop.bundle.grid.response_builder');

        return $responseBuilder->buildSearchResponse(
            $this->get('assembly.grid.definition.factory.assembly'),
            $request,
            assemblyGridDefinitionFactory::GRID_ID,
            'admin_assembly_list'
        );
    }

    /**
     * Show edit form
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function editAction(Request $request, int $id): Response
    {
        $this->get('assembly.form.data_provider')->setId($id);
        $form = $this->get('assembly.form_handler')->getForm();

        return $this->render('@Modules/eo_assembly/views/templates/admin/assembly/form.html.twig', [
            'form' => $form->createView(),
            'enableSidebar' => true,
            'closeLink' => $this->generateUrl('admin_assembly_list'),
            'formAction' => $this->generateUrl('admin_assembly_category_edit', ['id' => $id]),
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    /**
     * Process edit action
     *
     * @param Request $request
     * @param int $id
     *
     * @return RedirectResponse|Response
     *
     * @throws \Exception
     */
    public function editProcessAction(Request $request, int $id)
    {
        /** @var AssemblyFormDataProvider $formProvider */
        $formProvider = $this->get('assembly.form.data_provider');
        $formProvider->setId($id);

        /** @var FormHandlerInterface $formHandler */
        $formHandler = $this->get('assembly.form_handler');
        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $saveErrors = $formHandler->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
                return $this->redirectToRoute('admin_assembly_list');
            }

            $this->flashErrors($saveErrors);
        }

        return $this->render('@Modules/eo_assembly/views/templates/admin/assembly/form.html.twig', [
            'form' => $form->createView(),
            'enableSidebar' => true,
            'help_link' => $this->generateSidebarLink($request->attributes->get('_legacy_controller')),
        ]);
    }

    /**
     * Toggles category crate status
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function toggleCrateAction($id)
    {
        try {
            /** @var AssemblyRepository $repository */
            $repository = $this->get('assembly.repository');

            $category = $repository->getCategory((int) $id);
            $category->crate = !$category->crate;
            $category->save();
            $category->updateCrateForCategoryProducts();

            $this->addFlash(
                'success',
                $this->trans('The status has been successfully updated.', 'Admin.Notifications.Success')
            );
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_assembly_list');
    }

    /**
     * Gets the header tabs content.
     *
     * @return string
     */
    private function getHeaderTabsContent(): string
    {
        $smarty = Context::getContext()->smarty;

        $smarty->assign([
            'tabs' => Module::getInstanceByName('eo_assembly')->getTopTabs($this->tabName),
        ]);

        return $smarty->fetch('module:eo_assembly/views/templates/admin/header_tabs.tpl');
    }
}
