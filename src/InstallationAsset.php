<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation;

use yii\web\AssetBundle;

/**
 * Class InstallationAsset
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class InstallationAsset extends AssetBundle
{

    public $sourcePath = '@EngineCore/modules/installation/assets';

    public $js = [
        'js/install.js',
    ];

    public $depends = [
        'EngineCore\themes\Basic\assetBundle\SiteAsset',
    ];

}