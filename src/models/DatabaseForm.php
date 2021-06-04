<?php
/**
 * @link      https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license   BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\models;

use ekevin\dsn\Dsn;
use EngineCore\Ec;
use EngineCore\helpers\ArrayHelper;
use Exception;
use Yii;
use yii\db\Connection;

/**
 * Class DatabaseForm
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class DatabaseForm extends BaseForm
{
    
    public $username;
    
    public $password;
    
    public $hostname = 'localhost';
    
    public $port = '3306';
    
    public $database;
    
    public $scheme;
    
    public $tablePrefix = '';
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // scheme rules
            'schemeRequire'   => ['scheme', 'required'],
            // hostname rules
            'hostnameRequire' => ['hostname', 'required'],
            'testDb'          => ['hostname', 'testDb'],
            // database rules
            'databaseRequire' => ['database', 'required'],
            // port rules
            'portRequire'     => ['port', 'required'],
            'portType'        => ['port', 'integer'],
            // username rules
            'usernameRequire' => ['username', 'required'],
            // password rules
            'password'        => ['password', 'safe'],
            // tablePrefix rules
            'tablePrefix'     => ['tablePrefix', 'safe'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'scheme'      => Yii::t('ec/modules/installation', 'scheme'),
            'hostname'    => Yii::t('ec/modules/installation', 'hostname'),
            'database'    => Yii::t('ec/modules/installation', 'database'),
            'port'        => Yii::t('ec/modules/installation', 'port'),
            'username'    => Yii::t('ec/modules/installation', 'username'),
            'password'    => Yii::t('ec/modules/installation', 'password'),
            'tablePrefix' => Yii::t('ec/modules/installation', 'tablePrefix'),
        ];
    }
    
    /**
     * 测试数据库连接
     *
     * @return array
     * ```php
     * [
     *      'status',
     *      'info',
     *      'code'
     * ]
     * ```
     */
    public function testDb(): array
    {
        // 创建测试数据库链接
        Yii::$app->set('testDb', [
            'class'    => Connection::class,
            'dsn'      => Dsn::build([
                'scheme'   => $this->scheme,
                'hostname' => $this->hostname,
                'port'     => $this->port,
                'dbname'   => $this->database,
            ])->dsn,
            'username' => $this->username,
            'password' => $this->password,
            'charset'  => 'utf8',
        ]);
        
        $arr = [
            'status' => false,
            'code'   => 0,
            'info'   => '',
        ];
        try {
            Yii::$app->get('testDb')->open();
            $arr['status'] = true;
        } catch (Exception $e) {
            // 1049: database
            // 1045: password
            // 2002: hostname
            $arr['code'] = $e->getCode();
            $arr['info'] = $e->getMessage();
        }
        
        if (false === $arr['status']) {
            switch ($arr['code']) {
                case 1049:
                    $this->addError('database', $arr['info']);
                    break;
                case 1045:
                    $this->addError('password', $arr['info']);
                    break;
                case 2002:
                    $this->addError('hostname', $arr['info']);
                    break;
                default:
                    $this->addError('hostname', $arr['info']);
                    break;
            }
        }
        
        return $arr;
    }
    
    /**
     * 加载默认值
     */
    public function loadDefaultValues()
    {
        // 数据库默认值
        $db = [
            'scheme'      => 'mysql',
            'hostname'    => 'localhost',
            'port'        => 3306,
            'database'    => 'db_name',
            'username'    => 'root',
            'password'    => '',
            'tablePrefix' => '',
        ];
        
        // 加载系统数据库配置数据
        $definitions = Yii::$app->getComponents();
        if (isset($definitions['db'])) {
            if (isset($definitions['db']['dsn'])) {
                $dsn = Dsn::parse($definitions['db']['dsn']);
                $db['scheme'] = $dsn->getScheme();
                $db['hostname'] = $dsn->getHost();
                $db['port'] = $dsn->getPort();
                $db['database'] = $dsn->getDatabase();
            }
            $db['username'] = $definitions['db']['username'];
            $db['password'] = $definitions['db']['password'];
            $db['tablePrefix'] = $definitions['db']['tablePrefix'];
        }
        
        $this->setAttributes($db);
    }
    
    /**
     * 保存
     *
     * @return bool
     */
    public function save(): bool
    {
        if ($this->validate()) {
            return $this->updateDbConfigFile();
        }
        
        return false;
    }
    
    /**
     * 更新数据库配置文件
     *
     * @return bool
     */
    private function updateDbConfigFile(): bool
    {
        $data['components']['db'] = [
            'dsn'         => Dsn::build([
                'scheme'   => $this->scheme,
                'hostname' => $this->hostname,
                'port'     => $this->port,
                'dbname'   => $this->database,
            ])->dsn,
            'username'    => $this->username,
            'password'    => $this->password,
            'tablePrefix' => $this->tablePrefix,
        ];
        $file = Yii::getAlias(Ec::$service->getExtension()->getEnvironment()->dbConfigFile);
        $config = ArrayHelper::merge(require("$file"), $data);
        
        return Ec::$service->getExtension()->getEnvironment()->flushDbFile($config);
    }
    
}