<?php

namespace carono\giix;

use schmunk42\giiant\commands\BatchController;

class GiixController extends BatchController
{
    public $modelNamespace = 'app\models';
    public $overwrite = true;
    public $defaultAction = 'models';
    public $interactive = false;
    public $template = 'caronoModel';
    public $templatePath;
    public $generator = 'carono\giix\generators\model\Generator';

    public function init()
    {
        if (key_exists('@common', \Yii::$aliases)) {
            if ($this->modelNamespace == 'app\models') {
                $this->modelNamespace = 'common\models';
            }
            if ($this->modelQueryNamespace == 'app\models\query') {
                $this->modelQueryNamespace = 'common\models\query';
            }
        }
    }

    protected function getYiiConfiguration()
    {
        $config = parent::getYiiConfiguration();
        $name = 'giiant-model';
        $template = $this->templatePath ? $this->templatePath : '@vendor/carono/yii2-giix/templates/model';
        self::addTemplateToGiiGenerator($config, $this->generator, $name, $template);
        return $config;
    }

    public static function addTemplateToGiiGenerator(&$config, $generator, $name, $template)
    {
        self::prepareGii($config);
        $config['modules']['gii']['generators'][$name] = [
            'class' => $generator,
            'templates' => [
                'caronoModel' => $template
            ]
        ];
    }

    protected static function prepareGii(&$config)
    {
        if (!is_array($config['modules']['gii'])) {
            $config['modules']['gii'] = [
                'class' => 'yii\gii\Module',
                'generators' => []
            ];
        } elseif (isset($config['modules']['gii']['generators'])) {
            $config['modules']['gii']['generators'] = [];
        }
    }
}