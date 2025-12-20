var masking_time;
var masking_time_count = 120000;
var huf_no_scheme_req = [];

$(document).ready(function(){
    var referrerUrl = document.referrer.split('/');
//    debugger;
    if(referrerUrl[referrerUrl.length-1] == "login")
    {
     //  showlastloggin();
     if (typeof(_globalLastLogin) != 'undefined' && _globalLastLogin != '-1') {
        $.growl({message: "Last logged in time "+ _globalLastLogin},{type: "success"});
         }else{
            $.growl({message: "Last logged in time is not available"},{type: "success"}); 
     }
    }
});

function normal_addremove(id){
  
        $('.form60').removeClass('display-none');
        $('#tab'+id+' #customer_account_type-'+id).closest('.details-custcol-row').removeClass('display-none');
        $('#tab'+id+' #residential_status-'+id).prop('disabled', false);
        $('#tab'+id+' #huf_reletionship-'+id).addClass('display-none');
        $('#tab'+id+' #pancard_no-'+id).removeClass('pan_huf');
        $('#tab'+id+' #dob-'+id).closest('.details-custcol-row').find('.details-custcol-row-top').find('.lable-cus').text('DOB');
        $('#tab'+id+' #dob-'+id).removeClass("dof");
        $('#tab'+id+' #dob-'+id).addClass("dob");
}
function huf_addremove(id){
    
        $('.form60').addClass('display-none');
        $('#tab'+id+' #customer_account_type-'+id).closest('.details-custcol-row').addClass('display-none');
        $('#tab'+id+' #residential_status-'+id).prop('disabled', true);
        $('#tab'+id+' #huf_reletionship-'+id).removeClass('display-none');
        $('#tab'+id+' #dob-'+id).closest('.details-custcol-row').find('.details-custcol-row-top').find('.lable-cus').text('DOF (Date Of Formation)');
        $('#tab'+id+' #pancard_no-'+id).addClass('pan_huf');
        $('#tab'+id+' #dob-'+id).removeClass("dob");
        $('#tab'+id+' #dob-'+id).addClass("dof");
        $("#tab" + id + " #marital_status-" + id + " option[value='1']").remove();
        $("#tab" + id + " #marital_status-" + id + " option[value='2']").remove();

}

/** eG RUM Javasript Loader */
env='NONPROD';
if(env=='PROD'){
    (function(w,d){
         w['egrum-start_time'] = new Date().getTime();
         w['Site_Name'] = '0887eabd-8a2f-47ef-8f11-a6923014cd95-1604408941797';
         w['beacon-url'] = 'https://apmrum.dcbbank.com';
         var head = d.getElementsByTagName('head').item(0);
         var body = d.getElementsByTagName('body').item(0);
         var html = d.getElementsByTagName('html').item(0);
         var script = d.createElement('script');
         script.setAttribute('type', 'text/javascript');
         script.setAttribute('async', '');
         script.setAttribute('src', 'https://apmrum.dcbbank.com/rumcollector/egrum.js');
         if(head){
             head.appendChild(script);
         }else if(body){
             body.appendChild(script);
         } else if(html){
             html.appendChild(script);
         }
    })(window, document);
    /** eG RUM Javasript Loader */
}

var per_city = '';
var current_city = '';
var is_session = false;
var otherDeclarationsCount = 0;
var userRoles = [];
var ovdDetails = [];
var panResponse = [];
var panIsvalid = 0;
var isPanExists = 0;
var newCustomer = '';
var ovd_types = {1:'Aadhar',2:'Passport',3:'Driving Licence',4:'NREGA',5:'NPR',6:'Voter ID'};
var gpaValid = 0;
var nominee_exists = '';
var pan_check = '';
_globalSchemeDetails = ''; // Default for Savings
_globalTDSchemeDetails = '';
var global_growl = '';
var ovd_labels = {
        'add_proof_card_number':'Address Proof Card Number',
        'current_add_proof_card_number':'Add Proof Card Number',
        'current_address_line1':'Address Line1',
        'current_address_line2':'Address Line2',
        'current_city':'City',
        'current_country':'Country',
        'current_landmark':'landmark',
        'current_pincode':'Pincode',
        'current_state':'State',
        'first_name':'First Name',
        'gender':'Gender',
        'id_proof_card_number':'Id Proof Card Number',
        'last_name':'Last Name',
        'middle_name':'Middle Name',
        'per_address_line1' : 'Address Line1',
        'per_address_line2' : 'Address Line2',
        'per_city' : 'City',
        'per_country' : 'Country',
        'per_landmark' : 'Landmark',
        'per_pincode' : 'Pincode',
        'per_state' : 'State',
        'proof_of_address' : 'Proof Of Address',
        'proof_of_current_address' : 'Proof Of Current Address',
        'proof_of_identity' : 'Proof Of Identity',
        'short_name' : 'Short Name','initial_funding_type':'Initial Funding Type','initial_funding_date':'Initial Funding Date',
                        'amount':'Amount','reference':'Reference','ifsc_code':'IFSC Code','account_number':'Account Number',
                        'account_name':'Account Name','bank_name':'Bank Name','minor' :'Minor',
        'name_mismatch' :'Name Mismatch',
        'other' :'Other',
        'vernacular' :'Vernacular','pancard_no' : 'Pancard No',
        'dob' : 'DOB',
        'mobile_number' : 'Mobile Number',
        'email' : 'Email',
        'residential_status' : 'Residential Status'};
var gender = {'male':'MALE','female':'FEMALE','third_gender':'THIRD GENDER'};

function crudAjaxCall(object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    $.ajax({
        url: baseUrl+object.url,
        type: "POST",
        data: {'data':object.data},
        success: function(data) {
            try {
                var response = $.parseJSON(data);
            } catch (error) {

            }
            if(typeof(response) == "undefined")
            {
                var response = data;
            }
            if(response == "Token is Expired.")
            {
                window.location = baseUrl;
                return false;
            }
            redirectFn[object.data['functionName']](response,object);
        }
    }).fail(function(jqXHR, textStatus, errorThrown){
        var response = {};
        response['msg'] = 'Server Error. Please try again and inform CUBE Admin if this issue persists.';
        response['status'] = 'fail';
        redirectFn[object.data['functionName']](response,object);
    });
    return false;
}


function crudDocAjaxCall(docdata,url,functionName){
    debugger;
    jQuery.ajax({
         url: $('meta[name="base_url"]').attr('content')+url,
         data: docdata,
         cache: false,
         contentType: false,
         processData: false,
         method: 'POST',
         type: 'POST', 
         success: function(data){
             try{

                var response = $.parseJSON(data);
             }catch(error){
            
             }
             if(typeof(response) == "undefined")
                {
                    var response = data;
                }
            if(response == "Token is Expired.")
                {
                    window.location = baseUrl;
                    return false;
                }
            redirectDocFn[functionName](response);
         }
    }).fail(function(jqXHR, textStatus, errorThrown){
        var response = {};
        response['msg'] = 'Server Error. Please try again and inform CUBE Admin if this issue persists.';
        response['status'] = 'fail';
        redirectDocFn[functionName](response);
    });
    return false;
}

function addScrollBar(className,height)
{
    $('.'+className).slimScroll({
        height: height,
        position: 'left',
        opacity: 1,
        color: '#67aefe',
        wheelStep: 1500,
        axis: 'y',
        startY: "bottom",
    });
}

debugger;

function showChat(ClassName)
{
    $('.'+ClassName).toggle('slide', {direction: 'right'}, 500);
}


function addSelect2(ClassName,placeholder,action=false)
{
    if (action == true){
        $("."+ClassName).select2();
        $("."+ClassName).next().prepend('<div class="disabled-select"></div>');
        $(".select2-selection__arrow").addClass('display-none');
    }else{
        if(ClassName == "source_of_funds"){
            $("."+ClassName).select2({placeholder: "Select "+placeholder,multiple: true});
        }else if(ClassName == "basis_categorisation"){
            $("."+ClassName).select2({placeholder: placeholder,multiple: true});
        }else{
            $("."+ClassName).select2({placeholder: "Select "+placeholder,allowClear: true});
        }
        $(".disabled-select", $(".select2")).remove();
    }
}

function addSlimScroll(ClassName,height)
{
    $('.'+ClassName).slimScroll({
        height: height,
        position: 'right',
        opacity: 1,
        color: '#67aefe',
        wheelStep: 1500,
        axis: 'both',
    });
}

function datatableAjaxCall(tableObject,tableRemainingHeight,sort_idx=0,sort_type='desc', iDisplayLength=10)
{
    sort_idx = sortDashboardByRole(tableObject.url);
    
    var baseUrl = $('meta[name="base_url"]').attr('content');
    if ($.fn.DataTable.isDataTable( '#'+tableObject.data['table'] ) ) {
      $('#'+tableObject.data['table']).dataTable().fnDestroy();
    }
    var documentHeight = $(window).height();
    $("#"+tableObject.data['table']).DataTable({
        processing: true,
        // serverSide: true,
        // "order":[[sort_idx,sort_type]],
        "order":[],
        "scrollX": true,
        "scrollY":"auto",
        "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        "iDisplayLength": iDisplayLength,
        "language": { search: "", searchPlaceholder: "Search"  },
        "dom": '<"#datatable_search"f>t<"bottom"<"entries"li>p><"clear">',
        buttons: [{
            extend : 'excel',
            text : 'Export to Excel',
            exportOptions : {
                modifier : {
                    // DataTables core,
                    selected: true,
                    //order : 'index',  // 'current', 'applied', 'index',  'original'
                    page : 'all',      // 'all',     'current'
                    search : 'none'     // 'none',    'applied', 'removed'
                }
            }
        }],
        "ajax":{
            "url": baseUrl+tableObject.url,
            "dataType": "json",
            "type": "POST",
            "data":{data: tableObject.data}
        },
        'columnDefs': [{
                'targets': [-1],
                'searchable': false,
                'orderable': false,
        }],
        //Code for Fixing the additional row getting in data table
        "initComplete": function(settings, json) {
            $('.dataTables_scrollBody thead tr').css({visibility:'collapse'});

            if(json.status == 'fail'){
                $.growl({message:json.msg},{type:'warning'});
                return false;
            }
        },
        drawCallback: function () {
            $('#'+tableObject.data['table']+'_filter input').unbind();
            $('#'+tableObject.data['table']+'_filter input').bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    $("#"+tableObject.data['table']).DataTable().search(this.value).draw();
                    $(".dataTables_filter:eq(0)").find('input').after("<button type=button id=remove_search>x</button>");
                }
            });

            if($('#'+tableObject.data['table']+'_filter input').val() == ''){
                $("#datatable_search").css("display", "none");
            }

            $('#'+tableObject.data['table']+'_length select').removeClass();
            $('#'+tableObject.data['table']+'_length select').addClass("select-css");

            $("#remove_search").click(function(e){
                $(".dataTables_filter").val('');
                $("#"+tableObject.data['table']).DataTable().search('').draw();
                $(this).hide();
            });

            $('body').keydown(function(e) {
                if (e.keyCode == 27) {
                    $("#datatable_search").find('input').val('');
                    $("#"+tableObject.data['table']).DataTable().search('').draw();
                    $("#datatable_search").css("display", "none");
                }
            });

            //adding slimScroll
            addSlimScroll('dataTables_scrollBody',documentHeight-tableRemainingHeight);
            $.fn.dataTable.tables( { visible: true, api: true } ).columns.adjust();
            $('[data-toggle="tooltip"]').tooltip();
        },
    });
}

/**
 * Encrypt string.
 *
 * @link https://stackoverflow.com/questions/41222162/encrypt-in-php-openssl-and-decrypt-in-javascript-cryptojs Reference.
 * @link https://stackoverflow.com/questions/25492179/decode-a-base64-string-using-cryptojs Crypto JS base64 encode/decode reference.
 * @param string string The original string to be encrypt.
 * @param string key The key.
 * @return string Return encrypted string.
 */
