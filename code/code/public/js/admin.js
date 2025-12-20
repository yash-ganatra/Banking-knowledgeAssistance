var createtableRegex=/^[a-zA-Z0-9 _-]*$/;
$(document).ready(function(){
    $("body").on("click",".edit_user",function(){
        var url = document.URL;
        var baseUrl = url.substr(0, url.lastIndexOf("/"));
        var encodedParams =  $.base64.encode($(this).attr("id"));
        var key = $('meta[name="cookie"]').attr('content').split('.')[2];
        var encryptedData = encrypt(encodedParams,key);
        var form = $('<form action="' + baseUrl + '/edituser" method="post">' +
                        '<input type="text" name="encodedString" value="' + encryptedData + '" />' +
                    '</form>');
        $('body').append(form);
        form.submit();
        return false;
    });

    $("body").on("click",".toggle_text",function(){
        var fieldName = $(this).attr("id");
        if($("#"+fieldName+"_value").attr('type') == "password"){
            $("#"+fieldName+"_value").attr('type',"text");
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }else{
            $("#"+fieldName+"_value").attr('type',"password");
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        }
        return false;
    });

    // $("body").on("click","#userdetails",function() {
    //     if($("#emp_id").val() == '')
    //     {
    //         $.growl({message: "Please Enter Employee ID/AD ID."},{type: "warning"});
    //         return false;
    //     }

    //     var userObject = [];
    //     userObject.data = {};
    //     userObject['url'] = '/admin/getuserdetailsbyid';
    //     userObject.data['emp_id'] = $("#emp_id").val();
    //     userObject.data['functionName'] = 'userDetailsCallBack';

    //     //getting the data from here
    //     crudAjaxCall(userObject);
    // });



    $("body").on("click",".user_status_active,.user_status_inactive",function(){
        var status = "N";
        var alertTitle = "Deactivate";
        if($(this).attr("class") == "user_status_inactive")
        {
            status = "Y";
            alertTitle = "Activate";
        }
        var applicationId = $(this).attr('id');
        if($("#changeStatus").html() != "Save"){
            $.confirm({
                content: "Do yo want to "+alertTitle+" this user. Please confirm",
                title: alertTitle+' User',
                confirmButton:'Yes',
                cancelButton: 'No',
                confirm: function(button) {
                    var url = document.URL;
                    var baseUrl = url.substr(0, url.lastIndexOf("/"));
                    var userObject = [];
                    userObject.data = {};
                    userObject['url'] = '/admin/updateuserstatus';
                    userObject.data['id'] = applicationId;
                    userObject.data['status'] = status;
                    userObject.data['functionName'] = 'UserStatusCallBack';

                    crudAjaxCall(userObject);
                },
                cancel: function(button) {
                    $(".user_status").css("background-color", "springgreen");
                    return true;
                }
            });
        }
        var url = document.URL;
        var baseUrl = url.substr(0, url.lastIndexOf("/"));
        var userObject = [];
        applicationId.data = {};
        applicationId['url'] = baseUrl+'/getuserslist';
    });

    // $("body").on("click","#saveUser",function() {
    //     if($(".userRole").val() == ''){
    //         $.growl({message: "Please assign Role to User."},{type: "warning"});
    //         return false;
    //     }
    //     var button = $(this).html();
    //     var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/"));
    //     var url = document.URL.split('/');
    //     if(url[url.length-2] == "edituser")
    //     {
    //         var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/edituser"));
    //     }
    //     var userDetailsObject = [];
    //     userDetailsObject.data = {};
    //     userDetailsObject['url'] = '/admin/saveuserdeatils';
    //     $(".userDetailsEditField").each(function() {
    //         if(button != "Save"){
    //             if($(this).html() != ''){
    //                 if($(this).attr('id') == "role"){
    //                     userDetailsObject.data[$(this).attr('name').toUpperCase()] = $(this).val();
    //                 }else{
    //                     userDetailsObject.data[$(this).attr('id').toUpperCase()] = $(this).html();
    //                 }
    //             }
    //         }else{
    //             if($(this).val() != ''){
    //                 userDetailsObject.data[$(this).attr('name').toUpperCase()] = $(this).val();
    //             }
    //         }
    //     });

    //     if(typeof($(this).attr("userid")) != "undefined"){
    //         if($('#filter_type').val() == 1){

    //             $filter_ids = $('.regionals').val();

    //         }else if($('#filter_type').val() == 2){

    //             $filter_ids = $('.zones').val();
    //         }else if($('#filter_type').val() == 3){

    //             $filter_ids = $('.clusters').val();
    //         }else{
    //             $filter_ids = '';
    //         }

    //         userDetailsObject.data['id'] = $(this).attr("userid");
    //         userDetailsObject.data['is_edit'] = true;
    //         userDetailsObject.data['FILTER_TYPE'] = $('#filter_type').val();
    //         userDetailsObject.data['FILTER_IDS'] = $filter_ids;
    //     }
    //     userDetailsObject.data['functionName'] = 'saveUserDetailsCallBack';

    //     //getting the data from here
    //     crudAjaxCall(userDetailsObject);
    // });


    $("body").on("change",".userRole",function(){
        if($("#saveUser").html() != "Save"){
            $.confirm({
                content: "Role changed from "+userRoles[selectedRole]+" to "+userRoles[$(this).val()]+". Please confirm",
                title: 'Update Role',
                confirmButton:'Yes',
                cancelButton: 'No',
                confirm: function(button) {

                },
                cancel: function(button) {
                    $(".userRole").val(selectedRole);
                    return true;
                }
            });
        }
    });

    $("body").on('click',".updateSettings",function(){
        var secure = 0;
        if ($("#"+$(this).attr("id")+"_secure").is(":checked")){
            secure = 1;
        }
        var settingsObject = [];
        settingsObject.data = {};
        settingsObject['url'] = '/admin/updateSettings';
        settingsObject.data['field_name'] = $(this).attr("id");
        settingsObject.data['field_value'] = $("#"+$(this).attr("id")+"_value").val();
        settingsObject.data['comments'] = $("#"+$(this).attr("id")+"_comments").val();
        settingsObject.data['secure'] = secure;

        settingsObject.data['functionName'] = 'SettingsCallBack';

        //getting the data from here
        crudAjaxCall(settingsObject);
    });

    $("body").on("change","#userName,#serviceName",function(){
        getUserApiLogs('/admin/apirequestlogs','useractivitylogs');
    });

    $("body").on("focusout","#aofNumber",function(){
        getUserApiLogs('/admin/apirequestlogs','useractivitylogs');
    });

    $("body").on("focusout","#formId",function(){
        getUserApiLogs('/admin/apirequestlogs','useractivitylogs');
    });

    $("body").on("click",".service_modal",function(){
        $('.modal-title').html($(this).data('type'));
        $('.modal-body').html($(this).data('title'));
    });


    $("body").on("change","#role",function(){
        if($(".userRole").val() == 14){
            $('.role_type_list').removeClass('display-none');
        }else{
            $('.role_type_list').addClass('display-none');
            $('.regional_list').addClass('display-none');
            $('.zone_list').addClass('display-none');
            $('.cluster_list').addClass('display-none');
            $('.clusters').val('').trigger('change');
            $('.zones').val('').trigger('change');
            $('.regionals').val('').trigger('change');

        }
     });


    $("body").on("change","#filter_type",function(){

        $('.regional_list').addClass('display-none');
        $('.zone_list').addClass('display-none');
        $('.cluster_list').addClass('display-none');

        switch($(".filter_type").val()){

            case '1' :
                    $('.regional_list').removeClass('display-none');
                    $('.clusters').val('').trigger('change');
                    $('.zones').val('').trigger('change');
                    break;

            case '2' :
                    $('.zone_list').removeClass('display-none');
                    $('.clusters').val('').trigger('change');
                    $('.regionals').val('').trigger('change');
                    break;

            case '3' :
                    $('.cluster_list').removeClass('display-none');
                    $('.zones').val('').trigger('change');
                    $('.regionals').val('').trigger('change');
                    break;

            default:

                   $('.regional_list').addClass('display-none');
                   $('.zone_list').addClass('display-none');
                   $('.cluster_list').addClass('display-none');


        }
     });

     $("body").on("click",".branch_mapping",function(){
        $("#branch_name").html($(this).data("branch"));
        $("#mapBranch").attr("branchId",$(this).data("id"))
        $("#cluster").select2({placeholder: "Select Cluster",dropdownParent: $('#mapping_modal')});
        $("#regional").select2({placeholder: "Select Regional",dropdownParent: $('#mapping_modal')});
        $("#zone").select2({placeholder: "Select Zone",dropdownParent: $('#mapping_modal')});
        $("#cluster").val($(this).data("cluster")).trigger("change");
        $("#regional").val($(this).data("region")).trigger("change");
        $("#zone").val($(this).data("zone")).trigger("change");
        $('#mapping_modal').modal('show');
        // $("#auditor").val("1");
        return false;
    });


     $("body").on("click","#mapBranch",function(){
         if (($("#cluster").val()  == "") || ($("#manager").val() == "") || ($("#zone").val() == "")) {
                 $.growl({message: 'Please select all Field'},{type:'warning'});
                     setTimeout(function(){
                         $("#auditor").focus();
                    }, 1000)
         }else{
             var mapAuditorObject = [];
             mapAuditorObject.data = {};
             mapAuditorObject['url'] = '/admin/savebranchmapping';
             mapAuditorObject.data['branch_id'] = $(this).attr("branchId");
             mapAuditorObject.data['cluster_id'] = $("#cluster").val();
             mapAuditorObject.data['region_id'] = $("#regional").val();
             mapAuditorObject.data['zone_id'] = $("#zone").val();
             mapAuditorObject.data['functionName'] = 'BranchMappingDetailsCallBack';

             //getting the data from here
             crudAjaxCall(mapAuditorObject);
         }
     });


     $("body").on("change","#state",function(){
        var stateObject = [];
        stateObject.data = {};
        stateObject['url'] = '/admin/getcitiesbystate';
        stateObject.data['stateId'] = $(this).val();
        stateObject.data['functionName'] = 'StatesCallBack';

        //getting the data from here
        crudAjaxCall(stateObject);
    });
});

