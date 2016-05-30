<div class="container">
	<div class="row main">
		<div class="main-login main-center">
		<div class="panel-heading">
           <div class="panel-title text-center">
           		<h1 class="title">Git Manager</h1>
           		<hr />
           	</div>
        </div> 
			<form method="post" class="form-horizontal">
				<div class="form-group">
					<?php if(isset($error)){ ?>
						<div role="alert" class="alert alert-danger alert-dismissible fade in"> <button aria-label="Close" data-dismiss="alert" class="close" type="button"><span aria-hidden="true">×</span></button> <p class="text-center"><strong>Error:</strong> <?php echo $error; ?> </p></div>
					<?php } ?>
					<div class="input-group">
						<span class="input-group-addon"><i aria-hidden="true" class="fa fa-user fa-lg"></i></span>
						<input type="text" placeholder="Enter your Username" id="username" name="username" class="form-control text-center">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<span class="input-group-addon"><i aria-hidden="true" class="fa fa-lock fa-lg"></i></span>
						<input type="password" placeholder="Enter your Password" id="password" name="password" class="form-control text-center">
					</div>
				</div>
				<div class="form-group">
					<button class="btn btn-primary btn-lg btn-block login-button" type="submit">Login</button>
				</div>
			</form>
		</div>
	</div>
</div>