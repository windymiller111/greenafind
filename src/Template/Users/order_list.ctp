<?php //echo '<pre>'; print_r($Orderdata); die;?>
<section class="content">
  <div class="container-fluid">
    <div class="card">
     <div class="card-body table-responsive p-0">
        <table class="table table-hover" id="library_categories">
         
          <tbody>
          <?php 
           if(!empty($Orderdata)){?>           
            Order Number:  <?php echo $Orderdata['order_number']; ?> <br> 
            Total Amount:  <?php echo $Orderdata['amount']; ?>           
              <?php foreach ($Orderdata['cart']['cart_items'] as $key => $Orderdataval) {?>
                 <?php $extraMenu = (isset($extras[0]['menu_option']) && !empty($extras[0]['menu_option'])) ? $extras[0]['menu_option'] : 'No';?>
                 <?php $extraPrice = (isset($extras[0]['menu_option_price']) && !empty($extras[0]['menu_option_price'])) ? $extras[0]['menu_option_price'] : 'No';?>
             <tr><td>Item Name:  <?php echo $Orderdataval['menu_item']['item_name'];?></td></tr>
               <tr><td>Item Price:  <?php echo $Orderdataval['single_item_price'];?></td></tr>
                <tr><td>Menu Size:  <?php echo $Orderdataval['menu_size'];?></td></tr>
                 <tr><td>Menu Choice:  <?php echo $Orderdataval['menu_item']['menu_choice'];?></td></tr>
                 <?php if(!empty($Orderdataval['menu_options'])){
                  $extras = json_decode($Orderdataval['menu_options'], true); ?>
                <tr><td> Extra Menu:  <?php echo $extraMenu;?> </td></tr>
                   <tr><td> Extra Menu:  <?php echo $extraPrice;?> </td></tr>
                   <?php }
                 }
                 }else{?>
            <tr>
              <td colspan="5" align="center">No records found</td>
            </tr>
          <?php    
          } 
          ?>
        </tbody>
        </table>
      </div>
    </div>
  </div>
</section>