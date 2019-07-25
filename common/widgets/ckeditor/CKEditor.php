<?php

namespace common\widgets\ckeditor;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\InputWidget;
use common\widgets\ckeditor\assets\AppAsset;

/**
 * CKEditor 编辑器
 * User: worry
 * Date: 2019/4/15
 * Time: 16:02
 */
class CKEditor extends InputWidget
{
    public $config = [];

    public $formData = [];

    /**
     * CKEditor 挂件初始化
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        // 注册资源文件
        $asset = AppAsset::register($this->getView());
        $this->value = $this->hasModel() ? Html::getAttributeValue($this->model, $this->attribute) : $this->value;
        $this->name = $this->hasModel() ? Html::getInputName($this->model, $this->attribute) : $this->name;

        $uploadUrl = '/api/v1/ckeditor';
        //常用配置项
        $config = [
            "defaultLanguage" => 'zh-cn',
            "extraPlugins" => 'uploadimage,codesnippet',
            "image_previewText" => ' ',
            // Remove the redundant buttons from toolbar groups defined above.
            "removeButtons" => '',
            // 显示全部可用编辑按钮
            "toolbar" => [
                ['Maximize', 'Autoformat', 'Source'],
                ['PasteText', 'PasteFromWord', 'Replace'],
                ['RemoveFormat', 'Bold', 'Italic'],
                ['Link', 'Unlink', '-', 'TextColor', 'BGColor', 'Image', '-'],
                ['NumberedList', 'BulletedList', 'Outdent', 'Indent', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
                ['Table', 'HorizontalRule', 'CodeSnippet'],
                ['Format'],
                ['FontSize'],
            ],
            // 允许全部内容，不过滤，比如js
            "allowedContent" => true,
            // 自动把内容更新到 textArea 中
            "autoUpdateElement" => true,
            // 复制时只允许复制text，防止出现js等复制
            // "copyFormatting_allowedContexts" => ['text'],
            // 编辑内容不能有body等
            "fullPage" => false,
            "height" => '700px',
            // 'filebrowserBrowseUrl' => $uploadUrl,
            // 'filebrowserImageBrowseUrl' => $uploadUrl,
            // 'filebrowserFlashBrowseUrl' => $uploadUrl,
            'filebrowserUploadUrl' => $uploadUrl . '/file',
            'filebrowserImageUploadUrl' => $uploadUrl . '/images',
            'filebrowserFlashUploadUrl' => $uploadUrl . '/file',
            'language' => 'zh-cn',
        ];

        $this->config = ArrayHelper::merge($config, $this->config);
        $this->formData = ArrayHelper::merge([
            'drive' => 'local',
        ], $this->formData);
    }

    /**
     * @return string
     */
    public function run()
    {
        $id = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->id;
        $config = Json::encode($this->config);

        //  由于百度上传不能传递数组，所以转码成为json
        !isset($this->formData) && $this->formData = [];
        foreach ($this->formData as $key => &$formDatum) {
            if (!empty($formDatum) && is_array($formDatum)) {
                $formDatum = Json::encode($formDatum);
            }
        }

        $formData = Json::encode($this->formData);
        //ready部分代码
        $script = <<<CKEDITOR
CKEDITOR.replace( '{$id}', {$config});
CKEDITOR;

        $this->getView()->registerJs($script);

        if ($this->hasModel()) {
            return Html::activeTextarea($this->model, $this->attribute);
        }

        return Html::textarea(ArrayHelper::getValue($this->config, 'textarea', $this->name), $this->value, ['id' => $id]);
    }
}