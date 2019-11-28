<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Change Password</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><?php echo $this->Html->link('Dashboard', ['controller' => 'users', 'action' => 'dashboard'], ['escape' => false]);?></li>
          <li class="breadcrumb-item active">Change Password</li>
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
      
      <?php echo $this->Form->create('', ['id' => "changPassword", 'name' => "changPassword"]); ?>      
      <div class="card-body">        
        <div class="form-group">
          <?php echo $this->Form->password("oldpassword", array("class" => "form-control ", "placeholder" => "Enter old password", "label" => false, "autofocus" => "true")); ?>
        </div>

        <div class="form-group">
           <?php echo $this->Form->password("password", array("class" => "form-control", "placeholder" => "Enter new password", "label" => false, 'autocomplete' => 'new-password', "autofocus" => "true")); ?>
        </div>
        <div class="form-group">
          <?php echo $this->Form->password("confirm_password", array("class" => "form-control", "placeholder" => "Enter confirm password", "label" => false, "autofocus" => "true")); ?>
        </div>

        <div class="form-group">
          <?php echo $this->Form->button('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
      </div>
      <?php echo $this->Form->end(); ?>
      <div class="card-footer" style="border-top: 1px solid rgba(0,0,0,.125);">
        &nbsp;&nbsp;                
      </div>
    </div>
  </div>
</section>

