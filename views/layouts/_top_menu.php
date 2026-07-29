<?php $news = $app->News->getCountNew(); ?>
<header class="main-header">
    <!-- Logo -->
    <a href="/" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini">
            <svg class="YApps_Logo" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#Logo-YA-M"></use></svg>
        </span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg">
            <svg class="YApps_Logo" xmlns="http://www.w3.org/2000/svg"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#Logo-YA-M"></use></svg>
        </span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
            
        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
            
            <?php if ( $app->User->isRoot($authUser->ssid) ) { ?>
            <li>
                <?php
                    $prefix = ( strripos($_SERVER['REQUEST_URI'], '?') === false ) ? '?' : '&';
                ?>
                <a href="<?=$_SERVER['REQUEST_URI'].$prefix?>debug=1">
                    <i class="fa fa-bug" aria-hidden="true"></i>
                </a>
            </li>
            <?php } // is Root ?>
            <!-- Control Sidebar Toggle Button -->
            <li>
                <a href="/user/settings/">
                    <i class="fa fa-gears"></i>
                    <?php if ( $news > 0 ) { ?>
                    <span class="label label-success"><?=$news?></span>
                    <?php } ?> 
                </a>
            </li>
            
            <li>
                <a href="/user/?action=logout" title="Выход">
                    <i class="fa fa-sign-out"></i>
                </a>
            </li>
            
            </ul>
        </div>
    </nav>
</header>
