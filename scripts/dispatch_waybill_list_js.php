<script type="text/javascript">

	var isProcessing = false;

	refreshDispatchWaybillContent();

	$(document.body).on('click', '.modal-pop-up', function(e){
		e.preventDefault();

		var waybillNumber = $(this).parent().find('a .waybill-no').html().substr(1);
		var consigneeName = $(this).parent().find('.consignee').val();
		var contact = $(this).parent().find('.contact').val();
		var address = $(this).parent().find('.address').val();
		var description = $(this).parent().find('.description').val();

		$('#detail-waybill-no').html(waybillNumber);
		$('#detail-consignee').html(consigneeName);
		$('#detail-contact').html(contact);
		$('#detail-address').html(address);
		$('#detail-description').html(description);

		$('#myModal').modal('show');
	});

	$(document.body).on('click', '#refresh-content', function(e){
		e.preventDefault();
		refreshDispatchWaybillContent();
	});

	$(document.body).on('click', '.edit-action, .cancel-action', function(){
		toggleElementVisibility(this);
	});

	$(document.body).on('click', '.save-action', function(){
		
		var that = this;

		if (isProcessing)
			return;

		var remarksInput = $(this).parent().parent().find('.text-remarks').val();
		var statusInput = $(this).parent().parent().find('.drp-status').val();
		var waybillIdInput = $(this).parent().parent().find('.waybill-id').val();

		var dataParameters = 	{ 
									request_action : 'encode_waybill_status',
									remarks : remarksInput,
									status : statusInput,
									waybill_id : waybillIdInput
								};

		$.ajax({
			type: "POST",
			dataType : 'JSON',
			data: 'data=' + JSON.stringify(dataParameters),
			success: function(response) 
			{
				if (response.error != '')
					alert(response.error);
				else
				{
					$(that).parent().parent().find('.spn-status').html(getStatusLabel(statusInput));
					$(that).parent().parent().find('.spn-remarks').html(remarksInput);
					toggleElementVisibility(that);
				}

				isProcessing = false;
			}       
		});
	});

	function buildHTMLWaybillRows(dataSet)
	{
		var htmlContent = "";

		for (var i = 0; i < dataSet.length; i++) 
		{
			htmlContent += '<div class="single-tab">' + 
								'<a href="#" class="modal-pop-up"><div class="waybill-no">#' + dataSet[i].waybill + '</div></a>' +
								'<div class="dispatch-save">' +
								'	<button class="btn btn-primary edit-action"><i class="fa fa-edit fa-lg"></i></button>' +
								'	<button class="btn btn-danger cancel-action hide-element"><i class="fa fa-close fa-lg"></i></button>' +
								'	<button class="btn btn-success save-action hide-element"><i class="fa fa-save fa-lg"></i></button>' +
								'</div>' +
								'<div class="each-field">' +
								'	Remarks:' +
								'	<div class="spn-remarks">' + dataSet[i].remarks + '</div>' +
								'	<input type="text" class="text-remarks form-control" value="' + dataSet[i].remarks + '">' +
								'</div>' +
								'<div class="each-field status">' +
								'	Status:' +
								'	<span class="spn-status">' + dataSet[i].status + '</span>' + 
								'	' + buildStatusDropdown(dataSet[i].status) +
								'</div>' +
								'<input type="hidden" class="consignee" value="' + dataSet[i].consignee + '">' +
								'<input type="hidden" class="contact" value="' + dataSet[i].contact + '">' +
								'<input type="hidden" class="address" value="' + dataSet[i].address + '">' +
								'<input type="hidden" class="waybill-id" value="' + dataSet[i].waybill_id + '">' +
								'<input type="hidden" class="description" value="' + dataSet[i].description + '">' +
							'</div>';
		};

		$('.dispatch-list').html(htmlContent);
	}

	function refreshDispatchWaybillContent()
	{
		var dataParameters = { request_action : 'get_dispatch_waybill_list' };

		$.ajax({
			type: "POST",
			dataType : 'JSON',
			data: 'data=' + JSON.stringify(dataParameters),
			success: function(response) 
			{
				if (response.data.length === 0)
					alert('No waybill found!');
				else
					buildHTMLWaybillRows(response.data);
			}       
		});
	}

	function buildStatusDropdown(selectedValue)
	{
		selectedValue 		= getStatusValue(selectedValue);
		var dropdownHTML 	= '<select class="drp-status form-control">';

		var statusCategory = 	[
									{
										textContent : 'Received By Representative',
										value :  4
									},
									{
										textContent : 'Problematic',
										value : 5
									},
									{
										textContent : 'Consignee Unknown',
										value : 12
									},
									{
										textContent : 'No one to Receive',
										value : 13
									},
									{
										textContent : 'Incorrect / Incomplete Details',
										value : 14
									}
								];

		for (var i = 0; i < statusCategory.length; i++) 
		{
			var isSelected = '';
			if (selectedValue == statusCategory[i].value)
				isSelected = 'selected';

			dropdownHTML += '<option value="' + statusCategory[i].value + '" ' + isSelected + '>' + statusCategory[i].textContent + '</option>';
		};

		dropdownHTML += '</select>';

		return dropdownHTML;
	}

	function toggleElementVisibility(element)
	{
		$(element).parent().parent().find('.spn-status').toggle();
		$(element).parent().parent().find('.spn-remarks').toggle();
		$(element).parent().parent().find('.edit-action').toggle();
		$(element).parent().parent().find('.text-remarks').toggle();
		$(element).parent().parent().find('.drp-status').toggle();
		$(element).parent().parent().find('.cancel-action').toggle();
		$(element).parent().parent().find('.save-action').toggle();
	}
</script>