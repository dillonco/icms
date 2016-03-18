<?php
/**
 * ICMS - Intelligent Content Management System
 *
 * @package ICMS
 * @author Dillon Aykac
 */
use Respect\Validation\Validator as v;

/*
|--------------------------------------------------------------------------
| Blog Controller
|--------------------------------------------------------------------------
|
| Blog Controller Class - Called on /blog
|
*/
class BlogController extends Controller{

    public function getName() {
        return 'BlogController';
    }

    public function __construct(BlogModel $model) {
        $this->model = $model;
        $this->model->posts = $this->model->get_posts();
    }

    public function post($id) {
        if (v::intVal()->notEmpty()->validate($id)) {
            $this->model->posts = $this->model->get_post($id);
        } else {
            $this->alert("error", 'Invalid post ID');
            die();
        }
    }
    public function view($id) {
        if ($id) {
            if (v::intVal()->notEmpty()->validate($id)) {
            $this->model->posts = $this->model->get_post($id);
                if(empty($this->model->posts)) {
                    $this->alert("error", 'Post does not exist');
                }
            } else {
                $this->alert("error", 'Invalid post ID');
            }
        } else {
            $this->model->posts = $this->model->get_posts();
        }
    }
}