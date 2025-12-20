$(document).ready(function(){	

    $("body").on("click",".bankReview",function(){
        if(decodeURIComponent(getCookie("XSRF-TOKEN")) == ''){
            $.growl({message: "Please login session expire!"},{type: "warning"});
            setTimeout(function(){
                var baseUrl = $('meta[name="base_url"]').attr('content');
                window.location = baseUrl+'/login';
            },5000);
            return false;
        }else{
        var param = 1 + '_' + $(this).attr("id");
        if(typeof($(this).attr("status")) != "undefined")
        {
            param = 0 + '_' + $(this).attr("id");
        }
        
        redirectUrl(param,'/bank/addaccount');
        return false;
        }
    });

    $("#image_preview").css('display','none');

    $('.customer-type').on('change', function() {
        getUserApplications('/bank/userapplications','userApplicationsTable');
        return false;
    });

    $('.filter-close').on('click', function() {
       clearFilters();
    });

    $('body').on("click",".upload_document",function(){
        var applicantId = $(this).attr("data-id").split('-')[1];
        if(!$(this).hasClass("is_huf")){
            if (jQuery.inArray($(this).attr("id"), ["proof_of_identity", "proof_of_address", "proof_of_current_address"]) != "-1") {
                $.growl({ message: "Please select " + $(this).attr("id").replace(/_/g, ' ') }, { type: "warning" });
            return false;
        }
        }
        
        if ($(this).attr('data-doc') == 'pdf') {
  
            $('#inputImage').attr('data-doc', 'pdf');
            $('#inputImage').attr('accept', 'application/pdf');
        } else {
   
            $('#inputImage').attr('data-doc', 'image');
            $('#inputImage').attr('accept', 'image/*');
        }

        $(".document_name").find('h1').html('');
        $(".document_name").find('h1').html('Upload '+$(this).attr("data-document"));
        $(".document_name").attr('id',$(this).attr("data-id"));
        $(".document_name").attr('name',$(this).attr("data-name"));
        var id = $(".document_name").attr('id');
        if(id.substr(-4) == '_img'){
            $(".document_name").attr('value',$(this).attr("data-value"));
        }
        $(".image_preview").css('display','none');
        $("#img-preview-div").addClass('display-none');
        $($(this).data('target')).modal('toggle');
        $("#inputImage").click();
        return false;
    });

    // $('body').on('change','#inputImage',function() {
    //     var inputImagevalue = $(this).val().toLowerCase();
    //     if(inputImagevalue.substr(-3) == 'pdf'){
    //         $(".saveDocument").attr('id','uploadPdf');
    //          $(".saveDocument").removeClass("btn-lblue").addClass("btn-primary").attr("disabled",false);
    //     }else{
    //         $(".image_preview").css('display','block');
    //         $(".saveDocument").attr('id','uploadImage');
    //     }
    //     return false;
    // });

    $('body').on('change','#inputImage',function() {
        var documentType = $(this).attr('data-doc');
       
        var documentPath = $('#inputImage').val();
        var extension = documentPath.substr(-3).toLowerCase();
      
        if(documentType == 'pdf'){
            $(".saveDocument").attr('id','uploadPdf');
            $('#uploadPdf').removeAttr('disable');
             $(".saveDocument").removeClass("btn-lblue").addClass("btn-primary").attr("disabled",false);

        }else{
       
            $(".image_preview").css('display','block');
            $(".saveDocument").attr('id','uploadImage');
        }
        return false;
    });

    $('body').on('click','.image_crop',function(){
/*        var result = $('.preview_image').cropper('getCroppedCanvas', {
            width: 464,
            height: 432,
            beforeDrawImage: function(canvas) {
                var context = canvas.getContext('2d');
                context.imageSmoothingEnabled = false;
                context.imageSmoothingQuality = 'high';
            },
        });*/
		var result = $('.preview_image').cropper('getCroppedCanvas', {
			minWidth: 256,
			minHeight: 256,
			maxWidth: 4096,
			maxHeight: 4096, 
			fillColor: '#fff',
			imageSmoothingEnabled: true,
			imageSmoothingQuality: 'high',
        });
        var base64 = result.toDataURL('image/jpeg', (40 / 100)); 
        $("#img-preview-div").removeClass('display-none');
        $(".crop_image_preview").attr('src',base64);
        $(".saveDocument").removeClass("btn-lblue").addClass("btn-primary").attr("disabled",false);
    });

    $('body').on('click','#uploadImage',function(){
        $(".saveDocument").addClass("btn-lblue").removeClass("btn-primary").attr("disabled",true);
        var id = $(".document_name").attr('id');
        var uploadImageObject = [];
        uploadImageObject.data = {};
        uploadImageObject.url =  '/bank/fileupload';
        uploadImageObject.data['image'] = $(".crop_image_preview").attr('src');
        uploadImageObject.data['image_type'] = $(".document_name").attr('id');
        uploadImageObject.data['name'] = $(".document_name").attr('name');
        uploadImageObject.data['declarations'] = $('.declarationform').attr('name');
        
        

        
        if(id.substr(-4) == '_img'){
            uploadImageObject.data['form_id'] = $(".document_name").attr('value');
        }
        uploadImageObject.data['functionName'] = 'ImageCallBack';

        crudAjaxCall(uploadImageObject);
        return false;
    });

    $('body').on('click','#uploadPdf',function(){
   
        var docFakePath = $('#inputImage').val();
        var docName = docFakePath.split('\\')[2];
        var extension =  docFakePath.split('.')[1];
        var evId =  $('.document_name').attr('id');
        var module = 'cube';
        var pdfdata = new FormData();
        jQuery.each(jQuery('#inputImage')[0].files, function(i, file) {
            pdfdata.append('pdf_file', file);
            pdfdata.append('documentId', evId);
            pdfdata.append('module',module);
        });

        if(extension != ''){

           if(extension == 'pdf'){
    
        pdfDocumentUpload(pdfdata);
        }else{
            $.growl({message:'Please choose a pdf file'},{type:'warning'});
            return false;
        }
    }
});

    //     pdfDocumentUpload(pdfdata);
    // });
    

    $("body").on("click",".previoustab",function(){        
        $('a[data-id="'+$(this).attr('tab')+'"]').removeClass('text-muted-lnavs').unbind("click", false);
        // 22May23 - For BS5 - commented below line
        // $('a[data-id="'+$(this).attr('tab')+'"]').click();
        $('[href=#'+$(this).attr('tab')+']').tab('show'); 

        return false;
    });

    $("body").on("click",".deleteImage",function(){
        
        var split_id = $('.deleteImage').parent().parent().attr("id");
        var deleteImageObject = [];
        deleteImageObject.data = {};
        deleteImageObject.url =  '/bank/deleteimage';
        deleteImageObject.data['image_div'] = $(this).parent().parent().attr("id");
        deleteImageObject.data['image_sec'] = $(this).parent().parent().parent().attr("name");
        deleteImageObject.data['table'] = $(this).parent().parent().parent().attr("table");
        deleteImageObject.data['app_seq'] = $(this).parent().parent().parent().attr("data-seq");
        deleteImageObject.data['data-type'] = $(this).parent().parent().parent().attr("data-type");


        if(split_id.substr(-14) == '_clearance_div' || split_id.substr(-8) == '_img_div'){
            deleteImageObject.data['form_id'] =   $('#formId').val();
        }
        if($(this).hasClass('pdf')){
            var documentId = $(this).attr('data-id');
            if($('#'+documentId+'_proof_pdf').html() == undefined || $('#'+documentId+'_proof_pdf').html() == ''){
                deleteImageObject.data['imageName'] = $('#'+documentId+'_pdf').html();
            }else{
                deleteImageObject.data['imageName'] = $('#'+documentId+'_proof_pdf').html();
            }

        }else{
            var image = $(this).parent().parent().find('img').attr("src").split('/');
        deleteImageObject.data['imageName'] = image[image.length-1];
        }
        deleteImageObject.data['functionName'] = 'DeleteImageCallBack';

        crudAjaxCall(deleteImageObject);
        return false;
       
    });

    $("body").on("keyup",".current_pincode,.per_pincode,.nominee_pincode,.entity_pincode",function(){
        if($(this).val().length >= 6){
            var pincodeObject = [];
            pincodeObject.data = {};
            pincodeObject.url =  '/bank/getaddressdatabypincode';
            // pincodeObject.data['id'] = $(this).attr('name');
            pincodeObject.data['id'] = $(this).attr('id');
            pincodeObject.data['pincode'] = $(this).val();
            pincodeObject.data['functionName'] = 'AddressDataCallBack';
        
            crudAjaxCall(pincodeObject);
            return false;
        }
    });

    $("body").on("click",".editColumn",function(){
        //$(this).closest(".editColumnDiv").find(':input[type="checkbox"]').attr('id');
        if($(this).closest(".editColumnDiv").find('input').attr("type") == "checkbox")
        {
            $(this).closest(".editColumnDiv").find('input').prop('disabled',true);
            $(this).closest(".editColumnDiv").find('input').removeAttr("disabled");
        }else{
            if($(this).closest(".editColumnDiv").next().find('select').length != 0)
            {
                $(this).closest(".editColumnDiv").next().find('select').attr('readonly',false);
                var id = $(this).closest(".editColumnDiv").next().find('select').attr("id");
                
                $("#"+id).select2({placeholder: "Select "+id,allowClear: true});
                $("#"+id).removeAttr("disabled");
                $("#"+id).find(".disabled-select", $(".select2")).remove();

                }
            if($(this).closest(".editColumnDiv").next().find('input').length != 0){
                if($(this).closest(".editColumnDiv").next().find('input').attr("type") == "checkbox")
                {
                    $(this).closest(".editColumnDiv").find('input').prop('disabled',true);
                    $(this).closest(".editColumnDiv").find('input').removeAttr("disabled");
                }else{
                    $(this).closest(".editColumnDiv").next().find('input').attr('readonly',false);
                    if($(this).closest(".editColumnDiv").next().find('input').hasClass('single_ovd')){
                        $(this).closest(".editColumnDiv").next().find('input')[0].disabled = false;
                    }else{
                    $(this).closest(".editColumnDiv").next().find('input').removeAttr("disabled");
                }
            }
        }
        }
                
        $(this).closest(".editColumnDiv").next().find('i').removeClass('display-none');
        
        //for triggering gender
        var curr =  $(this).closest(".editColumnDiv").next().find(':input:not([type=hidden])').attr('id');
        if (typeof(curr)!= 'undefined') {
            currField = curr.split('-')[0];
            currId = curr.split('-')[1];
            if(currField == 'title'){
                  $('#gender-'+currId).trigger('change');
                  //$("#title-"+currId).select2({placeholder: "Select Title",allowClear: true});
            }
        }
        return false;
    });

    $("body").on("click",".updateColumn",function(){
        let up_btn = $(this);
        let input = up_btn.closest("div").find("input[table!=''], select[table!='']");
        // var table = $(this).prev().attr("table");
        // var column = $(this).prev().attr("id");
        // var value = $(this).prev().val();
        let table = input.attr("table");
        let column = input.attr("id");
        let value = input.val();

        $("#"+column).val(value);
        $("#"+column).attr("value", value);       

        if(typeof($(this).prev().attr("id")) == "undefined")
        {
            table = $(this).prev().find('input').attr("table");
            column = $(this).prev().find('input').attr("id");
            value = $(this).prev().find('input').val();
        }
        if($(this).prev().hasClass('select2'))
        {
            table = $(this).prev().prev().attr("table");
            column = $(this).prev().prev().attr("id");
            value = $(this).prev().prev().val();
        }
        //console.log(table);
        if ((typeof(table)== 'undefined') || (table == null) || (table == ''))
        {
            $.growl({message: "undefined table! Please take screenshot and contact with the IT Team."},{type: "warning"});
        }
        
        var submitToNpcObject = [];
        submitToNpcObject.data = {};
        submitToNpcObject.url =  '/bank/updatecolumn';
        if(typeof($("#applicantId-"+column.split('-')[1]).val()) != "undefined"){
            submitToNpcObject.data['account_id'] = $("#applicantId-"+column.split('-')[1]).val();
        }
        submitToNpcObject.data['formId'] = $("#formId").val();
        submitToNpcObject.data['table'] = table;
        if(typeof(column.split('-')[1]) != "undefined"){
            submitToNpcObject.data['column'] = column.split('-')[0];
            submitToNpcObject.data['applicantId'] = column.split('-')[1];    
        }else{
            submitToNpcObject.data['column'] = column;
        }
        submitToNpcObject.data['account_type'] = $('#account_type').val();
        submitToNpcObject.data['value'] = value;
        submitToNpcObject.data['functionName'] = 'UpdateCoulmnCallBack';

        if(up_btn.hasClass("coparcenar")){
            submitToNpcObject.data['coparcenar_id']=up_btn.attr("coparcenar_id");
            if(up_btn.hasClass("c_name")){
                input_id = input.attr("id").split('-')[0].replaceAll("coparcenar_name_field","");
                submitToNpcObject.data['column']="coparcenar_name";
                submitToNpcObject.data["coparcenar_number"]=input_id;
                $('.c_name').addClass('display-none');
            }else if(up_btn.hasClass("c_type")){
                input_id = input.attr("id").split('-')[0].replaceAll("coparcenar_type_field","");
                submitToNpcObject.data['column']="coparcener_type";
                submitToNpcObject.data["coparcenar_number"]=input_id;
                $('.c_type').addClass('display-none');
            }else if(up_btn.hasClass("c_rel")){
                input_id = input.attr("id").split('-')[0].replaceAll("coparcenar_rel_field","");
                submitToNpcObject.data['column']="huf_relation";
                submitToNpcObject.data["coparcenar_number"]=input_id;
                $('.c_rel').addClass('display-none');
            }else if(up_btn.hasClass("c_dob")){
                input_id = input.attr("id").split('-')[0].replaceAll("coparcenar_dob_field","");
                submitToNpcObject.data['column']="dob";
                submitToNpcObject.data["coparcenar_number"]=input_id;
                $('.c_dob').addClass('display-none');
            }else{
                return;
            }
        }
        crudAjaxCall(submitToNpcObject);
        return false;
    });

    $("#saveForm").click(function() {
        var formObject = [];
        formObject.data = {};
        formObject.url =  '/bank/saveform';
        formObject.data['functionName'] = 'saveFormCallBack';
    
        crudAjaxCall(formObject);
        return false;
    })
});

