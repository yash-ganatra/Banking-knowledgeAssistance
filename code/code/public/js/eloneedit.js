

function  email_domain(id){
    return true;
    if(/^([a-zA-Z0-9\+_\-]+)(\.[a-zA-Z0-9\+_\-]+)*@([a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$/.test(value)){
    	return true;
    }else{
    	return false;
    }
}

function mobile(id){

    var value = $('#'+id).val();
    if(parseInt(value.substr(0,1)) > 5){
        return true;
    }else{
        return false;
    }
}