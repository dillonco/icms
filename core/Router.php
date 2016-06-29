<?php
namespace Nixhatter\ICMS;
use Respect\Validation\Validator as v;

class Router {

    public function getRoute($model, $controller, $admin) {
        /**
         * Although Klein doesn't allow for characters,
         * it's best to double check.
         */
        if (!v::alpha()->validate($model) || !v::alpha('-')->validate($controller)) {

            $model = "";
            $controller = "";

        }

        if ($admin) {

            $route = new Route(ucfirst($model) . 'Model', $controller, $model . 'Controller', true);

            if (empty($model)) {
                $route = new Route('PagesModel', 'admin', 'pagesController', true);
            }

        } else {

            $route = new Route(ucfirst($model) . 'Model', $controller, $controller . 'Controller', false);

        }

        return $route;

    }

}