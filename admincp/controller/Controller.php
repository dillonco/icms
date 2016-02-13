<?php
/**
 * ICMS - Intelligent Content Management System
 *
 * @package ICMS
 * @author Dillon Aykac
 */

/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
| Basic Controller Class - Called on /index.php
|
*/
class Controller {
    private $model;
    public $user_id;

    public function getName() {
        return ''; //In the real world this may well be get_class($this), and this method defined in a parent class.
    }

    public function __construct(Model $model) {
        $this->model = $model;
        $this->model->posts = $model->posts;
       // $this->user_id    = $_SESSION['id'];      //put in general

    }

    public function success() {
        echo ("success");
    }
}