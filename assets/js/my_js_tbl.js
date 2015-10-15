// JavaScript Document
function my_table (tab, colarray, options) {
	this.tab = tab;
	this.colarray = colarray;
	this.tbo = "";
	this.mypage = null;
	
	this.options_d = options;
	this.ispaging = false;
	this.ishighlight_when_hover = false;
	this.iscursorchange_when_hover = false;
	this.isdeleteicon_when_hover = false;
	this.iscleanicon_when_hover = false;
	this.ispopup = false;
	if (typeof options !== 'undefined') {
		this.ispaging = typeof options.ispaging !== 'undefined' ? options.ispaging : false;
		this.ishighlight_when_hover = typeof options.tdhighlight_when_hover !== 'undefined' ? true : false;
		this.iscursorchange_when_hover = typeof options.iscursorchange_when_hover !== 'undefined' ? 
												options.iscursorchange_when_hover : false;
		this.isdeleteicon_when_hover = typeof options.isdeleteicon_when_hover !== 'undefined' ? 
												options.isdeleteicon_when_hover : false;
		this.iscleanicon_when_hover = typeof options.iscleanicon_when_hover !== 'undefined' ? 
												options.iscleanicon_when_hover : false;										
		this.ispopup = typeof options.tdpopup_when_hover !== 'undefined' ? true : false;
	}
	
	var delicon = this.isdeleteicon_when_hover;
	var cleanicon = this.iscleanicon_when_hover;
	var cursor = this.iscursorchange_when_hover;
	
	var this_d = this;
	
	if(this.ispaging == true){
		this.mypage = new my_js_tblpaging(this.tab.id);
	}
	
	if(this.ishighlight_when_hover == true){
		$("."+options.tdhighlight_when_hover).live("mouseover",function(){
			var crow = $(this).parent();
			//$(crow).css("background-color","#9EF");
			var origcolor = $(crow).css("background-color");
			// $(crow).attr("contextMenu",origcolor);
			// $(crow).css("background-color","#9EF");
			
			if( cursor ) {
				$(crow).css("cursor","pointer");
			}
			
			if( delicon ) {
				$(this).parent().find(".imgdel").css("display","table-cell");
				$(this).parent().find(".imgdel2").css("display","table-cell");
			}
			
			if( cleanicon ) {
				$(this).parent().find(".imgclean").css("display","table-cell");
			}
			
		}).live("mouseout",function(){
			var crow = $(this).parent();
			var origcolor = $(crow).attr("contextMenu");
			// $(crow).css("background-color",origcolor);
			/*
			if(origcolor == "rgb(153,238,255)") {
				origcolor = "rgb(255, 255, 255)";
				$(crow).attr("contextMenu", origcolor);
				$(crow).css("background-color","#FFFFFF");
			}
			else
			{
				$(crow).css("background-color","#9EF");
			}*/
			
			if( delicon ) {
				$(this).parent().find(".imgdel").css("display","none");
				$(this).parent().find(".imgdel2").css("display","none");
			}
			if( cleanicon ) {
				$(this).parent().find(".imgclean").css("display","none");
			}
		});
	}
	
	if(this.ispopup == true){
		
		var divpopup = document.createElement('div');
		divpopup.style.width = "auto";
		divpopup.className = "popupnode";
		this.colarray['colpopupdiv'] = { 
			header_title: "",
			edit: [divpopup],
			disp: [divpopup],
			td_class: "tablerow " + options.tdpopup_when_hover
		};
		
		$("."+options.tdpopup_when_hover).live("mouseenter",function(e){
			
			var node = $(this).parent().find(".popupnode");
			var node_width = $(node).width();
			var node_height = $(node).height();
			
			var node_x = (e.pageX + node_width < window.innerWidth + window.scrollX - 40)?
							e.pageX+10:e.pageX-node_width-1;
			var node_y = (e.pageY + node_height < window.innerHeight + window.scrollY)?
							e.pageY+5:e.pageY-node_height-5;
			
			$(node).css("left",node_x + "px");
			$(node).css("top",node_y + "px");
			$(node).css("display","block");		
		}).live("mousemove",function(e){
			var node = $(this).parent().find(".popupnode");
			var node_width = $(node).width();
			var node_height = $(node).height();
			
			var node_x = (e.pageX + node_width < window.innerWidth + window.scrollX - 40)?
							e.pageX+10:e.pageX-node_width-1;
			var node_y = (e.pageY + node_height < window.innerHeight + window.scrollY)?
							e.pageY+5:e.pageY-node_height-5;
	
			$(node).css("left",node_x + "px");
			$(node).css("top",node_y + "px");
		}).live("mouseleave",function(e){
			var node = $(this).parent().find(".popupnode");
			$(node).css("display","none");
		});
		
		
	}
	
	this.tbo = document.createElement('tbody');
	var row, cell, content;
	
	//header row
	row=document.createElement('tr');
	row.className = "tableheader"
	row.align = "center";
	for(var i in this.colarray) {
		cell=document.createElement('td');
		
		var header_elem = this.colarray[i].header_elem;
		if( header_elem != undefined ){
			content=header_elem;
		}
		else{
			content=document.createTextNode(this.colarray[i].header_title);
		}
		cell.appendChild(content);
		
		var headertdclass = this.colarray[i].headertd_class;
		if( headertdclass != undefined )
			cell.className = headertdclass;
			
		row.appendChild(cell);
	}
	
	this.tbo.appendChild(row);
	this.tab.appendChild(this.tbo);
	
	this.get_row_count = get_row_count_fnc;
	this.insert_row = insert_row_fnc;
	this.add_new_row = add_new_row_fnc;
	this.update_row = update_row_fnc;
	this.edit_row = edit_row_fnc;
	this.delete_row = delete_row_fnc;
	this.insert_row_with_value = insert_row_with_value_fnc;
	this.insert_multiplerow_with_value = insert_multiplerow_with_value_fnc;
	this.insert_multiplerow_with_value_viewonly = insert_multiplerow_with_value_viewonly_fnc;
	this.update_row_with_value = update_row_with_value_fnc;
	this.get_row_values = get_row_values_fnc;
	this.get_table_values = get_table_values_fnc;
	this.get_table_values_with_separator = get_table_values_with_separator_fnc;
	this.get_table_values_by_tdclass = get_table_values_by_tdclass_fnc;
	this.clear_table = clear_table_fnc;
	this.getvalue_by_elem = getvalue_by_elem_fnc;
	this.setvalue_to_elem = setvalue_to_elem_fnc;
	this.getelem_by_rowindex_tdclass = getelem_by_rowindex_tdclass_fnc;
	this.getvalue_by_rowindex_tdclass = getvalue_by_rowindex_tdclass_fnc;
	this.setvalue_to_rowindex_tdclass = setvalue_to_rowindex_tdclass_fnc;
	this.setvalue_to_rowindex = setvalue_to_rowindex_fnc;
	this.setrow_font_color = setrow_font_color_fnc;
	this.clean_table = clean_table_fnc;
	
	this.clear_events = clear_events_fnc;
}

