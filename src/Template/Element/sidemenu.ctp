<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <a href="" class="brand-link">
    <?php //echo $this->Html->image('logo.png', ['alt' => 'AdminLTE Logo', 'class' => 'brand-image img-circle elevation-3', 'style' => 'opacity: .8']);?>
    <span class="brand-text font-weight-bold">Green Warrior</span>
  </a>
  <div class="sidebar">
    <?php $action =  $this->request->action; ?>
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <?= $this->Html->link('<i class="nav-icon fa fa-dashboard"></i><p> Dashboard </p>', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false, 'class' => (!empty($action) && ($action=="dashboard")) ? "nav-link active" : "nav-link inactive"]) ?>          
        </li>
        <li class="nav-item has-treeview <?= (!empty($action) && ($action=='restautantList' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=="" || $action=='' || $action=='')) ? 'menu-open' : '' ?> ">

        <?php echo $this->Html->link('<i class="nav-icon fa fa-tree"></i><p> Restaurant <i class="fa fa-angle-left right"></i></p>', '#', ['escape' => false, 'class' => (!empty($action) && ($action=="restautantList" || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=='' || $action=="" || $action=='' || $action=='')) ? "nav-link active" : "nav-link inactive"]) ?>
        
          <ul class="nav nav-treeview">
            <li class="nav-item">
              <?= $this->Html->link('<i class="fa fa-circle-o nav-icon"></i><p> Restaurant List </p>', ['controller' => 'Users', 'action' => 'restautantList'], ['escape' => false, 'class' => (!empty($action) && ($action=="" || $action=="" || $action=='')) ? "nav-link active" : "nav-link inactive"]) ?>
            </li>        
          </ul>
        </li>
        <li class="nav-item">
        <?= $this->Html->link('<i class="nav-icon fa fa-circle-o"></i><p class="text">Change Password</p>', ['controller' => 'users', 'action' => 'changePassword'], ['escape' => false, 'class' => 'nav-link']);?>
        </li>
        <li class="nav-item">
        <?= $this->Html->link('<i class="nav-icon fa fa-circle-o"></i><p class="text">Logout</p>', ['controller' => 'users', 'action' => 'logout'], ['escape' => false, 'class' => 'nav-link']);?>
        </li>
      </ul>
    </nav>
  </div>
</aside>