function getUserApplications(url,table,tableRemainingHeight)
{
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['AOF_NUMBER'] = $("#aofNumber").val();
    tableObject.data['customer'] = $("#customerName").val();
    tableObject.data['customer_id'] = $("#customerId").val();

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

function getOccupation(id,description)
{
    var occupationListObject = [];
    occupationListObject.data = {};
    occupationListObject.url =  '/bank/getoccupation';
    occupationListObject.data['id'] = id;
    occupationListObject.data['description'] = description;
    occupationListObject.data['functionName'] = 'OccupationListCallBack';

    crudAjaxCall(occupationListObject);
    return false;
}

function redirectUrl(formId,url)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var csrf_field = '<input type="hidden" name="_token" value="'+csrf+'">';
    var encodedParams =  $.base64.encode(formId);
    var key = $('meta[name="cookie"]').attr('content').split('.')[2];
    var encryptedData = encrypt(encodedParams,key);
    var form = $('<form action="' + baseUrl + url + '" method="post">' +
                    '<input type="text" name="encodedString" value="' + encryptedData + '" />' + csrf_field +
                '</form>');
    $('body').append(form);
    form.submit();
    return false;
}
function redirectaadhaarurl(aof_no,url)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var csrf_field = '<input type="hidden" name="_token" value="'+csrf+'">';   
    var key = $('meta[name="cookie"]').attr('content').split('.')[2];
    var aof_number = aof_no;
    var form = $('<form action="' + baseUrl + url + '" method="post">' +
                    '<input type="text" name="number" value="' + aof_number + '" />' + csrf_field +
                '</form>');
    $('body').append(form);
    form.submit();
    return false;
}
function redirectdeleteuserUrl(dataId,url)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var csrf = document.querySelector('meta[name="csrf-token"]').content;
    var csrf_field = '<input type="hidden" name="_token" value="'+csrf+'">';   
    var key = $('meta[name="cookie"]').attr('content').split('.')[2];
    var DataId = dataId;
    var form = $('<form action="' + baseUrl + url + '" method="post">' +
                    '<input type="text" name="number" value="' + DataId + '" />' + csrf_field +
                '</form>');
    $('body').append(form);
    form.submit();
    return false;
}
function saveFormCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    setTimeout(function(){
        window.location = baseUrl+"/bank/dashboard";
    }, 1000);
}

function clearFilters(){
    $('#customerName').val("").trigger( "change" );
    $('#applicationStatus').val("").trigger( "change" );
    $('#courierList').val("").trigger( "change" );
    $('#clear-dates').click();
    $('.customer-type[value=all]').click();
    $('#batch_no').val("").keyup();
    $('#airway_bill_no').val("").keyup();
}



function getCallCenterUserApplications(url,table,tableRemainingHeight)
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