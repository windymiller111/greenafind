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
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <?php echo $this->Html->link('<b>Admin</b>Panel', '', ['escape' => false]); ?>            
        </div>    
        <div class="text-center">
            <?= $this->Flash->render() ?>
        </div>
        <!-- <div class="container clearfix"> -->
            <?= $this->fetch('content') ?>
        <!-- </div> -->
    </div>
</body>
</html>
