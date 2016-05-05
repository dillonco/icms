<?php if (count(get_included_files()) ==1) {
    header("HTTP/1.0 400 Bad Request", true, 400);
    exit('400: Bad Request');
}
$clientid = $this->settings->production->email->clientid;
$basicPass = $this->settings->production->email->pass;
?>
<div id="content">
    <div class="box">
        <div class="box-header">Settings</div>
        <div class="box-body">
            <div class="alert alert-warning" role="alert">
                <strong>Warning</strong> - Saving incorrect settings can break your website!
            </div>
            <form  method="post" action="/admin/site/settings" enctype="multipart/form-data">
                <div class="col-md-6">
                    <fieldset>
                        <h2 class="fs-title">Enter information about your website</h2>
                        <label>Site Name</label>
                        <input type="text" name="sitename" class="form-control" value="<?php echo $this->settings->production->site->name ?>" />
                        <label>Site File Location</label>
                        <input type="text" name="cwd" class="form-control" value="<?php echo $this->settings->production->site->cwd ?>" />
                        <label>URL</label>
                        <input type="text" name="url" class="form-control" value="<?php echo $this->settings->production->site->url ?>" />
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $this->settings->production->site->email ?>" />
                        <fieldset class="form-group">
                            <label for="template">Theme</label>
                            <select class="form-control" name="template">
                                <option selected disabled><?php echo $this->settings->production->site->template ?></option>
                                <option>default</option>
                                <option>decode</option>
                            </select>
                        </fieldset>
                    </fieldset>
                </div>
                <div class="col-md-6">
                    <fieldset>
                        <h2 class="fs-title">Database</h2>
                        <label>Database Host</label>
                        <input type="text" name="dbhost" class="form-control" value="<?php echo $this->settings->production->database->host ?>" />
                        <label>Database Port</label>
                        <input type="text" name="dbport" class="form-control" value="<?php echo $this->settings->production->database->port ?>" />
                        <label>Database Name</label>
                        <input type="text" name="dbname" class="form-control" value="<?php echo $this->settings->production->database->name ?>" />
                        <label>Database User</label>
                        <input type="text" name="dbuser" class="form-control" value="<?php echo $this->settings->production->database->user ?>" />
                        <label>Database Password</label>
                        <input type="text" name="dbpass" class="form-control" value="unchanged"/>
                        <small class="text-muted">Leave this as "unchanged" unless you're changing the password</small>
                    </fieldset>
                </div>
                <h1>Email Configuration</h1>
                <div class="col-md-8">
                    <fieldset class="form-group">
                        <label for="emailHost">Host</label>
                        <input type="text" class="form-control" name="emailHost" value="<?php echo $this->settings->production->email->host ?>">
                    </fieldset>
                </div>
                <div class="col-md-4">
                    <fieldset class="form-group">
                        <label for="emailPort">Port</label>
                        <input type="text" class="form-control" name="emailPort" value="<?php echo $this->settings->production->email->port ?>">
                    </fieldset>
                </div>
                <div class="col-md-12">
                <fieldset class="form-group">
                    <label for="emailUser">Email address</label>
                    <input type="email" class="form-control" name="emailUser" value="<?php echo $this->settings->production->email->user ?>">
                </fieldset>

                <div>
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="<?php echo $this->controller->isActive($clientid) ?>"><a href="#xoauth" aria-controls="home" role="tab" data-toggle="tab">XOAUTH</a></li>
                        <li role="presentation" class="<?php echo $this->controller->isActive($basicPass) ?>"><a href="#basic" aria-controls="profile" role="tab" data-toggle="tab">BASIC</a></li>
                    </ul>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane <?php echo $this->controller->isActive($clientid) ?>" id="xoauth">
                            <?php
                                if (version_compare(phpversion(), '5.5.0', '<')) {
                                echo("<div class=\"alert alert-danger\" role=\"alert\">You need at least php version 5.5 to use XOAUTH2, yours is <strong>".phpversion()."</strong></div>");
                                }
                            ?>
                            <fieldset class="form-group">
                                <label for="emailClientID">Client ID</label>
                                <input type="text" class="form-control" name="emailClientID" value="<?php echo $clientid ?>">
                            </fieldset>
                            <fieldset class="form-group">
                                <label for="emailClientSecret">Client Secret</label>
                                <input type="text" class="form-control" name="emailClientSecret" value="<?php echo $this->settings->production->email->clientsecret ?>">
                            </fieldset>
                            <form  method="post" action="/admin/site/oauth" enctype="multipart/form-data">
                                <button type="submit" class="btn btn-primary">Setup Google OAuth</button>
                            </form>
                        </div>
                        <div role="tabpanel" class="tab-pane <?php echo $this->controller->isActive($basicPass) ?>" id="basic">
                            <fieldset class="form-group">
                                <label for="emailUser">Email Password</label>
                                <input type="password" class="form-control" name="emailPassword" value="<?php echo $basicPass ?>">
                            </fieldset>
                        </div>
                    </div>
                </div>
                <br />
                <button type="submit" name="submit" value="submit" class="btn btn-primary">Save</button>
                </div>
            </form>

            <form  method="post" action="" enctype="multipart/form-data">
                <button type="submit" name="cwd" class="btn btn-primary" disabled>Scan Working Directory</button>
            </form>
        </div>
    </div>
</div>