function get_row_count_fnc()
{
	return this.tbo.childNodes.length;
}

function insert_row_fnc(row_index)
{
	this.insert_row_with_value(row_index, null, true);
}


function add_new_row_fnc()
{
	var max_row = this.tbo.childNodes.length;
	this.insert_row(max_row);
}

function update_row_fnc(row_index)
{
	var values_arr = {};
	
	var row = this.tbo.childNodes[row_index];
	var i_num = 0;
	for(var i in this.colarray) {
		var cell = row.childNodes[i_num];
		var tdclassname = this.colarray[i].td_class;
		//alert(tdclassname);
		values_arr[tdclassname] = [];
		for(var j=0; j<cell.childNodes.length; j++)
		{
			//get data from old elem
			var elem = cell.childNodes[j];
			var value = this.getvalue_by_elem(elem);
			
			//put data to disp elem
			var arr_elem_disp = this.colarray[i].disp[j].cloneNode(true);
			var new_elem = arr_elem_disp;
			this.setvalue_to_elem(value, new_elem);
			
			//swith elem edit to disp
			cell.insertBefore(new_elem ,cell.childNodes[j+1]);
			cell.removeChild(elem);
			
			//set return values
			values_arr[tdclassname][j] = value.toString();
		}//end if
		i_num++;
	}//end for
	
	return values_arr;
}

