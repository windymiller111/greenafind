<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Restaurant List</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false]);?></li>
          <li class="breadcrumb-item active">Cafe List</li>
        </ol>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <?php //echo $this->Html->link('Add Restaurant', ['controller' => 'users', 'action' => 'addRestaurant'],['class' => 'pull-left btn btn-primary']); ?>
      </div>

      <div class="card-body table-responsive p-0">
        <table class="table table-hover" id="library_categories">
          <thead>
            <tr>
              <th>SNo</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Restaurant</th>
              <th>Email</th>              
              <th>Phone</th>
              <th>Image</th>
              <th>Status</th>
              <th>Average Rating</th>
              <th>Rating</th>
			  <th>Created</th>
			  <th>View</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php 
           if(!empty($usersData)){
            $perPage = $this->Paginator->param('perPage');
            $page = $this->Paginator->param('page') - 1;
            	foreach ($usersData as $key => $userval) {?>
            <tr>
               <td><?php echo ($perPage * $page) + $key + 1 ?></td>
               <td><?php echo $userval->firstname;?></td>
              <td><?php echo $userval->lastname;?></td>
               <td><?php echo $userval->user_profile->restaurant_name;?></td>

               <td><?php echo $userval->email;?></td>
               <td><?php echo $userval->user_profile->phone_number;?></td>
               <td>
                 <?php if (!empty($userval->user_profile->profile_image)) {
                  echo $this->Html->image(base_url . '' . 'img/restaurant_uploads/'.$userval->user_profile->profile_image, array('width'=>'100px', 'alt' => 'green warrior',));
                }else{
                  echo $this->Html->image(base_url . '' . 'img/no-image.png', array('width'=>'100px', 'alt' => 'green warrior'));
                  }
                ?>
                </td>
				
               <td>
               <?php $data = $this->Common->checkOrder($userval->id, 3);
                if ($userval->status == 1) {
                    if($data == 0){
                      echo $this->Html->link("Active", array('action' => 'change_status', $userval->id, '0'), array('class' => 'btn btn-success', 'title' => 'Click here to Inactive', 'confirm' => 'Are you sure want to Inactive ?', 'escape' => false));
                    }else{
                       echo $this->Html->link("Active", array('action' => '', $userval->id, '0'), array('class' => 'btn btn-success', 'title' => 'Click here to Inactive', 'confirm' => 'You can not be Inactive, this restaurant order is not complete ?', 'escape' => false));
                    }
				} else {
               		echo $this->Html->link("Inactive", array('action' => 'change_status', $userval->id, '1'), array('class' => 'btn btn-danger', 'title' => 'Click here to Active', 'confirm' => 'Are you sure want to Active ?', 'escape' => false));
               		}
               	?>
               		
               	</td>
                 <?php $avgRating = $this->Common->getRatings($userval->id);?>
                 <?php $avg = (isset($avgRating) && !empty($avgRating)) ? $avgRating['rating'] : 0;?>
                <td><?php echo round($avg, 1);?></td>

                 <td><?php echo $this->Html->link("Rating", array('action' => 'userRating', $userval->id), array('class' => 'btn btn-primary', 'target' => '')); ?></td>

				<td><?php echo date('d-m-Y', strtotime($userval->created));?></td>
                 
                  <td>
                   <?php echo $this->Html->link('View', ['action' => 'viewRestaurant', $userval->id], ['escape' => false]); ?>
				   
				   <?php /*echo $this->Form->postLink('Delete',['action' => 'deleteRestaurant', $userval->id],['confirm' => 'Are you sure want to delete?', 'escape' => false]);*/ ?>
                  </td>
				   <td>
               <?php
                if ($userval->is_approved == '1') {
                  echo $this->Html->link("Approved", array('action' => 'change_approval', $userval->id, '0'), array('class' => 'btn btn-success', 'title' => 'Click here to disapproved', 'confirm' => 'Are you sure want to disapproved ?', 'escape' => false));
                } else {
                  echo $this->Html->link("Disapproved", array('action' => 'change_approval', $userval->id, '1'), array('class' => 'btn btn-danger', 'title' => 'Click here to approved', 'confirm' => 'Are you sure want to approved ?', 'escape' => false));
                }
                ?>
                  
                </td>
                 
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