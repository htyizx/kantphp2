<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <565364226@qq.com>
 * @copyright (c) 2011 KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */

namespace Kant\Validators;

use Kant\Kant;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
use yii\web\JsExpression;
use yii\helpers\Json;

/**
 * RegularExpressionValidator validates that the attribute value matches the specified [[pattern]].
 *
 * If the [[not]] property is set true, the validator will ensure the attribute value do NOT match the [[pattern]].
 *
 */
class RegularExpressionValidator extends Validator {

    /**
     * @var string the regular expression to be matched with
     */
    public $pattern;

    /**
     * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
     * the regular expression defined via [[pattern]] should NOT match the attribute value.
     */
    public $not = false;

    /**
     * @inheritdoc
     */
    public function init() {
        parent::init();
        if ($this->pattern === null) {
            throw new InvalidConfigException('The "pattern" property must be set.');
        }
        if ($this->message === null) {
            $this->message = Kant::t('yii', '{attribute} is invalid.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value) {
        $valid = !is_array($value) &&
                (!$this->not && preg_match($this->pattern, $value) || $this->not && !preg_match($this->pattern, $value));

        return $valid ? null : [$this->message, []];
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view) {
        $pattern = Html::escapeJsRegularExpression($this->pattern);

        $options = [
            'pattern' => new JsExpression($pattern),
            'not' => $this->not,
            'message' => Yii::$app->getI18n()->format($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
                    ], Yii::$app->language),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        ValidationAsset::register($view);

        return 'yii.validation.regularExpression(value, messages, ' . Json::htmlEncode($options) . ');';
    }

}
