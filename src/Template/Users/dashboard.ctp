<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Dashboard</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </div>
    </div>
  </div>
</section>
<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <div class="small-box bg-warning">
          <div class="inner">
            <h3><?= isset($user_counts) && !empty($user_counts) ? $user_counts : 0; ?></h3>
            <!-- <p> Total Users </p> -->
            <p> Total Cafe </p>
          </div>
          <div class="icon">
            <i class="ion ion-person-add"></i>
          </div>

           <?php echo $this->Html->link('<i class="fa fa-arrow-circle-right"></i><p> More info</p>', ['controller' => 'Users', 'action' => 'restautant-list'], ['escape' => false, 'class' => 'small-box-footer' ]) ?>
           <!-- <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a> -->
        </div>
      </div>
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <!-- <div class="small-box bg-success">
          <div class="inner">
            <h3><?= isset($category_counts) && !empty($category_counts) ? $category_counts : 0; ?></h3>
            <p> Total Categories </p>
          </div>
          <div class="icon">
            <i class="ion ion-stats-bars"></i>
          </div>
          <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div> -->
      </div> 
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <!-- <div class="small-box bg-info">
          <div class="inner">
            <h3><?= isset($child_category_counts) && !empty($child_category_counts) ? $child_category_counts : 0; ?></h3>
            <p> Child Categories </p>
          </div>
          <div class="icon">
            <i class="ion ion-bag"></i>
          </div>
          <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div> -->
      </div>
      <div class="col-lg-3 col-6">
        <!-- small box -->
        <!-- <div class="small-box bg-danger">
          <div class="inner">
            <h3><?= isset($sub_child_category_counts) && !empty($sub_child_category_counts) ? $sub_child_category_counts : 0; ?></h3>
            <p> Sub Child Categories </p>
          </div>
          <div class="icon">
            <i class="ion ion-pie-graph"></i>
          </div>
          <a href="#" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>
        </div> -->
      </div>
      <!-- ./col -->
    </div>
  </div>
</section>