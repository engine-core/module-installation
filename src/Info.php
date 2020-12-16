<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation;

use EngineCore\extension\repository\info\ModularityInfo;

class Info extends ModularityInfo
{
    
    protected $id = 'installation';
    
    /**
     * {@inheritdoc}
     */
    public function getConfig(): array
    {
        return [
            'modules' => [
                'gridview' => [
                    'class' => '\kartik\grid\Module'
                    // enter optional module parameters below - only if you need to
                    // use your own export download action or custom translation
                    // message source
                    // 'downloadAction' => 'gridview/export/download',
                    // 'i18n' => []
                ],
            ],
        ];
    }
    
}