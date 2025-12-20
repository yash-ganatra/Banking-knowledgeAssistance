$(document).ready(function(){
    $("body").on("click",".inward",function(){
        var param = $(this).attr("id");
        redirectUrl(param,'/inward/updateinward');
        return false;
    });


    $("body").on("click","#saveairwaybillno",function(){
        var airwaybillnoObject = [];
        airwaybillnoObject.data = {};
        airwaybillnoObject.url =  '/inward/saveairwaybillno';
        airwaybillnoObject.data['batchId'] = $("#batchId").val();
        airwaybillnoObject.data['airway_bill_no'] = $("#airway_bill_no").val();
        airwaybillnoObject.data['functionName'] = 'SaveAirwayBillCallBack';

        /*console.log(updateinwardObject);
        return false;*/

        crudAjaxCall(airwaybillnoObject);
        return false;

    });

    $("body").on("click","#updateinwardstatus",function(){
        var updateinwardObject = [];
        updateinwardObject.data = {};
        updateinwardObject.url =  '/inward/updateinwardstatus';
        updateinwardObject.data['aof_number'] = $("#aof_number").val();
        updateinwardObject.data['functionName'] = 'UpdateInwardStatusCallBack';

        /*console.log(updateinwardObject);
        return false;*/

        crudAjaxCall(updateinwardObject);
        return false;
    });

    $("body").on("keyup","#batch_no,#airway_bill_no",function(){
        getBatchDataApplications()
        return false;
    });

    $("body").on("change","#courierList",function(){
        getBatchDataApplications()
        return false;
    });
});

function getBatchDataApplications()
{
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }
    var tableRemainingHeight = $(".header-navbar").height()/*+$(".accountsgrid").height()*//*+$(".filtergrid").height()*/+210;

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['batch_no'] = $("#batch_no").val();
    tableObject.data['airway_bill_no'] = $("#airway_bill_no").val();
    tableObject.data['courier'] = $("#courierList").val();
    tableObject.data['customer_type'] = $('#customerType input:checked').val();
    if(typeof($('#sentDate').val()) != 'undefined'){
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = "batchApplicationsTable";
    tableObject.url =  '/inward/batchapplications';

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function getBatchFormDataApplications()
{
    var tableRemainingHeight = $(".header-navbar").height()/*+$(".accountsgrid").height()*//*+$(".filtergrid").height()*/+210;

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['batchId'] = $("#batchId").val();
    tableObject.data['table'] = "batchFormApplicationsTable";
    tableObject.url =  '/inward/batchformapplications';

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}