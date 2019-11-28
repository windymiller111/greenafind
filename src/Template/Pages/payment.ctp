<style type="text/css">
  .rate {
    box-shadow: 0px 0px 3px #ca3335;
    background-color: #ca333e;
    border-radius: 3px;
    padding: 12px;
    width: 100%;
    text-align: center;
  }

 .rate h4 {
  text-align: center;
    color: #fff;
    font-size: 20px;
    margin-top: 40px;
  }
  .panel.price.panel-red.custom-payment {
    border: 1px solid #dc3545;
    padding: 20px;
    width: 50%;
    margin: 30px auto;
    background-color: transparent;
}
.boxWrapper {
    width: 70%;
    margin: 25px auto;
}
@media(max-width: 991px){
.boxWrapper {
    width: 100%;
    margin: 25px auto;
    }
}
</style>

 <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

<div class="container-fluid">
<div class="row">
<div class="boxWrapper">
    <div class="row">

<?php
//echo 'we'; die;
//echo $user_id; die; 
if(!empty($subscriptions)){?>
	


<?php foreach ($subscriptions as $key => $subscription_value) {?>
<div class="col-lg-6 col-md-6 ">

<!-- PRICE ITEM -->

    <div class="panel price panel-red custom-payment">

        <div class="panel-heading  text-center">
        <h3><?php echo $subscription_value->plan_name; ?></h3>
        </div>
        <div class="panel-body text-center">
        <p class="lead" style="font-size:40px">$<strong><?php echo $subscription_value->plan_amount; ?></strong></p>
        </div>

        <div class="panel-footer">
        <button id = "paybutton" class = "paybutton btn btn-lg btn-block btn-danger" type="button" onclick="setAmt(<?php echo $subscription_value->id; ?>,<?php echo $subscription_value->plan_amount; ?>,'<?php echo $subscription_value->plan_name; ?>')"; class="btn btn-primary">BUY NOW!</button>

        </div>
    </div>    


<!-- /PRICE ITEM -->

</div>
      
   

    
    <?php 
    } 
   }
  ?>
  </div>
  </div>
  </div>
  </div>
<?php 

//$paypal_url= "http://localhost/greenwarrior/pay/";

$paypalURL = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; //Test PayPal API URL
$paypalID = "merchant.devendra@paypal.com";

?>

 
<form method="post" id="paypal" name="" action="<?php echo $paypalURL;?>">
 <input type="hidden" name="business" value="<?php echo $paypalID; ?>">
 <input type="hidden" name="cmd" value="_xclick">
 <input type="hidden" id="item_name" name="item_name" value="demo">
 <input type="hidden" id="item_number" name="item_number" value="">
 <input type="hidden" id="amount" name="amount" value="">
 <input type="hidden" id ="custom" name="custom" value="<?php echo $user_id; ?>">
 <input type="hidden" name="no_shipping" value="1">
 <input type="hidden" name="currency_code" value="USD">
 <input type='hidden' name='notify_url' value='http://35.153.158.83/greenwarrior/pay'>
  <input type="hidden" name="cancel_return" value="http://35.153.158.83/greenwarrior/cancel">
  <input type="hidden" name="return" value="http://35.153.158.83/greenwarrior/success">   
  </form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>

<script type="text/javascript">
	$(document).ready(function(){
		$('.paybutton').click(function(){
			//alert('check2');
			$('#paypal').submit();
		});
		
	});

	function setAmt(plan_id, plan_amt, plan_name){
			//alert('check');
			$("#item_number").val(plan_id);
		  $("#amount").val(plan_amt);
			$("#item_name").val(plan_name);
		}
</script>

  <!-- https://itsolutionstuff.com/post/paypal-payment-gateway-integration-in-php-source-code-exampleexample.html -->

  <!-- http://demo.itsolutionstuff.com/demo/demo-paypal-payment-gateway-integration-in-php-source-code-exampleexample.html -->