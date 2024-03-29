<?php
/**
 * @link https://github.com/engine-core/module-installation
 * @copyright Copyright (c) 2021 engine-core
 * @license BSD 3-Clause License
 */

declare(strict_types=1);

namespace EngineCore\modules\installation\helpers;

use Imagick;
use Yii;
use yii\base\BaseObject;
use YiiRequirementChecker;

require_once(Yii::getAlias("@vendor/yiisoft/yii2/requirements/YiiRequirementChecker.php"));

/**
 * Class EnvironmentHelper
 *
 * @author E-Kevin <e-kevin@qq.com>
 */
class EnvironmentHelper extends BaseObject
{

    /**
     * @var YiiRequirementChecker
     */
    public $checker;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->checker = new \YiiRequirementChecker();
        $this->checker->checkYii();
        $this->checker->check($this->requirements());
    }

    protected function requirements()
    {
        $gdMemo = $imagickMemo = 'Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required for image CAPTCHA.';
        $gdOK = $imagickOK = false;

        if (extension_loaded('imagick')) {
            $imagick = new Imagick();
            $imagickFormats = $imagick->queryFormats('PNG');
            if (in_array('PNG', $imagickFormats)) {
                $imagickOK = true;
            } else {
                $imagickMemo = 'Imagick extension should be installed with PNG support in order to be used for image CAPTCHA.';
            }
        }

        if (extension_loaded('gd')) {
            $gdInfo = gd_info();
            if (!empty($gdInfo['FreeType Support'])) {
                $gdOK = true;
            } else {
                $gdMemo = 'GD extension should be installed with FreeType support in order to be used for image CAPTCHA.';
            }
        }

        return [
            // Database :
            [
                'name' => 'PDO extension',
                'mandatory' => true,
                'condition' => extension_loaded('pdo'),
                'by' => 'All DB-related classes',
            ],
            [
                'name' => 'PDO SQLite extension',
                'mandatory' => false,
                'condition' => extension_loaded('pdo_sqlite'),
                'by' => 'All DB-related classes',
                'memo' => 'Required for SQLite database.',
            ],
            [
                'name' => 'PDO MySQL extension',
                'mandatory' => false,
                'condition' => extension_loaded('pdo_mysql'),
                'by' => 'All DB-related classes',
                'memo' => 'Required for MySQL database.',
            ],
            [
                'name' => 'PDO PostgreSQL extension',
                'mandatory' => false,
                'condition' => extension_loaded('pdo_pgsql'),
                'by' => 'All DB-related classes',
                'memo' => 'Required for PostgreSQL database.',
            ],
            // Cache :
            [
                'name' => 'Memcache extension',
                'mandatory' => false,
                'condition' => extension_loaded('memcache') || extension_loaded('memcached'),
                'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-caching-memcache.html">MemCache</a>',
                'memo' => extension_loaded('memcached') ? 'To use memcached set <a href="http://www.yiiframework.com/doc-2.0/yii-caching-memcache.html#$useMemcached-detail">MemCache::useMemcached</a> to <code>true</code>.' : '',
            ],
            [
                'name' => 'APC extension',
                'mandatory' => false,
                'condition' => extension_loaded('apc'),
                'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-caching-apccache.html">ApcCache</a>',
            ],
            // CAPTCHA:
            [
                'name' => 'GD PHP extension with FreeType support',
                'mandatory' => false,
                'condition' => $gdOK,
                'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-captcha-captcha.html">Captcha</a>',
                'memo' => $gdMemo,
            ],
            [
                'name' => 'ImageMagick PHP extension with PNG support',
                'mandatory' => false,
                'condition' => $imagickOK,
                'by' => '<a href="http://www.yiiframework.com/doc-2.0/yii-captcha-captcha.html">Captcha</a>',
                'memo' => $imagickMemo,
            ],
            // PHP ini :
            'phpExposePhp' => [
                'name' => 'Expose PHP',
                'mandatory' => false,
                'condition' => $this->checker->checkPhpIniOff("expose_php"),
                'by' => 'Security reasons',
                'memo' => '"expose_php" should be disabled at php.ini',
            ],
            'phpAllowUrlInclude' => [
                'name' => 'PHP allow url include',
                'mandatory' => false,
                'condition' => $this->checker->checkPhpIniOff("allow_url_include"),
                'by' => 'Security reasons',
                'memo' => '"allow_url_include" should be disabled at php.ini',
            ],
            'phpSmtp' => [
                'name' => 'PHP mail SMTP',
                'mandatory' => false,
                'condition' => strlen(ini_get('SMTP')) > 0,
                'by' => 'Email sending',
                'memo' => 'PHP mail SMTP server required',
            ],
        ];
    }

}