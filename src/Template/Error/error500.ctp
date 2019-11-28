<?php
use Cake\Core\Configure;
use Cake\Error\Debugger;

$this->layout = '';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf_test_name" content="86dbad8688fb56a4682223e19936cf0a">
    <title>Greenafind</title>
    <!-- google font -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700,800|Roboto:300,400,500,700,900" rel="stylesheet" /> 

    <!-- Fonts awesome font -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <!-- Bootstrap -->
    <?php echo $this->Html->script(['jquery.min', 'jquery.validate', 'custom', 'bootstrap.min']);?>
    <?php echo $this->Html->css(['custom-style', 'custom-media', 'custom', 'bootstrap.min']);?>
</head>
    <body>
    <div class="main-page-container">
    <?= $this->element('static/terms/header');?>
    <section class="my-4 cancelSuccSection">
        <div class="container">

            <p class="cancelSuccessMsg">Sorry! Something went wrong. Please try again ! </p> 
            <div class="text-center">
            <?php echo $this->Html->link(__('Back'), 'javascript:history.back()', ['class' => 'btn btn-info', 'escape' => false]);?></div>
        </div>
    </section>
        
    </div>



  </body>
</html>
