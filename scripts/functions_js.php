<script language="javascript" type="text/javascript">
	function bindTab(className)
	{
		$('.'+className).on('keydown',function(e){
			var index = $('.'+className).index(this);
			var length = $('.'+className).length;

			if (e.keyCode == 13 && ((length % (index+1)) != 0 || index == 0)) {

				e.preventDefault();
				$('.'+className).eq(index+1).focus();
			}
		});
	}


	function setCookie(c_name,value,exdays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	}

	function getCookie(c_name)
	{
		var i,x,y,ARRcookies=document.cookie.split(";");
		for (i=0;i<ARRcookies.length;i++)
		{
			x=ARRcookies[i].substr(0,ARRcookies[i].indexOf("="));
			y=ARRcookies[i].substr(ARRcookies[i].indexOf("=")+1);
			x=x.replace(/^\s+|\s+$/g,"");
			
			if (x==c_name)
			{
				return unescape(y);
			}
		}
	}

	function deleteCookie(name) {
   		setCookie(name,"",-1);
	}

	function messagebox(string,status)
	{
		var dom = "<div class='alert alert-"+status+"' role='alert'>"+string+"</div>";
		return dom;
	}

	$('#order').on('click',function(e){
		e.preventDefault();
		var value = $(this).html();
		value = value == 'ASC' ? 'DESC' : 'ASC';
		$(this).html(value);
	});
	
	function setDdllist(data,id)
	{
		var options = "";
    	for (var i = 0; i < data.length; i++) {
	        options += "<option value='"+data[i]['id']+"''>"+data[i]['name']+"</option>";
    	};

    	$('#'+id).html('').trigger("liszt:updated");
    	$('#'+id).append(options);
		$('#'+id).trigger("liszt:updated");
	}

	function clearDdllist(id)
	{
		$('#'+id).html('').trigger("liszt:updated");
	}

	function addNewRow()
	{
		var ok = true;
		var count = myjstbl.get_row_count() - 1;

		if (count > 0) {
			var id =  myjstbl.getvalue_by_rowindex_tdclass(count, 
												colarray["id"].td_class);
			if (id == 0) {
				ok = false;
			};
		}

		if (ok == false) return;

		var last_row_index = myjstbl.get_row_count();
		myjstbl.add_new_row();
		myjstbl.setvalue_to_rowindex_tdclass([last_row_index],
				last_row_index,colarray['number'].td_class);
	}

	function getStatusLabel(statusid)
	{
		var status = '';
		switch (statusid) {
			case '1':
				status ='<div class="stat stat-received">Received</div>';
				break;
			
			case '2':
				status ='<div class="stat stat-segregated">Segregated</div>';
				break;

			case '3':
				status ='<div class="stat stat-ondelivery">On Transit</div>';
				break;

			case '4':
				status ='<div class="stat stat-receivedbyconsignee">Received By Representative</div>';
				break;

			case '5':
				status ='<div class="stat stat-withproblem">Problematic</div>';
				break;

			case '6':
				status ='<div class="stat stat-onhold">On Hold</div>';
				break;

			case '7':
				status ='<div class="stat stat-pulloutbyshipper">Cancelled</div>';
				break;

			case '8':
				status ='<div class="stat stat-pullout">Pulled Out</div>';
				break;

			case '9':
				status ='<div class="stat stat-rts">Return to Shipper</div>';
				break;

			case '10':
				status ='<div class="stat stat-ondelivery">Forwarding</div>';		
				break;

			case '11':
				status ='<div class="stat stat-receivedbyconsignee">Forwarded</div>';		
				break;

			case '12':
				status ='<div class="stat stat-withproblem">Consignee Unknown</div>';
				break;

			case '13':
				status ='<div class="stat stat-withproblem">No one to Receive</div>';
				break;

			case '14':
				status ='<div class="stat stat-withproblem">Incorrect / Incomplete Details</div>';
				break;
		}

		return status;
	}

	function getStatusValue(label)
	{
		var status = 0;
		switch (label) {
			case '<div class="stat stat-received">Received</div>':
				status = 1;
				break;
			
			case '<div class="stat stat-segregated">Segregated</div>':
				status = 2;
				break;

			case '<div class="stat stat-ondelivery">On Transit</div>':
				status = 3;
				break;

			case '<div class="stat stat-receivedbyconsignee">Received By Representative</div>':
				status = 4;
				break;

			case '<div class="stat stat-withproblem">Problematic</div>':
				status = 5;
				break;

			case '<div class="stat stat-onhold">On Hold</div>':
				status = 6;
				break;

			case '<div class="stat stat-pulloutbyshipper">Cancelled</div>':
				status = 7;
				break;

			case '<div class="stat stat-pullout">Pulled Out</div>':
				status = 8;
				break;

			case '<div class="stat stat-rts">Return to Shipper</div>':
				status = 9;
				break;

			case '<div class="stat stat-ondelivery">Forwarding</div>':
				status = 10;
				break;

			case '<div class="stat stat-receivedbyconsignee">Forwarded</div>':
				status = 11;
				break;

			case '<div class="stat stat-withproblem">Consignee Unknown</div>':
				status = 12;
				break;

			case '<div class="stat stat-withproblem">No one to Receive</div>':
				status = 13;
				break;

			case '<div class="stat stat-withproblem">Incorrect / Incomplete Details</div>':
				status = 14;
				break;			
		}

		return status;
	}

	function buildErrorMessage(errorMessage)
	{
		var string = '';

		for (var i = 0; i < errorMessage.length; i++) {
			string += "<i class='fa fa-exclamation-triangle' />&nbsp;&nbsp;"+errorMessage[i]+"<br/>";
		};

		return string;
	}

	function validator(values,validation_type,field_name)
	{
		var errorMsg = [];

		for (var i = 0; i < values.length; i++) {
			switch(validation_type[i])
			{
				case 'NOT EMPTY':
					values[i] = String(values[i]).replace(/,/g,'');
					if (values[i] == '' || $.trim(values[i]) == '') {
						errorMsg.push(field_name[i]+' should not be empty!');
					};
					break;

				case 'NOT EMPTY / CODE':
					var reg = /[^A-Za-z0-9-]/;
					var result = reg.test(values[i]);
					values[i] = String(values[i]).replace(/,/g,'');

					if (values[i] == '' || $.trim(values[i]) == '') {
						errorMsg.push(field_name[i]+' should not be empty!');
					}else if (result) {
						errorMsg.push(field_name[i]+' should only contain letters and numbers!');
					};
					break;

				case 'NUMERIC':
					if (values[i] != '') {
						var reg = /[^0-9]/;
						values[i] = String(values[i]).replace(/,/g,'');
						var result = reg.test(values[i]);

						if (result) {
							errorMsg.push(field_name[i]+' should only contain numbers!');
						};
					};
					
					break;

				case 'NOT EMPTY / NUMERIC':
					var reg = /[^0-9]/;
					values[i] = String(values[i]).replace(/,/g,'');
					var result = reg.test(values[i]);
					

					if (values[i] == '' || $.trim(values[i]) == '') {
						errorMsg.push(field_name[i]+' should not be empty!');
					}else if (result) {
						errorMsg.push(field_name[i]+' should only contain numbers!');
					};
					break;

				case 'DECIMAL':
					if (values[i] != '') {
						var reg = /[^0-9.]/;
						values[i] = String(values[i]).replace(/,/g,'');
						var result = reg.test(values[i]);
						var res = values[i].match(/\./g);

						if (result) {
							errorMsg.push(field_name[i]+' should only contain numbers!');
						}else if (res !== null) {
							if (res.length > 1) {
								errorMsg.push(field_name[i]+' should only contain one decimal place!');
							};
						};
					}
					break;

				case 'NOT EMPTY / DECIMAL':
					var reg = /[^0-9.]/;
					values[i] = String(values[i]).replace(/,/g,'');
					var result = reg.test(values[i]);
					var res = values[i].match(/\./g);

					if (values[i] == '' || $.trim(values[i]) == '') {
						errorMsg.push(field_name[i]+' should not be empty!');
					}else if (result) {
						errorMsg.push(field_name[i]+' should only contain numbers!');
					}else if (res !== null) {
						if (res.length > 1) {
							errorMsg.push(field_name[i]+' should only contain one decimal place!');
						};
					};
					
					break;


				case 'EMAIL':
					var pattern = new RegExp(/^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);
    				var result 	= pattern.test(values[i]);

    				if (!result) {
    					errorMsg.push(field_name[i]+' should be a valid email format!');
    				};
					break;
			}
		};

		return errorMsg;
	}

	function show_loading(appear){
        if(appear==1){
            $("body .in").removeClass("in");
            $("html").css("overflow", "hidden");
            $("body").after("<div class='back-drop'></div><div class='loading-pink' align='center'><img class='loading-img' src='<?= base_url().IMG ?>loading-pink.gif'></div>");
            $(".back-drop").show();
            $(".loading-img").fadeIn(1500);
        }else{
            $("html").css("overflow", "auto");
            $(".back-drop").show();
            $(".loading-img").fadeOut(1000);
            $(".back-drop, .loading-pink").remove();
        }
    }
    
    function bindNumeric(element,isDecimal)
    {
    	var keyCodes = [46, 8, 9, 27, 13, 110];
    	if (isDecimal) {
    		keyCodes.push(190);
    	};

    	$(element).on('keydown',function (e) {
	        if ($.inArray(e.keyCode,keyCodes) !== -1 ||
	            (e.keyCode == 65 && e.ctrlKey === true) || 
	            (e.keyCode == 86 && e.ctrlKey === true) || 
	            (e.keyCode == 67 && e.ctrlKey === true) || 
	            (e.keyCode >= 35 && e.keyCode <= 40)) {
	                 return;
	        }
	        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
	            e.preventDefault();
	        }
	    });
    }

    function recomputeRowcount(object,object_array)
    {
    	for (var i = 1; i < object.get_row_count(); i++) {
    		object.setvalue_to_rowindex_tdclass([i],i,object_array["number"].td_class);
    	};
    }

</script>