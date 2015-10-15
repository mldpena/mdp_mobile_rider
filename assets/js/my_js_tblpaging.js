// JavaScript Document

function my_js_tblpaging(table_id_var){
	
	this.isOldPaging = false;
	
	this.tableid = table_id_var;
	this.btnprevious_id = table_id_var + "_btnprevious";
	this.btnnext_id = table_id_var + "_btnnext";
	this.txtpagenumber_id = table_id_var + "_txtpagenumber";
	this.divlastpage_id = table_id_var + "_divlastpage";
	this.txtfilternumber_id = table_id_var + "_txtfilternumber";
	
	this.pagingtable = document.createElement('table');
	var pagingtbody = document.createElement('tbody');
	
	var row1 = document.createElement('tr');
	var row2 = document.createElement('tr');
	pagingtbody.appendChild(row1);
	pagingtbody.appendChild(row2);
	
	var cell11 = document.createElement('td');
	var cell12 = document.createElement('td');
	var cell13 = document.createElement('td');
	var cell14 = document.createElement('td');
	var cell15 = document.createElement('td');
	row1.appendChild(cell11);
	row1.appendChild(cell12);
	row1.appendChild(cell13);
	row1.appendChild(cell14);
	row1.appendChild(cell15);
	
	var cell21 = document.createElement('td');
	cell21.setAttribute("colspan","5");
	row2.appendChild(cell21);
	
	var btnpreviouspage = document.createElement('input');
	btnpreviouspage.type = "button";
	btnpreviouspage.id = this.btnprevious_id;
	btnpreviouspage.value = "Previous";
	cell11.appendChild(btnpreviouspage);
	
	var txtpagenumber = document.createElement('input');
	txtpagenumber.type = "text";
	txtpagenumber.id = this.txtpagenumber_id;
	//txtpagenumber.setAttribute("onfocus","savepage(this)");
	//txtpagenumber.setAttribute("onchange","showing('"+this.divlastpage_id +"',this);");
	txtpagenumber.value = "1";
	txtpagenumber.size = "1";
	cell12.appendChild(txtpagenumber);
	
	cell13.innerHTML = " / ";
	
	var divlastpage = document.createElement('div');
	divlastpage.id = this.divlastpage_id;
	cell14.appendChild(divlastpage);
	
	var btnnextpage = document.createElement('input');
	btnnextpage.type = "button";
	btnnextpage.id = this.btnnext_id;
	btnnextpage.value = "Next";
	cell15.appendChild(btnnextpage);
	
	var txtfilternumber = document.createElement('input');
	txtfilternumber.type = "text";
	txtfilternumber.id = this.txtfilternumber_id;
	txtfilternumber.value = "10";
	txtfilternumber.size = "5";
	txtfilternumber.style.textAlign = "center";
	cell21.appendChild(txtfilternumber);
	cell21.appendChild(document.createTextNode(" Row per page"));
	
	this.pagingtable.appendChild(pagingtbody);
	
	this.temp_var_for_page = 1;
	this.base_mysql_interval = 100;
	this.mysql_interval = this.base_mysql_interval;
	this.rowcnt = 0;
	
	this.filter_number = 10;
	this.page_number = 1;
	this.total_pages = Math.ceil(($("#"+this.tableid).children().children().length-1)/this.filter_number);
	//for auto scoll
	this.scroll_val = 0;
	
	var this_var = this;
	
	this.assignScroll_val = assignScroll_val_fnc;
	this.useScroll_val = useScroll_val_fnc;
	this.checkTotalPage = checkTotalPage_fnc;
	this.filterPage = filterPage_fnc;
	this.hideRows = hideRows_fnc;
	this.go_to_last_page = go_to_last_page_fnc;
	this.initFilterPage = initFilterPage_fnc;
	this.resetPageFilter = resetPageFilter_fnc;
	this.recheckandsetTotalPage = recheckandsetTotalPage_fnc;
	this.refreshfilterPage = refreshfilterPage_fnc;
	this.resetTotalPageNumber_increase = resetTotalPageNumber_increase_fnc;
	this.set_last_page = set_last_page_fnc;
	this.get_last_page = get_last_page_fnc;
	this.pass_refresh_filter_page = pass_refresh_filter_page_func;
	this.refresh_filter_page_caller;
	this.get_row_range = get_row_range_fnc;
	this.clean_table = clean_table_func;
	this.set_mysql_interval = set_mysql_interval_func;
	this.set_paging_row_cnt = set_paging_row_cnt_func;
	this.get_paging_row_cnt = get_paging_row_cnt_func;
	this.adding_new_row = adding_new_row_func;
	this.deleting_row = deleting_row_func;
	this.get_current_page = get_current_page_func;
	
	
	this.clear_event = clear_event_fnc;
	this.go_to_page = go_to_page_fnc;
	
	$("#"+this.btnprevious_id).live("click",function(e) {
		
		//$('#lastpage').html(this_var.checkTotalPage()); not sure if still needed
		if((Number($("#"+this_var.txtpagenumber_id).val())) > 1 || e.handled === true){
			this_var.temp_var_for_page--;
			if((Number($("#"+this_var.txtpagenumber_id).val())-1)%(Math.ceil(this_var.mysql_interval/this_var.filter_number)) == 0){
				if(this_var.isOldPaging){
					var range = this_var.get_row_range(Number($("#"+this_var.txtpagenumber_id).val())-1);
					this_var.refresh_filter_page_caller(range[0],range[1]);
					this_var.page_number = (Math.ceil(this_var.mysql_interval/this_var.filter_number));
				}
				else{
					this_var.page_number--;	 // needed lang pra sa go to last page. 
				}
			}
			else{
				this_var.page_number--;	
			}
			// this_var.assignScroll_val(); 
			// this_var.hideRows();
			// this_var.filterPage();
			// this_var.useScroll_val();
			// var temppage = Number($("#"+this_var.txtpagenumber_id).val()) - 1;
			// $("#"+this_var.txtpagenumber_id).val(temppage);
			
			if(e.handled !== true) // This will prevent event triggering more than once
			{
				e.handled = true;
				//alert("IF");
				var temppage = Number($("#"+this_var.txtpagenumber_id).val()) - 1;
				$("#"+this_var.txtpagenumber_id).val(temppage);
				//this_var.assignScroll_val(); 
				this_var.hideRows();
				this_var.filterPage();
				//this_var.useScroll_val();
			}
			else
			{
				//alert("ELSE");
				//this_var.assignScroll_val(); 
				this_var.hideRows();
				this_var.filterPage();
				//this_var.useScroll_val();
			}
		}
	});
	
	$("#"+this.btnnext_id).live("click",function(e)
	{
	
		if(Number($("#"+this_var.txtpagenumber_id).val()) < Number($("#"+this_var.divlastpage_id).html()) || e.handled === true ){
			this_var.temp_var_for_page++;
			if(Number($("#"+this_var.txtpagenumber_id).val())%(Math.ceil(this_var.mysql_interval/this_var.filter_number)) == 0){
				if(this_var.isOldPaging){
					var range = this_var.get_row_range(Number($("#"+this_var.txtpagenumber_id).val())+1);
					this_var.refresh_filter_page_caller(range[0],range[1]);
					this_var.page_number = 1;
				}
				else{
					this_var.page_number++;	 // needed lang pra sa go to last page. 
				}
			}
			else{
				this_var.page_number++;	
			}
			
			
			if(e.handled !== true) // This will prevent event triggering more than once
			{
				e.handled = true;
				var temppage = Number($("#"+this_var.txtpagenumber_id).val()) + 1;
				$("#"+this_var.txtpagenumber_id).val(temppage);
				//this_var.assignScroll_val(); 
				this_var.hideRows();
				this_var.filterPage();
				//this_var.useScroll_val();
			}
			else
			{
				//this_var.assignScroll_val(); 
				this_var.hideRows();
				this_var.filterPage();
				//this_var.useScroll_val();
			}
		}
	});

	$("#"+this.txtpagenumber_id).live("keypress",function(e){
	
		if(e.keyCode == 13){
			var page = $.trim($("#"+this_var.txtpagenumber_id).val());
			var re = $.isNumeric(Number(page));
			
			if(page>(Number($("#"+this_var.divlastpage_id).html()))|| page<=0 || page == "" || !re ){
				alert("Invalid page number!");
			}
			else{
				
				//if in same range, don't refresh_page_filter
				var cur_range = this_var.get_row_range(this_var.temp_var_for_page); 
				var checked_range = this_var.get_row_range(Number($("#"+this_var.txtpagenumber_id).val())); 
				
				if(cur_range[0] != checked_range[0] || cur_range[1] != checked_range[1]){
					if(this_var.isOldPaging){
						this_var.refresh_filter_page_caller(checked_range[0],checked_range[1]);
					}
				}
				
				if(this_var.isOldPaging){
					this_var.page_number = (page%Math.ceil(this_var.mysql_interval/this_var.filter_number) == 0)?
									Math.ceil(this_var.mysql_interval/this_var.filter_number):
									page%Math.ceil(this_var.mysql_interval/this_var.filter_number);
				}
				else{
					this_var.page_number = $(this).val();
				}
				this_var.assignScroll_val(); 
				this_var.hideRows();			
				this_var.filterPage();
				this_var.useScroll_val();
				this_var.temp_var_for_page = Number($("#"+this_var.txtpagenumber_id).val());
			}
			
		}
	});
	var oldpaging="";
$("#"+this.txtpagenumber_id).live("focus",function(){

oldpaging=$(this).val();

});
$("#"+this.txtpagenumber_id).live("change",function(){
var page = $.trim($("#"+this_var.txtpagenumber_id).val());
			var re = $.isNumeric(Number(page));
			
			if(page>(Number($("#"+this_var.divlastpage_id).html()))|| page<=0 || page == "" || !re ){
				
				this_var.page_number = Number(oldpaging);
					this_var.assignScroll_val(); 
				this_var.hideRows();			
				this_var.filterPage();
				this_var.useScroll_val();
				this.value=Number(oldpaging);
			}
			else{
				
				//if in same range, don't refresh_page_filter
				var cur_range = this_var.get_row_range(this_var.temp_var_for_page); 
				var checked_range = this_var.get_row_range(Number($("#"+this_var.txtpagenumber_id).val())); 
				
				if(cur_range[0] != checked_range[0] || cur_range[1] != checked_range[1]){
					if(this_var.isOldPaging){
						this_var.refresh_filter_page_caller(checked_range[0],checked_range[1]);
					}
				}
				
				if(this_var.isOldPaging){
					this_var.page_number = (page%Math.ceil(this_var.mysql_interval/this_var.filter_number) == 0)?
									Math.ceil(this_var.mysql_interval/this_var.filter_number):
									page%Math.ceil(this_var.mysql_interval/this_var.filter_number);
				}
				else{
					this_var.page_number = $(this).val();
				}
				this_var.assignScroll_val(); 
				this_var.hideRows();			
				this_var.filterPage();
				this_var.useScroll_val();
				this_var.temp_var_for_page = Number($("#"+this_var.txtpagenumber_id).val());
			}
		


});
	$("#"+this.txtfilternumber_id).live("keypress",function(e){
	
		if(e.keyCode == 13){
			var page = $.trim($("#"+this_var.txtfilternumber_id).val());
			var re = $.isNumeric(Number(page));
			
			if(page<=0){
				alert("Value must be greater than 0");	
			} else if(!re || page == ""){
				alert("Value must be a number");
			} else{
				this_var.filter_number = Number(page);
				if(this_var.isOldPaging){
					this_var.mysql_interval = this_var.base_mysql_interval;
					while(this_var.mysql_interval%page!=0){
						this_var.mysql_interval++;
					}
					//var range = this_var.get_row_range(1);
					this_var.refresh_filter_page_caller();//range[0],range[1]
				}
				this_var.resetPageFilter();	
			}
		}
	});
	
}

