var createtableRegex=/^[a-zA-Z0-9 _-]*$/;
$(document).ready(function(){
    $("body").on("click",".edit_user",function(){

        var url = document.URL;
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var csrf_field = '<input type="hidden" name="_token" value="'+csrf+'">';
        var baseUrl = url.substr(0, url.lastIndexOf("/"));
        var encodedParams =  $.base64.encode($(this).attr("id"));
        var key = $('meta[name="cookie"]').attr('content').split('.')[2];
        var encryptedData = encrypt(encodedParams,key);
        var form = $('<form action="' + baseUrl + '/uamedituser" method="post">' +
                        '<input type="text" name="encodedString" value="' + encryptedData + '" />' +csrf_field+
                    '</form>');
        $('body').append(form);
        form.submit();
        
        return false;
       
    });

    getUamUserRoles();
    $('#export-excel').on("click",function(e){
        //$("#export-xls").addClass("display-none");
        e.preventDefault();

        $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});

        $("#usersTable").DataTable().page.len( -1 ).draw();

        if($("#usersTable").DataTable().page.len() == -1){
            setTimeout(function(){
                $("#usersTable").DataTable().button('0').trigger();
                $.growl({message: "Excel file Generated"},{type: "success"});
                //$("#export-xls").removeClass("display-none");
            },8000);
        }
        });
    
    $("body").on("click","#userdetails",function() {
        if($("#emp_id").val() == '')
        {
            $.growl({message: "Please Enter Employee ID/AD ID."},{type: "warning"});
            return false;
        }

        var userObject = [];
        userObject.data = {};
        userObject['url'] = '/uam/getuamuserdetailsbyid';
        userObject.data['emp_id'] = $("#emp_id").val(); 
        userObject.data['functionName'] = 'uamuserDetailsCallBack';

        //getting the data from here
        crudAjaxCall(userObject);
    });
    
    $("body").on("click",".user_status_active,.user_status_inactive",function(){
        var status = "N";
        var alertTitle = "Deactivate";
        $('#uamTitle').remove();
        if($(this).attr("class") == "user_status_inactive")
        {
            status = "Y";
            alertTitle = "Activate";
        }
        var applicationId = $(this).attr('id');     
        $('#uamdashModal').modal({backdrop:'static',keyboard:false});   
        $('#uamModal').append('<span id="uamTitle">'+alertTitle+' User<span>');
        
        if(status === 'Y'){
            $('#permDeactivated').css('display','');
            $('#tempDeactivated').css('display','none');
            $('#activatedUser').css('display','');
        }else{
            $('#activatedUser').css('display','none');
            $('#permDeactivated').css('display','');
            $('#tempDeactivated').css('display','');
        }

        $('#permDeactivated').on('click',function(){
            var permStatus = 'D';
            uamActivatedDeactivatedFunction(permStatus,applicationId);
        });

        $('#tempDeactivated').on('click',function(){
            var tempStatus = 'N';
            uamActivatedDeactivatedFunction(tempStatus,applicationId);
        });
        
        $('#activatedUser').on('click',function(){
            var activStatus = 'Y';
            uamActivatedDeactivatedFunction(activStatus,applicationId);
        });

        // if($("#changeStatus").html() != "Save"){
            // $.confirm({
            //     content: "Do yo want to "+alertTitle+" this user. Please confirm",
            //     title: alertTitle+' User',
            //     confirmButton:'Yes',
            //     cancelButton: 'No',
            //     confirm: function(button) {                
            //         var url = document.URL;
            //         var baseUrl = url.substr(0, url.lastIndexOf("/"));
            //         var userObject = [];
            //         userObject.data = {};
            //         userObject['url'] = '/uam/uamupdateuserstatus';
            //         userObject.data['id'] = applicationId; 
            //         userObject.data['status'] = status; 
            //         userObject.data['functionName'] = 'UamUserStatusCallBack';

            //         crudAjaxCall(userObject);
            //     },
            //     cancel: function(button) {
            //         $(".user_status").css("background-color", "springgreen");
            //         return true;
            //     }
            // });
        // }
        var url = document.URL;
        var baseUrl = url.substr(0, url.lastIndexOf("/"));
        var userObject = [];
        applicationId.data = {};
        applicationId['url'] = baseUrl+'/getuamuserslist';

        
    });

    $("#undelteUser").on("click", function() {
        var applicationId = $(this).attr('data-id'); 
        var activStatus = 'Y';
        var undeleteuser = 'Y';
        uamActivatedDeactivatedFunction(activStatus, applicationId,undeleteuser);
    });
    

    $("body").on("click","#saveUser",function() {

        if($(".userRole").val() == ''){
            $.growl({message: "Please assign Role to User."},{type: "warning"});
            return false;
        }
        if(($("#rm_code").val() == '') && ($(".userRole").val() == 2)){
            $.growl({message: "Please Enter mandatory RM Code."},{type: "warning"});
            return false;
        }
        var button = $(this).html();
        var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/"));
        var url = document.URL.split('/');
        if(url[url.length-2] == "edituser")
        {
            var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/uamedituser"));
        }
        var userDetailsObject = [];
        userDetailsObject.data = {};
        userDetailsObject['url'] = '/uam/uamsaveuserdeatils';
        $(".userDetailsEditField").each(function() {
            if(button != "Save"){
                if($(this).html() != ''){
                    if($(this).attr('id') == "role"){
                        userDetailsObject.data[$(this).attr('name').toUpperCase()] = $(this).val();    
                    }else{
                        userDetailsObject.data[$(this).attr('id').toUpperCase()] = $(this).html();
                    }                    
                }
            }else{
                if($(this).val() != ''){
                    userDetailsObject.data[$(this).attr('name').toUpperCase()] = $(this).val();    
                }    
            }
        });
        userDetailsObject.data['RM_CODE'] = $('#rm_code').val();    

        if(typeof($(this).attr("userid")) != "undefined"){
            if($('#filter_type').val() == 1){

                $filter_ids = $('.regionals').val();

            }else if($('#filter_type').val() == 2){

                $filter_ids = $('.zones').val();
            }else if($('#filter_type').val() == 3){

                $filter_ids = $('.clusters').val();
            }else{
                $filter_ids = '';
            }

            userDetailsObject.data['id'] = $(this).attr("userid");
            userDetailsObject.data['is_edit'] = true;
            userDetailsObject.data['FILTER_TYPE'] = $('#filter_type').val();
            userDetailsObject.data['FILTER_IDS'] = $filter_ids;
        }
        var nor = 'N';
        if($('#normal_flag').prop('checked')){
            nor = 'Y';
        }
        var priority = 'N';
        if($('#priority_flag').prop('checked')){
            priority = 'Y';
        }
        var nr = 'N';
        if($('#nr_flag').prop('checked')){
            nr = 'Y';
        }
        userDetailsObject.data['NORMAL_FLAG'] = nor;
        userDetailsObject.data['PRIORITY_FLAG'] = priority;
        // userDetailsObject.data['EMPLDAPUSERID'] = $('#emp_user_id').val();
        userDetailsObject.data['NR_FLAG'] = nr;
        userDetailsObject.data['functionName'] = 'saveUamUserDetailsCallBack';

        //getting the data from here
        crudAjaxCall(userDetailsObject);
    });
    
    
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

    $("body").on("click",".saveColumnData",function(){
        var table = $("#table_name").text();

        var columnDataObject = [];
        columnDataObject.data = {};

        columnDataObject['url'] = '/uam/savecolumndata';

        $(".ColumnEditField ").each(function() {
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
        var nor = 'N';
        if($('#normal_flag').prop('checked')){
            nor = 'Y';
        }
        var priority = 'N';
        if($('#priority_flag').prop('checked')){
            priority = 'Y';
        }
        var nr = 'N';
        if($('#nr_flag').prop('checked')){
            nr = 'Y';
        }

        if((nor=='N'&& priority=='N' && nr=='N') && ($('#rolelists option:selected').text() == 'NPC Reviewer1' || $('#rolelists option:selected').text() == 'NPC Reviewer2')){
          $.growl({message: "Please check any priority Flag."},{type: "warning"});
          return false;
        }
        columnDataObject.data['NORMAL_FLAG'] = nor;
        columnDataObject.data['PRIORITY_FLAG'] = priority;
        columnDataObject.data['NR_FLAG'] = nr;
        columnDataObject.data['functionName'] = 'SaveUserColumnDataCallBack';

        //getting the data from here
        crudAjaxCall(columnDataObject);
    });
});

