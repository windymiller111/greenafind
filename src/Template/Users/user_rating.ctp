<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Restaurant Ratings By Customers </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false]);?></li>
          <li class="breadcrumb-item active">Restaurant Ratings</li>
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
      <div class="card-body table-responsive p-0">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>SNo</th>
              <th>User Name</th>
              <th>User Image</th>
				<?php
				$i= 1;
					foreach ($Quesdata as $key => $quesvalue) { ?>
					<th title ='<?php echo $quesvalue->question ?>'>Q<?php echo $i; ?></th>
					<?php $i++;
				}?>
              <th>Rating</th>
              <th>Created</th>
            </tr>
          </thead>
          <tbody>
          <?php 
           if(!$RatingList->isEmpty()){
            $perPage = $this->Paginator->param('perPage');
            $page = $this->Paginator->param('page') - 1;
            	foreach ($RatingList as $key => $ratingval) {
                $ratingval = $ratingval->toArray();?>
            <tr>
               <td><?php echo ($perPage * $page) + $key + 1 ?></td>
               <td><?php echo $ratingval['username'];?></td>
              
              <td>
                 <?php if (!empty($ratingval['user_profile']['profile_image'])) {
                  echo $this->Html->image(base_url . '' . 'img/customer_uploads/'.$ratingval['user_profile']['profile_image'], array('width'=>'100px', 'height'=>'100px', 'alt' => 'green warrior',));
                }else{
                  echo $this->Html->image(base_url . '' . 'img/no-image.png', array('width'=>'100px', 'alt' => 'green warrior'));
                  }
                ?>
                </td>
				<?php
					if(!empty($ratingval['reviews'])){
					foreach ($ratingval['reviews'] as $key => $reviewvalue) {?>
					<td><?php echo $reviewvalue['review_option']['option'];?></td>
					<?php }
					}?>
                <td><?php echo $ratingval['Ratings']['rating'];?></td>
               <td><?php echo date('d-m-Y', strtotime($ratingval['Ratings']['created']));?></td>
                
            </tr>
            <?php
            
            }
          }else{ 
          ?>
            <tr>
              <td colspan="10" align="center">No records found</td>
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