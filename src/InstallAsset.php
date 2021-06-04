<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use yii\web\AssetBundle;

/**
 * Class InstallAsset
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
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