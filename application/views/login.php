	<body>
		<div class="view-message" align="center">
			PLEASE VIEW THIS SYSTEM ON YOUR MOBILE PHONE.<br>
			THANK YOU.
		</div>
		<div class="whole-system">
			<div class="header-panel" align="center">
				<img src="<?= base_url().IMG ?>jacaexpress-logo.png">
			</div>
			<div class="header-title">
				RIDER DISPATCH LOGIN
			</div>
			<div class="login-wrapper" align="center">
				<div class="login-panel" align="left">
					<div id ="messagebox"></div>
					<div class="form-group">
						Username:
						<input type="text" class="form-control" id="username">
					</div>
					<div class="form-group">
						Password:
						<input type="password" class="form-control" id="password">
					</div>
					<div class="form-group" align="right">
						<button class="btn btn-primary" id="login">Login</button>
					</div>
				</div>
			</div>
		</div>
		<?php require_once(SCRIPTS.'functions_js.php'); ?>
		<?php if(isset($script)) require_once(SCRIPTS.$script) ?>
	</body>
</html>
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<h4 class="modal-title" id="myModalLabel">Select a Branch to Login</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label>Select a Branch: </label>
					<select id="branch" class="form-control"></select>
				</div>
				<center>
					<div id ="messagebox2"></div>
				</center>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary" id="proceed">Proceed</button>
			</div>
		</div>
	</div>
</div>