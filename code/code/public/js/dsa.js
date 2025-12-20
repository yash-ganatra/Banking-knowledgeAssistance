
$(document).ready(function(){

            $("#master_table_name").select2({placeholder: "Select Table",allowClear: true});
            $("#api_table_name").select2({placeholder: "Select Table",allowClear: true});
});

 $("body").on("change","#api_table_name",function()
  {
      var tableObject = [];
      tableObject.data = {};
      tableObject['url'] = '/channelid/getapilogdata';
      tableObject.data['table_name'] = $('#api_table_name').find("option:selected").text();
      tableObject.data['functionName'] = 'OaoTableColumnsCallBack';

      crudAjaxCall(tableObject);
  });

 function getOaoDataApplications(url,table,height)
{
    let iDisplayLength = 5;
    var tableRemainingHeight = height;
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['AOF_NUMBER'] = $("#aofNumber").val();
    tableObject.data['MOBILE_NUMBER'] = $("#mobileNumber").val();
    tableObject.data['customer'] = $("#customerName").val();
    tableObject.data['customer_type'] = $('#customerType').val();

    //For filter Date
    tableObject.data['startDate'] = $('#filterStartDate').val();
    tableObject.data['endDate'] = $('#filterEndDate').val();
    //

    tableObject.data['table'] = table;
    tableObject.url =  url;
    datatableAjaxCall(tableObject,tableRemainingHeight, 0,"desc", iDisplayLength);
    return false;
}


    $("body").on("click",".fundReceived",function(){

        var fundReceivedObject = [];
        fundReceivedObject.data = {};
        fundReceivedObject.url =  '/channelid/fundReceived';
        fundReceivedObject.data['oao_id'] = $(this).attr('id');

        fundReceivedObject.data['functionName'] = 'fundReceivedCallBack';

        crudAjaxCall(fundReceivedObject);
        return false;
    });

    $("body").on("click",".oaoReview",function(){
        var param = $(this).attr("id");
        
        redirectUrl(param,'/channelid/oaoReview');
        return false;
    });

  $("body").on("change","#master_table_name",function()
  {
      var tableObject = [];
      tableObject.data = {};
      tableObject['url'] = '/channelid/getoaocolumnsdata';
      tableObject.data['table_name'] = $('#master_table_name').find("option:selected").text();
      tableObject.data['functionName'] = 'OaoTableColumnsCallBack';

      crudAjaxCall(tableObject);
  });

 $("body").on("click",".oao_modal",function(){
        $('.modal-title').html($(this).data('type'));
        $('.modal-body').html($(this).data('title'));
    });

$("body").on("click",'#addColumnDatas',function(){
        var table =  $("#table").val();
        redirectUrl(table,'/channelid/addoaocolumndata');
        return false;
    });

  $("body").on("click",'.edit_oao_table',function(){
        var table =  $("#table").val();
        var rowId =  $(this).attr('id');
        console.log(rowId);
        redirectUrl(table+'.'+rowId,'/channelid/addoaocolumndata');
        return false;
    });
   


$("body").on("click",".saveOaoColumnData",function(){
        var table = $("#table_name").text();

        var columnDataObject = [];
        columnDataObject.data = {};

        // if (table == 'BRANCH') {
        // }else{
        //     columnDataObject['url'] = '/admin/savecolumndata';
        // }

            columnDataObject['url'] = '/channelid/saveoaocolumndata';
        $(".ColumnEditField").each(function() {
            if($(this).val() !== '')
            {
                columnDataObject.data[$(this).attr('name')] = $(this).val();
            }
        });

        if($(this).attr("id") != '')
        {
            columnDataObject.data['rowId'] = $(this).attr("id");
        }

        columnDataObject.data['table'] = table;
        columnDataObject.data['functionName'] = 'SaveOaoColumnDataCallBack';

        //getting the data from here
        crudAjaxCall(columnDataObject);
    });

      $("body").on("click",".updateDsaField",function(){
          var field = $(this).attr('id').split('-')[0];
          var formId = $(this).attr('id').split('-')[1];
          var value = $('#'+field+'-value').val();
          var comment = $('#'+field+'-comment').val();

          if (value == '' || comment == '') {
              $.growl({message: 'Please enter value and comment as mandatory fields'}, {type: 'warning'});
              return false;
          }

          var oaoUpdateObject = [];
          oaoUpdateObject.data = {};
          oaoUpdateObject.url =  '/channelid/updateOaoDetails';
          oaoUpdateObject.data['form_id'] = formId;
          oaoUpdateObject.data['field'] = field;
          oaoUpdateObject.data['value'] = value;
          oaoUpdateObject.data['comment'] = comment;

          oaoUpdateObject.data['functionName'] = 'updateOaoDetailsCallBack';

          crudAjaxCall(oaoUpdateObject);
          return false;
    });

function OaoTableColumnsCallBackFunction(response,object){

     $('#oaoMasterTableDiv').html(response.data).DataTable({"dom": '<"top"f>rt<"bottom"lip><"clear">'});
    $('#oaoMasterTableDiv_length').css('width', '20%').css('display', 'inline');
    $('#oaoMasterTableDiv_info').css('display', 'inline').css('width', '30%').css('margin-left', '37%');
    $('#oaoMasterTableDiv_paginate').css('width', '30%').css('float', 'right').css('display', 'inline').css('margin-top', '2%');
    // $('#addColumnDataDiv').removeClass('display-none');
    $('#api_table_name').attr('disabled',true);
    $('#master_table_name').attr('disabled',true);
    
  }

  function fundReceivedCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }

    setTimeout(function(){
        window.location = baseUrl+'/channelid/oaodashboard';
    },1000);
    return false;
}

function updateOaoDetailsCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    
    setTimeout(function(){
        window.location.reload();
    },1000);
    return false;
}

    function SaveOaoColumnDataCallBackFunction(response,object){
    var baseUrl = $('meta[name="base_url"]').attr('content');
        if(response['status'] == 'success'){
        setTimeout(function(){
            window.location = baseUrl+'/channelid/mastertable';
        },2000);
        }
    return false;
  }
