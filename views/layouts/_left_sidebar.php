<!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <img src="<?=(($authUser->avatar)?$authUser->avatar:'/assets/img/avatar5.png')?>" class="img-circle" alt="User Image">
        </div>
        <div class="pull-left info">
          <p><?=$authUser->name?></p>
        </div>
      </div>
      
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu tree" data-widget="tree">
        
        <li class="header">Приложения</li>
        
        <?php foreach ( $GLOBALS['USER_APPS'] as $item ) HTML::leftMenu( $item, $currentRoute ); ?>

        <?php // Helper::sp_h($userApps); ?>
        
        <li class="header">Пользователь</li>
        
        <li class="treeview <?=(($currentRoute->section=='user')?'active menu-open':'')?>">
          <a href="#">
            <i class="fa fa-user"></i>
            <span>Пользователь</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu <?=(($currentRoute->section=='user')?'active menu-open':'')?>">
            <li<?=(($currentRoute->view=='news')?' class="active"':'')?>><a href="/user/news/"><i class="fa fa-check"></i> 
              Новости и релизы
              <?php if ( $news > 0 ) { ?>
              <span class="pull-right-container"><small class="label pull-right bg-green"><?=$news?></small></span>
              <?php } ?>
            </a></li>
            <?php /* <li<?=(($currentRoute->view=='support')?' class="active"':'')?>><a href="/user/support/"><i class="fa fa-lightbulb-o"></i> Тех. поддержка</a></li> */ ?>
            <li<?=(($currentRoute->view=='settings')?' class="active"':'')?>><a href="/user/settings/"><i class="fa fa-cog"></i> Настройки</a></li>
            <li><a href="/user/?action=logout"><i class="fa fa-sign-out"></i> Выход</a></li>
          </ul>
        </li>
        
        <?php if ( $app->User->isAdminUser( $authUser ) ) { ?>
        <li class="treeview <?=(($currentRoute->section=='admin')?'active menu-open':'')?>">
          <a href="#">
            <i class="fa fa-user-secret"></i>
            <span>Администратор</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(($currentRoute->view=='users')?' class="active"':'')?>><a href="/admin/users/"><i class="fa fa-users"></i> Пользователи и права</a></li>
            <li<?=(($currentRoute->view=='sites')?' class="active"':'')?>><a href="/admin/sites/"><i class="fa fa-laptop"></i> Сайты</a></li>
            <li<?=(($currentRoute->view=='showrooms')?' class="active"':'')?>><a href="/admin/showrooms/"><i class="fa fa-car"></i> Витрины</a></li>
            <li<?=(($currentRoute->view=='dcs')?' class="active"':'')?>><a href="/admin/dcs/"><i class="fa fa-building-o"></i> Дилерские центры</a></li>
            <li<?=(($currentRoute->view=='brands')?' class="active"':'')?>><a href="/admin/brands/"><i class="fa fa-angle-double-right"></i> Бренды</a></li>
            <li<?=(($currentRoute->view=='models')?' class="active"':'')?>><a href="/admin/models/"><i class="fa fa-car"></i> Модели</a></li>
            <li<?=(($currentRoute->view=='menu')?' class="active"':'')?>><a href="/admin/menu/"><i class="fa fa-bars"></i> Пункты меню</a></li>
            <li<?=(($currentRoute->view=='settings')?' class="active"':'')?>><a href="/admin/settings/"><i class="fa fa-cog"></i> Настройки</a></li>
          </ul>
        </li>
        <?php } ?>
        
        <?php if ( $app->User->isRootUser( $authUser ) ) { ?>
        
        <li class="header">Root</li>
        
        <li class="treeview <?=(($currentRoute->section=='root')?'active menu-open':'')?>">
          <a href="#">
            <i class="fa fa-user-secret"></i>
            <span>Суперпользователь</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li<?=(($currentRoute->view=='news')?' class="active"':'')?>><a href="/root/news/"><i class="fa fa-check"></i> Новости и релизы</a></li>
            <li<?=(($currentRoute->view=='apps')?' class="active"':'')?>><a href="/root/apps/"><i class="fa fa-database"></i> Приложения</a></li>
            <li<?=(($currentRoute->view=='support')?' class="active"':'')?>><a href="/root/support/"><i class="fa fa-lightbulb-o"></i> Тех. поддержка</a></li>
            <li<?=(($currentRoute->view=='settings')?' class="active"':'')?>><a href="/root/settings/"><i class="fa fa-cog"></i> Настройки</a></li>
          </ul>
        </li>
        <?php } ?>

        <li class="header">Последний вход в систему<br /><?=date('d.m.Y H:i:s', $authUser->last_login)?></li>
        
       </ul>
    </section>
    <!-- /.sidebar -->
  </aside>