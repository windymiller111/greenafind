<?php //echo '<pre>'; print_r($paymentData); die;?>
<script type="text/javascript">
  var orderUrl = "<?php echo $this->Url->build(['controller' => 'Users', 'action' => 'orderList']); ?>";
</script>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Users Payment List</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false]);?></li>
          <li class="breadcrumb-item active">Payment List</li>
        </ol>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header"><?php echo $this->Html->link('Back', ['controller' => 'users', 'action' => 'restautantList'],['class' => 'btn btn-primary']); ?>
      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-hover" id="library_categories">
          <thead>
            <tr>
              <th>SNo</th>
              <th>User Name</th>
              <th>Restaurant Name</th>
              <th>Order Details</th>
              <th>Transaction Id</th>
              <th>Total Amount</th>
              <th>Device Type</th>              
              <th>Payment Date</th>
            </tr>
          </thead>
          <tbody>
          <?php 
           if(!$paymentData->isEmpty()){
            $perPage = $this->Paginator->param('perPage');
            $page = $this->Paginator->param('page') - 1;
              foreach ($paymentData as $key => $payval) {
                //echo '<pre>'; print_r($payval);
                $payval = $payval->toArray();?>
            <tr >
               <td><?php echo ($perPage * $page) + $key + 1 ?></td>
               <td><?php echo $payval['user']['username'];?></td>
              <td><?php echo $payval['UserProfiles']['restaurant_name'];?></td>
              <td><input type="button" name="view" value="view" id="<?php echo $payval["order_id"]; ?>" class="btn btn-info btn-xs view_data" /></td>
               <td><?php echo $payval['transaction_id'];?></td>
              <td><?php echo $payval['total_amount'];?></td>
              <td><?php echo $payval['device_type'];?></td>
              <td><?php echo date('d-m-Y H:i:s', strtotime($payval['created']));?></td>
                
            </tr>

            <?php
          }
        }else{ 
          ?>
            <tr>
              <td colspan="5" align="center">No records found</td>
            </tr>
          <?php    
          } 
          ?>
        </tbody>
        </table>
      </div>
      <div class="card-footer">
         <div class="paginator">
              <ul class="pagination">
                  <?= $this->Paginator->first('<< ' . __('first')) ?>
                  <?= $this->Paginator->prev('< ' . __('previous')) ?>
                  <?= $this->Paginator->numbers() ?>
                  <?= $this->Paginator->next(__('next') . ' >') ?>
                  <?= $this->Paginator->last(__('last') . ' >>') ?>
              </ul>
              <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
          </div>
      </div>

    </div>
  </div>
</section>

<div id="dataModal" class="modal fade">  
      <div class="modal-dialog">  
           <div class="modal-content">  
                <div class="modal-header">  
                     <button type="button" class="close" data-dismiss="modal">&times;</button>  
                     <h4 class="modal-title">Order Details</h4> 
                </div>  
                <div class="modal-body" id="employee_detail">  
                </div>  
                <div class="modal-footer">  
                     <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>  
                </div>  
           </div>  
      </div>  
 </div> 

<!-- <script src="https://code.jquery.com/jquery-1.9.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){ 
//alert('check'); 
      $('.view_data').click(function(){
      var orderUrl = "<?php //echo $this->Url->build(['controller' => 'users', 'action' => 'orderList']); ?>"; 
           var order_id = $(this).attr("id");  
           //alert(order_id); return false;
           $.ajax({  
                url:orderUrl, 
                method:"post",  
                data:{order_id:order_id},  
                success:function(data){
                //console.log(data);  
                     $('#employee_detail').html(data);  
                     $('#dataModal').modal("show");  
                }  
           });  
      });  
 });  
 </script> -->