// function userDetailsCallBackFunction(response,object)
// {
//     if(response['status'] == "success"){
//         $("#emp_name").val(response.employeeDetails.EMP_FIRST_NAME+' '+response.employeeDetails.EMP_MIDDLE_NAME+' '+response.employeeDetails.EMP_LAST_NAME);
//         $("#mobile").val(response.employeeDetails.EMPMOBILENO);
//         $("#email").val(response.employeeDetails.EMPEMAILID);
//         $("#emp_sol").val(response.employeeDetails.EMPSOL);
//         $("#emp_user_id").val(response.employeeDetails.EMPLDAPUSERID);
//         $("#emp_businessunit").val(response.employeeDetails.EMPBUSINESSUNIT);
//         $("#emp_location").val(response.employeeDetails.EMPLOCATION);
//         $("#branch_name").val(response.employeeDetails.EMPBRANCH);
//         $("#emp_id").prop('readonly', true);
//         $("#notEditableFields").removeClass('display-none');
//         $("#userdetails").html('Save');
//         $("#userdetails").attr('id','saveUser');
//         $("#adduser").html('Cancel');
//         $("#adduser").attr("href", "../admin/adduser");
//         addSelect2('userRole','Role',false);
//         addSelect2('filter_type','Role Type',false);
//         addSelect2('regionals','Regionals',false);
//         addSelect2('zones','Zones',false);
//         addSelect2('clusters','Clusters',false);
//     }else if(response['status'] == "error"){
//         $.growl({message: "Please Check Employee ID."},{type: "warning"});
//     }
//     else if(response['status'] == "fail"){
//         $.growl({message: "Please Enter Valid Employee ID."},{type: "warning"});
//     }else{
//         $("#viewuserdetails").html(response);
//         selectedRole = $(".userRole").val();
//         // addSelect2('userRole','Role',false);
//     }
//     return false;
// }


