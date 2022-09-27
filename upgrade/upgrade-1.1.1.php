<?php
// File: /upgrade/upgrade-1.1.1.php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_1($module)
{
    return Db::getInstance()->execute(
        "UPDATE eo_tab_lang SET
        id_tab = '308',
        id_lang = '1',
        name = 'Время сборки и обрешетка'
        WHERE id_tab = '308' AND id_lang = '1';
        ALTER TABLE eo_category
            ADD COLUMN IF NOT EXISTS crate tinyint(1) NOT NULL DEFAULT 0;
        ALTER TABLE eo_product
            ADD COLUMN IF NOT EXISTS crate tinyint(1) NOT NULL DEFAULT 0;"
    );
}
