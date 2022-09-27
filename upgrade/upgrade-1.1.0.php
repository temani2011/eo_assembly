<?php
// File: /upgrade/upgrade-1.1.0.php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    return Db::getInstance()->execute(
        'CREATE TABLE `eo_features_category_assembly` (
            `id_category` int(11) NOT NULL,
            `id_feature_value` int(11) NOT NULL,
            `assembly_time` int(11) NOT NULL,
            PRIMARY KEY  (`id_category`, `id_feature_value`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
    );
}
