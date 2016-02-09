<nav class="navbar navbar-default navbar-static-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/admin"> <?php echo $settings->production->site->name." Administrator Panel" ?></a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li>
                    <a href="#" class="b-userbar__icons-item">
                        <i class="fa fa-paper-plane-o fa-lg"></i>
                        <span class="b-userbar__icons-item-notify i-font_normal">5</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $settings->production->site->url ?>" class="b-userbar__icons-item">
                        <i class="fa fa-sitemap"></i>
                    </a>
                </li>
                <li>
                    <a href="#" class="b-userbar__icons-item ">
                        <?php echo $this->user['username']?>
                    </a>
                </li>
                <li>
                    <a href="/logout" class="b-userbar__icons-item">
                        <i class="fa fa-power-off fa-lg"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
