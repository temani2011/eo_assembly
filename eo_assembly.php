<?php

use PrestaShop\PrestaShop\Adapter\SymfonyContainer;

class eo_assembly extends Module
{
    public $topTabs;

    public function __construct()
    {
        $this->name = 'eo_assembly';
        $this->version = '1.1.1';
        $this->displayName = 'Модуль управления времинем сборки';
        $this->author = 'Express Office';
        $this->bootstrap = true;

        $this->tabs = [
            [
                'class_name' => 'AdminAssembly',
                'visible' => true,
                'name' => 'Время сборки',
                'parent_class_name' => 'AdminAdvancedParameters',
            ],
        ];

        $container = SymfonyContainer::getInstance();
        $router = SymfonyContainer::getInstance()->get('router');

        if ($container) {
            $this->tabRepository = SymfonyContainer::getInstance()->get('prestashop.core.admin.tab.repository');
        }

        $this->topTabs = [
            [
                'id'     => '1',
                'name'   => 'Категории',
                'link'   => $router->generate('admin_assembly_list'),
            ],
            [
                'id'     => '2',
                'name'   => 'Товары',
                'link'   => $router->generate('admin_assembly_products_list'),
            ],
        ];

        parent::__construct();
    }

    /**
     * install
     *
     * @return bool
     */
    public function install(): bool
    {
        if (!parent::install() || !$this->installTab()) {
            return false;
        }

        return true;
    }

    /**
     * uninstall
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        if (!parent::uninstall() || !$this->uninstallTab()) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminAssembly')
        );
    }

    public function installTab(): bool
    {
        if ($this->tabRepository->findOneIdByClassName('AdminAssembly')) {
            return true;
        }

        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminAssembly';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Время сборки';
        }
        $tab->id_parent = (int) $this->tabRepository->findOneIdByClassName('AdminParentShipping');
        $tab->module = $this->name;

        return $tab->add();
    }

    private function uninstallTab(): bool
    {
        $tabId = (int) $this->tabRepository->findOneIdByClassName('AdminAssembly');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }

    public function getTopTabs($name): array
    {
        foreach ($this->topTabs as $key => $tab) {
            $this->topTabs[$key]['active'] = $tab['name'] === $name ? true : false;
        }

        return $this->topTabs;
    }
}
