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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    
    <script type="text/javascript">
         var ajaxUrl = "<?php echo $this->Url->build(['controller' => 'users', 'action' => 'getAjax']); ?>";
         var newsUrl = "<?php echo $this->Url->build(['controller' => 'users', 'action' => 'getNewsLetter']); ?>";
    </script>

   <?php echo $this->Html->script(['jquery.min', 'jquery.validate', 'custom']);?>
   <?php echo $this->Html->css(['custom-style', 'custom-media']);?>
   
   <!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-153413643-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-153413643-1');
</script>

</head>
<body>
    <div class="main-page-container">
    <?= $this->element('static/header');?>

        <section class="banner-section">
            <div class="container">
                <div class="row banner-content-row">
                    <section class="col-md-8 col-sm-7 text-col">
                        <h2 class="title-text">Welcome To GreenaFind</h2>
                        <h4 class="sub-content">
                        An App for people tired of dumping plastic after they eat or drink something, find the greenest establishments anywhere you are! 
                        </h4>
                    </section><!-- =text Col End//= -->
                    <section class="col-md-4 col-sm-5 image-col">
                        <div class="image-block"></div>
                    </section>
                </div><!-- =banner Content Row End//= -->
            </div>
        </section><!-- =banner-section End//= -->

        <section class="content-section content-bg" id="how-it-works">
            <div class="container">
                <h2 class="content-title green-txt">How it Works</h2>
				<h2 class="content-title green-txt">
                    <iframe src="https://www.youtube.com/embed/ZPmoZfOAjLg" width="560" height="315" frameborder="0" allowfullscreen></iframe>
                </h2>
	
                <div class="row grid-row">
                    <section class="col col-sm-4 grid-box">
                        <div class="box">
                            <h3 class="title"><u>Find</u></h3>
                            <article>
                                <p class="para">
                                   Wherever you are, just open the App and immediately be presented with a <b>SPK (Sustainable Packaging) Rating</b> of all the eateries in your location or desired location.
                                </p>
                            </article>
                        </div>
                    </section><!-- =Easy to use Grid End//= -->

                    <section class="col col-sm-4 grid-box">
                        <div class="box">
                            <h3 class="title"><u>Review</u></h3>
                            <article>
                                <p class="para">
                                    <b>We need YOU to get involved.</b> Next time you’re grabbing a take-away. Find your café and hit the Survey button. By answering 5 simple questions, the SPK rating will registered and everyone can benefit.
                                </p>
                            </article>
                        </div>
                    </section><!-- =Powerful Design End//= -->

                    <section class="col col-sm-4 grid-box">
                        <div class="box">
                            <h3 class="title"><u>Order</u></h3>
                            <article>
                                <p class="para">
                                    Review the menu’s, benefit from offers and submit orders of participating eateries ahead of time to beat the queue.
                                </p>
                            </article>
                        </div>
                    </section><!-- =Powerful Design End//= -->
                </div><!-- =Grid Row End//= -->
            </div>
        </section><!-- =Why Special Section End//= -->

        <section class="content-section app-store-section">
            <div class="container">
                <div class="row grid-row">
                    <section class="col-sm-6 content-col">
                        <div class="grid-content-box">
                            <h2 class="title-text">Greenafind App</h2>
                            <p class="para">
                                The movement towards sustainable single use packaging is here but how do you know who is getting involved? GreenaFind gives you a platform to quickly find the best options to get your desired food and drinks with green packaging in mind. Enjoy your food and drink knowing it’s not cost the “Earth”.
                            </p>
                            <div class="store-btn-frame">
                                <h3 class="title-text">available on</h3>
                                <div class="btn-row">
                                    <button class="btn btn-google-play"></button>
                                    <button class="btn btn-ios-store"></button>
                                </div><!-- =Btn Row End//= -->
                            </div><!-- =Store Btn Frame End//= -->
                        </div><!-- =Grid Content Box End//= -->
                    </section><!-- =content-col End//= -->

                    <section class="col-sm-6 image-col">
                        <div class="image-frame">
                        <?php echo $this->Html->image('mobile_show.png', ['alt' => 'greenafind', 'class' => '']);?>
                        </div><!-- =image-frame= -->
                    </section><!-- =image Col End//= -->
                </div><!-- =Grid Row End//= -->
            </div>                
        </section><!-- =App Store Section=-->
        <section class="content-section owner-app-store-section">
            <div class="container">
                <div class="row grid-row">
                    <section class="col-sm-5 image-col">
                        <div class="image-frame">
                         <?php echo $this->Html->image('bg_ipad.png', ['alt' => 'greenafind', 'class' => '']);?>
                        </div><!-- =image-frame= -->
                    </section><!-- =image Col End//= -->
                    
                    <section class="col-sm-7 content-col">
                        <div class="grid-content-box">
                            <h2 class="title-text">Greenafind Kiosk App</h2>
                            <p class="para">
                                At last a place for you to show what you're doing to make you establishment as green as practically possible.
                                Download Greenafind Kiosk to take advantage of :
                                Uploading your Menu items
                                Recieving order directly from your customers.
                                Allow users to mark you as their favourites.
                                Clearly highlight your establishment in Gold on the map.
                                Be prioritised in the list view.
                                + lots more to come.
                            </p>
                            <div class="store-btn-frame">
                                <h3 class="title-text">available on</h3>
                                <div class="btn-row">
                                    <button class="btn btn-google-play"></button>
                                    <button class="btn btn-ios-store"></button>
                                </div><!-- =Btn Row End//= -->
                            </div><!-- =Store Btn Frame End//= -->
                        </div><!-- =Grid Content Box End//= -->
                    </section><!-- =content-col End//= -->
                </div><!-- =Grid Row End//= -->
            </div>                
        </section><!-- =owner-app-store-section Section=-->

       <?= $this->element('static/footer');?>
        
    </div><!-- =main page Container End//= -->

    <!-- Modal -->
    <div id="becomePartnerModal" class="modal fade becomePartnerModal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content action_form">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Submit your requirement</h4>
                </div>

                 <?php echo $this->Form->create('', ['id' => 'insert_form', 'name' => "insert_form"]); ?>   
                <div class="modal-body">
                    <div class="contact-form">
                        <div class="row">
                            <div class="col-md-6 form-col form-group">
                            <?php echo $this->Form->input("firstname", array("class" => "form-control", "placeholder" => "Enter First name", "label" => "", "autofocus" => "true")); ?> 
                            </div>
                            <div class="col-md-6 form-col form-group">
                             <?php echo $this->Form->input("lastname", array("class" => "form-control", "placeholder" => "Enter Last name", "label" => "")); ?> 
                            </div>
                        </div><!-- =row 01 End//= -->

                        <div class="row">
                            <div class="col-xs-12 form-col form-group">
                             <?php echo $this->Form->input("email", array("type" => "email", "class" => "form-control", "placeholder" => "Enter an Email", "label" => "", "autocomplete" => "off")); ?> 
                            </div>                            
                        </div><!-- =row 02 End//= -->
                        <div class="row">
                            <div class="col-xs-12 form-col form-group"> 
                            <?php echo $this->Form->input("phone_number", array("class" => "form-control", "placeholder" => "Enter a Phone number", "label" => "", "autocomplete" => "off")); ?>
                            </div>                            
                        </div><!-- =row 03 End//= -->
						 <div class="row">
                            <div class="col-xs-12 form-col form-group"> 
                            <?php echo $this->Form->input("restaurant_name", array("class" => "form-control", "placeholder" => "Enter your Restaurant Name", "label" => "", "autocomplete" => "off")); ?>
                            </div>                            
                        </div>
						<div class="row">
                            <div class="col-xs-12 form-col"> 
                            <?php echo $this->Form->input("address", array('type' => 'textarea', "class" => "form-control", "placeholder" => "Enter your address", "label" => "", "autocomplete" => "off")); ?>
                            </div>                            
                        </div>
                       
                    </div>
                </div>
                
                <div class="modal-footer">
                 <span class="" id = "loading"></span>
                    <?php echo $this->Form->button('Submit', ['class' => 'btn btn-default btn-submit', 'id' => 'submitData']) ?>
                        
                </div>
                <?php echo $this->Form->end(); ?>
            </div><!-- =Modal Content End//= -->


            <!-- Modal content thank you messgae-->
            <div class="modal-content action_thankyou" style="display: none;">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title thanksMsg">Thanks for registering with Greenafind.<br>We are just going to run some checks and will get back to you shortly with your activation instructions.</h4>
                </div>
            </div><!-- =Modal Content End//= -->
        </div>
    </div><!-- Modal -->

<!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        Latest compiled JavaScript
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
        <style type="text/css">
            label.error{
                 color: red;
                 font-weight: normal;
            }
           span.error{
            color: red;
           } 
        </style>
    </body>
</html>

