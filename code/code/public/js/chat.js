var inputRegex=/^[a-zA-Z0-9 .,\/\\()_{}/\[\].\n\r]*$/;
var _currentID = "";
$(document).ready(function(){
    // open chat box with userslist
    $('body').on('click','#userslist',function() {
        if($(".showChat").is(":visible"))
        {
            return false;
        }
        getchatusers();        
    });

    $('body').on('click','.searchUserIcon',function() {
        getchatusers();
        if (!$("#searchuser").val().match(inputRegex)) {
            $.growl({message: "Special characters are not allowed."},{type: "warning"});
            return false;
        }        
    });
    
     $("body").on("keypress","#searchuser",function(e){
        if(e.which == 13) {
            getchatusers();
        }
        if (!$("#searchuser").val().match(inputRegex)) {
            $.growl({message: "Special characters are not allowed."},{type: "warning"});
            return false;
        }
    });

    $('body').on('click','#closeSearchUserIcon',function() {
        $("#searchuser").val('');
        getchatusers();        
    });


    
    $('body').on('click','.back_chatBox',function() {
        $('.showChat_inner').toggle();
        $("#searchuser").val('');
    });	
    // open user chat box with messages and start Timer!
    $('body').on('click','.userchat',function() {
		
       _currentID =  $(this).attr("id");
       console.log('Fetching chats for Sender: '+ _currentID);
       getuserchatbyid(_currentID);

       var _globalTimer = setInterval(function(){ 
				//console.log('Polling every 5sec for Sender: '+ _currentID+" with Timer: "+_globalTimer);
				if(isChatVisible()){
					getuserlastchatid(_currentID);					
					var lastID = $('#lastchat_id').val();
					var lastID_new = $('#lastchat_id_new').val();
					if(lastID_new != "" && lastID !== lastID_new){ 
						 console.log('LastID: '+lastID+" NewID: "+lastID_new);
						 $('#toggleNewMessage').fadeIn(1500);    
					}
					//console.log("Found last chat ID: "+$('#lastchat_id_new').val()); 
				}else{
					clearInterval(_globalTimer); 
				}
			},10000);    
    });
	
	
	$('body').on('click','.new-message',function() { 
			 $('#toggleNewMessage').fadeOut(1500);
			 setTimeout(function(){
				getuserchatbyid(_currentID);
			 },1500);
	}); 

    $('body').on('click','.saveMessage',function() {
        saveMessage();
        if (!$("#message").val().match(inputRegex)) {
            $.growl({message: "Comments with following [ .,%?/\()_ ] characters are allowed. <br>Other special characters are not permitted due to Security reasons."},{type: "warning"});
            return false;
        }

    });

    $("body").on("keypress","#message",function(e){
        if(e.which == 13) {
           saveMessage();
        }
        if (!$("#message").val().match(inputRegex)) {
            $.growl({message: "Comments with following [ .,(){}[]_ ] characters are allowed. <br>Other special characters are not permitted due to Security reasons."},{type: "warning"});
            return false;
        }
    });

    $('body').on('click','.saveMessageicon',function() {
        
            getchatusers();
    
        if (!$("#message").val().match(inputRegex)) {
            $.growl({message: "Comments with following [ .,%?/\()_ ] characters are allowed. <br>Other special characters are not permitted due to Security reasons."},{type: "warning"});
            return false;
        }        
    });


  
    
});

function isChatVisible(){
	var element = document.querySelector('.chat-reply-box');
	if(typeof(element) !== "undefined" && element !== null){
		const rect = element.getBoundingClientRect();
		if(rect.top > 0 || rect.left > 0 || rect.bottom > 0 || rect.right > 0) return true;
		else return false;     
	}else return false;
}



function getchatusers()
{
    var chatObject = [];
    chatObject.data = {};
    chatObject.url =  '/getchatusers';
    chatObject.data['searchParam'] = $("#searchuser").val();
    chatObject.data['functionName'] = 'chatUsersCallBack';

    crudAjaxCall(chatObject,true);
    return false;
}


function getuserchatbyid(userId)
{
    var userObject = [];
    userObject.data = {};
    userObject.url =  '/getuserchatbyid';
    userObject.data['userId'] = userId;
    userObject.data['functionName'] = 'usersChatDetailsCallBack';

    crudAjaxCall(userObject,true);
    return false;
}

function getuserlastchatid(userId)
{
    var userObject = [];
    userObject.data = {};
    userObject.url =  '/getuserlastchatid';
    userObject.data['userId'] = userId;
    userObject.data['functionName'] = 'usersLastChatIdCallBack';

    crudAjaxCall(userObject,true);
    return false;
}


function updateIsRead(object)
{    
	object.url = '/updateisread';
    object.data['functionName'] =  'updateIsReadCallBack';

    crudAjaxCall(object);
    return false;
}

function saveMessage()
{   
    var messageObject = [];
    messageObject.data = {};
    messageObject.url =  '/savemessage';
    messageObject.data['receipent_id'] = $(".saveMessage").attr("id");
    messageObject.data['chat_text'] = $("#message").val();
    messageObject.data['functionName'] = 'saveMessageCallBack';

    crudAjaxCall(messageObject);
    return false;
}




