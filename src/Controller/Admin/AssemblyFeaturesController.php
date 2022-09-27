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
use EO\Assembly\Grid\Definition\Factory\AssemblyFeaturesGridDefinitionFactory;
use EO\Assembly\Grid\Filters\AssemblyFeaturesFilters;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use PrestaShopBundle\Service\Grid\ResponseBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssemblyFeaturesController extends FrameworkBundleAdminController
{

    public $tabName = 'Характеристики';

    /**
     * List assembly features
     *
     * @param AssemblyFeaturesFilters $filters
     *
     * @return Response
     */
    public function indexAction(AssemblyFeaturesFilters $filters): Response
    {
        $gridFactory = $this->get('assembly.grid.factory.assembly_features');
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
            $this->get('assembly.grid.definition.factory.assembly_features'),
            $request,
            AssemblyFeaturesGridDefinitionFactory::GRID_ID,
            'admin_assembly_features_list'
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
        $this->get('assembly.form.data_provider.assembly_features')->setId($id);
        $form = $this->get('assembly.form_handler.assembly_features')->getForm();

        return $this->render('@Modules/eo_assembly/views/templates/admin/assembly/form.html.twig', [
            'form' => $form->createView(),
            'enableSidebar' => true,
            'closeLink' => $this->generateUrl('admin_assembly_features_list'),
            'formAction' => $this->generateUrl('admin_assembly_features_edit', ['id' => $id]),
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
        $formProvider = $this->get('assembly.form.data_provider.assembly_features');
        $formProvider->setId($id);

        /** @var FormHandlerInterface $formHandler */
        $formHandler = $this->get('assembly.form_handler.assembly_features');
        $form = $formHandler->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $saveErrors = $formHandler->save($data);

            if (0 === count($saveErrors)) {
                $this->addFlash('success', $this->trans('Successful update.', 'Admin.Notifications.Success'));
                return $this->redirectToRoute('admin_assembly_features_list');
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
     * Gets the header tabs content.
     *
     * @return string
     */
    private function getHeaderTabsContent(): string
    {
        $smarty = Context::getContext()->smarty;

        $smarty->assign([
            'tabs' => Module::getInstanceByName('eo_assembly')->getTopTabs($this->tabName),
            ,
        ]);

        return $smarty->fetch('module:eo_assembly/views/templates/admin/header_tabs.tpl');
    }
}
