<?php
/**
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

$this->title = '许可协议';
?>
<h2>License agree</h2>

<?php $form = ActiveForm::begin([
    'options' => [
        'id' => 'install-form',
    ],
]); ?>

    感谢您选择&nbsp;Hassium&nbsp;产品。<br/>
    Hassium网站管理系统的官方网站为&nbsp;http://www.hassium.org，是&nbsp;Hassium&nbsp;产品的开发商，依法独立拥有&nbsp;Hassium&nbsp;产品著作权。<br>
    Hassium&nbsp;著作权受到法律和国际公约保护。使用者：无论个人或组织、盈利与否、用途如何（包括以学习和研究为目的），均需仔细阅读本协议，在理解、同意、并遵守本协议的全部条款后，方可开始使用&nbsp;Hassium&nbsp;软件。&nbsp;<br>
    <h5>协议许可的权利&nbsp;</h5>
    您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。&nbsp;<br>
    您可以在协议规定的约束和限制范围内修改&nbsp;Hassium&nbsp;源代码(如果被提供的话)或界面风格以适应您的网站要求。&nbsp;<br>
    获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持期限、技术支持方式和技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。&nbsp;<br>
    <h5>协议规定的约束和限制&nbsp;</h5>
    未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目或实现盈利的网站）。购买商业授权请登陆http://www.hassium.org参考相关说明。&nbsp;<br>
    不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。&nbsp;<br>
    无论如何，即无论用途如何、是否经过修改或美化、修改程度如何，只要使用&nbsp;Hassium&nbsp;的整体或任何部分，未经书面许可，网站页面页脚处的&nbsp;Hassium&nbsp;名称和&nbsp;http://www.hassium.org&nbsp;的链接都必须保留，而不能清除或修改。&nbsp;<br>
    禁止在&nbsp;Hassium&nbsp;的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。&nbsp;<br>
    如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。&nbsp;<br>
    <h5>有限担保和免责声明&nbsp;</h5>
    本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。&nbsp;<br>
    用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。&nbsp;<br>
    有关&nbsp;Hassium&nbsp;最终用户授权协议、商业授权与技术服务的详细内容，均由&nbsp;Hassium&nbsp;官方网站独家提供。<br>
    <br>
    电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始安装&nbsp;Hassium，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。

    <div class="checkbox">
        <?= Html::checkbox('license', false, ['label' => '<strong>同意并签署安装协议</strong>']); ?>
    </div>

<?php ActiveForm::end(); ?>