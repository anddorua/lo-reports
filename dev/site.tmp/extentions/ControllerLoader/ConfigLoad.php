<?php
namespace Extention\ControllerLoader;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Silex\Application;

/**
 * Description of ConfigLoad
 *
 * @author orionla2
 */
class ConfigLoad {
    
    public static function loadControllers($controllers, Application $app){
        foreach ($controllers as $title => $controller) {
            $class =
                "\\" . $controller['namespace']
                . '\\' . $controller['name']
                . '\\controllers\\' . $controller['className'];
            $app->mount($controller['name'], new $class());
        }
    }
}
