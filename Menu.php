<?php

namespace bariew\cmsBootstrap;
use Yii;
use yii\bootstrap\Nav;

class Menu extends Nav
{
    public $excludedModules = ['main', 'gii', 'debug'];
    
    public function init() 
    {
        parent::init();
        $this->items = array_merge($this->items, $this->adminItems());
    }
    
    private function adminItems()
    {
        $result = [];
        foreach (\Yii::$app->modules as $module) {
            $params = is_object($module) ? $module->params : (isset($module['params']) ? $module['params'] : []);
            if (isset($params['menu'])) {
                $result[] = $params['menu'];
            }
        }

        return $result;
    }
}