function get_row_values_fnc(row_index)
{
	var values_arr = {};
	var row = this.tbo.childNodes[row_index];
	var i_num = 0;
	for(var i in this.colarray) {
		var cell = row.childNodes[i_num];
		var tdclassname = this.colarray[i].td_class;
		values_arr[tdclassname] = [];
		for(var j=0; j<cell.childNodes.length; j++)
		{
			//get data from elem
			var elem = cell.childNodes[j];
			var value = this.getvalue_by_elem(elem);
			
			//set return values
			values_arr[tdclassname][j] = value.toString();
		}//end if
		i_num++;
	}//end for
	
	return values_arr;
}

function get_table_values_fnc(){
	var datas = [];
	for(var row_num=1; row_num<this.tbo.childNodes.length; row_num++){
		datas[row_num-1] = this.get_row_values(row_num);
	}
	return datas;
}

function get_table_values_with_separator_fnc(){
	var datas = [];
	for(var row_num=1; row_num<this.tbo.childNodes.length; row_num++){
		var row_val = this.get_row_values(row_num);
		var filtered_row = {};
		for (key in row_val) {
			var filtered_key = $.trim(key).replace(/ /g,"--");
			filtered_row[filtered_key] = [];
			for(var i=0; i<row_val[key].length; i++){
				filtered_row[filtered_key].push(row_val[key][i]); 
			}
		}
		datas[row_num-1] = filtered_row;
	}
	return datas;
}

function get_table_values_by_tdclass_fnc(td_cols){
	var datas = [];
	for(var row_num=1; row_num<this.tbo.childNodes.length; row_num++){
		var row_data = [];
		for(var i = 0 in td_cols) {
			row_data[i] = this.getvalue_by_rowindex_tdclass(row_num, td_cols[i]);
		}
		datas[row_num-1] = row_data;
	}
	return datas;
}



function edit_row_fnc(row_index)
{
	var row = this.tbo.childNodes[row_index];
	var i_num = 0;
	for(var i in this.colarray) {
		var cell = row.childNodes[i_num];
		for(var j=0; j<cell.childNodes.length; j++)
		{
			//get data from old elem
			var elem = cell.childNodes[j];
			var value = this.getvalue_by_elem(elem);
			
			//put data to edit elem
			var arr_elem_edit = this.colarray[i].edit[j].cloneNode(true);
			var new_elem = arr_elem_edit;
			this.setvalue_to_elem(value, new_elem);
			
			//swith elem disp to edit
			cell.insertBefore(new_elem ,cell.childNodes[j+1]);
			cell.removeChild(elem);
		}
		i_num++;
	}
	
	var tabid = this.tab.id;
/*	$.getScript("../assets/js/my_js_lib.js", function(){
		fire_tab_when_entered_table(tabid);
		//fire_tab_when_entered("input");
		//fire_tab_when_entered("select");
	});*/
	
}

function delete_row_fnc(row_index)
{
	var row = this.tbo.childNodes[row_index];
	this.tbo.removeChild(row);
	
	if(this.ispaging == true){
		this.mypage.refreshfilterPage();
	}
}

function insert_row_with_value_fnc(row_index, value_arr, bool, options)
{
	var color_status = 0;
	
	if( typeof options !== 'undefined' ){
		color_status = (typeof options.color_status !== 'undefined')?options.color_status:color_status;
	}
	
	var row, cell, content;
	row=document.createElement('tr');
	this.setrow_font_color(row, color_status);
	var i_num=0;
	for(var i in this.colarray) {
		cell=document.createElement('td');
		cell.className = this.colarray[i].td_class;
		for(var j in this.colarray[i].disp )
		{
			var content = this.colarray[i].edit[j].cloneNode(true);
			if(value_arr != null){
				content = this.colarray[i].disp[j].cloneNode(true);
				this.setvalue_to_elem(value_arr[i_num][j].toString(), content);
			}
			cell.appendChild( content );
		}
		row.appendChild(cell);
		i_num++;
	}
	this.tbo.insertBefore(row,this.tbo.childNodes[row_index]);
	
	bool = typeof bool !== 'undefined' ? bool : true;
	
/*	if(bool){
		var tabid = this.tab.id;
		$.getScript("../assets/js/my_js_lib.js", function(){
			fire_tab_when_entered_table(tabid);
			//fire_tab_when_entered("input");
			//fire_tab_when_entered("select");
		});
	}*/
	
}


