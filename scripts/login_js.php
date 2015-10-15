<script language="javascript" type="text/javascript">

	bindTab('nexttab');
	var flag = 0;

	$('#login').on('click',function(e){
		e.preventDefault();
		validateUser();
	});

	$('#password').on('keydown',function(e){
		if (e.keyCode == 13) {
			e.preventDefault();
			validateUser();
		};
	});

	$('#proceed').on('click',function(e){
		finalVerification();
	});

	$('#myModal').on('hidden.bs.modal', function (e) {
		$('#login').removeAttr('disabled');
		flag = 0;
	});

	function validateUser()
	{
		if (flag == 1) { return; };
		flag = 1;

		clear_messagebox();
		var username_val 	= $("#username").val();
		var password_val 	= $("#password").val();

		var arr = 	{ 
						fnc: 'check_login', 
						user: username_val, 
						pass: password_val
					};

		$.ajax({
			type: "POST",
			dataType : 'JSON',
			data: 'data=' + JSON.stringify(arr),
			success: function(data) {
				if (data.error != '') {
					$("#messagebox").html(messagebox(data.error,'danger'));
					$("#username").val('');
					$("#password").val('');
				}
				else
				{
					if (data.type == 'shipper') {
						$("#messagebox").html(messagebox('Connected!','success'));
						window.location = '<?= base_url() ?>shipperwaybill/list';
					}else{
						$("#messagebox").html('');
						getBranchList('branch',data.userid);
						$('#submit').attr('disabled','disabled');
					}
					
				}
				flag = 0;
			}       
		});
	}

	function getBranchList(id,userid_val)
	{
		var options = "";
		var arr = 	{ 
						fnc : 'get_user_branch_list',
						userid : userid_val
					};

		$('#'+id).html("");

		$.ajax({
			type: "POST",
			dataType : 'JSON',
			data: 'data=' + JSON.stringify(arr),
			success: function(data) {
				if (data.length != 1) {
					options += "<option value='0'></option>";
				};

				for (var i = 0; i < data.length; i++) {
					options += "<option value='"+data[i]['id']+"''>"+data[i]['name']+"</option>";
				};
				$('#'+id).append(options);

				if (data.length != 1) {
					$('#myModal').modal('show');
				}else{
					finalVerification();
				}  	
			}       
		});
	}

	function finalVerification()
	{
		if (flag == 1) { return; };
		flag = 1;

		var username_val 	= $("#username").val();
		var password_val 	= $("#password").val();
		var branchid_val 	= $("#branch").val();

		if (branchid_val == 0 ) {
			$("#messagebox2").html(messagebox("Please select a branch!",'danger'));
			flag = 0;
			return;
		};

		var arr = 	{ 
						fnc: 'final_verification', 
						user: username_val, 
						pass: password_val,
						branchid : branchid_val
					};

		$.ajax({
			type: "POST",
			dataType : 'JSON',
			data: 'data=' + JSON.stringify(arr),
			success: function(data) {
				if (data.error != '') {
					$("#messagebox").html(messagebox(data.error,'danger'));
				}
				else
				{
					$('#myModal').hide();
					$("#messagebox").html(messagebox('Connected!','success'));
					window.location = '<?= base_url() ?>waybill';
				}
				flag = 0;
			}       
		});
	}

	function clear_messagebox(){
		$('#messagebox2').html('');
		$("#messagebox").html('');
	}
</script>