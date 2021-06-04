<?php
/**
 * @link      https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\models;

use EngineCore\Ec;
use EngineCore\extension\setting\SettingProviderInterface;
use Yii;

/**
 * Class SetSiteForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SetSiteForm extends BaseForm
{
    
    /**
     * @var string 网站名称
     */
    public $title;
    
    /**
     * @var string 网站描述
     */
    public $description;
    
    /**
     * @var string 网站关键词
     */
    public $keyword;
    
    /**
     * @var string 网站备案号
     */
    public $icp;
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // title rules
            'titleLength'       => ['title', 'string', 'max' => 10],
            // description rules
            'descriptionLength' => ['description', 'string', 'max' => 128],
            // keyword rules
            'keywordLength'     => ['keyword', 'string', 'max' => 128],
            // ICP rules
            'icpLength'         => ['icp', 'string', 'max' => 30],
        ];
    }
    
    /**
     * 加载默认值
     */
    public function loadDefaultValues()
    {
        $this->setAttributes([
            'title'       => Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::SITE_TITLE),
            'description' => Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::SITE_DESCRIPTION),
            'keyword'     => Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::SITE_KEYWORD),
            'icp'         => Ec::$service->getSystem()->getSetting()->get(SettingProviderInterface::SITE_ICP),
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'title'       => Yii::t('ec/modules/installation', 'title'),
            'description' => Yii::t('ec/modules/installation', 'description'),
            'keyword'     => Yii::t('ec/modules/installation', 'keyword'),
            'icp'         => Yii::t('ec/modules/installation', 'icp'),
        ];
    }
    
    public function attributeHints()
    {
        return [
            'icp' => Yii::t('ec/modules/installation', 'icp example'),
        ];
    }
    
    /**
     * 保存
     *
     * @return bool
     */
    public function save(): bool
    {
        if ($this->validate()) {
            $config = Ec::$service->getSystem()->getSetting()->getAll();
            $map = [
                'title'       => SettingProviderInterface::SITE_TITLE,
                'description' => SettingProviderInterface::SITE_DESCRIPTION,
                'keyword'     => SettingProviderInterface::SITE_KEYWORD,
                'icp'         => SettingProviderInterface::SITE_ICP,
            ];
            foreach ($this->getAttributes() as $attribute => $value) {
                $config[$map[$attribute]]['value'] = $value;
            }
            
            return Ec::$service->getExtension()->getEnvironment()->flushUserSettingFile($config);
        }
        
        return false;
    }
    
}