function SaveUserColumnDataCallBackFunction(response,object)
{
    var baseUrl = $('meta[name="base_url"]').attr('content');
    setTimeout(function(){
        window.location = baseUrl+'/uam/dashboard';
    },2000);
    return false;
}

function uamuserDetailsCallBackFunction(response,object)
{
    if(response['status'] == "success"){
        $("#emp_name").val(response.employeeDetails.EMP_FIRST_NAME+' '+response.employeeDetails.EMP_MIDDLE_NAME+' '+response.employeeDetails.EMP_LAST_NAME);
        $("#mobile").val(response.employeeDetails.EMPMOBILENO);
        $("#email").val(response.employeeDetails.EMPEMAILID);
        $("#emp_sol").val(response.employeeDetails.EMPSOL);
        $("#emp_user_id").val(response.employeeDetails.EMPLDAPUSERID);
        $("#emp_businessunit").val(response.employeeDetails.EMPBUSINESSUNIT);
        $("#emp_location").val(response.employeeDetails.EMPLOCATION);
        $("#normal_flag").val(response.employeeDetails.NORMAL_FLAG);
        $("#priority_flag").val(response.employeeDetails.PRIORITY_FLAG);
        $("#nr_flag").val(response.employeeDetails.NR_FLAG);
        $("#branch_name").val(response.employeeDetails.EMPBRANCH);
        $("#emp_id").prop('readonly', true);
        $("#notEditableFields").removeClass('display-none');            
        $("#userdetails").html('Save');
        $("#userdetails").attr('id','saveUser');
        $("#adduser").html('Cancel');
        $("#adduser").attr("href", "../uam/uamadduser");
        addSelect2('userRole','Role',false);
        addSelect2('filter_type','Role Type',false);
        addSelect2('regionals','Regionals',false);
        addSelect2('zones','Zones',false);
        addSelect2('clusters','Clusters',false);
    }else if(response['status'] == "error"){
        $.growl({message: "Please Check Employee ID."},{type: "warning"});
    }
    else if(response['status'] == "fail"){
        $.growl({message: response['msg']},{type: "warning"});
    }else{
        $("#viewuserdetails").html(response);
        selectedRole = $(".userRole").val();
        // addSelect2('userRole','Role',false);
    }
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


function getUserActivityLogs(url,table,tableRemainingHeight)
{
    if(typeof($('#sentDate').val()) != 'undefined'){
        var sentDateRange = $('#sentDate').val();
        var sentDates = sentDateRange.split(" to ");
    }

    var tableObject = [];
    tableObject.data = {};
    tableObject.data['users'] = $("#users").val();
    
    
    if(typeof($('#sentDate').val()) != 'undefined'){
        tableObject.data['startDate'] = sentDates[0];
        tableObject.data['endDate'] = sentDates[1];
    }
    tableObject.data['table'] = table;
    tableObject.url =  url;

    datatableAjaxCall(tableObject,tableRemainingHeight);
    return false;
}


function saveUamUserDetailsCallBackFunction(response,object)
{
    if(response['status'] == "success"){
        var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/"));
        var url = document.URL.split('/');
        if(url[url.length-2] == "edituser")
        {
            var baseUrl = document.URL.substr(0, document.URL.lastIndexOf("/uamedituser"));
        }
        setTimeout(function(){
            window.location = baseUrl+"/dashboard";
        }, 1000);
    }else{
        $.growl({message: response['msg']},{type: 'warning'});
        return false;
    }
    $.growl({message: response['msg']},{type: response['status']});
    return false;
}

function getUamUserRoles()
{
    var userObject = [];
    userObject.data = {};
    userObject['url'] = '/uam/getuamuserroles';
    userObject.data['functionName'] = 'userRolesCallBack';

    //getting the data from here
    crudAjaxCall(userObject);
}

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

// function undeleteUsers(url,table,tableRemainingHeight){
//     // alert(url);
//     var dataId=$('#users').val();
//     // alert(dataId);
//     var userObject = [];
//     userObject.data = {};    
//     userObject['url'] = url;
//     userObject.data['table'] = table;
//     userObject.data['ID'] = dataId; 
//     userObject.data['functionName'] = 'undeleteUsersCallBack';
//     datatableAjaxCall(userObject,tableRemainingHeight);
// }
function getUserApiLogs(url,table,tableRemainingHeight)
{
    var tableObject = [];
    tableObject.data = {};
    tableObject.data['formId'] = $("#formId").val();
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

/*function getUserRoles()
{
    var userObject = [];
    userObject.data = {};
    userObject['url'] = '/admin/getuamuserroles';
    userObject.data['functionName'] = 'userUamRolesCallBack';

    //getting the data from here
    crudAjaxCall(userObject);
}*/


function userUamRolesCallBackFunction(response,object)
{
    userRoles = response.userRoles;
    return false;
}



function uamActivatedDeactivatedFunction(status,applicationId,undeleteuser=''){
    var url = document.URL;
    var baseUrl = url.substr(0, url.lastIndexOf("/"));
    var userObject = [];
    userObject.data = {}; 
    userObject['url'] = '/uam/uamupdateuserstatus';
    userObject.data['id'] = applicationId; 
    userObject.data['status'] = status; 
    userObject.data['type'] = undeleteuser;
    userObject.data['functionName'] = 'UamUserStatusCallBack';
    crudAjaxCall(userObject);
    // return false;
}

$(document).ready(function(){
  addSelect2('userRole','Role',false);
  //getUserRoles();
  selectedRole = $(".userRole").val();

});   

/*checkbox enable flag*/
$('body').on('change','#role',function(){
  var data=$('#role').val();
if(data==3 ||data==4){
    $('#uam-flag').css('display', 'block')
}
else{
    $('#uam-flag').css('display', 'none')
}
});

$('body').on('change','#rolelists',function(){
    var data=$('#rolelists').val();
  if(data==3 ||data==4){
      $('#uam-flag').css('display', 'block')
  }
  else{
      $('#uam-flag').css('display', 'none')
  }

  });


//   undeleteUsers();
    $('#export-excel').on("click",function(e){
        //$("#export-xls").addClass("display-none");
        e.preventDefault();

        $.growl({message: "Generating Excel file..."},{type: "success",delay:7800});

        $("#usersTable").DataTable().page.len( -1 ).draw();

        if($("#usersTable").DataTable().page.len() == -1){
            setTimeout(function(){
                $("#usersTable").DataTable().button('0').trigger();
                $.growl({message: "Excel file Generated"},{type: "success"});
                //$("#export-xls").removeClass("display-none");
            },8000);
        }
        });
  
$('.empstatus').on('click',function(){
    var dataid = $(this).attr('data-id');
    $('#undelteUser').attr('data-id',dataid);
});

$(".deleteusers").on("change",function(){
        var dataId = $('#users').val();
        var url = '/uam/UserUnDelete';
        redirectUrl(dataId,url);
});

    