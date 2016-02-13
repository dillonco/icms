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
| Admin Pages Controller
|--------------------------------------------------------------------------
|
| Admin Pages Controller Class - Called on /admin
|
*/
class pagesController {
    public $model;
    public $user_id;
    private $settings;
    private $users;

    public function __construct(PagesModel $model) {
        $this->model = $model;
        $this->model->pages = $model->get_pages();
        $this->users = $model->users;

        // $this->user_id    = $_SESSION['id'];      //put in general

    }
    public function getName() {
        return 'pages';
    }
    public function success() {
        echo ("success");
    }
    public function setGlobals(\Pimple\Container $globals) {
        $this->settings = $globals['settings'];
        //$this->users = $globals['users'];
    }

    public function edit($id) {
        $this->model->action = "edit";
        $this->model->id = $id;
        $this->model->pages = $this->model->get_page($id);

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
            //AJAX Request Detected
            if (crypt($_SESSION['token'], $_POST['token']) == $_POST['token']) {
                if (isset($_POST['action'])) {
                    $action = $_POST['action'];

                    if (isset($_POST['pageURL'])) {
                        $pageUrl = $_POST['pageURL'];
                    } else {
                        $errors[] = 'URL is Required';
                    }
                    if (empty($errors) === true) {
                        //User wants to delete the given URL
                        if ($action == "delete") {
                            $this->model->delete_nav("index.php?page=".$pageUrl);
                            $this->users->delete_all_page_permissions($pageUrl);
                            if ($this->model->delete_page($pageUrl, $this->settings->production->site->cwd)) {
                                echo("<script> successAlert();history.go(-1);</script>");
                            } else {
                                $errors[] = 'Delete page MAY HAVE failed. Return to the Edit pages to find out! ';
                            }
                        }
                        //User has edited a file and wants to save it
                        elseif ($action == "update") {
                            if (isset($_POST['pageContent'])) {
                                $text = $_POST['pageContent'];
                                if($this->model->edit_page($pageUrl, $this->settings->production->site->cwd, $text)) {
                                    echo("<script> successAlert();</script>");
                                } else {
                                    $errors[] = 'Failed updating the page.';
                                }
                            } else {
                                $errors[] = 'Text is Required';
                            }

                        }
                    }
                }
                if (empty($errors) === false) {
                    echo '<p>' . implode('</p><p>', $errors) . '</p>';
                }
            }
        }
    }
    public function delete($id) {
        $this->model->delete_page($id);
        header('Location: /admin/pages/edit');
        die();
    }
    public function update() {

        if (isset($_POST['action'])) {
            $action = $_POST['action'];

            if (isset($_POST['pageURL'])) {
                $pageUrl = $_POST['pageURL'];
            } else {
                $errors[] = 'URL is Required';
            }
            if (empty($errors) === true) {
                //User wants to delete the given URL
                if ($action == "delete") {
                    $this->model->delete_nav("index.php?page=".$pageUrl);
                    $this->users->delete_all_page_permissions($pageUrl);
                    if ($this->model->delete_page($pageUrl, $this->settings->production->site->cwd)) {
                        echo("<script> successAlert();history.go(-1);</script>");
                    } else {
                        $errors[] = 'Delete page MAY HAVE failed. Return to the Edit pages to find out! ';
                    }
                }
                //User has edited a file and wants to save it
                elseif ($action == "update") {
                    if (isset($_POST['pageContent'])) {
                        $text = $_POST['pageContent'];
                        if($this->model->edit_page($pageUrl, $this->settings->production->site->cwd, $text)) {
                            echo("<script> successAlert();</script>");
                        } else {
                            $errors[] = 'Failed updating the page.';
                        }
                    } else {
                        $errors[] = 'Text is Required';
                    }

                }
            }
        }
        if (empty($errors) === false) {
            echo '<p>' . implode('</p><p>', $errors) . '</p>';
        }
    }

    public function create() {
        if (isset($_POST['submit'])) {
            $config = HTMLPurifier_Config::createDefault();
            $purifier = new HTMLPurifier($config);

            if (!isset($_POST['pageTitle']) || !isset($_POST['pageURL']) || !isset($_POST['pageContent'])
                || !isset($_POST['pagePermission']) || !isset($_POST['pagePosition']) ) {

                $errors[] = 'All fields are required.';

            } else {

                if (v::alnum()->notEmpty()->validate($_POST['pageTitle'])) {
                    $title = $_POST['pageTitle'];
                } else {
                    $errors[] = 'Invalid title.';
                }
                if (v::alnum()->notEmpty()->validate($_POST['pageURL'])) {
                    $url = $_POST['pageURL'];
                } else {
                    $errors[] = 'invalid URL';
                }

                $pageContent = $purifier->purify($_POST['pageContent']);

                if (v::alnum(',')->validate($_POST['pagePermission'])) {
                    $permission = $_POST['pagePermission'];
                } else {
                    $errors[] = 'Permissions must be an integer from 1 - X';
                }
                if (v::int()->validate($_POST['pagePosition'])) {
                    $position = htmlentities($_POST['pagePosition']);
                } else {
                    $errors[] = 'invalid page position';
                }
            }
            if (empty($errors) === true) {
                $userArray = explode(', ', $permission); //split string into array seperated by ', '
                foreach($userArray as $usergroup) //loop over values
                {
                    $this->users->add_usergroup($usergroup, $url);
                }

                $this->model->generate_page($title, $url, $pageContent);
                $url = "/pages/".$url;
                $this->model->create_nav($title, $url, $position);


            }  elseif (empty($errors) === false) {
                echo '<p>' . implode('</p><p>', $errors) . '</p>';
            }
        }
    }
    public function menu() {
        /**************************************************************
        Update Menu
         ***************************************************************/
        if (isset($_POST['update'])) { //if yes is submitted...
            $Name = $_POST['nav_name']; //get post id
            $Link = $_POST['nav_link'];
            $Position = $_POST['nav_position'];
            //echo confirmation if successful
            if ($this->model->update_nav($Name, $Link, $Position)) {
                echo("<script> successAlert();</script>");
            } else {
                echo 'Update Failed.';
            }
        }
        /**************************************************************
        DELETE Menu
         ***************************************************************/
        if (isset($_POST['nav_delete'])) { //if yes is submitted...
            $url = $_POST['nav_link']; //get post id
            //echo confirmation if successful
            $this->model->delete_nav($url);
        }
        /**************************************************************
        Create new Menu
         ***************************************************************/
        if (isset($_POST['create'])) { //if yes is submitted...
            $Name = $_POST['nav_name'];
            $Link = $_POST['nav_link'];
            $Position = $_POST['nav_position'];

            $this->model->delete_nav($Link);

            //echo confirmation if successful
            $this->model->create_nav($Name, $Link, $Position);
            echo("<script> successAlert();</script>");
        }
    }

}