function encrypt(string, key) {
    var encryptMethod = "AES-256-CBC";
    var encryptMethodLength = parseInt(encryptMethod.match(/\d+/)[0]);
    var iv = CryptoJS.lib.WordArray.random(16);// the reason to be 16, please read on `encryptMethod` property.

    var salt = CryptoJS.lib.WordArray.random(256);
    var iterations = 999;
    encryptMethodLength = (encryptMethodLength/4);// example: AES number is 256 / 4 = 64
    var hashKey = CryptoJS.PBKDF2(key, salt, {'hasher': CryptoJS.algo.SHA512, 'keySize': (encryptMethodLength/8), 'iterations': iterations});

    var encrypted = CryptoJS.AES.encrypt(string, hashKey, {'mode': CryptoJS.mode.CBC, 'iv': iv});
    var encryptedString = CryptoJS.enc.Base64.stringify(encrypted.ciphertext);

    var output = {
        'ciphertext': encryptedString,
        'iv': CryptoJS.enc.Hex.stringify(iv),
        'salt': CryptoJS.enc.Hex.stringify(salt),
        'iterations': iterations
    };
    return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(output)));
}// encrypt

function redirectUrl_todelete(param,url)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    var encodedParams =  $.base64.encode(param);
    var key = $('meta[name="cookie"]').attr('content').split('.')[2];
    var encryptedData = encrypt(encodedParams,key);
    var form = $('<form action="' + baseUrl + url + '" method="post">' +
                    '<input type="text" name="encodedString" value="' + encryptedData + '" />' +
                '</form>');
    $('body').append(form);
    form.submit();
}


function imageCropper(id)
{
    $("#"+id).cropper({
        aspectRatio: 640 / 320,
        autoCropArea: 0.6,
        autoCrop:false,
        dragCrop: false,
        resizable: false,
        built: function () {
            $(this).cropper("setDragMode", 'move');
            $(this).cropper("clear");
        }
    });
}

var redirectDocFn = {
    //pdfDocumentSaveCallBack
    pdfAllDocumentSaveCallBack(response){
            
        if(response['status'] == 'success'){
            var docName = response['data'];
            var documentBladeId = response.docId;
            var baseUrl = $('meta[name="base_url"]').attr('content');

            // $('#upload_amend_crf').modal('hide');
            if(response.module == 'amend'){
                $('#'+documentBladeId+'_div').remove();
                $('#upload_amend').modal('hide');
                $('#upload_amend_crf').modal('hide');

                var buildImgHtml = '<div id="'+documentBladeId+'_div">'+
                '<div class="upload-delete">'+
                '<button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light pdfDelete" data-id="'+documentBladeId+'">'+
                '<i class="fa fa-trash" aria-hidden="true"></i>'+
                '</button>'+
                '</div>'+
                '<i class="fa fa-file-pdf-o" style="font-size:38px;color:red">'+'</i>'
                +'<a id="'+documentBladeId+'_pdf" href="dowloadshowDoc/'+docName+'" class="pdfDocumentSave" target="_blank" style="font-size:14px;text-decoration:none;margin-top:14px;">'+docName+'</a>'+
                '</div>';
                $('#amend_card-'+documentBladeId).append(buildImgHtml);
                $('#amend_image_check_-'+documentBladeId).attr('disabled',false);   
                $('#upload_amend_card-'+documentBladeId).css('display','none');
            }else{
                var buildImgHtml = '<div id="'+documentBladeId+'_div">'+
                '<div class="upload-delete">'+
                '<button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage pdf" data-id="'+documentBladeId+'">'+
                '<i class="fa fa-trash" aria-hidden="true"></i>'+
                '</button>'+
                '</div>'+
                '<i class="fa fa-file-pdf-o" style="font-size:48px;color:red">'+'</i>'
                +'<a id="'+documentBladeId+'_pdf" href="'+baseUrl+'/imagestemp/'+docName+'" target="_blank" style="font-size:14px;text-decoration:none;margin-top:14px;">'+docName+'</a>'+
                '</div>';
                $("#"+documentBladeId).children().addClass('display-none');
                $("#"+documentBladeId).append(buildImgHtml);
                $(".saveDocument").attr('id','uploadPdf');
                $(".saveDocument").addClass("btn-lblue").removeClass("btn-primary").attr("disabled",true);
                $($(".upload_document").data('target')).modal('toggle');
            }     
            $.growl({message:response['msg']},{type:response['status']});
            return false;
        }else{
            $.growl({message:response['msg']},{type:'warning'});
            return false;   
        }
    },
}

