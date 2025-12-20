function rulesSelectedField(){
	var nomineeDel = $('#customCheck-29:checked').val();
	var nomineeMod = $('#customCheck-30:checked').val();
	var status = true;
	var message = '';
	if(nomineeDel == 'on' && nomineeMod == 'on'){
		status = false;
		message = 'Please either select nominee deletion or nominee modification.'
	}

	return {'status':status,'message':message};
}	