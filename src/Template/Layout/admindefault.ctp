<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

$cakeDescription = 'Admin:';
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>
    <?= $this->Html->css(['https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css']); ?>
    <?= $this->Html->css(['https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css']); ?>
    <?= $this->Html->css(['adminlte.min', 'iCheck/square/blue', 'custom']);?>
    <?= $this->Html->css(["https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css"]); ?>
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>    
</head>
<body class="hold-transition sidebar-mini">       
    <div class="wrapper">        
        <nav class="main-header navbar navbar-expand navbar-light border-bottom">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
          </li>
      </ul>
            <div class="mx-auto">
                <?= $this->Flash->render() ?>
            </div>
        </nav>
        <?= $this->element('sidemenu');?>
        <div class="content-wrapper"> 
            <?= $this->fetch('content') ?>
        </div>
        <?= $this->element('footer');?>
    </div>
   
<?= $this->Html->script(['jquery.min', 'bootstrap.bundle.min', 'jquery.slimscroll.min', 'fastclick', 'adminlte.min', 'demo', 'custom', 'jquery.validate']); ?>
<?= $this->fetch('script') ?>
</body>
</html>

<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css">
<!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css"> -->
<!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

<?= $this->Html->script(["https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"]); ?>
<?= $this->Html->script(["https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"]); ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $(function () {
                $('.datetimepicker3').datetimepicker({
                format: 'LT'
                });
            });
        });

    $('#library_categories').DataTable({
        "paging": false,
        "lengthChange": false,
        "searching": true,
        "ordering": false,
        "info": false,
        "autoWidth": false
    });

    </script>