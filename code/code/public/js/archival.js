$(document).ready(function(){
    $("body").on("click",".archival",function(){
    
        let currentScreen = window.location.href.substr(window.location.href.lastIndexOf('/') +1 );
        redirectUrl($(this).attr('data-aof_number') +  '.' + currentScreen, '/archival/editarchivalexcelrecord');

        // $(".savearchival").attr('data-formId', $(this).attr("id"));
        // $(".savearchival").attr("id",$(this).attr('data-archivalId'));
        // $('#addarchivalno').modal('toggle');
        return false;
    });

    // $("body").on("click",".addarchivalno",function(){
    //     $("#formId").val($(this).attr("id"));
    //     $("#archival_ref_one").val($(this).parent().prev().prev().prev().html());
    //     $("#archival_ref_two").val($(this).parent().prev().prev().html())
    //     $(".savearchival").attr("id",$(this).attr('data-archivalId'));
    //     $('#addarchivalno').modal('toggle');
    //     return false;
    // });

    // $("body").on("click",".savearchival",function(){
    //     var saveBatchObject = [];
    //     saveBatchObject.data = {};
    //     saveBatchObject.url =  '/archival/savearchival';
    //     saveBatchObject.data['id'] = $(this).attr('data-formId');
    //     saveBatchObject.data['archivalId'] = $(this).attr('id');
    //     saveBatchObject.data['archival_ref_one'] = $("#archival_ref_one").val();
    //     saveBatchObject.data['archival_ref_two'] = $("#archival_ref_two").val();
    //     saveBatchObject.data['functionName'] = 'SaveArchivalNumberCallBack';

    //     crudAjaxCall(saveBatchObject);
    //     return false;
    // });
});


function getUserApplications(url,table,tableRemainingHeight)
{
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['customer'] = $("#customerName").val();
    if($("#applicationStatus").val() != 'undefined')
    {
        tableObject.data['status'] = $("#applicationStatus").val();
    }
    tableObject.data['customer_type'] = $('#customerType').val();
    if(typeof($('#sentDate').val()) != 'undefined'){
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}