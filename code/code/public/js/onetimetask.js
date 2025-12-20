$(document).ready(function(){
  
    $("body").on("click","#resetAofCounter",function(){
       var resetAofComment = $('#resetAofComment').val();
       if(resetAofComment == ''){
        $.growl({message: "Please enter Mandatory comment"},{type: "warning"});
        return false;
       }

       	var resetAofObject = [];
        resetAofObject.data = {};
        resetAofObject.url =  '/admin/resetaofcounter';
        resetAofObject.data['activity'] = 'resetAofComment';
        resetAofObject.data['resetAofComment'] = resetAofComment;
        resetAofObject.data['functionName'] = 'resetAofCommentCallBack';

        crudAjaxCall(resetAofObject);
        // return false;
	});

});

function resetAofCommentCallBackFunction(response, object) {
    if(response['status'] == "success"){
        $.growl({message: response['msg']},{type: response['status']});
    }else{
        $.growl({message: response['msg']},{type: "warning"});
    }
    return false;
}

