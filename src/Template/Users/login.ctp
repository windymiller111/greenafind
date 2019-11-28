<div class="card">
	<div class="card-body login-card-body">
      <p class="login-box-msg">Sign in to start your session</p>

     <?php echo $this->Form->create(); ?>
        <div class="input-group mb-3">
        
        <?php echo $this->Form->control('email',['templates' => ['inputContainer' => '{{content}}'], 'class' => 'form-control', 'placeholder' => 'Email', 'label' => false]); ?>
          <div class="input-group-append">
              <span class="fa fa-envelope input-group-text"></span>
          </div>
        </div>
        <div class="input-group mb-3">
         <?php echo $this->Form->control('password',['templates' => ['inputContainer' => '{{content}}'], 'class' => 'form-control', 'placeholder' => 'Password', 'label' => false]); ?>
          <div class="input-group-append">
              <span class="fa fa-lock input-group-text"></span>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
           
          </div>
          <!-- /.col -->
          <div class="col-4">
            <?php echo $this->Form->button('Sign In', ['templates' => ['inputContainer' => '{{content}}'], 'class' => 'btn btn-primary btn-block']); ?>
          </div>
          <!-- /.col -->
        </div>
     <?php echo $this->Form->end(); ?>
     </div>
   </div>