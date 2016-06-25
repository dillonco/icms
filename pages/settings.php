<?php
use Nixhatter\ICMS;
if (count(get_included_files()) ==1) {
    header("HTTP/1.0 400 Bad Request", true, 400);
    exit('400: Bad Request');
}
/**
 * Settings
 * A page where users can edit their account settings.
 */
?>
<div class="container section-md">
    <div class="row">
        <div id="center-form">
            <h2 id="form-header">Settings</h2>
            <hr>
            <form action="" method="post" enctype="multipart/form-data">
                <div id="profile_picture">
                    <?php
                    if (!empty ($this->controller->avatar) && file_exists($this->controller->avatar)) {
                        echo "<img src='$this->controller->avatar'>";
                    } else {
                        echo "You have no avatar.";
                    }
                    ?>

                    <input type="file" class="form-control-file" name="myfile" />

                </div>
                <hr>
                <div id="personal_info">
                    <h3 >Profile Information </h3>

                    <h4>Username:</h4>
                    <input type="text" class="form-control" name="username" value="<?php if (isset($_POST['username']) ) {echo htmlentities(strip_tags($_POST['username']));} else { echo $this->controller->username; }?>">

                    <h4>Full name: </h4>
                    <input type="text" class="form-control" name="full_name" value="<?php if (isset($_POST['full_name']) ) {echo htmlentities(strip_tags($_POST['full_name']));} else { echo $this->controller->full_name; }?>">

                    <h4>Gender:</h4>
                    <?php
                    $gender    = $this->model->user['gender'];
                    $options    = array("undisclosed", "Male", "Female");
                    echo '<select class="form-control" name="gender">';
                    foreach ($options as $option) {
                        if ($gender == $option) {
                            $sel = 'selected="selected"';
                        } else {
                            $sel='';
                        }
                        echo '<option '. $sel .'>' . $option . '</option>';
                    }
                    ?>
                    </select>
                    <h4>Bio:</h4>
                    <textarea name="bio" class="form-control" cols="40" rows="10"> <?php if (isset($_POST['bio']) ) {echo htmlentities(strip_tags($_POST['bio']));} else { echo $this->controller->bio; }?></textarea>
                </div>
                <input type="submit" name="submit" value="Update" class="btn btn-primary btn-block btn-lg">
            </form>
        </div>
    </div>
</div>