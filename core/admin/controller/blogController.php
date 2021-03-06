<?php
namespace Nixhatter\ICMS\admin\controller;

/**
 * Blog Controller
 *
 * @package ICMS
 * @author Dillon Aykac
 */

defined('_ICMS') or die;

use Nixhatter\ICMS as ICMS;
use Respect\Validation\Validator as v;

class BlogController extends Controller{
    public $model;
    public $id;
    public $posts;
    public $settings;
    private $user;


    public function getName() {
        return 'blog';
    }
    public function __construct(ICMS\model\BlogModel $model) {
        $this->model = $model;
        $this->posts = $model->get_posts();
        $this->settings = $model->container['settings'];
        $this->user     = $model->container['user'];

        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);
    }

    /**
     * Retrieve a specific blog post
     * @param $bid - blog id
     */
    public function edit($bid = NULL) {
        if(!empty($bid) && v::intVal()->validate($bid)) {
            $this->id = $bid;
            $this->posts = $this->model->get_post($bid);
            $this->published = "<span class=\"label label-warning\">Draft</span>";
            if($this->posts[0]['post_published'] === 1) {
                $this->published = "<span class=\"label label-success\">Published</span>";
            }
        }
    }
    public function delete($bid) {
        if(!empty($bid) && v::intVal()->validate($bid)) {
            if($this->model->delete_posts($bid)) {
                $response = array('result' => "success", 'message' => 'Post Deleted');
            } else {
                $response = array('result' => "fail", 'message' => 'Could not delete post');
            }
        } else {
            $response = array('result' => "fail", 'message' => 'Invalid post ID');
        }
        exit(json_encode($response));
    }
    public function create() {

        // check for a submitted form
        if (isset($_POST['submit'])) {
            //Check to make sure fields are filled in
            if (empty($_POST['postName']) or empty($_POST['postContent'])) {
                $response = array('result' => "fail", 'message' => 'Make sure you filled out all the fields!');
            } else {
                $post_name = $this->postValidation($_POST['postName']);
                $postContent = $this->purifier->purify($_POST['postContent']);

                $ip = $this->filterIP($_SERVER['REMOTE_ADDR']);
                if($_POST['submit'] == "publish") $published = 1; else $published = 0;


                    $post_desc = !empty($_POST['postDesc']) ? $this->postValidation($_POST['postDesc']) : "";
                    if($this->model->newBlogPost($post_name, $postContent, $ip, $post_desc, $published, $this->user['full_name'])) {
                        $response = array('result' => "success", 'message' => 'Blog Created!');
                    } else {
                        $response = array('result' => "fail", 'message' => 'Blog post could not be created');
                    }

            }
            exit(json_encode($response));
        }
    }
    public function update($bid) {
        if (!empty($_POST['postName']) && !empty($_POST['postContent'])
            && !empty($_POST['postDesc']) && !empty($bid)) {

            $post_name = $this->postValidation($_POST['postName']);

            if (!v::notEmpty()->validate($post_name)) {
                $this->errors[] = 'Can\t use special characters';
            }

            if (!v::intVal()->validate($bid)) {
                $this->errors[] = 'Post ID must be a valid integer.';
            }

            if (v::alnum()->validate($_POST['postDesc'])) {
                $this->errors[] = 'Please fill out the Description';
            }

            if (empty($errors)) {

                $post_desc = $this->postValidation($_POST['postDesc']);
                $post_content = $this->purifier->purify($_POST['postContent']);

                if($_POST['submit'] == "publish") $published = 1; else $published = 0;
                if ($this->model->update_post($post_name, $post_content, $bid, $post_desc, $_SERVER['REMOTE_ADDR'], $published, $this->user['full_name'])) {
                    $response = array('result' => "success", 'message' => 'Blog Updated');
                } else {
                    $response = array('result' => "fail", 'message' => 'Database error while updating blog');
                }
            } elseif (!empty($errors)) {
                $response = array('result' => "fail", 'message' => implode($this->errors));
            }
        }

        exit(json_encode($response));
    }
}