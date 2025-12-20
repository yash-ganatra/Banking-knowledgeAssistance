$(document).ready(function(){
    $('.customer-type').on('change', function() {
        getUserApplications('/bank/dispatchapplications','dispatchApplicationsTable');
        return false;
    });

    $("body").on("click",".createBatch",function(){
        if ($('.dispatchapplications:checked').length == 0) {
           $.growl({message: "Please Select At least One Application"},{type: "warning"});
           return false;
        }
        var val = [];
        var aofNumbers = [];
        $(':checkbox:checked').each(function(i){
            val[i] = $(this).val();
            aofNumbers[i] = $(this).closest('td').next().html() + '-' + $(this).closest('td').next().next().html();
        });
        var creeateBatchObject = [];
        creeateBatchObject.data = {};
        creeateBatchObject.url =  '/bank/createbatchid';
        creeateBatchObject.data['applicantIds'] = val;
        creeateBatchObject.data['aofNumbers'] = aofNumbers;
        creeateBatchObject.data['functionName'] = 'CreateBatchIdCallBack';

        crudAjaxCall(creeateBatchObject);
        return false;
    });

    $("body").on("click",".printeairBatch",function(){
        var creeateBatchObject = [];
        creeateBatchObject.data = {};
        creeateBatchObject.url =  '/bank/printairbatchid';
        creeateBatchObject.data['batchId'] = $(this).attr('id');
        creeateBatchObject.data['functionName'] = 'PrintAirBatchIdCallBack';      
  
        crudAjaxCall(creeateBatchObject);
        return false;        
    });

    $("body").on("click",".editairwaybill",function(){
        // $("#batchId").html();    
        // $('#airwaybill_no').val();
        var creeateBatchObject = [];
        creeateBatchObject.data = {};
        creeateBatchObject.url =  '/bank/editairwaybillno';
        creeateBatchObject.data['batchId'] = $(this).attr('id');
        creeateBatchObject.data['functionName'] = 'EditAirwaybillCallBack';

        crudAjaxCall(creeateBatchObject);
        return false;        
    });
    
    $("body").on("click",".updatedairwaybill", function(e){
        if($('#airwaybill_no').val()==''){
            $.growl({message: "Please Insert data"},{type: "warning"});
            return false;
        }
        if($('#courierData').val()==''){
            $.growl({message: "Please Select Company Name"},{type: "warning"});
            return false;
        }
        var updateBatchObject = [];
        updateBatchObject.data = {};
        updateBatchObject.url =  '/bank/updatedairwaybill';
        updateBatchObject.data['batch_id'] = $("#batchId").text();
        updateBatchObject.data['airwaybill_no'] = $("#airwaybill_no").val();
        updateBatchObject.data['courierData'] = $("#courierData").val();
        updateBatchObject.data['functionName'] = 'UpdateAirwaybillCallBack';

        crudAjaxCall(updateBatchObject);
        return false;
    });

    $("body").on("click",".saveBatch", function(e){
        if($('#airwaybill_number').val()==''){
            $.growl({message: "Please Insert data"},{type: "warning"});
            return false;
        }
        if($('#courier').val()==''){
            $.growl({message: "Please Select Company Name"},{type: "warning"});
            return false;
        }
        var saveBatchObject = [];
        saveBatchObject.data = {};
        saveBatchObject.url =  '/bank/savebatch';
        saveBatchObject.data['batch_id'] = $("#batchId").html();
        saveBatchObject.data['airwaybill_number'] = $("#airwaybill_number").val();
        saveBatchObject.data['accountIds'] = $(this).attr('id');
        saveBatchObject.data['courier'] = $("#courier").val();
        saveBatchObject.data['functionName'] = 'SaveBatchCallBack';

        crudAjaxCall(saveBatchObject);
        return false;
    });

    $("body").on("click","#saveairwaybillno",function(){
        var saveBatchObject = [];
        saveBatchObject.data = {};
        saveBatchObject.url =  '/bank/saveairwaybillno';
        saveBatchObject.data['id'] = $(this).parent().attr('id').split('_')[1];
        saveBatchObject.data['airway_bill_no'] = $("#airway_bill_no").val();
        saveBatchObject.data['functionName'] = 'SaveAirwayBillNumberCallBack';

        crudAjaxCall(saveBatchObject);
        return false;
    });
});