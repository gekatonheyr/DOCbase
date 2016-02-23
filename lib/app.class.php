<?php

class App{

    protected static $router;
    protected static $user;

    public static $db;

    public static function getRouter(){
        return self::$router;
    }

    public static function getUser(){
        return self::$user;
    }

    public static function run($uri){
        self::$router = new Router($uri);
        self::$db = new DB(Config::get('db.host'), Config::get('db.user'), Config::get('db.password'), Config::get('db.db_name'));
        self::$user = new User();

        Lang::load(self::$router->getLanguage());

        $controller_class = ucfirst(self::$router->getController()).'Controller';
        $controller_method = strtolower(self::$router->getMethodPrefix().self::$router->getAction());

        if(!User::isAuthorised()){
            $controller_class = 'UsersController';
            $controller_method = 'login';
        }

        $layout = self::$router->getRoute();

         // Calling controller's method
        $controller_object = new $controller_class();
        if ( method_exists($controller_object, $controller_method) ){
            // Controller's action may return a view path
            $view_path = $controller_object->$controller_method(self::$router->getParams());
            $view_object = new View($controller_object->getData(), $view_path);
            $content = $view_object->render();
        } else {
            throw new Exception('Method '.$controller_method.' of class '.$controller_class.' does not exist.');
        }

        $layout_path = VIEWS_PATH.DS.$layout.'.html';
        $layout_view_object = new View(compact('content'), $layout_path);
        $tmp = $layout_view_object->render();
        echo $tmp['body'];
    }

}