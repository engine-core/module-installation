<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation;

use yii\web\AssetBundle;

class InstallAsset extends AssetBundle
{
    
    public $sourcePath = '@EngineCore/modules/installation/assets';
    
    public $js = [
        'js/install.js',
    ];
    
    public $depends = [
        'EngineCore\themes\BootstrapV3\assetBundle\SiteAsset',
    ];
    
}