function insert_multiplerow_with_value_viewonly_fnc(row_index, value_arr, options_arr)
{
	var first_view_insert = (row_index == 1);
	
	for(var cnt in value_arr) {
		if(typeof options_arr !== 'undefined' && 
			typeof options_arr[cnt] !== 'undefined' ) {
			this.insert_row_with_value(row_index, value_arr[cnt], false, options_arr[cnt]);
		}
		else {
			this.insert_row_with_value(row_index, value_arr[cnt], false);
		}
		
		row_index++;
	}
	
	if(this.ispaging == true){
		if(!this.mypage.isOldPaging && (typeof value_arr !== "undefined")){
			if(first_view_insert){ this.mypage.resetPageFilter();}
			else{this.mypage.resetTotalPageNumber_increase();}
		}
		else if(this.mypage.isOldPaging){
			this.mypage.refreshfilterPage();
		}
		else{}
	}
}


function insert_multiplerow_with_value_fnc(row_index, value_arr, options_arr)
{
	for(var cnt in value_arr) {
		if(typeof options_arr !== 'undefined' && 
			typeof options_arr[cnt] !== 'undefined' ) {
			this.insert_row_with_value(row_index, value_arr[cnt], false, options_arr[cnt]);
		}
		else {
			this.insert_row_with_value(row_index, value_arr[cnt], false);
		}
		
		row_index++;
	}
	
	if(this.ispaging == true){
		//this.mypage.resetPageFilter();
		//this.mypage.resetTotalPageNumber_increase();
	    if(!this.mypage.isOldPaging && (typeof value_arr !== "undefined")){
			this.mypage.set_mysql_interval(value_arr.length);
		}
		this.mypage.refreshfilterPage();
	}
	
	var tabid = this.tab.id;
/*	$.getScript("../assets/js/my_js_lib.js", function(){
		fire_tab_when_entered_table(tabid);
		//fire_tab_when_entered("input");
		//fire_tab_when_entered("select");
	});*/
}

function update_row_with_value_fnc(row_index, value_arr)
{
	this.delete_row(row_index);
	this.insert_row_with_value(row_index, value_arr);
	if(this.ispaging == true){
		this.mypage.refreshfilterPage();
	}
}

function clear_table_fnc()
{
	var row_length = this.tbo.childNodes.length;
	for(var i= row_length-1 ; i>0; i--)
	{
		var row = this.tbo.childNodes[i];
		this.tbo.removeChild(row);
		
	}
	
	if(this.ispaging == true){
		//this.mypage.refreshfilterPage();
		this.mypage.resetPageFilter();
	}
}

function clean_table_fnc()
{
	var row_length = this.tbo.childNodes.length;
	for(var i= row_length-1 ; i>0; i--)
	{
		var row = this.tbo.childNodes[i];
		this.tbo.removeChild(row);
		
	}
	
	if(this.ispaging == true){
		//this.mypage.refreshfilterPage();
		this.mypage.clean_table();
	}
}



function getelem_by_rowindex_tdclass_fnc(row_index, tdclass)
{
	var row = this.tbo.childNodes[row_index];
	for(var i = 0; i < row.childNodes.length; i++) {
		var cell = row.childNodes[i];
	    if(cell.className === tdclass) {
			return cell.childNodes;
	    }
	}
}

function getvalue_by_rowindex_tdclass_fnc(row_index, tdclass)
{
	var elem_arr = this.getelem_by_rowindex_tdclass(row_index, tdclass);
	var value_arr = new Array();
	for(var i = 0; i < elem_arr.length; i++)
	{
		var value = this.getvalue_by_elem(elem_arr[i]);
		value_arr.push( value );
	}
	return value_arr;
}

function setvalue_to_rowindex_tdclass_fnc(value, row_index, tdclass)
{
	var elem_arr = this.getelem_by_rowindex_tdclass(row_index, tdclass);
	for(var i = 0; i < elem_arr.length; i++)
	{
		this.setvalue_to_elem(value[i], elem_arr[i]);
	}
}

