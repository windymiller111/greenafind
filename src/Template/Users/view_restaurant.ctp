<?php //print_r($users); die;?>
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>View Restaurant</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false]);?></li>
          <li class="breadcrumb-item active">View Restaurant</li>
        </ol>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card card-default">
      <div class="card-header">
        <?php echo $this->Html->link('Back', ['controller' => 'users', 'action' => 'restautantList'],['class' => 'btn btn-primary']); ?>
      </div>
      <?php echo $this->Form->create($users) ?>      
      <div class="card-body">        
       
       <table class="table table-striped table-advance table-hover">
        <tr>
            <th scope="row"><?= __('Firstname') ?></th>
            <td><?php echo $users->firstname ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Lastname') ?></th>
            <td><?php echo $users->lastname ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Email') ?></th>
            <td><?php echo $users->email ?></td>
        </tr>
         <tr>
            <th scope="row"><?= __('Restaurant Name') ?></th>
            <td><?php echo $users->user_profile->restaurant_name ?></td>
        </tr>
         <tr>
            <th scope="row"><?= __('Phone Number') ?></th>
            <td><?php echo $users->user_profile->phone_number ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Address') ?></th>
            <td><?php echo $users->user_profile->address ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Description') ?></th>
            <td><?php echo $users->user_profile->description ?></td>
        </tr>
        <tr>
          <th scope="row"><?= __('Image') ?></th>
          <td>
               <?php if (!empty($users->user_profile->profile_image)) {
                echo $this->Html->image(base_url . '' . 'img/restaurant_uploads/'.$users->user_profile->profile_image, array("width"=>"100", 'alt' => 'green warrior',));
              }else{
                echo $this->Html->image(base_url . '' . 'img/no-image.png', array("width"=>"100", 'alt' => 'green warrior'));
                }
              ?>
              </td>
        </tr>   
        
  </table>        
  <table class="table table-striped table-advance table-hover">
    <tr>
         
          <th colspan="4" ><?= __('View Restaurant Timings') ?></th>
    </tr>
    <?php if(!empty($users->restaurant_times)){?>
      <tr>
            <th scope="row" ><?= __('Weekday') ?></th>
            <th scope="row" ><?= __('Start Time') ?></th>
            <th scope="row" ><?= __('End Time') ?></th>
            <th scope="row" ><?= __('Restaurant Status') ?></th>
        </tr>


        <?php foreach ($users->restaurant_times as $key => $value) {
          if($value->restaurant_status == 1){$status = 'open';}else{$status = 'close';}?>
        <tr>
            <td><?php echo $value->weekday; ?></td>
           
           <td> <?php echo $value->start_time->format('h:i A');?></td>
           <td> <?php echo $value->end_time->format('h:i A');?></td>
           <td> <?php echo $status;?></td>
        </tr>
        <?php }
      }else{ ?>
      <tr>
        <th colspan="4" style="text-align: center;">No Timing Yet</th>
      </tr>
    <?php }?>
</table>

        <div class="form-group">
          <?php //echo $this->Form->button('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
      </div>
      <?php echo $this->Form->end() ?>
      <div class="card-footer" style="border-top: 1px solid rgba(0,0,0,.125);">
        &nbsp;&nbsp;                
      </div>
    </div>
  </div>
</section>