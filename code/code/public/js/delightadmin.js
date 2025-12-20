$(document).ready(function(){
    $("body").on("click","button[id^='kit_count_approve']",function(){
        var approverIds = [];
        var solIds = [];
        var schemeCodes = [];
        var requestCounts = [];
        var drStatus = [];
        approverIds[0] = $(this).attr("id").split('-')[1];
        solIds[0] = $(this).parent().parent().find('td').eq(1).html();
        schemeCodes[0] = $(this).parent().parent().find('td').eq(2).html();
        requestCounts[0] = $(this).parent().parent().find('td').eq(3).html();
        drStatus[0] = $('#dr_status-'+approverIds[0]).text();

        var approverObject = [];
        approverObject.data = {};
        approverObject.url =  '/delightadmin/markapprover';
        approverObject.data['solIds'] = solIds;
        approverObject.data['schemeCodes'] = schemeCodes;
        approverObject.data['approverIds'] = approverIds;
        approverObject.data['requestCounts'] = requestCounts;
        approverObject.data['drStatus'] = drStatus;
        approverObject.data['functionName'] = 'MarkApproverCallBack';

        crudAjaxCall(approverObject);
        return false;
    });

    $("body").on("click","button[id^='updateStatus']",function(){
        var requestId = $(this).attr("id").split('-')[1];

        var approverObject = [];
        approverObject.data = {};
        approverObject.url =  '/delightadmin/updatedrstatus';
        approverObject.data['requestId'] = requestId;
        approverObject.data['status'] = $(this).parent().prev().html();
        approverObject.data['functionName'] = 'UpdateDRStatusCallBack';

        crudAjaxCall(approverObject);
        return false;
    });

    $('body').on('click','.approval_checkbox',function () {
        if ($("input:checkbox:checked").length > 1) {
            $('.kit-approvals-button').removeClass('display-none');
        }else{
            $('.kit-approvals-button').addClass('display-none');
        }
    });

    $("body").on("click","#multiKitApproval",function(){
        var approverIds = [];
        var solIds = [];
        var schemeCodes = [];
        var requestCounts = [];
        var drStatus = [];
        $(':checkbox:checked').each(function(i){
            approverIds[i] = $(this).val();
            solIds[i] = $(this).parent().parent().find('td').eq(1).html();
            schemeCodes[i] = $(this).parent().parent().find('td').eq(2).html();
            requestCounts[i] = $(this).parent().parent().find('td').eq(3).html();
            drStatus[i] = $('#dr_status-'+approverIds[i]).text();
        });

        var approverObject = [];
        approverObject.data = {};
        approverObject.url =  '/delightadmin/markapprover';
        approverObject.data['solIds'] = solIds;
        approverObject.data['schemeCodes'] = schemeCodes;
        approverObject.data['approverIds'] = approverIds;
        approverObject.data['requestCounts'] = requestCounts;
        approverObject.data['drStatus'] = drStatus;
        approverObject.data['functionName'] = 'MarkApproverCallBack';

        crudAjaxCall(approverObject);
        return false;
    });

    $('body').on('click','.approval_checkbox_all',function (){
        $('.approval_checkbox').click();
    });

    $("body").on("click","button[id^='kit_dispatch']",function(){
        dr_id = $(this).attr("id").split('-')[1];
        solId = $(this).parent().parent().find('td').eq(1).html();
        schemeCode = $(this).parent().parent().find('td').eq(2).html();

        var param = solId+'_'+schemeCode+'_'+dr_id;
        redirectUrl(param,'/delightadmin/kitdispatch');
        return false;
    });

    $("body").on("click","#kitDispatch",function (){
        var kitIds = [];
        $(':checkbox:checked').each(function(i){
            kitIds[i] = $(this).val();
        });

        var approverObject = [];
        approverObject.data = {};
        approverObject.url =  '/delightadmin/updatekitstatus';
        approverObject.data['kitIds'] = kitIds;
        approverObject.data['functionName'] = 'KitDispatchCallBack';

        crudAjaxCall(approverObject);
        return false;
    });

    $("body").on("click","#CheckGenerated",function (){

        var approverObject = [];
        approverObject.data = {};
        approverObject.url =  '/delightadmin/checkgeneratedkits';
        approverObject.data['functionName'] = 'KitsGeneratedCallBack';

        crudAjaxCall(approverObject);
        return false;
    });

    $("body").on("click",".select_all_checkbox",function (){
        $(".kit-checkbox").trigger('click');
    });

    $("body").on("change","#branchId",function (){
        var branchObject = [];
        branchObject.data = {};
        branchObject.url =  '/delightadmin/inventorydetails';
        branchObject.data['branchId'] = $(this).val();
        branchObject.data['functionName'] = 'InventoryDetailsCallBack';

        crudAjaxCall(branchObject);
        return false;
    });

    $("body").on("click",".kitDetails",function (){
        var schemCode  = $(this).attr("id");
        var status  = $(this).attr("status");
        var param = schemCode+'_'+status;

        if ($('#branchId').is(':visible') && $('#branchId').val() != '') {
           var branchId = $('#branchId').val();
           var param = schemCode+'_'+status+'_'+branchId;
        }
        redirectUrl(param,'/delightadmin/kitdetails');
        return false;
    });

});

function getKitCountApprovalTable(url, table, tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};

    tableObject.data['SolId'] = $("#SolId").val();
    tableObject.data['drStatusId'] = $("#drStatus").val();
    tableObject.data['delightSchemeCode'] = '';
    tableObject.data['startDate'] = '';
    tableObject.data['endDate'] = '';
    if($("#delightSchemeCode").val() !== '')
    {
        var delightSchemeCode = $("#delightSchemeCode option:selected").text();
        tableObject.data['delightSchemeCode'] = delightSchemeCode.substring(0,5);
    }
    if($('#sentDate').val() !== ''){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function MarkApproverCallBackFunction(response, object)
{
    if(response['status'] === "success"){
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            location.reload();
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}
function KitDispatchCallBackFunction(response, object){
    if(response['status'] === "success"){
        $.growl({message: response['msg']},{type: response['status']});

    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}

function getKitDispatchTable(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};

    tableObject.data['dr_no'] = $("#dr_no").val();
    tableObject.data['kitNumber'] = $("#kitNumber").val();
    tableObject.data['customerId'] = $("#customerId").val();
    tableObject.data['accountNumber'] = $("#accountNumber").val();

    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function UpdateDRStatusCallBackFunction(response, object)
{
    if(response['status'] === "success"){
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            location.reload();
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}

function KitDispatchCallBackFunction(response,object)
{
    if(response['status'] === "success"){
        var baseUrl = $('meta[name="base_url"]').attr('content');
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            window.location = baseUrl+"/delightadmin/dashboard";
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}

function KitsGeneratedCallBackFunction(response,object)
{
    if(response['status'] == "success"){
        var baseUrl = $('meta[name="base_url"]').attr('content');
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            window.location = baseUrl+"/delightadmin/dashboard";
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}

function InventoryDetailsCallBackFunction(response,object)
{
    if(response != '')
    {
        $("#inventoryDetails").html('');
        $("#inventoryDetails").html(response);
    }
    return false;
}