function setvalue_to_rowindex_fnc(values, row_index)
{
	var cnt = 0;
	for(var i in this.colarray) {
		var className = this.colarray[i].td_class;
		var elem_arr = this.getelem_by_rowindex_tdclass(row_index, className);
		for(var j = 0; j < elem_arr.length; j++)
		{
			this.setvalue_to_elem(values[cnt][j], elem_arr[j]);
		}
		cnt++;
	}
}

function getvalue_by_elem_fnc(elem)
{
	var tagname = elem.tagName.toLowerCase();
	var value = "";
	if( tagname == "input" )
	{
		var type = elem.type.toLowerCase();
		if( type == "text" || type == "hidden" )
		{
			value = elem.value;
		}
		else if ( type == "checkbox" )
		{
			value = elem.checked;
		}
	}
	else if (tagname == "span" || tagname == "div" || tagname == "a" )
	{
		//value = $("<div/>").html(elem.innerHTML).text();
		value = elem.innerHTML;
	}
	else if( tagname == "select" || tagname == "textarea" )
	{
		value = elem.value;
	}
	else if( tagname == "img" )
	{
	}
	
	return value;
}


function setvalue_to_elem_fnc(value, elem)
{
	//alert(value);
	var tagname = elem.tagName.toLowerCase();
	if( tagname == "input" )
	{
		var type = elem.type.toLowerCase();
		if( type == "text" || type == "hidden" )
		{
			elem.value = $("<div/>").html(value).text();
			//elem.value = value;
		}
		else if ( type == "checkbox" )
		{
			
			if ( value == "true" || value == "1" || value == 1 ) {
				elem.setAttribute("checked","checked");
			}
			else {
				elem.checked = false;
			}
		}
	}
	else if (tagname == "span" || tagname == "div" || tagname == "a")
	{
		//elem.innerHTML = $("<div/>").html(value).text();
		elem.innerHTML = value;
	}
	else if (tagname == "textarea")
	{
		elem.value = $("<div/>").html(value).text();
	}
	else if( tagname == "select")
	{
		elem.value = value;
	}
	else if( tagname == "img" )
	{
	}
}

function escapeHtml(unsafe) {
  return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}


function setrow_font_color_by_index_fnc(row_index, colorcode){
	var row = this.tbo.childNodes[row_index];
	row.style.backgroundColor = colorcode;
}

function setrow_font_color_fnc(row, color_status){
	
	var color_code = "";
	switch(color_status){
		//normal
		case "1": color_code =  "#9EE5BD"; break;
		
		//pending
		case "2": color_code =  "#FFF27E"; break;
		
		//close to dedline
		case "3": color_code =  "#FFB800"; break;
		
		//after dedline
		case "4": color_code =  "#F33"; break;
		
		//inactive
		case "5": color_code =  "#6F6F6F"; break;
	}
	
	row.style.backgroundColor = color_code;
}

function clear_events_fnc()
{
	if(this.ishighlight_when_hover == true){
		$("."+this.options_d.tdhighlight_when_hover).unbind("mouseover");
		$("."+this.options_d.tdhighlight_when_hover).unbind("mouseout");
	}
	
	if(this.ispopup == true){
		$("."+this.options_d.tdpopup_when_hover).unbind("mouseenter");
		$("."+this.options_d.tdpopup_when_hover).unbind("mousemove");
		$("."+this.options_d.tdpopup_when_hover).unbind("mouseleave");
	}
	
	if(this.ispaging == true){
		this.mypage.clear_events();
	}
	
	
}



/*
function getvalue_by_index(row_index, col_index)
{
	var row = this.tbo.childNodes[row_index];
	var cell = row.childNodes[col_index];
	var value_arr = [];
	for(var i=0; i<cell.childNodes.length; i++)
	{
		var elem = cell.childNodes[i];
		var value = getvalue_by_elem(elem);
		value_arr[i] = value;
	}
	return value_arr;
}

function setvalue_by_index(value, row_index, col_index)
{
	var row = this.tbo.childNodes[row_index];
	var cell = row.childNodes[col_index];
	var value_arr = [];
	for(var i=0; i<cell.childNodes.length; i++)
	{
		var elem = cell.childNodes[i];
		setvalue_to_elem(value, elem);
	}
}
*/