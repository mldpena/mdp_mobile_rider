// JavaScript Document


function my_ajax_exephp(phpUrl, respfunc){
	var ajaxRequest;
	try{
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				alert("Your browser broke!");
				return false;
			}
		}
	}
	
	ajaxRequest.onreadystatechange=function()
	{
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200)
		{
			var reply = ajaxRequest.responseText;
			respfunc(reply);
		}
	}
	ajaxRequest.open("GET",phpUrl,true);
	ajaxRequest.send();
}


function my_ajax_exephp_async(phpUrl)
{
	var ajaxRequest2;
	try{
		ajaxRequest2 = new XMLHttpRequest();
	} catch (e){
		try{
			ajaxRequest2 = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest2 = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				alert("Your browser broke!");
				return false;
			}
		}
	}
	
	ajaxRequest2.open("GET",phpUrl,false);
	ajaxRequest2.send(null);
	
	if (ajaxRequest2.status === 200) {
		return ajaxRequest2.responseText;
	}
}

function my_autocomplete(sel, url, func)
{
	$(sel).unbind( "autocomplete" );
	$(sel).live("focus", function (event) {
		$(this).autocomplete({
			source: function(req, add){
				$.getJSON(url, req, function(data) {
					var suggestions = [];
					$.each(data, function(i, val){
						suggestions.push(val.prod);
					});
					add(suggestions);
				})
				.error(function() {
					add("");
				});
				
			},
			selectFirst: true,
			minLength: 1,
			select: func
		});
	});
	
	$(sel).unbind( "keypress" );
	$(sel).live("keypress", function(e){
		if(e.keyCode == 13){
			$(this).blur();
		}
	})
	
}
var request;
function my_autocomplete_add(sel, url_ac, options ){
	//fnc_callback(this,label, value, ret_datas, error)
	//error = "not selected" or customized error
	var enable_add = typeof options.enable_add !== 'undefined' ? options.enable_add : false;
	var adding_style = typeof options.adding_style !== 'undefined' ? options.adding_style : "immediate";
	var adding_popup_id = typeof options.adding_popup_id !== 'undefined' ? options.adding_popup_id : "";
	var url_add = typeof options.url_add !== 'undefined' ? options.url_add : "";
	var struct_add = typeof options.struct_add !== 'undefined' ? options.struct_add : "";
	var fnc_callback = typeof options.fnc_callback !== 'undefined' ? options.fnc_callback : "";
	var fnc_render = typeof options.fnc_render !== 'undefined' ? options.fnc_render : "";

	$(sel).unbind( "autocomplete" );
	$(sel).live("focus", function (event) {
		$(this).autocomplete({
            autoFocus: true,
            delay: 0,
			source: function(req, add){

				var arr = 	{ fnc : 'autocomplete', term : req.term }
				if (request) { request.abort(); };
				request = $.ajax({
		            type: "POST",
		            dataType : 'JSON',
		            data: 'data=' + JSON.stringify(arr),
		            success: function(data) {
		            	var suggestions = [];
						$.each(data, function(i, val){ suggestions.push(val); });
						if(enable_add && suggestions.length == 0) {
							suggestions.push({ label: req.term, value: 0, ret_datas:[], error:"add new" });
						}
						add(suggestions);
		        	}       
		    	});
			},
			focus: function( event, ui ) {
				//$(this).val( ui.item.label );
				return false;
			},
			change: function( event, ui ) {
				var newlabel = $(this).val();
				if( ui.item == null ){
					var label = newlabel;
					var value = 0;
					var ret_datas = [];
					var error = "not selected";
					fnc_callback(this, label, value, ret_datas, error);
				}
				return false;
			},
			minLength: 1,
			select: function(event, ui) {
				$(this).val( ui.item.label );
				var this_d = this;
				if( enable_add && ui.item.value == 0)
				{
					var newlabel = ui.item.label;
					struct_add.name = newlabel;
					
					if(adding_style == "immediate") {
						$.get(url_add, struct_add,
							function(reply){
								var patt1 = new RegExp("Saved");
								if (patt1.test(reply))
								{
									var datas = reply.split("<nssep>");
									var label = newlabel;
									var value = datas[1];
									var ret_datas = datas.slice(2, datas.length);
									var error = "";
									fnc_callback(this_d,label, value, ret_datas, error);
								}
								else
								{
									var label = newlabel;
									var value = 0;
									var ret_datas = [];
									var error = reply;
									fnc_callback(this_d,label, value, ret_datas, error);
								}
							});
					}
					else if (adding_style == "popup"){
						if(struct_add.ret_vals == "row_index"){
							var row_index = $(this_d).parent().parent().index();
							struct_add.ret_vals = row_index;
						}
						$(adding_popup_id).load(url_add, struct_add);
					}
				}
				else if( !enable_add && ui.item.value == 0){
					var label = newlabel;
					var value = 0;
					var ret_datas = [];
					var error = "not selected";
					fnc_callback(this_d,label, value, ret_datas, error);
				}
				else
				{
					var label = ui.item.label;
					var value = ui.item.value;
					var ret_datas = ui.item.ret_datas;
					var error = "";
					fnc_callback(this_d,label, value, ret_datas, error);
				}
				return false;
			}
		}).data( "autocomplete" )._renderItem = function( ul, item ) {
			if( fnc_render != "" ) {
				return fnc_render(ul, item);
			}
			else {
				var appendstr = "";
				if(enable_add && item.error == "add new"){
					appendstr = "Add New: ";
				}
				return $( "<li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + appendstr + item.label + "</a>" )
					.appendTo( ul );
			}
		};
		
	});
	
	
}

var request2;
function my_autocomplete_add_single(sel, url_ac, options ){
	//fnc_callback(this,label, value, ret_datas, error)
	//error = "not selected" or customized error
	
	var enable_add = typeof options.enable_add !== 'undefined' ? options.enable_add : false;
	var adding_style = typeof options.adding_style !== 'undefined' ? options.adding_style : "immediate";
	var adding_popup_id = typeof options.adding_popup_id !== 'undefined' ? options.adding_popup_id : "";
	var url_add = typeof options.url_add !== 'undefined' ? options.url_add : "";
	var struct_add = typeof options.struct_add !== 'undefined' ? options.struct_add : "";
	var fnc_callback = typeof options.fnc_callback !== 'undefined' ? options.fnc_callback : "";
	var fnc_render = typeof options.fnc_render !== 'undefined' ? options.fnc_render : "";
	
	$(sel).unbind( "autocomplete" );
	//$(sel).live("focus", function (event) {
		$(sel).autocomplete({
			autoFocus : true,
			delay : 0,
			source: function(req, add){
				if (request2) { request2.abort(); };
				request2 =  $.getJSON(url_ac, req, function(data) {
					var suggestions = [];
					$.each(data, function(i, val){ suggestions.push(val); });
					if(enable_add && suggestions.length == 0) {
						suggestions.push({ label: req.term, value: 0, ret_datas:[], error:"add new" });
					}
					add(suggestions);
				})
				.error(function() {
					add("");
				});
			},
			focus: function( event, ui ) {
				//$(this).val( ui.item.label );
				return false;
			},
			change: function( event, ui ) {
				var newlabel = $(this).val();
				if( ui.item == null ){
					var label = newlabel;
					var value = 0;
					var ret_datas = [];
					var error = "not selected";
					fnc_callback(this, label, value, ret_datas, error);
				}
				return false;
			},
			minLength: 1,
			select: function(event, ui) {
				$(this).val( ui.item.label );
				var this_d = this;
				if( enable_add && ui.item.value == 0)
				{
					var newlabel = ui.item.label;
					struct_add.name = newlabel;
					
					if(adding_style == "immediate") {
						$.get(url_add, struct_add,
							function(reply){
								var patt1 = new RegExp("Saved");
								if (patt1.test(reply))
								{
									var datas = reply.split("<nssep>");
									var label = newlabel;
									var value = datas[1];
									var ret_datas = datas.slice(2, datas.length);
									var error = "";
									fnc_callback(this_d,label, value, ret_datas, error);
								}
								else
								{
									var label = newlabel;
									var value = 0;
									var ret_datas = [];
									var error = reply;
									fnc_callback(this_d,label, value, ret_datas, error);
								}
							});
					}
					else if (adding_style == "popup"){
						if(struct_add.ret_vals == "row_index"){
							var row_index = $(this_d).parent().parent().index();
							struct_add.ret_vals = row_index;
						}
						$(adding_popup_id).load(url_add, struct_add);
					}
				}
				else if( !enable_add && ui.item.value == 0){
					var label = newlabel;
					var value = 0;
					var ret_datas = [];
					var error = "not selected";
					fnc_callback(this_d,label, value, ret_datas, error);
				}
				else
				{
					var label = ui.item.label;
					var value = ui.item.value;
					var ret_datas = ui.item.ret_datas;
					var error = "";
					fnc_callback(this_d,label, value, ret_datas, error);
				}
				return false;
			}
		}).data( "autocomplete" )._renderItem = function( ul, item ) {
			if( fnc_render != "" ) {
				return fnc_render(ul, item);
			}
			else {
				var appendstr = "";
				if(enable_add && item.error == "add new"){
					appendstr = "Add New: ";
				}
				return $( "<li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + appendstr + item.label + "</a>" )
					.appendTo( ul );
			}
		};
		
	//});
	
	
}




function my_autocomplete_render_fnc(ul, item, style, datas, params){
	switch(style){
		case "code_name":
			if(item.error == "add new"){
				return $( "<li>" )
					.data( "item.autocomplete", item )
					.append( "<a style='display:block; float:left; clear:left;'>"+
								"Add New: " + item.label + "</a>" )
					.appendTo( ul );
			}else{
				
				var defaultvalue = {
					width : ["50px","200px","150px","300px","50px"]
				};
				
				var spanlist = "";
				var cnt = datas.length;
			
				var widths = defaultvalue.width;
				if(typeof params !== 'undefined') { if(typeof params.width !== 'undefined'){
					for(var i=0; i<cnt; i++){
						widths[i] = (typeof params.width[i] !== 'undefined')?params.width[i]:defaultvalue.width[i];
					}
				}}
				
				for(var i=0; i<cnt; i++){
					spanlist += "<span style='display:block; float:left; width:"+widths[i]+";'>" + 
									item.ret_datas[datas[i]] + "</span>";
									
				}
				
				return $( "<li style='width:100%'>" )
					.data( "item.autocomplete", item )
					.append( "<a style='display:block; float:left; clear:left;'>"+spanlist+"</a>" )
					.appendTo( ul );
			}
			break;
	}
	
}




Number.prototype.formatMoney = function(c, d, t){
	var n = this; 
	var c = isNaN(c = Math.abs(c)) ? 2 : c;
	var d = d == undefined ? "." : d;
	var t = t == undefined ? "," : t;
	var s = n < 0 ? "-" : "";
	var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "";
	var j = (j = i.length) > 3 ? j % 3 : 0;
	return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + 
			(c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
};

function pad (str, max) {
	return str.length < max ? pad("0" + str, max) : str;
}

function restrictnumber(id, slength) {
	$(id).live("keydown",function(event) {
		// Allow: backspace, delete, tab, escape, and enter
		if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || 
				event.keyCode == 27 || event.keyCode == 13 || 
			 // Allow: Ctrl+A
			(event.keyCode == 65 && event.ctrlKey === true) || 
			 // Allow: Ctrl+C
			(event.keyCode == 67 && event.ctrlKey === true) ||
			 // Allow: Ctrl+V
			(event.keyCode == 86 && event.ctrlKey === true) ||
			 // Allow: home, end, left, right
			(event.keyCode >= 35 && event.keyCode <= 39)) {
				 // let it happen, don't do anything
				 return;
		}
		else {
			if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) 
					&& (event.keyCode < 96 || event.keyCode > 105 )) {
				event.preventDefault(); 
			}   
		}
	});
	
	$(id).on('change',function(){
		if ( !$.isNumeric($(this).val()) )
		{
			$(this).val("0");
		}
		else
		{
			var str = $(this).val();
			str = str.replace(/\./g,"");
			if(slength > 0)
			{
				var length = str.length;
				var str = pad (str, slength);
			}
			$(this).val(str);
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

function isInteger(string)
{
	var numericExpression = /^[0-9]+$/;
	if(string.match(numericExpression)) {
		return true;
	} else {
		return false;
	}
}

function getqvalbyid(id)
{
	var data = document.getElementById(id).value;
	data = $.trim(data);
	return encodeURIComponent(data);
}

function showhideadvance(idbtn, idtbl, cookiename, displaycss ) {
	displaycss = typeof displaycss !== 'undefined' ? displaycss : "block";
	
	var showadvance=getCookie(cookiename);
	if (showadvance!=null && showadvance!="")
	{
		if(showadvance == "show")
		{
			$(idtbl).css("display",displaycss);
			//idtbl.style.display="block";
			idbtn.value = "Hide Advance Info";
		}
		else
		{
			$(idtbl).css("display","none");
			//idtbl.style.display="none";
			idbtn.value = "Show Advance Info";
		}
	}
	else 
	{
		$(idtbl).css("display","none");
		//idtbl.style.display="none";
		idbtn.value = "Show Advance Info";
		setCookie(cookiename,"hide",1);
	}
	
	
	
	$(idbtn).mouseup( function() {
        
		if( $(idtbl).css("display") == displaycss)
		{
			$(idtbl).css("display","none");
			//idtbl.style.display = "none";
			idbtn.value = "Show Advance Info";
			setCookie(cookiename,"hide",1);
		}
		else
		{
			$(idtbl).css("display",displaycss);
			//idtbl.style.display = "block";
			idbtn.value = "Hide Advance Info";
			setCookie(cookiename,"show",1);
		}
		
	});
	/*
	var txt = document.getElementById(idbtn).value;
	if(txt == "Show Advance Info")
	{
		document.getElementById(idtbl).style.display="block";
		document.getElementById(idbtn).value = "Hide Advance Info";
		setCookie(cookiename,"show",1);
	}
	else
	{
		document.getElementById(idtbl).style.display="none";
		document.getElementById(idbtn).value = "Show Advance Info";
		setCookie(cookiename,"hide",1);
	}*/
}



function showhideadvance_arr(idbtn, idtbl_arr, cookiename, displaycss_arr ) {
	
	var size = idtbl_arr.length;
	
	
	var showadvance=getCookie(cookiename);
	if (showadvance!=null && showadvance!="")
	{
		if(showadvance == "show")
		{
			for(var i =0; i<size; i++)
			{
				$(idtbl_arr[i]).css("display",displaycss_arr[i]);
			}
			idbtn.value = "Hide Advance Info";
		}
		else
		{
			for(var i =0; i<size; i++)
			{
				$(idtbl_arr[i]).css("display","none");
			}
			idbtn.value = "Show Advance Info";
		}
	}
	else 
	{
		for(var i =0; i<size; i++)
		{
			$(idtbl_arr[i]).css("display","none");
		}
		idbtn.value = "Show Advance Info";
		setCookie(cookiename,"hide",1);
	}
	
	$(idbtn).click( function() {
		if($(this).val() == "Hide Advance Info")
		{
			for(var i =0; i<size; i++)
			{
				$(idtbl_arr[i]).css("display","none");
			}
			idbtn.value = "Show Advance Info";
			setCookie(cookiename,"hide",1);
		}
		else
		{
			for(var i =0; i<size; i++)
			{
				$(idtbl_arr[i]).css("display",displaycss_arr[i]);
			}
			idbtn.value = "Hide Advance Info";
			setCookie(cookiename,"show",1);
		}
		
	});
}

function fire_tab_when_entered(sel)
{
	$(sel).not( $(":button") ).unbind("keypress");
	$(sel).not( $(":button") ).keypress( function (evt) {
		if (evt.keyCode == 13) {
			iname = $(this).val();
			if (iname !== 'Submit'){
				next_focus(this);
				return false;
			}
		}
	});
}

function set_fire_tab_when_entered(elem_id)
{
	$(elem_id).live("keypress", function (evt) {
		if (evt.keyCode == 13) {
			next_focus(this);
			return false;
		}
	});
}

function js_fire_tab_when_entered(evt, elem) {
	if (evt.keyCode == 13) {
		next_focus(elem);
		return false;
	}
}

function next_focus(elem) {
    var fields = document.querySelectorAll('button, input, textarea, select');
    var fields = Array.prototype.slice.call( fields );
    
    var index = fields.indexOf(elem); 
	if ( index > -1 && ( index + 1 ) < fields.length ) {
		index++;
		while( $(fields[index]).parent().css("display") == "none" || 
				$(fields[index]).parent().parent().css("display") == "none" ||
				$(fields[index]).attr("disabled") == "disabled" ||
				$(fields[index]).attr("readonly") == "readonly" ||
				$(fields[index]).attr("type") == "hidden" || 
				$(fields[index]).css("display") == "none" )
		{
			index++;
		}
		$(fields[index]).focus();
	}
}

function isValidTime(time){
	var matches = /^(([0-1]?[0-9])|([2][0-3])):([0-5]?[0-9]):([0-5]?[0-9])$/.test(time); 
	return matches;
}

function isValidDate(date)
{
    var matches = /^(\d{4})[-\/](\d{2})[-\/](\d{2})$/.exec(date);
    if (matches == null) return false;
    var y = matches[1];
	var m = matches[2] - 1;
	var d = matches[3];
    var composedDate = new Date(y, m, d);
    return composedDate.getDate() == d &&
            composedDate.getMonth() == m &&
            composedDate.getFullYear() == y;
}

function ddlchosen_dynamic_add_item_4(ddlid, phpurl, struct, replystatus)
{
	$(ddlid+"_chzn").find("li.no-results").live("click",function(){
		var new_name = $(this).children("span").html();
		struct.name = new_name;
		
		$.get(phpurl,
			struct,
			function(reply) {
				var patt1 = new RegExp("Saved");
				if (patt1.test(reply))
				{
					var datas = reply.split("<nssep>");
					var new_wid = datas[1];
					$(ddlid).append('<option value="'+new_wid+'" selected>'+
													new_name+'</option>');
					$(ddlid).trigger("liszt:updated");
					$(ddlid).trigger("liszt:close");
				}
				else
				{
					replystatus.innerHTML = reply + "<br />";
				}
			});
	});
}


function ddlchosen_dynamic_add_item_fnc(ddlid, phpurl, struct, replystatus, callbackfnc)
{
	$(ddlid+"_chzn").find("li.no-results").live("click",function(){
		var new_name = $(this).children("span").html();
		struct.name = new_name;
		$.get(phpurl,
			struct,
			function(reply) {
				var patt1 = new RegExp("Saved");
				if (patt1.test(reply))
				{
					var datas = reply.split("<nssep>");
					var new_wid = datas[1];
					$(ddlid).append('<option value="'+new_wid+'" selected>'+
													new_name+'</option>');
					$(ddlid).trigger("liszt:updated");
					$(ddlid).trigger("liszt:close");
					
					callbackfnc(new_wid);
				}
				else
				{
					replystatus.innerHTML = reply + "<br />";
				}
			});
	});
}


function ddlchosen_dynamic_add_item(ddlid, phpurl, struct, elem, replystatus)
{
	$(ddlid+"_chzn").find("li.no-results").live("click",function(){
		var new_name = $(this).children("span").html();
		struct.name = new_name;
		struct.deptid = $(elem).val();
		$.get(phpurl,
			struct,
			function(reply) {
				var patt1 = new RegExp("Saved");
				if (patt1.test(reply))
				{
					var datas = reply.split("<nssep>");
					var new_wid = datas[1];
					$(ddlid).append('<option value="'+new_wid+'" selected>'+
													new_name+'</option>');
					$(ddlid).trigger("liszt:updated");
					$(ddlid).trigger("liszt:close");
				}
				else
				{
					replystatus.innerHTML = reply + "<br />";
				}
			});
	});
}

function table_filter_elements(elems, fnc){
	for(var i in elems){
		var elemsel = elems[i].sel;
		var elemevt = elems[i].evt;
		switch(elemevt){
			case "click": $(elemsel).live("click", function(){ fnc (); }); break;
			case "change": $(elemsel).live("change", function(){ fnc (); } ); break; 
			case "enterpress": $(elemsel).live("keypress", function(e){ if(e.keyCode == 13) fnc(); }); break;
		}
		
	}
}


function get_filter_date_from()
{
	//current date;
	var cur_year = new Date().getFullYear();
	var cur_month = new Date().getMonth() + 1;
	var cur_day = new Date().getDate();
	//var first_day = cur_year + "-" +  cur_month + "-" + 1;
	
	if(cur_month < 10)
		cur_month = "0" + cur_month;
		
	if(cur_day < 10)
		cur_day = "0" + cur_day;
	
	var first_day = cur_year + "-" + cur_month + "-" + cur_day
	
	return first_day;
}

function get_filter_date_to()
{
	//current date;
	var cur_year = new Date().getFullYear();
	var cur_month = new Date().getMonth() + 1;
	var cur_day = new Date().getDate();
	//var last_day = cur_year + "-" +  cur_month + "-" + new Date( (new Date(cur_year, cur_month, 1))-1 ).getDate();
	
	if(cur_month < 10)
		cur_month = "0" + cur_month;
		
	if(cur_day < 10)
		cur_day = "0" + cur_day;
		
	var last_day = cur_year + "-" + cur_month + "-" + cur_day
	
	return last_day;
}


function get_filter_date_now()
{
	//current date;
	var cur_year = new Date().getFullYear();
	var cur_month = new Date().getMonth() + 1;
	var cur_day = new Date().getDate();
	//var last_day = cur_year + "-" +  cur_month + "-" + new Date( (new Date(cur_year, cur_month, 1))-1 ).getDate();
	
	if(cur_month < 10)
		cur_month = "0" + cur_month;
	
	if(cur_day < 10)
		cur_day = "0" + cur_day;
	
	var last_day = cur_year + "-" + cur_month + "-" + cur_day
	
	return last_day;
}

function get_date_from_int(date_int)
{
	var d = new Date(date_int);
	var year = d.getFullYear();
	var month = d.getMonth() + 1;
	var day = d.getDate();
	//var last_day = cur_year + "-" +  cur_month + "-" + new Date( (new Date(cur_year, cur_month, 1))-1 ).getDate();
	
	if(month < 10)
		month = "0" + month;
	
	if(day < 10)
		day = "0" + day;
	
	var date_ret = year + "-" + month + "-" + day
	
	return date_ret;
}
