<body>
	<div class="view-message" align="center">
		PLEASE VIEW THIS SYSTEM ON YOUR MOBILE PHONE.<br>
		THANK YOU.
	</div>
		<div class="whole-system">
			<div class="header-panel">
				<img src="<?= base_url().IMG ?>jacaexpress-logo.png">
				<div class="logout-btn">
					<a href="<?= base_url() ?>login/logout">
						<i class="fa fa-power-off fa-2x"></i>
					</a>
					<a href="#" id="refresh-content">
						<i class="fa fa-refresh fa-2x"></i>
					</a>
				</div>
			</div>
			<div class="header-title">
				DISPATCH DATE
				<div class="sub-title">
					<?= date('m-d-Y') ?>
				</div>
			</div>
			<div class="dispatch-wrapper" align="center">
				<div class="dispatch-list"></div>
			</div>
		</div>
		<?php require_once(SCRIPTS.'functions_js.php'); ?>
		<?php if(isset($script)) require_once(SCRIPTS.$script) ?>
	</body>
</html>
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h5 class="modal-title" id="myModalLabel">Waybill Details</h5>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-xs-6">
						Waybill #:
						<div class="field-data" id="detail-waybill-no"></div>
						Contact #:
						<div class="field-data" id="detail-contact"></div>
					</div>
					<div class="col-xs-6">
						Consignee :
						<div class="field-data" id="detail-consignee"></div>
						Address :
						<div class="field-data" id="detail-address"></div>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12">
						Description :
						<div class="field-data" id="detail-description"></div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>