<?php
use Nixhatter\ICMS;
if (count(get_included_files()) ==1) {
    header("HTTP/1.0 400 Bad Request", true, 400);
    exit('400: Bad Request');
}
?>

<div class="container section-lg">
    <div class="row">
        <div id="center-form">
            <h1 id="form-header">Recover Account</h1>
            <hr>
            <form method="post" action="/user/recover">
                <h4>Email:</h4>
                <input type="text" name="email" value="<?php if(isset($_POST['email'])) echo htmlentities($_POST['email']); ?>" />
                <input type="submit" name="submit" value="Recover" />
                <br /><br />
                <a href="/user/register">Not registered yet? Sign up here!</a>
            </form>
        </div>
    </div>
</div>