function set_mysql_interval_func(val){
	this.base_mysql_interval = val;
	this.mysql_interval = val;
}
function clean_table_func(){
	this.assignScroll_val();
	this.hideRows();
	this.filterPage();	
	this.useScroll_val();
}
function get_row_range_fnc(start){
	
	var page =  start*this.filter_number;
	var interval = this.mysql_interval;
	if(Number(this.filter_number)>this.mysql_interval){
		this.mysql_interval = this.filter_number;
		interval = this.filter_number;
	}
	if(page%interval == 0 ){//if not included, value for temp_page and page can be 100 to 100 or 200 to 200
		page -= 1; 
	}
	var temp_page = page;
	
	while(page%interval != 0){ //get the upper limit of the interval
		page++;
	}
	while(temp_page%interval != 0){ //lower limit
		temp_page--;
	}
	//alert(temp_page+" and "+(page-1));
	//values will be like 0 - 99 instead of 1-100 since sql needs to start from 0
	return [temp_page,page-1];
}
function set_last_page_fnc(val){
	$("#"+this.divlastpage_id).html(val);
}
function set_paging_row_cnt_func(val){
	this.rowcnt = val;
}
function get_paging_row_cnt_func(){
	return this.rowcnt;
}
function adding_new_row_func(true_row_cnt){
	// myjstbl.rowcnt - 2 is also needed since the blank row shows up at the last 
	// page where data is available.(after refreshed / new loaded page) 
	this.rowcnt++;
	
	if((Number(true_row_cnt) - 1)%this.filter_number == 0 ||
	   (Number(true_row_cnt) - 2)%this.filter_number == 0 ){
		 
		 //Math.ceil((this.get_paging_row_cnt()+1)/this.filter_number )){  
	   if((Number(this.get_last_page()) + 1)<=
				Math.ceil((this.get_paging_row_cnt())/this.filter_number )){
			var temppage =Number(this.get_last_page()) + 1;
			this.set_last_page(temppage);
	   }
	   
	}
}
function deleting_row_func(true_row_cnt){
	this.rowcnt--;
	if((Number(true_row_cnt) - 1)%this.filter_number == 0){
		var temppage =Number(this.get_last_page()) - 1;
		this.set_last_page(temppage);
		
		//if current page > last page after deleting, go to last page.
		if(this.get_current_page() > this.get_last_page()){
			this.go_to_last_page();
		}
	}
}
function get_current_page_func(){
	return $("#"+this.txtpagenumber_id).val();
}
function get_last_page_fnc(){
	return $("#"+this.divlastpage_id).html();
}
function pass_refresh_filter_page_func(fnc){
	this.refresh_filter_page_caller = fnc;
}
function assignScroll_val_fnc(){
	this.scroll_val = $(window).scrollTop();
}
function useScroll_val_fnc(){
	window.scroll(0,this.scroll_val); 
}
function checkTotalPage_fnc(){//working
		//check total pages again so that it's updated if there was an insert before. 
		this.total_pages = Math.ceil(($("#"+this.tableid).children().children().length-1)/this.filter_number);
		return this.total_pages;
}
function filterPage_fnc(){//working
	var start = (this.page_number - 1)*this.filter_number + 1;
	var increment_val = this.filter_number;
	var total_row_number = $("#"+this.tableid).children().children().length;
	var table = document.getElementById(this.tableid);
	var ctr = start;
	while(ctr<=start+increment_val-1 && ctr<total_row_number){
		rows = table.rows[ctr];
		rows.style.display = "table-row";
		ctr++;	
	}
	if(ctr < total_row_number){
		rows = table.rows[ctr];
		rows.style.display = "none";
	}
}
function hideRows_fnc(){//working
	//$("#"+this.tableid).find(".table-row").css("display","none");
	$("#"+this.tableid).children().children().css("display","none");
	$("#"+this.tableid).children().children().eq(0).show();
	/*
	for(var ctr =1; ctr<=$("#"+this.tableid).children().children().length;ctr++){
		$("#"+this.tableid).children().children().eq(ctr).hide();
	}*/
}
function go_to_last_page_fnc(){
	if(!this.isOldPaging)
	{
		$("#"+this.divlastpage_id).html(this.checkTotalPage());
		$("#"+this.txtpagenumber_id).val(this.checkTotalPage());
		this.page_number = $("#"+this.divlastpage_id).html();
	}
	else{
		$("#"+this.txtpagenumber_id).val($("#"+this.divlastpage_id).html());
		this.go_to_page($("#"+this.divlastpage_id).html());
	}
	this.assignScroll_val();
	this.hideRows();
	this.filterPage();	
	this.useScroll_val();				
}
function go_to_page_fnc(toPage){
	var cur_range = this.get_row_range(this.temp_var_for_page); 
	var checked_range = this.get_row_range(Number(toPage)); 
	//alert(cur_range+ " " +checked_range);
	if(cur_range[0] != checked_range[0] || cur_range[1] != checked_range[1]){
		if(this.isOldPaging){
			this.refresh_filter_page_caller(checked_range[0],checked_range[1]);
		}
	}
	this.page_number = (Number($("#"+this.divlastpage_id).html())%Math.ceil(this.mysql_interval/this.filter_number) == 0)?
					Math.ceil(this.mysql_interval/this.filter_number):
					Number($("#"+this.divlastpage_id).html())%Math.ceil(this.mysql_interval/this.filter_number);
	this.temp_var_for_page = Number($("#"+this.divlastpage_id).html());
}
function initFilterPage_fnc(){//working
	//initial filtering of page
	this.hideRows();
	this.filterPage();
	//set total number of pages
	$("#"+this.divlastpage_id).html(this.checkTotalPage());
}
function resetPageFilter_fnc(){//working
	//reset values of filtering 
	this.temp_var_for_page = 1;
	this.page_number = 1;
	$("#"+this.txtpagenumber_id).val(1);
	this.assignScroll_val();
	this.hideRows();
	this.filterPage();	
	this.useScroll_val();
	
	if(!this.isOldPaging)
	{
		$("#"+this.divlastpage_id).html(this.checkTotalPage());
	}	
}
function resetTotalPageNumber_increase_fnc(){
	this.hideRows();
	this.filterPage();
	$("#"+this.divlastpage_id).html(this.checkTotalPage());
}

