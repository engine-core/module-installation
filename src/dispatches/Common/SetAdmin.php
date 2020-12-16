<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

namespace EngineCore\modules\installation\dispatches\Common;

use EngineCore\modules\installation\dispatches\Dispatch;
use EngineCore\modules\installation\models\AdminForm;
use Yii;

/**
 * Class SetAdmin
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class SetAdmin extends Dispatch
{
    
    public function run()
    {
        $model = new AdminForm();
        $model->loadDefaultValues();
    
        if ($model->load(Yii::$app->request->post())) {
            if (!$model->save()) {
                $this->response->error();
            }
        
            $error = $this->installDb();
            if ($error != null) {
                return $this->renderJsonMessage(false, $error);
            }
            $this->installConfig();
            // 创建用户
            $error = $this->createAdminUser();
            if ($error != null) {
                return $this->renderJsonMessage(false, $error);
            }
        
            \Yii::$app->getCache()->flush();
            //安装完成
            Module::getInstance()->setInstalled();
        
            return $this->renderJsonMessage(true);
        
        }
    
        return $this->response->setAssign([
            'model' => $model,
        ])->render();
    }
    
    /**
     * 安装数据库
     */
    public function installDb()
    {
        $class = "m151209_185057_migration";
        require Yii::getAlias("@hass/install/migrations/" . $class . ".php");
        
        $migration = new $class();
        
        $error = "";
        // yii2 迁移是在命令行下操作的。。会输出很多垃圾信息
        ob_start();
        try {
            if ($migration->up() == false) {
                $error = "数据库迁移失败";
            }
        } catch (\Exception $e) {
            $error = "数据表已经存在，或者其他错误！";
        }
        ob_end_clean();
        
        if (!empty($error)) {
            return $error;
        }
        
        return null;
    }
    
    //写入配置文件
    public function installConfig()
    {
        Module::getInstance()->setCookieValidationKey();
        $data = \Yii::$app->getCache()->get(SiteForm::CACHE_KEY);
        foreach ($data as $name => $value) {
            $config = new Config();
            $config->name = preg_replace_callback('/([a-z]*)([A-Z].*)/', function ($matches) {
                return $matches[1] . "." . strtolower($matches[2]);
            }, $name);
            $config->value = $value;
            $config->save();
        }
        
        return true;
    }
    
    public function createAdminUser()
    {
        $data = \Yii::$app->getCache()->get(AdminForm::CACHE_KEY);
        $user = new User();
        $user->setScenario("create");
        $user->email = $data["email"];
        $user->username = $data["username"];
        $user->password = $data["password"];
        
        if ($user->create() == false) {
            return $user->formatErrors();
        }
        //添加管理员权限
        $connection = \Yii::$app->getDb();
        $connection->createCommand()
                   ->insert('{{%auth_assignment}}', [
                       'item_name'  => 'admin',
                       'user_id'    => $user->id,
                       "created_at" => time(),
                   ])
                   ->execute();
        
        return null;
    }
    
}