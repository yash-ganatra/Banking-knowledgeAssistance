$(document).ready(function(){
    addSelect2('clusterlist','Cluster');
    addSelect2('regionlist','Region');
    addSelect2('zonelist','Zone');
    addSelect2('branch_list','Branch');
    addSelect2('rolelist','Role');

    $("body").on("change","#table_name",function(){
        $("#filterInputDiv").empty();

        if ($(this).find("option:selected").text() == 'ETB_CUST_DETAILS') {
            $('#addColumnData').addClass('display-none');
        }else{
            $('#addColumnData').removeClass('display-none');
        }
        
        if ($(this).find("option:selected").text() == 'BRANCH') {
            $("#branch-id-filter").removeClass('display-none');
        }else{
            $("#branch-id-filter").addClass('display-none');
        }
        mastertablebyColumn(this);
    });

    $("body").on("click",'#addColumnData',function(){
        var table =  $("#masterTableDiv").attr('table');
        if (table == 'BRANCH') {
            //$.growl({message: "Please check with IT team!"},{type: "warning"});
            redirectUrl(table,'/admin/addbranchtabledata');
        }else{
            redirectUrl(table,'/admin/addtabledata');
        }
        return false;
    });

    $("body").on("click",'.editTableData',function(){
        var table =  $("#masterTableDiv").attr('table');
        var rowId =  $(this).attr('id');
        if (table == 'BRANCH') {
            redirectUrl(table+'.'+rowId,'/admin/addbranchtabledata');
        }else{
            redirectUrl(table+'.'+rowId,'/admin/addtabledata');
        }
        return false;
    });

    $("body").on("click",".saveColumnData",function(){
        var table = $("#table_name").text();

        var columnDataObject = [];
        columnDataObject.data = {};

        if (table == 'BRANCH') {
            columnDataObject['url'] = '/admin/savebranchcolumndata';
        }else{
            columnDataObject['url'] = '/admin/savecolumndata';
        }

        $(".ColumnEditField").each(function() {
            if($(this).val() !== '')
            {
                columnDataObject.data[$(this).attr('name')] = $(this).val();
            }
        });

        if (table == 'BRANCH') {
            var is_active = 0;
            if($('#is_active').prop('checked')){
                is_active = 1;
            }
            columnDataObject.data['is_active'] = is_active;
        }
        
        if($(this).attr("id") != '')
        {
            columnDataObject.data['rowId'] = $(this).attr("id");
        }

        columnDataObject.data['table'] = table;
        columnDataObject.data['functionName'] = 'SaveColumnDataCallBack';

        //getting the data from here
        crudAjaxCall(columnDataObject);
    });

    $("body").on("click",".export-excel",function(e){
        e.preventDefault();
        var table_name = $(this).attr('id');
        
        $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});
        $("#"+table_name).DataTable().page.len( -1 ).draw();

        if($("#"+table_name).DataTable().page.len() == -1){
            setTimeout(function(){
                $("#"+table_name).DataTable().button('0').trigger();
                $.growl({message: "Excel file Generated"},{type: "success"});
            },5000);

        }
    });
});

function TableColumnsCallBackFunction(response,object)
{
    var filterColumns = getFilterInputField(response.table);
    var navbarHeight = $(".header-navbar").height();
      var filterHeight = $(".filtergrid").height();
      var paginationHeight = 300;
      if(isNaN(navbarHeight)) navbarHeight = 25;
      if(isNaN(filterHeight)) filterHeight = 25;

    var tableRemainingHeight = navbarHeight+filterHeight+paginationHeight;
    $("#masterTableDiv").html('');
    var table = '<table class="table table-striped table-bordered nowrap MasterTable">'+
                    '<thead>'+
                        '<tr>'+
                        '</tr>'+
                    '</thead>'+
                '</table>';
    $("#masterTableDiv").append(table);

    $("#export-excel-div").html('');
    var exportExcelDiv = '<div class="col-md-12 filter-icon-main dis">'+
                            '<i class="fa fa-file-excel-o fa-1x export-excel" aria-hidden="true" data-toggle="tooltip" id="'+response.table+'" data-placement="top" title="Export Data"><span class="export ml-2">Export Excel</span></i>'+
                        '</div>';
    $("#export-excel-div").append(exportExcelDiv);

    
    for (var i = 0; i < filterColumns.length; i++) {
        var filterColumn = filterColumns[i].replace(/ /g,'');
	    var filterColumnValue = $("#"+filterColumn).val();
        if (!$("#"+filterColumn).is(':visible')) {
    	    if (typeof(filterColumnValue) == 'undefined' || filterColumnValue == '') {
                if (!$("#filterInputDiv").find('.filterInputColumn').length > filterColumns.length) {
    		      $("#filterInputDiv").empty();
                }
    	    	filterColumnValue = '';
    	    }
    		
    		var filterInput = '<div class="col-sm-6"> <input type="text" class="form-control ColumnEditField filterInputColumn" id="'+filterColumn+'" name="'+filterColumn+'" placeholder="'+filterColumn+'" value="'+filterColumnValue+'" > </div>';
    	    if (typeof(filterColumnValue) != 'undefined' && filterColumnValue != '') {
    		}else{
    			$("#filterInputDiv").append(filterInput);
    		}
        }
    }

    $("#masterTableDiv").attr("table",response.table);
    var tableHeader = '';
    $(response.columns).each(function(i,column){
        tableHeader += '<th>'+column+'</th>';
    });
    $("#masterTableDiv").find('table').find('thead tr').append(tableHeader);
    $("#masterTableDiv").find('table').attr("id",response.table);
    $("#addColumnDataDiv").removeClass('display-none');
    object.data['table'] = response.table;
    if (response.table == 'BRANCH') {
        object.data['branch_id'] = $("#branch_id").val();
        object.url =  '/admin/branchtabledata';

    }else{
        object.data['filterColumns'] = {};
    	for (var i = 0; i < filterColumns.length; i++) {
            var filterColumn = filterColumns[i].replace(/ /g,'');
	        var filterInputField = $("#"+filterColumn).attr('id');
	        object.data['filterColumns'][filterInputField] = $('#'+filterColumn).val();

	    }
        object.url =  '/admin/mastertabledata';
    }

    datatableAjaxCall(object,tableRemainingHeight,0,'asc');
}

function SaveColumnDataCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    setTimeout(function(){
        window.location = baseUrl+'/admin/mastertables';
    },2000);
    return false;
}
function mastertablebyColumn(select) {
    var tableObject = [];
    tableObject.data = {};
    tableObject['url'] = '/admin/getcolumnsbytable';
    tableObject.data['table_name'] = $(select).find("option:selected").text();
    tableObject.data['functionName'] = 'TableColumnsCallBack';

    //getting the data from here
    crudAjaxCall(tableObject);
}

function getFilterInputField(table) {
    
    if (typeof(_tableColumnsForfilter[table]) == 'undefined') {
        return [];
    }
	var filterCounts = _tableColumnsForfilter[table].split(',').length;
	var filterColumn = _tableColumnsForfilter[table];

	if (filterCounts>2) {
		$.growl({message: "Error! Invalid count of filters found. Please contact NPC admin"},{type: "warning"});
	}

	if (filterCounts == 1) {
		return [filterColumn];
	}else{
		var filterColumn = _tableColumnsForfilter[table].split(',');
		return [filterColumn[0],filterColumn[1]];
	}
    
}