function recheckandsetTotalPage_fnc(){
	var current_displayed_total_pages = Number($("#"+this.divlastpage_id).html());
	if(current_displayed_total_pages != this.checkTotalPage()){
		if(this.page_number > this.checkTotalPage())
		{
			this.page_number = this.checkTotalPage();
			$("#"+this.txtpagenumber_id).val(this.page_number);
		}
		this.filterPage();
		$("#"+this.divlastpage_id).html(this.checkTotalPage());
	}
}
function refreshfilterPage_fnc(){
	
	if((Number($("#"+this.divlastpage_id).html()) == 1) || (Number($("#"+this.divlastpage_id).html()) == 0)){
		this.page_number = 1;
		$("#"+this.txtpagenumber_id).val(1); 
	}
	
	if(Number($("#"+this.txtpagenumber_id).val()) > (Number($("#"+this.divlastpage_id).html())) 
								&& (Number($("#"+this.divlastpage_id).html())) > 0)
	{
		$("#"+this.txtpagenumber_id).val(Number($("#"+this.divlastpage_id).html()));
		this.page_number = this.mysql_interval;
	}
	this.assignScroll_val();
	this.hideRows();
	this.filterPage();
	this.useScroll_val();
	
	if(!this.isOldPaging)
	{
		$("#"+this.divlastpage_id).html(this.checkTotalPage());
	}
	
}
/*
var oldpagenumber="";
function savepage(item){
oldpagenumber=item.value;

}/*
function showing(item,item2){

var lastpage=document.getElementById(item).innerHTML;
var value=item2.value;
if(Number(lastpage)<Number(value)||Number(value)===0||Number(value)===''){
item2.value=oldpagenumber;
}



}*/

function clear_event_fnc(){
	$("#"+this.btnprevious_id).unbind("click");
	$("#"+this.btnnext_id).unbind("click");
	$("#"+this.txtpagenumber_id).unbind("keypress");
	$("#"+this.txtfilternumber_id).unbind("keypress");
}
