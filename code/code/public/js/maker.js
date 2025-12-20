$(document).ready(function(){
    $("body").on("click","#addIndent",function(){
        var schemeCode  = $(this).closest('tr').find('.kitDetails').html();
        var kitAvailableCount  = $(this).parent().prev().html();


        $("#kitRequestCount").attr('min', _configKitRequestThreshold.DEFAULT_MIN);
        $("#kitRequestCount").attr('max', _configKitRequestThreshold.DEFAULT_MAX);
        $("#kitRequestCount").val(_configKitRequestThreshold.DEFAULT_MIN);

        if(_configKitRequestThreshold.SPECIAL_SCHEME.includes(schemeCode.trim())){
            $("#kitRequestCount").attr('min', _configKitRequestThreshold.SPECIAL_MIN);
            $("#kitRequestCount").attr('max', _configKitRequestThreshold.SPECIAL_MAX);
            $("#kitRequestCount").val(_configKitRequestThreshold.SPECIAL_MIN);
        }

        $("#schemeCode").html(schemeCode);
        $("#kitAvailableCount").html(kitAvailableCount);
        $('#addIndentModal').modal('toggle');
        return false;
    });

    $("body").on("click","#saveIndent",function(){
        if (!checkRequestCount()) {
            return false;
        }

        $('#saveIndent').addClass('disabled','disabled');

        var indentObject = [];
        indentObject.data = {};
        indentObject.url =  '/maker/saveindent';
        indentObject.data['SCHEME_CODE'] = $.trim($("#schemeCode").html());
        indentObject.data['REQUEST_COUNT'] = $("#kitRequestCount").val();
        indentObject.data['functionName'] = 'SaveIndentCallBack';

        crudAjaxCall(indentObject);
        return false;
    });

    $("body").on("click",".kitDetails",function (){
        // var schemeCode = $(this).attr('id')+'-'+$(this).parent().next().html();
        var status = '';
        var data = '';
        var schemeCode = $(this).attr('id');
        if(typeof($(this).attr('status')) != "undefined")
        {
            status = $(this).attr('status');
        }
        data = schemeCode+'_'+status;
        redirectUrl(data,'/maker/kitdetails');
    })

    /*$("body").on("click","span[id^=kitStatus-]",function(){
        var schemeCode = $("#schemeCode").val();
        var KitStatus = $(this).attr('id').split('-')[1];
        var param = schemeCode+'_'+KitStatus;
        redirectUrl(param,'/maker/kitdetails');
        return false;
    });*/

    $("body").on("change",".delightSchemeCodes, .delightKitStatus",function(){
        getDKitTable();
    });

    $("body").on("keyup","#kitNumber, #customerID, #accountID",function(){
        getDKitTable();
    });

    $('body').on('click','#clear-dates',function () {
        $('.date-input').val('');
        getDKitTable();
    });

    $('body').on('click','.open-seek-approval-modal',function () {
        /*for seekApproval table*/
        var kitNumber = $(this).attr('id').split('-')[1];
        var request_comment = $('#request_comment-'+kitNumber).text();
        var status = $("#status-"+kitNumber).text();
        var approval_comment = $('#approval_comment-'+kitNumber).text();

        if ((request_comment == '') || (status == '')) {
            $('#status').val('');
            $('#request_comment').val('');
        }else if (approval_comment == '') {
            $('#approval_comment').val('');
        }

        if (status == 'Missing_pa') {
            $('#sendApprovalRadio-10').val(14).prop('checked', true);
        }else if(status == 'Damaged_pa'){
            $('#sendApprovalRadio-6').val(12).prop('checked', true);
        }else if(status == 'Destroyed_pa'){
            $('#sendApprovalRadio-9').val(13).prop('checked', true);
        }
        $('.modal-kit-number').text(kitNumber);
        $('#modal_request_comment').text(request_comment);

        // /*if status already approved*/
        // if ($('#status-'+kitNumber).text() == $('#status-'+kitNumber).text())
        // {
        //     $('#approval_comment').val(approval_comment).attr('disabled',true);
        //     $('.approved').attr('disabled',true);
        // }

        /*for kit details table*/
        // if ($('#cr_status-'+kitNumber).text() != '')
        // {
        //     if (status == 'MISSING') {
        //         $('#seekApprovalRadio-10').val(10).prop('checked', true);
        //     }else if(status == 'DAMAGED'){
        //         $('#seekApprovalRadio-6').val(6).prop('checked', true);
        //     }else if(status == 'DESTROYED'){
        //         $('#seekApprovalRadio-9').val(9).prop('checked', true);
        //     }

        //     $('.seek-approval').attr('disabled',true);

        //     var requestComment = $('#request_comment-'+kitNumber).text();
        //     $('#request-comment').val(requestComment).attr('disabled', true);
        //     $('.statusUpdateApproval').attr('disabled', true);
        // }
    });

    $('body').on('click','.statusUpdateApproval',function () {
        var kitNumber = $('.modal-kit-number').text();
        var approvalStatus = $("input[name='seekApprovalRadio']:checked").val();
        var requestComment = $('.approval-comment').val();

        var requestApprovalObject = [];
        requestApprovalObject.data = {};
        requestApprovalObject.url =  '/maker/statusupdateapproval';
        requestApprovalObject.data['schemeCode'] = $("#delightSchemeCode option:selected").text();
        requestApprovalObject.data['kitStatus'] = $("#delightKitStatus option:selected").text();
        requestApprovalObject.data['kit_number'] = kitNumber;
        requestApprovalObject.data['approval_status'] = approvalStatus;
        requestApprovalObject.data['request_comment'] = requestComment;
        requestApprovalObject.data['functionName'] = 'StatusUpdateApprovalCallBack';

        crudAjaxCall(requestApprovalObject);
        return false;
    });

    $("body").on("click",".kit-approval-button",function(){
        // var kitNumber = $("input:checkbox:checked").attr('id').split('-')[1];
        // var status = $("#status-"+kitNumber).text();
        // var request_comment = $("#request_comment-"+kitNumber).text();
        // $('.modal-kit-number').text(kitNumber);
        // $('#request_comment').text(request_comment);
        // if (status == 'MISSING') {
        //     $('#sendApprovalRadio-10').val(10).prop('checked', true);
        // }else if(status == 'DAMAGED'){
        //     $('#sendApprovalRadio-6').val(6).prop('checked', true);
        // }else if(status == 'DESTROYED'){
        //     $('#sendApprovalRadio-9').val(9).prop('checked', true);
        // }
        // $('#request_comment').val(status);

        var kits = [];
        if ($("input:checkbox:checked").length > 1) {
            for (var i = 0; i < $(".kit-checkbox:checked").length; i++) {
                var kitNumber = $(".kit-checkbox:checked")[i].id.split('-')[1];
                var status = $("#status-"+kitNumber).text();

                if (status == 'Missing_pa') {
                    status = '10';
                }else if(status == 'Damaged_pa'){
                    status = '6';
                }else if(status == 'Destroyed_pa'){
                    status = '9';
                }
                kits.push({'kitNumber': kitNumber, 'status': status});
            }

            var submitApprovalObject = [];
            submitApprovalObject.data = {};
            submitApprovalObject.url =  '/checker/submitApproval';
            submitApprovalObject.data['approval_kits'] = kits;
            submitApprovalObject.data['functionName'] = 'SubmitApprovalCallBack';

            crudAjaxCall(submitApprovalObject);
            return false;
        }
    });

    $("body").on("click",".approved",function(){
        var kitNumber = $('.modal-kit-number').text();
        var status = $("input[name='sendApprovalRadio']:checked").val();
        var updatedStatus = '';
        if (status == '12') {
            updatedStatus = 6;
        }else if(status == '13'){
            updatedStatus = 9;
        }else if(status == '14'){
            updatedStatus = 10;
        }
        var approval_comment = $(".approved-comment").val();

        var submitApprovalObject = [];
        submitApprovalObject.data = {};
        submitApprovalObject.url =  '/checker/submitApproval';
        submitApprovalObject.data['kit_number'] = kitNumber;
        submitApprovalObject.data['status'] = updatedStatus;
        submitApprovalObject.data['approval_comment'] = approval_comment;
        submitApprovalObject.data['functionName'] = 'SubmitApprovalCallBack';

        crudAjaxCall(submitApprovalObject);
        return false;
    });

    $("body").on("click","input[id^=kitStatus-]",function(){
        $("#updateKitStatus").removeClass('display-none');
    });

    $("body").on("click","#updateKitStatus",function(){
        var kitIds = [];
        var kitStatus = [];
        var differentStatus = 0;
        var status = '';

        $('.kit-checkbox:checked').each(function(i){
            kitIds[i] = $(this).val();
            status = $(this).parent().parent().find('td').eq(7).html();

            if ($('#status_tooltip-'+kitIds[i]).is(":visible")) {
                status = $('#status_tooltip-'+kitIds[i]).find('.mytooltip').text();
            }

            if(kitStatus.length !== 0)
            {

                if(jQuery.inArray(status, kitStatus) !== 0)
                {
                    differentStatus = 1;
                }
            }
            kitStatus[i] = status;

        });
        if(differentStatus)
        {
            $.growl({message: 'Select record with same status'},{type: 'warning'});
        }else{
            $("#request_comment").val('');
            $("#dKitStatus").val('').trigger("change");
            $("#delightStatus").val('').trigger("change");
            if (status == "Allocated") {
                $("#dKitStatus option[value='7']").remove();
                if ($("#dKitStatus option[value='11']").length < 1) {
                    $("#dKitStatus").append('<option value="11">UNALLOCATED</option>');
                }
            }else{
                if ($("#dKitStatus option[value='7']").length < 1) {
                    $("#dKitStatus").append('<option value="7">ALLOCATE TO SALES</option>');
                }
                $("#dKitStatus option[value='11']").remove();
            }
            if(status == "Unallocated" || status == "Allocated")
            {
                $("#delightStatusDiv").addClass('display-none');
                $("#dKitStatusDiv").removeClass('display-none');
                $("#usersDiv").addClass('display-none');
                $("#commentDiv").addClass('display-none');
            }
            if(status == "Dispatched"){
                $("#delightStatusDiv").removeClass('display-none');
                $("#dKitStatusDiv").addClass('display-none');
                $("#usersDiv").addClass('display-none');
                $("#commentDiv").addClass('display-none');
            }
            $("#updateStatus").attr("KitIds",kitIds);
            $('#seekApproval').modal('toggle');
        }
        return false;
    });

    // $("body").on("click","#delightStatus",function (){
    //     if($(this).val() == 4)
    //     {
    //         $("#dKitStatusDiv").removeClass('display-none');
    //     }else if($(this).val() == 5){
    //         if($("#dKitStatusDiv").is(":visible"))
    //         {
    //             $("#dKitStatusDiv").addClass('display-none');
    //         }
    //         if($("#usersDiv").is(":visible"))
    //         {
    //             $("#usersDiv").addClass('display-none');
    //         }
    //     }
    //     return false;
    // })

    $("body").on("click","#dKitStatus",function (){
        if($(this).val() == 7)
        {
            $("#usersDiv").removeClass('display-none');
            $("#commentDiv").addClass('display-none');

        }else{
            if ($(this).val() != 11) {
                $("#commentDiv").removeClass('display-none');
            }else{
                $("#commentDiv").addClass('display-none');
            }

            if($("#usersDiv").is(":visible"))
            {
                $("#usersDiv").addClass('display-none');
            }
        }
        return false;
    })

    $("body").on("click","#updateStatus",function (){
         var kitStatusObject = [];
        kitStatusObject.data = {};
        kitStatusObject.url =  '/maker/updatekitstatus';
        kitStatusObject.data['kitIds'] = $("#updateStatus").attr("KitIds");
        kitStatusObject.data['Status'] = $("#delightStatus").val();
        kitStatusObject.data['request_comment'] = $("#request_comment").val();
        if(($("#delightStatus").val() == 11) && ($("#dKitStatus").val() != ''))
        {
            if ($("#delightStatus").val() == 11) {
                kitStatusObject.data['Status'] = $("#dKitStatus").val();

            }

            kitStatusObject.data['Status'] = $("#dKitStatus").val();
            if($("#dKitStatus").val() == 7)
            {
                kitStatusObject.data['userId'] = $("#users").val();
            }else{
                kitStatusObject.data['request_comment'] = $("#request_comment").val();
            }
        }else if(($("#delightStatus").val() == '') && ($("#dKitStatus").val() != '')){

            kitStatusObject.data['Status'] = $("#dKitStatus").val();

            if ($("#users").val() != '') {
                kitStatusObject.data['userId'] = $("#users").val();
            }
            if($("#dKitStatus").val() == 7)
            {
                kitStatusObject.data['userId'] = $("#users").val();
            }else{
                kitStatusObject.data['request_comment'] = $("#request_comment").val();
            }
        }
        kitStatusObject.data['functionName'] = 'updateKitStatusCallBack';

        crudAjaxCall(kitStatusObject);
        return false;
    });

    $("body").on("click",".kit-checkbox",function(){
        if ($("input:checkbox:checked").length > 1) {
            $('.kit-approval-button').removeClass('display-none');
            $('#updateKitStatus').removeClass('display-none');
        }else{
            $('.kit-approval-button').addClass('display-none');
            $('#updateKitStatus').removeClass('display-none');
        }
        if ($("input:checkbox:checked").length < 1) {
            $('#updateKitStatus').addClass('display-none');
        }

    });

    $("body").on("click",".select_all_checkbox",function (){
        $(".kit-checkbox").trigger('click');
    });

});