function userDetailsCallBackFunction(response,object)
{
    if(response['status'] == "success"){
        $("#emp_name").val(response.employeeDetails.EMP_FIRST_NAME+' '+response.employeeDetails.EMP_MIDDLE_NAME+' '+response.employeeDetails.EMP_LAST_NAME);
        $("#mobile").val(response.employeeDetails.EMPMOBILENO);
        $("#email").val(response.employeeDetails.EMPEMAILID);
        $("#emp_sol").val(response.employeeDetails.EMPSOL);
        $("#emp_user_id").val(response.employeeDetails.EMPLDAPUSERID);
        $("#emp_businessunit").val(response.employeeDetails.EMPBUSINESSUNIT);
        $("#emp_location").val(response.employeeDetails.EMPLOCATION);
        $("#branch_name").val(response.employeeDetails.EMPBRANCH);
        $("#emp_id").prop('readonly', true);
        $("#notEditableFields").removeClass('display-none');
        $("#userdetails").html('Save');
        $("#userdetails").attr('id','saveUser');
        $("#adduser").html('Cancel');
        $("#adduser").attr("href", "../admin/adduser");
        addSelect2('userRole','Role',false);
        addSelect2('filter_type','Role Type',false);
        addSelect2('regionals','Regionals',false);
        addSelect2('zones','Zones',false);
        addSelect2('clusters','Clusters',false);
    }else if(response['status'] == "error"){
        $.growl({message: "Please Check Employee ID."},{type: "warning"});
    }
    else if(response['status'] == "fail"){
        $.growl({message: "Please Enter Valid Employee ID."},{type: "warning"});
    }else{
        $("#viewuserdetails").html(response);
        selectedRole = $(".userRole").val();
        // addSelect2('userRole','Role',false);
    }
    return false;
}

