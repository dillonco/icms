<?php

class Router {
    private $table = array();

    public function __construct() {
        //$this->table['controller'] = new Route('Model', 'home', 'Controller');
        //$this->table['blog'] = new Route('BlogModel', 'blog', 'BlogController');
    }

    public function getRoute($model, $controller, $admin) {
        $model = strtolower($model);
        $controller = strtolower($controller);

        if ($admin) {
            if (empty($model)) {
                $this->table['controller'] = new Route('PagesModel', 'admin', 'pagesController', true);
                return $this->table['controller'];
            } else {
                $this->table[$controller] = new Route($model . 'Model', $controller, $model . 'Controller', true);
                return $this->table[$controller];
            }
        } else {
            if ($model == "blog") {
              $this->table['controller'] = new Route('blogModel', 'blog', 'blogController', false);
              return $this->table['controller'];
            } else {
                $this->table[$controller] = new Route($model . 'Model', $controller, $controller . 'Controller', false);
                return $this->table[$controller];
            }
        }

    }
}