function getDKitTable(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['delightKitStatus'] = $("#delightKitStatus option:selected").val();
    tableObject.data['kitNumber'] = $("#kitNumber").val();
    tableObject.data['customerID'] = $("#customerID").val();
    tableObject.data['accountID'] = $("#accountID").val();
    if($("#branchID").val() !== '')
    {
        tableObject.data['branchID'] = $("#branchID").val();
    }
    if($("#delightSchemeCode").val() !== '')
    {
        tableObject.data['delightSchemeCode'] = $("#delightSchemeCode option:selected").text().substr(0,5);
    }

    if($('#sentDate').val() !== '')
    {
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

function KitTableCallBackFunction(response,object)
{
    console.log(response);
    console.log(object);
    if(response['status'] == "success"){
        $.growl({message: 'redirect to table'},{type: response['status']});
    }else{
        // $.growl({message: 'fail to redirect'},{type: "warning"});
    }
    return true;
}

function SaveIndentCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            window.location = baseUrl+"/maker/dashboard";
        }, 1000);
    }else{
        $('#saveIndent').removeClass('disabled');
        $.growl({message: response['msg']},{type: 'warning'});
    }
    return false;
}

function StatusUpdateApprovalCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            var schemeCode = object.data['schemeCode'];
            var KitStatus = object.data['kitStatus'];
            var param = schemeCode+'_'+KitStatus;
            redirectUrl(param,'/maker/kitdetails');
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: 'warning'});
    }
    return false;
}

function SubmitApprovalCallBackCallBackFunction(response,object) {
    var baseUrl = $('meta[name="base_url"]').attr('content');

   if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            var schemeCode = object.data['schemeCode'];
            var KitStatus = object.data['kitStatus'];
            var param = schemeCode+'_'+KitStatus;
            redirectUrl(param,'/checker/dashboard');
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: 'warning'});
    }
    return false;
}

function updateKitStatusCallBackFunction(response,object)
{
    if(response['status'] == "success"){
        $('#seekApproval').modal('toggle');
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            var schemeCode = $("#delightSchemeCode option:selected").text();
            redirectUrl(schemeCode,'/maker/kitdetails');
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: 'warning'});
    }
}
function checkRequestCount() {
    var kitRequestCount = $("#kitRequestCount").val();
    if ((kitRequestCount % 5 != 0) || (kitRequestCount == 0)) {
        $.growl({message: "Please add Kit request count as multiple of 5"},{type: 'warning'});
        return false;
    }else{
        return true;
    }
    return false;
}