function getBranches(tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['state'] = $("#state").val();
    tableObject.data['city'] = $("#city").val();
    //tableObject.data['branch'] = $("#branch").val();
    tableObject.data['table'] = "branchTable";
    tableObject.url =  '/admin/getbranch';

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function getUser(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['users'] = $("#users").val();
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}


function getl1ReviewLogs(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['formId'] = $("#formId").val();
    tableObject.data['aofNumber'] = $("#aofNumber").val();
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function getapiQueueLogs(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['formId'] = $("#formId").val();
    tableObject.data['aofNumber'] = $("#apiqueueaofNumber").val();
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function getamendapiQueueLogs(url,table,tableRemainingHeight){
    var tableObject = [];
    tableObject.data = {};
    // tableObject.data['formId'] = $("#formId").val();
    tableObject.data['crfNumber'] = $("#amendapiqueuecrfNumber").val();
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight,4,'asc');
    return false;
}

function getUserActivityLogs(url,table,tableRemainingHeight)
{
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['users'] = $("#users").val();
    tableObject.data['module'] = $("#module").val();


    if(typeof($('#sentDate').val()) != 'undefined'){
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}

function getEmailSmsMessagesLogs(url,table,tableRemainingHeight)
{
    if ($("#activityCodes").val()!= '') {
        $activityCode = $("#activityCodes option:selected" ).text();
    }else{
        $activityCode = '';
    }
    var tableObject = [];
    tableObject.data = {};

    tableObject.data['aofNumber'] = $("#aofNumber").val();
    tableObject.data['activityCode'] = $activityCode;

    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}


// function saveUserDetailsCallBackFunction(response,object)
// {
//     if(response['status'] == "success"){
//         var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/"));
//         var url = document.URL.split('/');
//         if(url[url.length-2] == "edituser")
//         {
//             var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/edituser"));
//         }
//         setTimeout(function(){
//             window.location = baseUrl+"/dashboard";
//         }, 1000);
//     }
//     $.growl({message: response['msg']},{type: response['status']});
//     return false;
// }

/*
function getUserRoles()
{
    var userObject = [];
    userObject.data = {};
    userObject['url'] = '/admin/getuserroles';
    userObject.data['functionName'] = 'userRolesCallBack';

    //getting the data from here
    crudAjaxCall(userObject);
}*/

function userRolesCallBackFunction(response,object)
{
    userRoles = response.userRoles;
    return false;
}

function SettingsCallBackFunction(response,object)
{
    $.growl({message: response.msg},{type: response.status});
    return false;
}

function getUserApiLogs(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['formId'] = $("#formId").val();
    tableObject.data['aofNumber'] = $("#aofNumber").val();
    tableObject.data['userName'] = $("#userName").val();
    if($("#serviceName").val() != "")
    {
        tableObject.data['serviceName'] = $("#serviceName option:selected").text();
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}


$(document).ready(function(){
  addSelect2('userRole','Role',false);
  //getUserRoles();
  selectedRole = $(".userRole").val();
});