var redirectFn = {
    saveChannelIDTemplateDetailsCallBack:function(response,object){
        if(response['status'] == "success"){
                var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/"));
                var url = document.URL.split('/');
                setTimeout(function(){
                window.location = baseUrl+"/dashboard";
                }, 1000);
            }
        $.growl({message: response['msg']},{type: response['status']});
        return false;
    },
    ImageCallBack:function(response,object){        
        if(typeof(response.imageCall)!="undefined" && (response.imageCall == 'STRICT' || response.imageCall == 'WARNING')){
            if(typeof(response['info'])!="undefined" && response['info'] != null && typeof(response.info.blurFFT)!="undefined"){
                console.dir(response['info']);
                if(response.info.blurFFT=='Y' && response.info.blurStd[0] == true){
                    $.growl({message: 'Blurred image found! Please replace with a clear image.'},{type: "warning"});
                    
                }else{
                    if(typeof(object.data.image_type)!='undefined'){                                                
                        fld = object.data.image_type.split('-');
                        img_type = fld[0];
                        applicant = fld[1];
                        if(img_type == 'pf_type_card'){
                            if($('#pf_type-'+applicant+':checked').val()=='pancard' && !response.info.isPAN){
                                $.growl({message: 'PAN Card image not found! Please recheck'},{type: "warning"});

                                if((response.imageCall == 'STRICT')){
                                    $($(".upload_document").data('target')).modal('toggle');
                                    return false;
                            }
                        }
                    }
                }
                }
                
            }
        }
        if(response['status'] == "success")
        {
            $('#upload_note').modal('toggle');
            var baseUrl = $('meta[name="base_url"]').attr('content');
            var image_type = object.data['image_type'];
            var name = object.data['name'];
            if(typeof object.data['upload_type'] != 'undefined' && object.data['upload_type'] == 'link'){
                // more of Link upload callback here.
            }else{
                $($(".upload_document").data('target')).modal('toggle');
            }
            $("#"+image_type).children().addClass('display-none');
             $($(".upload_document_amend").data('target')).modal('toggle');
            if($("#"+image_type).hasClass('do-not-crop')){
                var buildImgHtml = '<div id="'+image_type+'_div">'+
                                        '<div class="upload-delete">'+
                                            '<button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light deleteImage">'+
                                                '<i class="fa fa-trash" aria-hidden="true"></i>'+
                                            '</button>'+
                                        '</div>'+
                                        '<img width="100%" height="100%" class="imagetoenlarge" name="" src="'+baseUrl+'/imagestemp/'+response.imageName+'" id="level3tempimage_'+image_type+'"  >'+
                                    '</div>';
                $("#"+image_type).append(buildImgHtml);
                document.querySelector(".imagetoenlarge").addEventListener('click',showPreviewModal);
                return true;
            }

            if(image_type.substr(0,5) == 'amend'){
                var deleteClass = 'deleteamendImage';
            }else{
                var deleteClass = 'deleteImage';
            }

            var buildImgHtml = '<div id="'+image_type+'_div">'+
                                    '<div class="upload-delete">'+
                                        '<button type="button" class="delete-icon btn btn-danger btn-icon waves-effect waves-light '+deleteClass+'">'+
                                            '<i class="fa fa-trash" aria-hidden="true"></i>'+
                                        '</button>'+
                                    '</div>'+
                                    '<img class="uploaded_image entityreview amend_image" name="'+name+'" id="document_preview_'+image_type+'" src="'+baseUrl+'/imagestemp/'+response.imageName+'">'+
                                '</div>';
            $("#"+image_type).append(buildImgHtml);
            $("#"+image_type).next().val(baseUrl+'/imagestemp/'+response.imageName).click();
            imageCropper("document_preview_"+image_type);
            $(".saveDocument").addClass("btn-lblue").removeClass("btn-primary").attr("disabled",true);
            if(image_type == "current_add_proof_image-1")
            {
                $("#"+image_type).next().next().removeClass('display-none');
            }
            if(typeof($("#"+image_type).closest('.accordion').next().html()) != "undefined")
            {
                $("#"+image_type).closest('.accordion').next().removeClass('display-none');
            }
            var otherCheckboxId = image_type.split("_")[0];
            if (typeof otherCheckboxId != 'undefined' && otherCheckboxId.substr(0,5) == 'other') {
                //document.getElementById(otherCheckboxId).checked = true;
                otherCheckboxId.checked = true;
            }
        }else{
            $(".saveDocument").removeClass("btn-lblue").addClass("btn-primary").attr("disabled",false);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    saveCustomerDetailsCallBack:function(response,object){
        var baseUrl = $('meta[name="base_url"]').attr('content');
        if(response['status'] == "success"){
            setTimeout(function(){
                window.location = baseUrl+"/bank/dashboard";
            }, 1000);
        }
        $.growl({message: response['msg']},{type: response['status']});
        return false;
    },
    verifyCustomerDetailsCallBack:function(response,object){
        var baseUrl = $('meta[name="base_url"]').attr('content');
        if(response['status'] == "success"){
            $("#aadhar").removeClass('o-bg');
            $("#aadhar").addClass('g-bg');
            $(".aadhar-icon").remove();
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveAccountDetailsCallBack:function(response,object){
        if(response['status'] == "success"){
            jQuery.each(response.data['accountIds'], function( id, val ) {
                $("#applicantId-"+id).val(val);
            });
             registerScreenFlow(1,2);
            // return false;
            setTimeout(function(){
                redirectUrl(response.data['formId'],'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            var saveAndContinueBtn = $('.saveAccountDetails');
            enableSaveAndContinue(saveAndContinueBtn);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveOvdDetailsCallBack:function(response,object){
        if(response['status'] == "success"){
            registerScreenFlow(2,3);
            setTimeout(function(){
                redirectUrl(object.data['formId'],'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            var saveAndContinueBtn = $('.saveOvdDetails');
            enableSaveAndContinue(saveAndContinueBtn);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveRiskClassificationObjectDetailsCallBack:function(response,object){
        if(response['status'] == "success"){
            registerScreenFlow(3,4);
            setTimeout(function(){
                redirectUrl(object.data['formId'],'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            var saveAndContinueBtn = $('.riskClassification');
            enableSaveAndContinue(saveAndContinueBtn);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveNomineeDetailsCallBack:function(response,object){
        if(response['status'] == "success"){
            registerScreenFlow(5,6);
            setTimeout(function(){
                redirectUrl(object.data['formId'],'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            var saveAndContinueBtn = $('.nomineeDetails');
            enableSaveAndContinue(saveAndContinueBtn);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveFinancialDetailsCallBack:function(response,object){
        if(response['status'] == "success"){
            registerScreenFlow(4,5);
            setTimeout(function(){
                redirectUrl(object.data['formId'],'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            var saveAndContinueBtn = $('.financialinfo');
            enableSaveAndContinue(saveAndContinueBtn);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    ApplyDigiSignCallBack:function(response,object){
        registerScreenFlow(6,7);
        if(response['status'] == "success"){
            setTimeout(function(){
                redirectUrl(object.data['formId'],'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            var saveAndContinueBtn = $('.applyDigiSign');
            enableSaveAndContinue(saveAndContinueBtn);
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    DeleteImageCallBack:function(response,object){
        var ImageDiv = object.data['image_div'];

        // CG -- to fix the OVD image validation failures - 150425
        $("#"+ImageDiv).parent().next('input').val('');  
        
        //$("#"+ImageDiv).find('img').remove();

        var otherCheckboxId = ImageDiv.split("_")[0];
        if (typeof otherCheckboxId != 'undefined' && otherCheckboxId.substr(0,5) == 'other') {
            //document.getElementById(otherCheckboxId).checked = false
            otherCheckboxId.checked = false
        }
        var otherImageDiv = '';
        if(response['status'] == "success"){
            
            if(ImageDiv.match('front')){
                otherImageDiv = ImageDiv.replace('front','back');
                $("#"+otherImageDiv).siblings().removeClass('display-none');
                $("#"+otherImageDiv).remove();
            }
            if(ImageDiv.match('back')){
                otherImageDiv = ImageDiv.replace('back','front');
                $("#"+otherImageDiv).siblings().removeClass('display-none');
                $("#"+otherImageDiv).remove();
            }
            $("."+ImageDiv).siblings().removeClass('display-none');
            $("."+ImageDiv).remove();

            $("#"+ImageDiv).siblings().removeClass('display-none');
            $("#"+ImageDiv).remove();
            if(1){ // Force remove trashicon if not cleared!
                forceRemoveTrashIcon = ImageDiv.substr(0,ImageDiv.length-4);                
                $("#"+forceRemoveTrashIcon).find('.upload-delete').addClass('display-none');
            }   
            if(ImageDiv.substr(-7) == 'img_div'){
                var toggle = ImageDiv.replace('_img_div','');
                $("#"+toggle+"_toggle").attr('checked');
                $("."+toggle).prop("checked",false);
                $("#"+toggle+'_reviewComments').removeClass('display-none');
                $("#"+toggle+'_reviewComments').addClass('display-none');
                $(".clear").removeClass('display-none');
                $("#"+toggle).removeClass('display-none').attr("style",'');
                $(".clear").addClass('display-none');
                $(".discrepent").removeClass('display-none');
            }else if(ImageDiv.substr(-14) == '_clearance_div'){
                var toggle = ImageDiv.replace('_clearance_div','');
                $("#"+toggle+"_toggle").attr('checked');
                $("#"+toggle+'_reviewComments').removeClass('display-none');
                $("#"+toggle+'_reviewComments').addClass('display-none');
                $("."+toggle).prop("checked",false);
                $(".clear").addClass('display-none');
                $(".discrepent").removeClass('display-none');
                $("#"+toggle).removeClass('display-none').attr("style",'');
            }         
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            // To be on safe side and allow for replacement
            $("#"+ImageDiv).siblings().removeClass('display-none');
            $("#"+ImageDiv).remove();
            $.growl({message: response['msg']},{type: "warning"});
             if(ImageDiv.substr(-7) == 'img_div'){
                var toggle = ImageDiv.replace('_img_div','');
                $("#"+toggle+"_toggle").attr('checked');
                $("#"+toggle+'_reviewComments').removeClass('display-none');
                $("#"+toggle+'_reviewComments').addClass('display-none');
                $("."+toggle).prop("checked",false);
                $(".clear").addClass('display-none');
                $(".discrepent").removeClass('display-none');
                $("#"+toggle).removeClass('display-none').attr("style",'');
            }else if(ImageDiv.substr(-14) == '_clearance_div'){
                var toggle = ImageDiv.replace('_clearance_div','');
                $("#"+toggle+"_toggle").attr('checked');
                $("."+toggle).prop("checked",false);
                $("#"+toggle+'_reviewComments').removeClass('display-none');
                $("#"+toggle+'_reviewComments').addClass('display-none');
                $(".clear").addClass('display-none');
                $(".discrepent").removeClass('display-none');
                $("#"+toggle).removeClass('display-none').attr("style",'');
            }    
        }
        return false;
    },
    AddressDataCallBack:function(response,object){
        if(response['status'] == "success"){
            /*$('input[name="'+object.data['id']+'"]').parent().parent().parent().parent().find('.state').val(response.data['state_name']);
            $('input[name="'+object.data['id']+'"]').parent().parent().parent().parent().find('.city').val(response.data['city_name']);*/
            $('#'+object.data['id']).parent().parent().parent().parent().find('.state').val(response.data['statedesc']);
            $('#'+object.data['id']).parent().parent().parent().parent().find('.city').val(response.data['citydesc']);
        }else{

            /*$('input[name="'+object.data['id']+'"]').parent().parent().parent().parent().find('.state').val('');
            $('input[name="'+object.data['id']+'"]').parent().parent().parent().parent().find('.city').val('');*/
            $('#'+object.data['id']).parent().parent().parent().parent().find('.state').val('');
            $('#'+object.data['id']).parent().parent().parent().parent().find('.city').val('');
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    GuardianAddressDataCallBack:function(response,object){
        var seqID = object.data['id'].split('-')[1];
        if(response['status'] == "success"){
            $('#guardian_city-'+seqID).val(response.data['citydesc']);
            $('#guardian_state-'+seqID).val(response.data['statedesc']);
        }else{
            $('#guardian_city-'+seqID).val('');
            $('#guardian_state-'+seqID).val('');
        }
        return false;
    },
    SubmitToNpcCallBack:function(response,object){
        if(response['status'] == "success"){
            var baseUrl = $('meta[name="base_url"]').attr('content');
            setTimeout(function(){
                //window.location = baseUrl+"/bank/dashboard";
                window.location = baseUrl+response['url'];
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            
            enableWorkAreaAfterBranchSubmit();
            var saveAndContinueBtn = $('.submit_to_npc');
            enableSaveAndContinue(saveAndContinueBtn);
            setTimeout(function(){
               $.growl({message: response['msg']},{type: "warning"});
            }, 1000);
            $('#Username-blck').modal('toggle');
        }
        return false;
    },
    AlreadyreviewCallBack:function(response,object){
           var baseUrl = $('meta[name="base_url"]').attr('content');
        if(response['status'] == "success"){

                 redirectUrl(object.data['form_id'],'/npc/review');

        }else{

            if(typeof(response['data'])!='undefined' && response['data'].length == 2){
                $('#reviewModal').modal('show');
                txt = 'Form in review by '+response['data'][1];
                if(parseInt(response['data'][0]) < 60){
                   txt += ' since '+response['data'][0]+' minutes';
                }
                $('.modal-body').html(txt+'.');
                $('#reviewModalFormId').val(object.data['form_id']);
            }else{
                $.growl({message: response['msg']},{type: "warning"});
                setTimeout(function(){
                    window.location = baseUrl+"/npc/dashboard";
                }, 2000);
            }

            

        }
        return false;
    },
    AlreadyAmendreviewCallBack:function(response,object){
       var baseUrl = $('meta[name="base_url"]').attr('content');
       if(response['status'] == "success"){
             redirectUrl(object.data['form_id'],'/amendnpc/amendreview');
        }else{
            if(typeof(response['data'])!='undefined' && response['data'].length == 2){
                $('#amendreviewModal').modal('show');
                txt = 'Form in review by '+response['data'][1];
                if(parseInt(response['data'][0]) < 60){
                   txt += ' since '+response['data'][0]+' minutes';
                }
                $('.modal-body').html(txt+'.');
                $('#amendreviewModalFormId').val(response['data'][2]);
            }else{
                $.growl({message: response['msg']},{type: "warning"});
                setTimeout(function(){
                    window.location = baseUrl+"/amendnpc/dashboard";
                }, 2000);
            }
            

        }
        return false;
    },

    EntityReviewCallBack:function(response,object){
        if(response['status'] == "success"){
            var is_active = object.data['is_active'];
            var clearance_id = object.data['blade_id'];
            $("#"+clearance_id+"_toggle").attr('checked');
            var baseUrl = $('meta[name="base_url"]').attr('content');
            var formId = $('#formId').val();
            if(is_active == 1){
               $("."+clearance_id).prop('checked',true)
                $("#"+clearance_id).hide();
                $('#'+clearance_id+'comment').hide();
                $("#"+clearance_id+"_reviewComments").addClass('display-none');
                if(typeof($("#"+clearance_id+"_img_pdf").html()) != 'undefined'){
                    $("#"+clearance_id+"_img_pdf").attr('href',baseUrl+'/imagesattachments/'+formId+'/'+$("#"+clearance_id+"_img_pdf").html());
                }
            }else{
                $("."+clearance_id).prop('checked',false);
                $("#"+clearance_id).show();
            }
        }else{
            $.growl({message: response['msg']},{type: "warning"});

        }

    },
    SaveCommentsCallBack:function(response,object){
        if(response['status'] == "success"){
            $("#"+object.data['column_name']).parent().addClass('display-none');
            var buildHtml = '<p>'+
                                object.data['comments']+
                                ' <a href="javascript:void(0);" class="editComments" id="'+response.data['reviewId']+'">'+
                                    'Edit'+
                                '</a>'+
                            '</p>';
            $("#"+object.data['column_name']).parent().next().html(buildHtml).removeClass('display-none');
        }
        $.growl({message: response['msg']},{type: response['status']});
        return false;
    },
    SaveRejectedCommentsCallBack:function(response,object){
        if(response['status'] == "success"){
            $("#"+object.data['column_name']).parent().parent().addClass('display-none');
            $("#"+object.data['column_name']).parent().parent().next().find('p').html(object.data['comments']);
            $("#"+object.data['column_name']).parent().parent().next().removeClass('display-none');
            $("#"+object.data['column_name']).parent().parent().next().find('a').attr('id',response.data['reviewId']);
        }
        return false;
    },
    UpdateCoulmnCallBack:function(response,object){

        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
       
        if(typeof(object.data['applicantId']) == "undefined"){
            if($("#"+object.data['column']).hasClass('select2-hidden-accessible')){
                $("#"+object.data['column']).next().next().addClass('display-none');
                $("#"+object.data['column']).attr('readonly',true);
                addSelect2(object.data['column'],'Mode of Operation',true);
            }else{
                $("#"+object.data['column']).next().addClass('display-none');
                $("#"+object.data['column']).attr('readonly',true);
            }
        }else{
            if($("#"+object.data['column']+'-'+object.data['applicantId']).hasClass('select2-hidden-accessible')){
                $("#"+object.data['column']+'-'+object.data['applicantId']).next().next().addClass('display-none');
                $("#"+object.data['column']+'-'+object.data['applicantId']).attr('readonly',true);
                if($("#"+object.data['column']+'-'+object.data['applicantId']).val() != ''){
                    $("#"+object.data['column']+'-'+object.data['applicantId']).attr('disabled',true);
                }

            }else{
                $("#"+object.data['column']+'-'+object.data['applicantId']).next().addClass('display-none');
                $("#"+object.data['column']+'-'+object.data['applicantId']).attr('readonly',true);
            }
        }
        }else{
            $.growl({message: response['msg']},{type: "warning"});
            // var baseUrl = $('meta[name="base_url"]').attr('content');
            // location.href = baseUrl+'/trespassed';   
        }
        return false;
    },
    SubmitToBankCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }

        var baseUrl = $('meta[name="base_url"]').attr('content');
        setTimeout(function(){
            window.location = baseUrl+"/npc/dashboard";
        }, 1000);
        return false;
    },
    SubmitToFinacleCallBack:function(response,object){
        var baseUrl = $('meta[name="base_url"]').attr('content');
        if(response['status'] == "success"){
            
            $.growl({message: response['msg']},{type: response['status']});
            if(response['data'] == "createcustomerid")
            {
               $("#NpcModal2").modal('show');
                disableWorkAreal2Customer();
                createCustomerId(object.data['form_id']);
            }else{
                setTimeout(function(){
                    window.location = baseUrl+"/npc/dashboard";
                }, 1000);
            }
        }else{
            $.growl({message: response['msg']},{type: "warning"});
            setTimeout(function(){
                window.location = baseUrl+"/npc/dashboard";
            }, 2000);
        }
        /*var baseUrl = $('meta[name="base_url"]').attr('content');
        setTimeout(function(){
            window.location = baseUrl+"/npc/dashboard";
        }, 1000);*/
        return false;
    },
    UserStatusCallBack:function(response,object){
        if(response['status'] == "success"){
            if(object.data['status'] == 'Y'){
                $("#"+object.data['id']).removeClass('user_status_inactive').addClass('user_status_active');
            }else{
                $("#"+object.data['id']).removeClass('user_status_active').addClass('user_status_inactive');
            }
        }else{
            if(object.data['status'] == 'Y'){
                $("#"+object.data['id']).removeClass('user_status_active').addClass('user_status_inactive');
            }else{
                $("#"+object.data['id']).removeClass('user_status_inactive').addClass('user_status_active');
            }
        }
        return false;
    },

    UamUserStatusCallBack:function(response,object){
        if(response['status'] == "success"){
            if(object.data['status'] == 'Y'){
                $("#"+object.data['id']).removeClass('user_status_inactive').addClass('user_status_active');
            }else{
                $("#"+object.data['id']).removeClass('user_status_active').addClass('user_status_inactive');
            }
            $.growl({message: response['msg']},{type: response['status']});
            setTimeout(function(){
                location.reload();
            },1000);
        }else{
            if(object.data['status'] == 'Y'){
                $("#"+object.data['id']).removeClass('user_status_active').addClass('user_status_inactive');
            }else{
                $("#"+object.data['id']).removeClass('user_status_inactive').addClass('user_status_active');
            }
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },

    AddApplicantCallBack:function(response,object){
        var no_of_applicants = object.data['no_of_applicants']-1;
        if(no_of_applicants == 1){
            normal_addremove(no_of_applicants);
        }
        if(_nonInd == "NON_IND_HUF" && object.data['no_of_applicants'] == 2 ){

        var dynamicHtml = '<li class="nav-item"  id="applicant'+object.data['no_of_applicants']+'" onclick="registerTabEvent('+object.data['no_of_applicants']+')">'+
                                '<a href="#tab'+object.data['no_of_applicants']+'"class="nav-link check" data-id="nextapplicant-'+no_of_applicants+'" data-toggle="tab"  role="tab">HUF'+'</a>'+
                            '</li>';
                $(".tabList li:last-child").after(dynamicHtml);
                $( "#tab"+no_of_applicants ).after( response );
                            huf_addremove(no_of_applicants+1);
        }else{
        var dynamicHtml = '<li class="nav-item"  id="applicant'+object.data['no_of_applicants']+'" onclick="registerTabEvent('+object.data['no_of_applicants']+')">'+
                                '<a href="#tab'+object.data['no_of_applicants']+'"class="nav-link check" data-id="nextapplicant-'+no_of_applicants+'" data-toggle="tab"  role="tab">Applicant'+object.data['no_of_applicants']+'</a>'+
                            '</li>';
        $(".tabList li:last-child").after(dynamicHtml);
        $( "#tab"+no_of_applicants ).after( response );
        normal_addremove(no_of_applicants+1);
        }
        for(i=1;i<=object.data['no_of_applicants']-1;i++)
        {
            //add class(nextapplicant) for Next Applicant multiple users
            $("#nextapplicant-"+i).addClass('nextapplicant').html("Next");
            $("#nextapplicant-"+i).removeClass('saveAccountDetails').html("Next");
        }
        $("#tab"+object.data['no_of_applicants'] ).attr('style','display: none;');

        // Click function
        $('#tabs-nav li').click(function(){
            $('#tabs-nav li').removeClass('active');
            $(this).addClass('active');
            $('.tab-content-cust').hide();

            var activeTab = $(this).find('a').attr('href');
            $(activeTab).fadeIn();
            return false;
        });
        addSelect2('education','Education');
        addSelect2('gross_income','Gross Income');
        addSelect2('residential_status','Residential Status');
        addSelect2('country_of_birth','Country of Birth');
        addSelect2('citizenship','Citizenship');
        addSelect2('marital_status','Marital Status');
        addSelect2('customer_account_type','Customer Account Type');
        $(".dob").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",
        });
        $(".dof").datepicker({
            clearBtn: true,
            format: "dd-mm-yyyy",
            endDate: "today",
            maxDate: "today",
        });
        $('.pan').inputmask("aaaa-a-9999-a", {
            "placeholder": "XXXX-X-9999-X",
            autoUnmask: true,
        });
        $('.mobile').inputmask('9999-999-999', {
            clearMaskOnLostFocus: false,
            autoUnmask: true,
        });
        //To greyout residential status
        $(".residential_status option[value='2']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='3']").prop('disabled',true).trigger('change');
        $(".residential_status option[value='4']").prop('disabled',true).trigger('change');

        // Define Pan trigger for the recently added applicant
        definePANcheckTrigger(object.data['no_of_applicants']);
        return false;
    },
    CreateBatchIdCallBack:function(response,object){
        $("#AOF_numbers").html(object.data['aofNumbers']);
        var html = '';
        jQuery.each( object.data['aofNumbers'], function( id, batch ) {
            html += '<tr>'+
                        '<th scope="row">'+batch.split('-')[0]+'</th>'+
                        '<td>'+batch.split('-')[1]+'</td>'+
                    '</tr>';
        });
        $("#accountsListTable").find('tbody').html(html);
        $("#batchId").html(response.data);
        $(".saveBatch").attr('id',object.data['applicantIds']);
        $($(".createBatch").data('target')).modal('toggle');
        return false;
    },
    SaveBatchCallBack:function(response,object){
        if(response['status'] == "success"){
            $($(".createBatch").data('target')).modal('toggle');
            $.growl({message: response['msg']},{type: response['status']});
            setTimeout(function(){
                location.reload();
            }, 1000);
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    PrintAirBatchIdCallBack:function(response,object){
        $("#batchId").html(object.data['id']);
        $('#batch_Id').html($("#airwayBillnoDiv_"+object.data['batchId']).parent().prev().html());
        $('#airwaybill_number').html($("#airwayBillnoDiv_"+object.data['batchId']).html());
        $('#courier').html($("#airwayBillnoDiv_"+object.data['batchId']).parent().next().html());
        var html = '';
        jQuery.each( response.data, function( idx, Obj ) {
         html += '<tr>'+
         '<th scope="row">'+Obj.aof_number+'</th>'+
         '<td>'+Obj.user_name+'</td>'+
         '</tr>';
         });
        $("#airListTable").find('tbody').html(html);
        $($(".printeairBatch").data('target')).modal('toggle');
        return false;
    },
    SaveAirwayBillNumberCallBack:function(response,object){
        if(response['status'] == "success"){
            $('#airwayBillnoDiv_'+object.data['id']).html(response.data);
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveArchivalNumberCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
            setTimeout(function(){
                // redirectUrl(object.data['id'],'/archival/addarchivalno');
                redirectUrl(object.data['id'],'/archival/dashboard');
            }, 1000);
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    EditAirwaybillCallBack:function(response,object){
        $("#batchId").html(object.data['batchId']);
        $("#batch_id").html($("#airwayBillnoDiv_"+object.data['batchId']).parent().prev().html());
        $('#airwaybill_no').val($("#airwayBillnoDiv_"+object.data['batchId']).html());
        $("#courierData option").remove();
        jQuery.each( response.data, function( val, courier ) {
            $("#courierData").append($('<option>').val(val).html(courier));
        });
        if($("#airwayBillnoDiv_"+object.data['batchId']).parent().parent().find('.editairwaybill').data('courier') != '')
        {
            $("#courierData").val($("#airwayBillnoDiv_"+object.data['batchId']).parent().parent().find('.editairwaybill').data('courier'));
        }
        $(".saveBatch").attr('id',object.data['applicantIds']);
        $($(".editairwaybill").data('target')).modal('toggle');
        return false;
    },
    UpdateAirwaybillCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
            setTimeout(function(){
                location.reload();
            }, 1000);
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    UpdateArichvalCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
            setTimeout(function(){
                location.reload();
            }, 1000);
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SchemeDataCallBack:function(response,object){
        var  accounttype = $('#account_type').val();
        var accountHolders =  $("#qty_input").val();
        var customertype =  $("#applicantId-1").attr('customertype');

        if (typeof(response.data['schemeCodeChanged']) != 'undefined' && response.data['schemeCodeChanged']) {
        //    blankBasicDetails(accountHolders);
        }

        if(response.data['accountType']=='SA' || response.data['accountType']=='CA'){
            _globalSchemeDetails = response.data['schemeData'];
            if(accounttype != 4) _globalTDSchemeDetails = '';
        }else{
            _globalTDSchemeDetails = response.data['schemeData'];
            if(accounttype != 4) _globalSchemeDetails = '';
        }

        // If TD schemecode is for staff Only one applicant permissible
        if(response.data['accountType']=='TD' && _globalTDSchemeDetails.staff_customer != null){
            if(accounttype == 3 && _globalTDSchemeDetails.staff_customer.toLowerCase() =='staff' && $('#qty_input').val()>1 ){
                $.growl({message: "Validation Failed! For TD Staff schemes only one applicant permissible. Please retry!"},{type: "warning"});
                $('#qty_input').val(1);
                setTimeout(function(){location.reload(true);},1500);
            }
        }

        // If Savings schemecode is for staff Only one applicant permissible
        if(response.data['accountType']=='SA' && _globalSchemeDetails.staff_customer != null){
          
            if(accounttype == 1 && _globalSchemeDetails.staff_customer.toLowerCase() =='staff' && $('#qty_input').val()>1 ){
                $.growl({message: "Validation Failed! For Staff schemes only one applicant permissible. Please retry!"},{type: "warning"});
                $('#qty_input').val(1);
                setTimeout(function(){location.reload(true);},1500);
            }
        }

        if(response.data['schemeData']['scheme_code'] == null){

           $("#PAN_F60_Tabs").addClass('pan60active');
           $("#pan60active_blur").addClass('pan60active_blur');
           return false;
        }else{
           $("#PAN_F60_Tabs").removeClass('pan60active');
           $("#pan60active_blur").removeClass('pan60active_blur');

        }

        if(response.data['schemeData']['td_scheme_code'] == ''){

           $("#PAN_F60_Tabs").addClass('pan60active');
           $("#pan60active_blur").addClass('pan60active_blur');
           return false;
        }else{
           $("#PAN_F60_Tabs").removeClass('pan60active');
           $("#pan60active_blur").removeClass('pan60active_blur');

        }

        if(typeof(response.data['schemeData']['scheme_code']) != "undefined")
            {
               if(response.data['accountType'] == 'SA' || response.data['accountType'] == 'CA'){

                    var description = response.data['schemeData']['scheme_code'] + '\n' + '- AQB : ' + response.data['schemeData']['aqb']
                                + '\n' + '- WITHDRAW LIMIT : ' + response.data['schemeData']['withdraw_limit'] + '\n' + '- SPENDING LIMIT : ' + response.data['schemeData']['spending_limit']
                                + '\n' + '- FREE LEAVES : ' + response.data['schemeData']['free_leaves']
                                + '\n' + '- MINIMUM SWEEPS AMOUNT : ' + response.data['schemeData']['sweeps_parameter'];
                    $("#scheme_code_description").attr('aria-label',description);

               }else{

                    var description = response.data['schemeData']['scheme_code'] + '\n' + '- VALIDATION DAYS : ' + response.data['schemeData']['validation_days']
                                + '\n' +'- MIN : ' + response.data['schemeData']['min'] + '\n' + '- MAX : ' + response.data['schemeData']['max']
                                + '\n' + '- PAYOUT : ' + response.data['schemeData']['payout'] + '\n' + '- MIN AGE : ' + response.data['schemeData']['min_age']
                                + '\n' + '- MAX AGE : ' + response.data['schemeData']['max_age']
                                + '\n' + '- CALLABLE : ' + response.data['schemeData']['callable_noncallable'];
                    if(accounttype == 4){

                        $("#td_scheme_code_description").attr('aria-label',description);
                    }else{

                        $("#scheme_code_description").attr('aria-label',description);
                    }

                 }

            }else{
                $("#scheme_code_description").attr('aria-label',"Please select scheme code");
            }



       if(accounttype == 2)
        {
            if(_globalSchemeDetails.pan_mandatory.toLowerCase()=='y')
            {
                $('.form60').addClass('display-none');
            }else{
                $('.form60').removeClass('display-none');
            }
        }
        
        if(accounttype == 3){
             var TD_AGE;
            if (response.data['schemeData']['age'] == null) {
               TD_AGE = 'Any age permissible';
            }else if (response.data['schemeData']['age'] == 'Senior') {
               TD_AGE = 'Senior';
            }


            if(_globalTDSchemeDetails.staff_customer.toLowerCase() =='staff'){
                $('.form60').addClass('display-none');
                $(".customer_account_type").val('3').trigger('change');
                $('.empno').removeClass('display-none');
                addSelect2('customer_account_type','customer account type',true);
            }else{
                $('.form60').removeClass('display-none');
                $('.empno').addClass('display-none');
                //addSelect2('customer_account_type','customer account type',false);
                $(".customer_account_type").val('1').trigger('change').prop('disabled',false);
                $("#customer_account_type-1 option[value='3']").prop('disabled',true);
                $("#customer_account_type-1").select2();
            }

            if(_globalTDSchemeDetails.pan_mandatory.toLowerCase() =='y'){
                $('.form60').addClass('display-none');

            }else{
                $('.form60').removeClass('display-none');
            }

            return false;
        }else{


            if(accounttype == 1 || accounttype == 5){ // For Savings Staff Scheme - Default customer_account_type to Staff
                if(_globalSchemeDetails.staff_customer.toLowerCase() =='staff'){
                    $('.form60').addClass('display-none');
                    $(".customer_account_type").val('3').prop('disabled',true).trigger('change');
                    $('.empno').removeClass('display-none');
                    //$(".customer_account_type option[value='3']").prop('disabled',false).trigger('change');
                    addSelect2('customer_account_type','customer account type',true);

                }else{
                    $('.form60').removeClass('display-none');
                    //$(".customer_account_type").val('3').prop('disabled',false).trigger('change');
                    //$("#customer_account_type-1").select2();
                    $('.empno').addClass('display-none');
                    $(".customer_account_type").val('1').trigger('change');
                    $(".customer_account_type").val('1').prop('disabled',false).trigger('change');
                    addSelect2('customer_account_type','customer account type',false);
                }

                if(_globalSchemeDetails.staff_customer.toLowerCase() =='non staff'){

                    $("#customer_account_type-1 option[value='3']").prop('disabled',true);
                    $("#customer_account_type-1").select2();
                }else if(_globalSchemeDetails.staff_customer.toLowerCase() =='only_ja'){

                    $("#customer_account_type-1 option[value='3']").prop('disabled',true);
                    $("#customer_account_type-1").select2();
                }
                else{
                    $("#customer_account_type-1 option[value='3']").prop('disabled',false);
                    $("#customer_account_type-1").select2();
                    // $("#customer_account_type-1 option[value='3']").unwrap();
                }

                // if(_globalSchemeDetails.staff_customer.toLowerCase() =='only_ja'){

                //     $("#customer_account_type-1 option[value='3']").prop('disabled',true);
                //     $("#customer_account_type-1").select2();
                // }else{
                //     $("#customer_account_type-1 option[value='3']").prop('disabled',false);
                //     $("#customer_account_type-1").select2();
                //     // $("#customer_account_type-1 option[value='3']").unwrap();
                // }

                //if scheme code is salaray account
                if($("#scheme_code").val() == 8)
                {
                    $(".labelCodeDiv").removeClass('display-none');
                }else{
                    $(".labelCodeDiv").addClass('display-none');
                }
                //if scheme code is DCB Elite account
                var getSchemeText = $("#scheme_code option:selected").text().slice(0,5);
                if($("#scheme_code").val() == 11)
                {
                    $(".eliteAccountNumberDiv").removeClass('display-none');
                }else{
                    $(".eliteAccountNumberDiv").addClass('display-none');
                }
                //$(".customer_account_type option[value='3']").prop('disabled',false).trigger('change');

            }else if(accounttype == 4){
                $('.form60').removeClass('display-none');
                $("#scheme_code option[value='3']").wrap('<span class="scheme_code" style="display: none;" />');
                if($(".customer_account_type").val() == 3)
                {
                    $(".customer_account_type").val('1').trigger('change');
                    addSelect2('customer_account_type','customer account type',false);
                }
                $(".customer_account_type option[value='3']").prop('disabled', !$(".customer_account_type option[value='3']").prop('disabled'));
                $('.customer_account_type').select2();


                if(($("#td_scheme_code").val() == "") || ($("#scheme_code").val() == null)){
                        $("#PAN_F60_Tabs").addClass('pan60active');
                        $("#pan60active_blur").addClass('pan60active_blur');
                        return false;
                    }else{
                        $("#PAN_F60_Tabs").removeClass('pan60active');
                        $("#pan60active_blur").removeClass('pan60active_blur');
                    }
            }

            if(accounttype == 2){

                if(_globalSchemeDetails.staff_customer.toLowerCase() =='non staff'){
                    $("#customer_account_type-1 option[value='3']").prop('disabled',true);
                    $("#customer_account_type-1").select2();
                }

                if($("#scheme_code").val() == 4)
                {
                    $(".eliteAccountNumberDiv").removeClass('display-none');
                    
                }else{
                    $(".eliteAccountNumberDiv").addClass('display-none');
                  
                }
                $("#currentAccountProInd").removeClass('display-none');
                $("#current_prop_indi").removeClass('display-none');

                // $("#current_prop_indi").attr('disabled','disabled');
            }else{
                $("#currentAccountProInd").addClass('display-none');
                $("#current_prop_indi").addClass('display-none');

            }
           
            return false;
        }
    },
    GpaDataCallBack:function(response,object){
        var description = response.data['gpaData']['plan_name'] + ' - SUM INSURED : ' + response.data['gpaData']['sum_insured']
                        + ' - COVERAGE : ' + response.data['gpaData']['coverage'] + ' - PREMIUM INCL GST : ' + response.data['gpaData']['premium_incl_gst'] ;
        $("#gpa_plan_description").attr('aria-label',description);
        return false;
    },
    NpcBankCodeCallBack:function(response,object){
        if(response['status'] == "success"){
            $("#ifsc_code1").val(response.data['ifsc_code_prefix']);
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    BankCodeCallBack:function(response,object){
        if(response['status'] == "success"){
            $("#ifsc_code").val(response.data['ifsc_code_prefix']);
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    MaturityBankCodeCallBack:function(response,object){
        if(response['status'] == "success"){
            if($("#maturity_flag").prop('checked') == false)
            {
                $("#maturity_ifsc_code").val(response.data['ifsc_code_prefix']);
            }
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    RiskClassficationRatingCallBack:function(response,object){
         var riskapplicantId = object.data['applicantId'];
         if(object.data['occupation'] == 15){
            $('[data-attr = '+"pepno-"+riskapplicantId+']').removeAttr('checked');
            $('#pep-'+riskapplicantId).prop('checked',true);
        }else{
            $('#pep-'+riskapplicantId).removeAttr('checked')
            $('[data-attr = '+"pepno-"+riskapplicantId+']').prop('checked',true)
        }
        

        if(response['status'] == "success"){
            
            getCategorisation(riskapplicantId);

            $('#risk_classification_rating-'+object.data['applicantId']).val(response.data['riskRating']);
                if((response.data['individualRisk'].countryOfBirthRisk == 'H')|| (response.data['individualRisk'].countryRisk =='H') || (response.data['individualRisk'].citizenshipRisk  == 'H') || (response.data['individualRisk'].residenceForTaxRisk  == 'H')){

                  _globalCountryRisk[riskapplicantId] = "H";

                }else{

                   _globalCountryRisk[riskapplicantId] = "NH";
                }

            if ((_globalCountryRisk[riskapplicantId] == 'H') && ((_selectedID == 'country_name') || (_selectedID == 'country_of_birth') || (_selectedID == 'citizenship') || (_selectedID == 'residence')) ) {
                  $.growl({message: 'Account opening is prohibited for restricted countries.'},{type: "warning"});
              }else{
                }
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    CategorisationCallBack:function(response,object){
        
        var applicantId = object.data['applicantId'];
        if(object.data['occupation'] == 15){
            $('[data-attr = '+"pepno-"+applicantId+']').removeAttr('checked');
            $('#pep-'+applicantId).prop('checked',true);
        }else{
            $('#pep-'+applicantId).removeAttr('checked')
            $('[data-attr = '+"pepno-"+applicantId+']').prop('checked',true)
        }
        $("#basis_categorisation-"+applicantId).val(response.data['response']).trigger('change');
        addSelect2('basis_categorisation','Categorisation',true);
        // getCategorisation(applicantId);
       
        return false;
    },
    PanIsValidCallBack:function(response,object){
        var applicantId = object.data['applicantId'];
        panIsvalid = 0;
        if(response['status'] == "success"){

             panIsvalid = 1;
             _globalPanOkToContinue = true;

            // savePanDetails(response.data['PANRes']);
            $.growl({message:response['msg']},{type:response['status']});
            return true;
        }else{
            if(object.data['etb_ntb'] == 'NTB'){
               $('#pancard_no-'+applicantId).val($('#pancard_no-'+applicantId).val().substr(0,8));
            }

            $.growl({message: ''+response['msg']},{type: "warning",allow_dismiss:false});

            return false;
        }
    },
    savePanDetailsCallBack:function(response,object){
        if(response['status'] == "success"){

        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    PanExistsCallBack:function(response,object){
        if(response.data != '')
        {
            newCustomer = 1;
        }else{
            newCustomer = 0;
        }
        panResponse = response;
        return false;
    },
    UpdateInwardStatusCallBack:function(response,object){
        if(response['status'] == "success"){
            var baseUrl = $('meta[name="base_url"]').attr('content');
            if(response['status'] == "success"){
                setTimeout(function(){
                    window.location = baseUrl+"/inward/dashboard";
                }, 1000);
            }
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveAirwayBillCallBack:function(response,object){
        if(response['status'] == "success"){
            var baseUrl = $('meta[name="base_url"]').attr('content');
            if(response['status'] == "success"){
                setTimeout(function(){
                    var param = object.data['batchId'];
                    redirectUrl(param,'/inward/updateinward');
                    return false;
                }, 1000);
            }
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
    },
    SaveETBAccountCallBack:function(response,object){
        if(typeof object.data['etb'] != undefined){
            customerType = object.data['etb'] == 'Y' ? "etb" : "ntb";
        }else{
            customerType = "ntb";
        }
        if(customerType=="etb"){
            if(response['status'] == "fail"){
                $('.br_submit_loader').addClass('display-none-existing-customer-loader');
                enableWorkAreaEtbCustomer();
                $.growl({message:response['msg']},{type: "warning"});
                return false;
            }


            if($('#ccrole').val() == 'Y' ){
                enableWorkAreaEtbCustomer();
                 $('.br_submit_loader').removeClass('display-none-existing-customer-loader');
                $("#customer_modal").modal('hide');
                $('#DelightModal').replaceWith(response);
                $('#DelightModal').modal('show');
                return false;
            }
        }
        //$("#customer_modal").modal('toggle');
        $('.br_submit_loader').removeClass('display-none-existing-customer-loader');
        $("#customer_modal").modal('hide');
        $("#tab"+object.data['applicantId']).replaceWith(response);

        $('.mobile').inputmask('9999-999-999', {
            clearMaskOnLostFocus: false,
            autoUnmask: true,
        });

        // Even if ETB, triger focusout to check for NSDL validation!
        var pf_type = $('input[name=pf_type-'+object.data['applicantId']+']:checked').val();

        if(customerType=="etb" && $("#pancard_no-"+object.data['applicantId']).val() != '' && pf_type == 'pancard'){
            panIsValid(object.data['applicantId'],'ETB');
            // setTimeout(function(){
            //     createEtbClearButton(object.data['applicantId']);
            // },1000);
        }
        global_growl.close();
        return false;
    },

    SaveETBccCallBack:function(response,object){

        if(response['status'] == "success"){

           _global_cc_data = response.data.global_cc_data;
           //_global_cc_data['form_id'] = response.data['formId'];

             // registerScreenFlow(1,2);
            // return false;
            setTimeout(function(){
                redirectUrl(response.data['formId']+'_'+JSON.stringify(_global_cc_data),'/bank/'+response.data['url']);
            }, 1000);
            $.growl({message: response['msg']},{type: response['status']});
        }else{

            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;

    },

    DebugInfoCallback:function(response,object){
        $("#displayContent").css("display","");
        $("#formId").html("<b>Form ID: </b>"+ response.data['formId']);
        $("#aof_number").html("<b>AOF Number: </b>"+ response.data['aof_number']);
        $("#acct_details").html("<b>Account Details: </b>"+ "<pre>"+JSON.stringify(response.data['acct_details'],undefined,4)+ "</pre>").html();
        $("#ovd_details").html("<b>OVD Details: </b>"+ "<pre>"+JSON.stringify(response.data['ovd_details'],undefined,4)+ "</pre>").html();
        
        $.growl({message: 'DebugInfo imported successfully'},{type: "success"});
        return false;
    },

    AmendDebugInfoCallback:function(response,object){
        $("#displayAmendContent").css("display","");
        $("#crfId").html("<b>CRF ID: </b>"+ response.data['crfId']);
        $("#crf_number").html("<b>CRF Number: </b>"+ response.data['crf_number']);
        $("#amend_master").html("<b>Amend Master: </b>"+ "<pre>"+JSON.stringify(response.data['amend_master'],undefined,4)+ "</pre>").html();
        $("#amend_queue").html("<b>Amend Queue: </b>"+ "<pre>"+JSON.stringify(response.data['amend_queue'],undefined,4)+ "</pre>").html();
    
        $.growl({message: 'AmendDebugInfo imported successfully'},{type: "success"});
        return false;
    },
    

    SchemeCodebyAccountTypeCallBackFunction:function(response,object){
        $("#scheme_code option").remove();
        jQuery.each( response.data['getschemecodebyaccounttype'], function( id, scheme ) {
          $("#scheme_code").append($('<option>').val(id).html(scheme));
        });
        $("#scheme_code").val('').trigger('change');

        if($('#account_type').val() != ""){
              $.growl({message: "Please select Scheme Code"},{type: "warning",delay:1500,allow_dismiss:false});
              $("#PAN_F60_Tabs").addClass('pan60active');
              $("#pan60active_blur").addClass('pan60active_blur');

        }
        return false;
    },
    UpdateFieldValueCallBack:function(response,object){
        UpdateFieldValueCallBackFunction(response,object);
    },
    AddressCallBack:function(response,object){
        var address = '';
        if(response['status'] == "fail"){
            $.growl({message: response['msg']},{type: "warning"});
            return false;
        }else{

        if (object.data['address_type'] == "permanent") {
            $("#nominee_name-"+object.data['applicantId']).val(response.data['nomineeName']).prop('disabled', false);
            $("#relatinship_applicant-"+object.data['applicantId']).val(response.data['nomineeRelTypeID']).trigger('change').prop('disabled', false);
            $("#nominee_address_line1-"+object.data['applicantId']).val(response.data['per_address_line1']).prop('disabled', false);
            $("#nominee_address_line2-"+object.data['applicantId']).val(response.data['per_address_line2']).prop('disabled', false);
            $("#nominee_pincode-"+object.data['applicantId']).val(response.data['per_pincode']).trigger('keyup').prop('disabled', false);
            $("#nominee_dob-"+object.data['applicantId']).val(response.data['nomineeBirthDt']).prop('disabled', false);
            $("#nominee_age-"+object.data['applicantId']).val(response.data['nomineeAge']).prop('disabled', false);

            //    $("#nominee_pincode-"+object.data['applicantId']).val('421503').trigger('keyup');
            //    $("#nominee_state-"+object.data['applicantId']).val(response.data['per_state']);
            //    $("#nominee_city-"+object.data['applicantId']).val(response.data['per_city']);
            // $("#nominee_pincode-"+object.data['applicantId']).trigger('change');
    
    
        }else{
                $("#nominee_name-"+object.data['applicantId']).val(response.data['nomineeName']).prop('disabled', false);
                $("#relatinship_applicant-"+object.data['applicantId']).val(response.data['nomineeRelTypeID']).trigger('change').prop('disabled', false);
                $("#nominee_address_line1-"+object.data['applicantId']).val(response.data['current_address_line1']).prop('disabled', false);
                $("#nominee_address_line2-"+object.data['applicantId']).val(response.data['current_address_line2']).prop('disabled', false);
                $("#nominee_pincode-"+object.data['applicantId']).val(response.data['current_pincode']).trigger('keyup').prop('disabled', false);
                $("#nominee_dob-"+object.data['applicantId']).val(response.data['nomineeBirthDt']).prop('disabled', false);
                $("#nominee_age-"+object.data['applicantId']).val(response.data['nomineeAge']).prop('disabled', false);
            //    $("#nominee_pincode-"+object.data['applicantId']).val('421503').trigger('keyup');
            //    $("#nominee_state-"+object.data['applicantId']).val(response.data['current_state']);
            //    $("#nominee_city-"+object.data['applicantId']).val(response.data['current_city']);
        }
        }
        // return false;
    },
    NomineeDetailsByAccIdCallBack:function(response,object){

        if(response['status'] == "fail"){
            $("#nominee_address_line1-"+object.data['applicantId']).val("");
            $("#nominee_address_line2-"+object.data['applicantId']).val("");
            $("#nominee_pincode-"+object.data['applicantId']).val("");
            $("#nominee_city-"+object.data['applicantId']).val("");
            $("#nominee_state-"+object.data['applicantId']).val("");
            $("#nominee_country-"+object.data['applicantId']).val("");

            $.growl({message: response['msg']},{type: "warning"});
            return false;
       }else{

        if (object.data['nominee_Details'] == "savingnomineeDetails") {
            $("#nominee_name-"+object.data['applicantId']).val(response.data['nomineeName']).prop('disabled',true);
            $("#relatinship_applicant-"+object.data['applicantId']).val(response.data['nomineeRelTypeID']).trigger('change').prop('disabled',true);
            $("#nominee_address_line1-"+object.data['applicantId']).val(response.data['addr1']).prop('disabled',true);
            $("#nominee_address_line2-"+object.data['applicantId']).val(response.data['addr2']).prop('disabled',true);
            $("#nominee_pincode-"+object.data['applicantId']).val(response.data['postalCode']).trigger('keyup').prop('disabled',true);
            $("#nominee_city-"+object.data['applicantId']).prop('disabled',true);
            $("#nominee_state-"+object.data['applicantId']).prop('disabled',true);
            $("#nominee_country-"+object.data['applicantId']).prop('disabled',true);
            $("#nominee_dob-"+object.data['applicantId']).val(response.data['nomineeBirthDt']).prop('disabled',true);
            $("#nominee_age-"+object.data['applicantId']).val(response.data['nomineeAge']).prop('disabled',true);
            
            if($("#nominee_age-"+object.data['applicantId']).val()  < 18){
                $("#guardian_name-"+object.data['applicantId']).val(response.data['guardianName']).prop('disabled',true);
                $("#guardian_address_line1-"+object.data['applicantId']).val(response.data['g_addr1']).prop('disabled',true);
                $("#guardian_address_line2-"+object.data['applicantId']).val(response.data['g_addr2']).prop('disabled',true);
                $("#guardian_pincode-"+object.data['applicantId']).val(response.data['g_postalCode']).trigger('keyup').prop('disabled',true);
            }
    
        }
        }

        
    },
    TrackingDetailsCallBack:function(response,object)
    {
        if(typeof(response)=='object'){
            if(typeof(response.status)=='string' && response.status == 'fail'){
                $.growl({message: response.msg},{type: "warning"});
                return false;
            }
        }
        $("#tracking_details").html(response);
        setTimeout(function(){
            setClipboardCode();
        }, 500);
        return false;
    },
    AmedTrackingDetailsCallBack:function(response,object){
        if(typeof(response.status) == 'string' && response.status == 'fail'){
            $.growl({message:response.msg},{type:"warning"});
            return false;
        }
        $("#amend_tracking_details").html(response);
        setTimeout(function(){
            setClipboardCode();
        }, 500);
        return false;

    },
    FormDetailsCallBack:function(response,object)
    {
        if(response['status'] == "fail"){
             $.growl({message: response['msg']},{type: "warning"});
        }else{
            var html = response;
            var formTitle = '';

            if(object.data['aof_tracking_no']!='') formTitle = object.data['aof_tracking_no'];
            else formTitle = object.data['customerName'];

            var submission = window.open();
            submission.document.write(html);
            submission.document.title='AOF: '+formTitle;
            return false;
        }
    },
    printRequestForm:function(response,object){
         if(response['status'] == "fail"){
             $.growl({message: response['msg']},{type: "warning"});
        }else{
            var html = response;
            var submission = window.open();
            submission.document.write(html);
            submission.document.title='CRF:'+object.data['crf_number'];
            return false;
        }
    },

    saveTemplateDetailsCallBack:function(response,object){
        if(response['status'] == "fail"){
            $.growl({message: "Special characters are not allowed."},{type: "warning"});
            return false;
        }else{
            if(response['status'] == "success"){
                var baseUrl = $('meta[name="base_url"]').attr('content');
                setTimeout(function(){
                    window.location = baseUrl+"/admin/templates";
                }, 300);
            }
            return false;
        }
    },
    saveChannelIDTemplateDetailsCallBack:function(response,object){
    if(response['status'] == "success"){
        var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/"));
        setTimeout(function(){
            window.location = baseUrl+"/emailsmstemplate";
        }, 1000);
    }
    $.growl({message: response['msg']},{type: response['status']});
    return false;
    },


    registerScreenFlowCallBack:function(response,object){
        // console.log(response.data);
        return false;
    },



    // userDetailsCallBack:function(response,object)
    // {
    //     userDetailsCallBackFunction(response,object);
    // },
    // saveUserDetailsCallBack:function(response,object)
    // {
    //     saveUserDetailsCallBackFunction(response,object);
    // },

    uamuserDetailsCallBack:function(response,object)
    {
        uamuserDetailsCallBackFunction(response,object);
    },
    saveUamUserDetailsCallBack:function(response,object)
    {
        saveUamUserDetailsCallBackFunction(response,object);
    },
    ImportExcelDataCallBack:function(response,object)
    {
        ImportExcelDataCallBackFunction(response,object)
    },
    SaveArchivalRecordFunction:function(response,object)
    {
        SaveArchivalRecordDataCallbackFunction(response,object);
    },

    chatUsersCallBack:function(response,object){
        $("#usersList").html('');
        $("#usersList").append(response);
        $("#searchuser").val(object.data['searchParam']).focus();
        if((typeof(object.data['searchParam']) == 'undefined') || (object.data['searchParam'] == '')){
            $("#closeSearchUserIcon").css("display","none");
        }else{
            $("#closeSearchUserIcon").css("display","block");
        }
        $(".has_message").remove();
        showChat("showChat");
        var documentHeight = $(document).height();
        var chatRemainingHeight = $(".header-navbar").height()+95;
        addScrollBar("chat-slim-scroll",documentHeight-chatRemainingHeight);
        return false;
    },
    usersChatDetailsCallBack:function(response,object){
        var chatVisible = isChatVisible();

        $("#userChat").html('');
        $("#userChat").append(response);

        if(chatVisible){// Dont animate slider if chat window is already visible!
            $('.showChat_inner').toggle();
        }else{
            showChat("showChat_inner");
        }

        var documentHeight = $(window).height();
        var chatRemainingHeight = ($(".chat-inner-header").height()*9)+$(".chat-reply-box").height();
        addScrollBar("chat-slim-scroll",documentHeight-chatRemainingHeight);

        updateIsRead(object);
        return false;
    },
    usersLastChatIdCallBack:function(response,object){
        var parsedResponse = JSON.parse(response);
        _lastchatid = parsedResponse['chat_id'];
        $('#lastchat_id_new').val(_lastchatid);
        return false;
    },

    chatUsersCallBack:function(response,object){
        $("#usersList").html('');
        $("#usersList").append(response);
        $("#searchuser").val(object.data['searchParam']).focus();
        if((typeof(object.data['searchParam']) == 'undefined') || (object.data['searchParam'] == '')){
            $("#closeSearchUserIcon").css("display","none");
        }else{
            $("#closeSearchUserIcon").css("display","block");
        }
        $(".has_message").remove();
        showChat("showChat");
        var documentHeight = $(document).height();
        var chatRemainingHeight = $(".header-navbar").height()+95;
        addScrollBar("chat-slim-scroll",documentHeight-chatRemainingHeight);
        return false;
    },
    usersChatDetailsCallBack:function(response,object){
        var chatVisible = isChatVisible();
        $("#userChat").html('');
        $("#userChat").append(response);
        if(chatVisible){// Dont animate slider if chat window is already visible!
            $('.showChat_inner').toggle();
        }else{
            showChat("showChat_inner");
        }
        var documentHeight = $(window).height();
        var chatRemainingHeight = ($(".chat-inner-header").height()*9)+$(".chat-reply-box").height();
        addScrollBar("chat-slim-scroll",documentHeight-chatRemainingHeight);

        updateIsRead(object);
        return false;
    },
    BranchMappingDetailsCallBack:function(response,object){
        $('#mapping_modal').modal("toggle");
        $.growl({message: response['msg']},{type: response['status']});
        setTimeout(function(){
            location.reload();
        }, 1000)
        return false;
    },
    StatesCallBack:function(response,object){
        $('#city').find('option').remove();
        $('#city').append($('<option>').val('').html('Select City'));
        jQuery.each( response.data, function( id, city ) {
            $('#city').append($('<option>').val(id).html(city));
        });
        getBranches();
        return false;
    },
    AmendStateCityDataCallBack:function(response,object){
        AmendStateCityDataCallBackFunction(response,object);
    },
    // SaveAmendCommentsCallBack:function(response,object){
    //     SaveAmendCommentsCallBackFunction(response,object);
    // },
    SaveAmendNpcSubmitCallBack:function(response,object){
        SaveAmendNpcSubmitCallBackFunction(response,object);
    },
    UpdateAmendFieldValueCallBack:function(response,object){
        UpdateAmendFieldValueCallBackFunction(response,object);
    },
    CitiesCallBack:function(response,object){
        $('#branch').find('option').remove();
        $('#branch').append($('<option>').val('').html('Select Branch'));
        jQuery.each( response.data, function( id, branch ) {
            $('#branch').append($('<option>').val(id).html(branch));
        });
        getBranches();
        return false;
    },
    usersLastChatIdCallBack:function(response,object){
        var parsedResponse = JSON.parse(response);
        _lastchatid = parsedResponse['chat_id'];
        $('#lastchat_id_new').val(_lastchatid);
        return false;
    },
    saveMessageCallBack:function(response,object){
        if(response['status'] == "success"){
            $("#message").val('');
            $.growl({message: response.msg},{type: response.status});
            getuserchatbyid(response.data['receipent_id']);
        }
        return false;
    },
    updateIsReadCallBack:function(response,object){
        if(response['status'] == "success"){
            $("#"+response['userId']+"_count").remove();
        }
        return false;
    },/*
    accountDetailsCallBack:function(response,object)
    {
        accountDetailsCallBackFunction(response,object);
    },*/
    updateOaoDetailsCallBack:function(response,object)
    {
        updateOaoDetailsCallBackFunction(response,object);
    },
    EKYCDetailsCallBack:function(response,object)
    {
        EKYCDetailsCallBackFunction(response,object);
    },
    saveFormCallBack:function(response,object)
    {
        saveFormCallBackFunction(response,object);
    },
    panExistsCallBack:function(response,object)
    {
        panExistsCallBackFunction(response,object);
    },
    UpdateApiQueueCallBack:function(response,object)
    {
        UpdateApiQueueCallBackFunction(response,object);
    },
    userRolesCallBack:function(response,object)
    {
        userRolesCallBackFunction(response,object);
    },
    userUamRolesCallBack:function(response,object)
    {
        userUamRolesCallBackFunction(response,object);
    },
    SettingsCallBack:function(response,object){
        SettingsCallBackFunction(response,object);
    },
    createCustomerIdCallBack:function(response,object){
        createCustomerIdCallBackFunction(response,object);
    },
    checkFundingStatusCallBack:function(response,object){
        checkFundingStatusCallBackFunction(response,object);
    },
    createAccountNumberCallBack:function(response,object){
        createAccountNumberCallBackFunction(response,object);
    },
    fundTransferCallBack:function(response,object){
        fundTransferCallBackFunction(response,object);
    },
    checkTDaccountCreatedCallBack:function(response,object){
        checkTDaccountCreatedCallBackFunction(response,object);
    },
    AccountDetailsCallBack:function(response,object){
        AccountDetailsCallBackFunction(response,object);
    },
    PrivilegeUpdateAccountDetailsCallBack:function(response,object){
        PrivilegeUpdateAccountDetailsCallBackFunction(response,object);
    },
    generateQueryIdCallBack:function(response,object){
        generateQueryIdCallBackFunction(response,object);
    },
    checkDedupeStatusCallBack:function(response,object){
        checkDedupeStatusCallBackFunction(response,object);
    },
    markFormForQCCallBack:function(response,object){
        markFormForQCCallBackFunction(response,object);
    },
    SchemeDetailsCallBack:function(response,object){
        SchemeDetailsCallBackFunction(response,object);
    },
    TableColumnsCallBack:function(response,object){
        TableColumnsCallBackFunction(response,object);
    },
    OaoTableColumnsCallBack:function(response,object){
        OaoTableColumnsCallBackFunction(response,object);
    },
    updateAadhaarMaskingAppCallBack:function(response,object){
        updateAadhaarMaskingCallBack(response,object);
    },
    SaveOaoColumnDataCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message:response['msg']},{type: "warning"});
            return false;
        }
        SaveOaoColumnDataCallBackFunction(response,object);
    },
    SaveColumnDataCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message:response['msg']},{type: "warning"});
            return false;
        }
        SaveColumnDataCallBackFunction(response,object);
    },
    SaveUserColumnDataCallBack:function(response,object){
        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message:response['msg']},{type: "warning"});
            return false;
        }
        SaveUserColumnDataCallBackFunction(response,object);
    },
    L3UpdateCallback:function(response,object){
        L3UpdateCallbackFunction(response,object);
    },
    AmendL3UpdateCallback:function(response,object){
        AmendL3UpdateCallbackFunction(response,object);
    },
    DelightKitCallBack:function(response,object){
        DelightKitCallBackFunction(response,object);
    },
    UpdateDedupeStatusCallBack:function(response,object){
        UpdateDedupeStatusCallBackFunction(response,object)
    },
    UpdateCustomerIdCallBack:function(response,object){
        UpdateCustomerIdCallBackFunction(response,object)
    },
    UpdateKycCallBack:function(response,object){
        UpdateKycCallBackFunction(response,object)
    },
    UpdateInternetBankCallBack:function(response,object){
        UpdateInternetBankCallBackFunction(response,object)
    },
    UpdateFundinStatusCallBack:function(response,object){
        UpdateFundinStatusCallBackFunction(response,object)
    },
    UpdateAccountNoCallBack:function(response,object){
        UpdateAccountNoCallBackFunction(response,object)
    },
    UpdateFundTransferCallBack:function(response,object){
        UpdateFundTransferCallBackFunction(response,object)
    },
    UpdateSignatureFlagCallBack:function(response,object){
        UpdateSignatureFlagCallBackFunction(response,object)
    },
    UpdateCardFlagCallBack:function(response,object){
        UpdateCardFlagCallBackFunction(response,object)
    },
    UpdateNextRoleCallBack:function(response,object){
        UpdateNextRoleCallBackFunction(response,object)
    },
    FormAbortCallBack:function(response,object){
        FormAbortCallBackFunction(response,object)
    },
    RejectFormCallback:function(response,object){
        RejectFormCallbackFunction(response,object)
    },
    SaveIndentCallBack:function(response,object){
        SaveIndentCallBackFunction(response,object)
    },
    StatusUpdateApprovalCallBack:function(response,object){
        StatusUpdateApprovalCallBackFunction(response,object);
    },
    SubmitApprovalCallBack:function(response,object){
        SubmitApprovalCallBackCallBackFunction(response,object);
    },
    StateCityDataCallBack:function(response,object){
        StateCityDataCallBackFunction(response,object);
    },
    MarkApproverCallBack:function(response,object){
        MarkApproverCallBackFunction(response,object);
    },
    undeleteUsersCallBack:function(response,object){
        undeleteUsersCallBackFunction(response,object);
    },
    getchartdataCallBack:function(response,object){
        //console.log(response.data[0]);
        _rtData = response.data['filteredRtData'];
        _rtApiData = response.data['filteredRtApiData'];

        _rtAvgTat = response.data['filteredRtAvgTat'];
        _rtErrDetection = response.data['filteredRtErrDetection'];

        _rtL1Desc = response.data['filteredRtL1Data'];
        _rtL2Desc = response.data['filteredRtL2Data'];
        _rtQCDesc = response.data['filteredRtQCData'];
        _rtAUDesc = response.data['filteredRtAUData'];
        _rtFtrData = response.data['filteredRtFtrData'];

        updateEquilizer(_rtData, _rtApiData);
        updateAvgTatRadar(_rtAvgTat);
        updateErrorRate(_rtErrDetection);

        updateDescChart(_rtL1Desc, 'l1_desc', '#f0d762');
        updateDescChart(_rtL2Desc, 'l2_desc', '#f9b105');
        updateDescChart(_rtQCDesc, 'qc_desc', '#e1840e');
        updateDescChart(_rtAUDesc, 'au_desc', '#b31d15');
        updateFtrTable(_rtFtrData);

        if(response['status'] == "success"){
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message: response['msg']},{type: "warning"});
            return false;
        }
    },
    getfilterchartdataCallBack:function(response,object){

        if(response['status'] == "success"){
         _rtData = response.data['filteredArray']['filteredRtData'];
         _rtApiData = response.data['filteredArray']['filteredRtApiData'];

         _rtAvgTat = response.data['filteredArray']['filteredRtAvgTat'];
         _rtErrDetection = response.data['filteredArray']['filteredRtErrDetection'];

         _rtL1Desc = response.data['filteredArray']['filteredRtL1Data'];
         _rtL2Desc = response.data['filteredArray']['filteredRtL2Data'];
         _rtQCDesc = response.data['filteredArray']['filteredRtQCData'];
         _rtAUDesc = response.data['filteredArray']['filteredRtAUData'];
         _rtFtrData = response.data['filteredArray']['filteredRtFtrData'];

         updateEquilizer(_rtData, _rtApiData);
         updateAvgTatRadar(_rtAvgTat);
         updateErrorRate(_rtErrDetection);

         updateDescChart(_rtL1Desc, 'l1_desc', '#f0d762');
         updateDescChart(_rtL2Desc, 'l2_desc', '#f9b105');
         updateDescChart(_rtQCDesc, 'qc_desc', '#e1840e');
         updateDescChart(_rtAUDesc, 'au_desc', '#b31d15');
         updateFtrTable(_rtFtrData);

            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message: response['msg']},{type: "warning"});
            return false;
        }
    },
    UpdateDRStatusCallBack:function(response,object){
        UpdateDRStatusCallBackFunction(response,object);
    },
    KitDispatchCallBack:function(response,object){
        KitDispatchCallBackFunction(response,object);
    },
    updateKitStatusCallBack:function(response,object){
        updateKitStatusCallBackFunction(response,object);
    },
    KitsGeneratedCallBack:function (response,object) {
        KitsGeneratedCallBackFunction(response,object);
    },
    InventoryDetailsCallBack:function(response,object){
        InventoryDetailsCallBackFunction(response,object);
    },
    tatReportCallBack:function(response,object) {
        tatReportCallBackFunction(response,object);
    },
    resetAofCommentCallBack:function(response,object) {
        resetAofCommentCallBackFunction(response,object);
    },
    fundReceivedCallBack:function(response,object) {
        fundReceivedCallBackFunction(response,object);
    },
    vkycDoneCallBack:function(response,object) {
        vkycDoneCallBackFunction(response,object);
    },l3ReportCallBack:function(response,object) {
        l3ReportCallBackFunction(response,object);
    },
    getAmendDataCallBack:function(response,object){
        getAmendDataCallBackFunction(response,object);
    },
    custidaccfetchDataCallBack:function(response,object){
        custidaccfetchDataCallBackFunction(response,object);
    },selectdocumentCallBack:function(response,object){
        selectdocumentCallBackFunction(response,object);
    },customerChnageCallBack:function(response,object){
        customerChnageCallBackFunction(response,object);
    },insertedDataCallBack:function(response,object){
        insertedDataCallBackFunction(response,object);
    },fetchdatacustidCallBack:function(response,object){
        fetchdatacustidCallBackFunction(response,object);
    },fetcPincodeDataCallBack(response,object){
        fetcPincodeDataCallBackFunction(response,object);
    },AmendDeleteImageCallBack(response,object){
        var ImageDiv = object.data['image_div'];
        // var img_div = ImageDiv.substr(-1);
        // if(ImageDiv.substr(-3) == 'div'){
        if(ImageDiv.split('_')[2] == 'div'){
           var  img_div = ImageDiv;
           var check_2 = ImageDiv.split('-');
           var check_1 = check_2[1].split('_');
           var check_id = check_1[0];
        }else{
            var img_div = "amend_div-"+ImageDiv.substr(-1);
            var check_id = ImageDiv.substr(-1);
        }

        if(ImageDiv.split('_')[1] == 'div'){
            $('#'+ImageDiv).remove();
            $('#upload_amend_card-'+ImageDiv.split('_')[0]).css('display','');
        }

        var otherCheckboxId = ImageDiv.split("_")[0];
        if (typeof otherCheckboxId != 'undefined' && otherCheckboxId.substr(0,5) == 'other') {
            //document.getElementById(otherCheckboxId).checked = false
            otherCheckboxId.checked = false
        }
        if(response['status'] == "success"){
            $("#"+img_div).siblings().removeClass('display-none');
            $("#"+img_div).remove();
            if(1){ // Force remove trashicon if not cleared!
                forceRemoveTrashIcon = ImageDiv.substr(0,ImageDiv.length-4);                
                $("#"+forceRemoveTrashIcon).find('.upload-delete').addClass('display-none');
            }
            if($('#amend_image_check_-'+check_id).is(':checked') == true){
                $('#amend_image_check_-'+check_id).removeAttr('checked');
                $('#amend_image_check_-'+check_id).removeAttr('disabled');
            }
            $.growl({message: response['msg']},{type: response['status']});
        }else{
            $.growl({message: response['msg']},{type: "warning"});
        }
        return false;
        
    },checkAmendAuthFunction:function(response,object){
        if(response['status'] == 'success'){
            savecrf_cb(response,object);  
            disableSaveAndContinue(this);

        }else{
            savecrf_cb(response,object);  
            $('.submitamenddetailNpc').removeAttr('disabled');
        }
    },updateCrfCallback:function(response,object){
        updateCrfCallbackBackFunction(response,object);         
    },uploadCRFDocumentCall(response,object){
        if(response['status'] == 'success'){
            $.growl({message:response['msg']},{type:response['status']});
             var baseUrl = $('meta[name="base_url"]').attr('content');
                setTimeout(function(){
                window.location = baseUrl+"/bank/amenddashboard";
                }, 1000);
            return false;
        }else{
            $.growl({message:response['msg']},{type:'warning'});
            return false;
        }
    },amendOtpCallBack:function(response,object)
    {
        // console.log(response);
        if(response['status'] == 'fail'){
            alert(response['msg']);
        }
        if(response['status'] == 'success'){
            alert('CRF VERIFIED SUCCESSFULLY');
            window.open('https://www.dcbbank.com',"_self");
        }    
    },
    checkValidDomainCallBack(response,object){
        checkValidDomainCallBackFunction(response,object);
    },
    PanIsAmendValidCallBack(response,object){
        PanIsAmendValidCallBackFunction(response,object);
    },

    custIdValidCallBack(response,object){
        custIdValidCallBackFunction(response,object);
    },
    countryRestrictedCallBack(response,object){
        countryRestrictedCallBackFunction(response,object);
    },
    crfcustomerdropdownCallBack(response,object){
        crfcustomerdropdownCallBackFunction(response,object);
    },
    amendReportCallBack(response,object){
        amendReportCallBackFunction(response,object);
    },UpdateAmendApiQueueCallBack(response,object){
        UpdateAmendApiQueueCallBackFunction(response,object);
    },
    checkValidAccountNo(response,object){
        checkValidAccountNoBackFunction(response,object);
    },
    koriReportCallBack(response,object){
        koriReportCallBackFunction(response,object);
    },PermEKYCDetailsCallBack(response,object){
        PermEKYCDetailsCallBackFunction(response,object);
    },custAmendRequestPendingPopUpCallback(response,object){
	  custAmendRequestPendingPopUpFunction(response,object);
	},
    identityDetailsCallBack(response,object){
        identityDetailsCallBackFunction(response,object)
    },
    ovdETBNTBDetailsCallBack(response,object){
        ovdETBNTBDetailsCallBackFunction(response,object)
    },
    proofSelectedDropdownCallBack(response,object){
        proofSelectedDropdownCallBackFunction(response,object)
    },
    // usercreatewithroleCallBack(response,object){
    //     usercreatewithroleCallBackFunction(response,object);
    // },
    // checkAadhaarNo(response,object){
    //     checkValidAadhaarNoBackFunction(response,object);
    // },
    // ,selectGenderTitleCallBack(response,object){
    //     if(response['status'] == 'success'){
    //         $.growl({message:response['msg']},{type:response['status']});
           
    //         var findId = $('.input_field').attr('data-func');
    //         $(this).find(findId).remove();
    //         $(this).find(findId).append($('<option>').val('').html('Select title'));
    //         jQuery.each( response.data, function( id, title ) {
    //              $(this).find(findId).append($('<option>').val(id).html(title));
    //         });
       
    //         return false;
    //     }else{
    //         $.growl({message:response['msg']},{type:'warning'});
    //         return false;
    //     }
    // }
}

//below code added to remove extra spaces from input text fields
$('input[type="text"]').change(function(){
 currVal = $(this).val();

$(this).val(currVal.replace(/ +/g, ' '));

});

var enableWorkAreaAfterBranchSubmit = function(){
    $('#Username-blck').css('pointer-events', 'auto');
    $('#Username-blck').unbind("keydown");
    $('.modal-dialog').css('opacity', '1');
    $('.modal-dialog').css('pointer-events', 'auto');
    $('.modal-dialog').unbind("keydown");
    $('.modal-backdrop.show').css('opacity', '0') ;
    $('.close').click();
    global_growl.close();
}

var disableWorkAreaDuringBranchSubmit = function(){
    global_growl = $.growl({message:'Please wait while we process your request...<i class="fa fa-2x fa-spinner fa-spin"></i>'},{type: "warning",delay:180000,allow_dismiss:false});
    $('#Username-blck').css('pointer-events', 'none');
    $('#Username-blck').keydown(function(event) {
        return false;
    });

    $('.modal-dialog').css('opacity', '0.8');
    $('.modal-dialog').css('pointer-events', 'none');
    $('.modal-dialog').keydown(function(event) {
        return false;
    });
    $('.modal-backdrop.show').css('opacity', '0.8');
    setTimeout(function(){
        $('.br_submit_loader').addClass('display-none-br-submit-loader');
        enableWorkAreaAfterBranchSubmit();
        $.growl({message:'Taking longer time than usual, please check with admin team!'},{type: "warning"});
    }, 180000);
}


var registerScreenFlow = function(from,to){


        var screenObject = [];
        screenObject.data = {};
        screenObject.url =  '/bank/registerScreenFlow';
        screenObject.data['from'] = from;
        screenObject.data['to'] = to;
        screenObject.data['functionName'] = 'registerScreenFlowCallBack';

        crudAjaxCall(screenObject);
        return false;

}

 function disabledMenuItems(){

    $('#branch_menu_options').css('pointer-events','none');
    $('#branch_menu_options a').css({ "backgroundColor": "", "color": "lightblue" })

}

function disableRefresh() {
    $(document).on('keydown', function(e) {
            // F5 is pressed
            if(((e.which || e.keyCode) == 116) || (e.ctrlKey && (e.which === 82)) ) {
                e.preventDefault();
                $.growl({message: "Refresh not permitted during account opening. Please save first to exit the flow."},{type: "warning",delay:5000,allow_dismiss:false});
            }

    });
}

function disableSaveAndContinue(saveAndContinueBtn){
    $(saveAndContinueBtn).css('opacity', '0.5');
    $(saveAndContinueBtn).css('pointer-events', 'none');
    $(saveAndContinueBtn).html('Please wait...');
}
 
function enableSaveAndContinue(saveAndContinueBtn){
    $(saveAndContinueBtn).css('opacity', '1');
    $(saveAndContinueBtn).css('pointer-events', 'auto');
    $(saveAndContinueBtn).html('Save and Continue');
}

//PrivilageAccessButton(Waiting State)
function disablePrivilageAccessButton(disablePrivilageAccessBtn){
    $(disablePrivilageAccessBtn).css('opacity', '0.5');
    $(disablePrivilageAccessBtn).css('pointer-events', 'none');
}

function enablePrivilageAccessButton(enablePrivilageAccessBtn){
    $(enablePrivilageAccessBtn).css('opacity', '1');
    $(enablePrivilageAccessBtn).css('pointer-events', 'auto');
}

function imgNotFound(element){
    $.growl({message: element +' image not found. Previous image could not be retrived or saved. Please delete and update image.'},{type: "warning" ,allow_dismiss:true});
    return false;
}

function cgdecode(str) {
    return unescape(str.replace(/\\/g, "%"));
}

function pdfDocumentUpload(pdfdata){

    var url  = '/bank/pdffileupload';
    var functionName = 'pdfAllDocumentSaveCallBack';
    crudDocAjaxCall(pdfdata,url,functionName);
    
    return false;
}

// making and unmasking code

function unmaskingfield(){
    var button = document.getElementById('maskfields');
    clearTimeout(masking_time);
    masking_time = setTimeout(function(){
        button.click();
    },masking_time_count);

    $('.maskingfield').css('display','none');
    $('.unmaskingfield').css('display','');
    $('#unmaskfields').css('display','none');
    $('#maskfields').css('display','');
    blur_img(30,$(".uploaded-img-ovd"),"unblur");
    mask = 'Y';
}

function unmaskingfieldIn(e){
    um = e.closest(".editColumnDiv").next("div").find(".unmaskingfield");
    m = e.closest(".editColumnDiv").next("div").find(".maskingfield");
    m.css('display','none');
    um.css('display','');

    d = e.closest(".form-group").find(".uploaded-img-ovd");
    blur_img(30,d,"unblur");
}

function maskfields(){
    if($("input.unmaskingfield").next(".error:visible").length != 0){
        return;
    }
    clearTimeout(masking_time);
    $('.unmaskingfield').css('display','none');
    $('.maskingfield').css('display','');
    $('#maskfields').css('display','none');
    $('#unmaskfields').css('display','');
    blur_img(30,$(".uploaded-img-ovd"),"blur");
    mask = 'N';
    $("input.maskingfield").prop("disabled","true");
}


$('#unmaskfields').on('click',function(){
    unmaskingfield();
});

$('.editColumn').on('click',function(){
    unmaskingfieldIn($(this));
});

$('#maskfields').on('click',function(){
    maskfields();
});

var mask = 'N';
$(document).on("keydown",function(e){
    if(e.ctrlKey && e.keyCode == "77"){
        e.preventDefault();
        if(mask == 'N'){
            unmaskingfield();
        }else{
            maskfields();
        }
    }
});

function blur_img(val = "3", ele = undefined ,way = "tauggle") {
    if(way=="tauggle"){
        if (ele == undefined) {
            ele = $("img.blur_ing_tug");
        }
        if (ele.css("filter") == `blur(${val}px)`) {
            ele.css("filter", `blur(0px)`);
        } else {
            ele.css("filter", `blur(${val}px)`);
        }
    }else if(way=="blur"){
        ele.css("filter", `blur(${val}px)`);
    }else if(way=="unblur"){
        ele.css("filter", `blur(0px)`);
    }
}

function decrypt(string,key){
    const tests = ["" , undefined , null , NaN];
    if(tests.indexOf(string) != -1 || tests.indexOf(key) != -1){
        return string;
    }
    let encryptMethod = "AES-256-CBC";
    let encryptMethodLength = parseInt(encryptMethod.match(/\d+/)[0]);
    encryptMethodLength = (encryptMethodLength/4);
    try {
        let res = atob(string);
        data = JSON.parse(res);
        ciphertext= data["ciphertext"];
        iv= CryptoJS.enc.Hex.parse(data["iv"]);
        salt= CryptoJS.enc.Hex.parse(data["salt"]);
        let hash = CryptoJS.PBKDF2(key,salt, {'hasher': CryptoJS.algo.SHA512,'keySize':(encryptMethodLength/8),'iterations': 999 });
        let Decrypted = CryptoJS.AES.decrypt({ciphertext : CryptoJS.enc.Base64.parse(ciphertext)},hash,{'mode': CryptoJS.mode.CBC,iv:iv});
        return CryptoJS.enc.Utf8.stringify(Decrypted);
    } catch (error) {
        return string;
    }
    
}

function decrypt_filds(){
    let enc_field = $(".unmaskingfield").find("label");
    let enc_label = $(".enc_label");
    let enc_input = $(".enc_input");
    let cuki= $('meta[name="cookie"]').attr('content');
    let len = cuki.length;
    let key = cuki.substring(len,len-5);
    enc_field.each(function(){
        test_data = $(this).text();
        $(this).text(decrypt(test_data, key));
    });
    enc_label.each(function(){
        test_data = $(this).text();
        $(this).text(decrypt(test_data, key));
    });
    enc_input.each(function(){
        test_data = $(this).val();
        $(this).val(decrypt(test_data, key));
    });
}

$(document).ready(function(){
    decrypt_filds();
    $("input.maskingfield").prop("disabled","true");
});

//// making and unmasking code end

function simulatedatechange(el){
    $(el).datepicker('show'); 
   
}

function custAmendRequestPendingPopUpFunction(response,object){
    if(response["status"]==true){
        $.growl({message: `Amendment request raised for the custormer ID - ${response["data"]["customer_id"]} is yet to processed. CRF - ${response["data"]["crf_number"]}`},{type: "warning",allow_dismiss:true,delay: 0});
        return;
    }
}

function custAmendRequestPendingPopUp(cust_id){
    let cscreenObject = [];
    cscreenObject.data = {};
    cscreenObject.url =  '/bank/custamendrequestispending';
    cscreenObject.data['cust_id'] = cust_id;
    cscreenObject.data['functionName'] = 'custAmendRequestPendingPopUpCallback';
    crudAjaxCall(cscreenObject);
}

$("body").on("change keyup",".dob , .dof",function(){
    let dob_input = $(this);
    var regExp = /[a-zA-Z]/g;
    let value = dob_input.val();
    if(regExp.test(value)){
        console.log("hello");
        dob_input.val(value.replaceAll(regExp,""));
    }
})
