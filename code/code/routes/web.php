<?php

/*
|--------------------------------------------------------------------------
| AppInSource Technologies
|--------------------------------------------------------------------------
|
| Routes for DCB CUBE. Feb. 2019 AIS-CG
|
|
*/
#Route::get('/initialization', function () {
#    return view('auth.login')->with('is_init',true);
#});
Route::post('init', [App\Http\Controllers\Auth\LoginController::class,'init'])->name('init');
Route::any('/', [App\Http\Controllers\Auth\LoginController::class,'showLoginForm'])->name('rootloginform');
Route::get('login', [App\Http\Controllers\Auth\LoginController::class,'showLoginForm'])->name('loginform');
Route::post('login', [App\Http\Controllers\Auth\LoginController::class,'login'])->name('login');
Route::any('/logout', [App\Http\Controllers\Auth\LoginController::class,'logout'])->name('logout');

#Route::any('/debugInfo/{auth}/{aof}', 'Bank\ReviewController@debugInfo')->name('debugInfo');

Route::post('/extapi/usercreatewithrole', [App\Http\Controllers\ExtApi\UAMApiController::class,'usercreatewithrole'])->name('usercreatewithrole');
Route::post('/extapi/updateuser', [App\Http\Controllers\ExtApi\UAMApiController::class,'updateuser'])->name('updateuser');
Route::post('/extapi/userdeactivate', [App\Http\Controllers\ExtApi\UAMApiController::class,'userdeactivate'])->name('userdeactivate');
Route::post('/extapi/useractivate', [App\Http\Controllers\ExtApi\UAMApiController::class,'useractivate'])->name('useractivate');

// Auth::routes();
Route::group(['middleware' => ['jwt.verify']], function() {

	Route::any('/imagestemp/{filename}', [App\Http\Controllers\ImagesController::class,'showimagestemp'])->name('imagestemp');
	Route::any('/imagesattachments/{foldername}/{filename}', [App\Http\Controllers\ImagesController::class,'showimagesattach'])->name('imagesattachments');
	Route::any('/imagesmarkedattachments/{foldername}/{filename}', [App\Http\Controllers\ImagesController::class,'showimagesmarkattach'])->name('imagesmarkedattachments');
	Route::any('/imageslevelthree/{foldername}/{filename}', [App\Http\Controllers\ImagesController::class,'imageslevelthree'])->name('imageslevelthree');
	Route::any('/showamendimage/{foldername}', [App\Http\Controllers\ImagesController::class,'showamendImage'])->name('showamendimage');
	Route::any('/versions', [App\Http\Controllers\VersionController::class,'versions'])->name('versions');

	Route::any('/index', 'IndexController@index')->name('index');
	Route::any('/dashboard', [App\Http\Controllers\HomeController::class,'dashboard'])->name('dashboard');
	Route::any('/getchatusers', [App\Http\Controllers\HomeController::class,'getchatusers'])->name('userchat');
	Route::any('/getuserchatbyid', [App\Http\Controllers\HomeController::class,'getuserchatbyid'])->name('userchatid');
	Route::any('/getuserlastchatid', [App\Http\Controllers\HomeController::class,'getuserlastchatid'])->name('lastchatid');
	Route::any('/updateisread', [App\Http\Controllers\HomeController::class,'updateisread'])->name('updateisread');
	Route::any('/savemessage', [App\Http\Controllers\HomeController::class,'savemessage'])->name('savemessage');
	Route::any('/userhelp', [App\Http\Controllers\HomeController::class,'userhelp'])->name('userhelp');
	Route::any('/showlastloggin', [App\Http\Controllers\Auth\LoginController::class,'showlastloggin'])->name('showlastloggin');
	//Routes for send email

	//Route::any('/email', 'NotificationController@email')->name('email');
	Route::any('/scheduledata', [App\Http\Controllers\ScheduleDataController::class,'scheduledata'])->name('scheduledata');
	//Route::any('/checkSmsToBeSend', 'SmsController@checkSmsToBeSend')->name('checkSmsToBeSend');
	//Route::any('/sendSMS', 'SmsController@sendSMS')->name('sendSMS');

	Route::any('/insertemailcontent', [App\Http\Controllers\NotificationController::class,'insertemailcontent'])->name('insertemailcontent');
	Route::any('/insertschedulecontent', [App\Http\Controllers\ScheduleDataController::class,'insertschedulecontent'])->name('insertschedulecontent');
	Route::any('/sendemail', [App\Http\Controllers\NotificationController::class,'sendemail'])->name('email');
	Route::any('/checkEmailToBeSend', [App\Http\Controllers\NotificationController::class,'checkEmailToBeSend'])->name('checkEmailToBeSend');

	  /*==============Admin ROLE =======================*/
	Route::any('/admin/dashboard', [App\Http\Controllers\Reports\CurrentDayReportController::class,'currentDayReport'])->name('admindashboard');
	
	Route::any('/admin/allusers', [App\Http\Controllers\Uam\UamDashboardController::class,'dashboard'])->name('allusers');
	Route::any('/admin/uamedituser', [App\Http\Controllers\Uam\UamDashboardController::class,'uamedituser'])->name('edituser');


	// Route::any('/admin/getuserslist', 'Admin\DashboardController@getuserslist')->name('getuserslist');
	//Route::any('/admin/getuserdetailsbyid', 'Admin\DashboardController@getuserdetailsbyid')->name('getuserdetailsbyid');
	//Route::any('/admin/getuserroles', 'Admin\DashboardController@getuserroles')->name('getuserroles');
	//Route::any('/admin/adduser', 'Admin\DashboardController@adduser')->name('adduser');

	//Route::any('/admin/saveuserdeatils', 'Admin\DashboardController@saveuserdeatils')->name('saveuserdeatils');
	//Route::any('/admin/updateuserstatus', 'Admin\DashboardController@updateuserstatus')->name('updateuserstatus');

    /*==============UAM ROLE =======================*/
    Route::any('/uam/dashboard', [App\Http\Controllers\Uam\UamDashboardController::class,'dashboard'])->name('uamdashboard');
    Route::any('/uam/getuamuserslist', [App\Http\Controllers\Uam\UamDashboardController::class,'getuamuserslist'])->name('getuamuserslist');
    Route::any('/uam/getuamuserdetailsbyid', [App\Http\Controllers\Uam\UamDashboardController::class,'getuamuserdetailsbyid'])->name('getuamuserdetailsbyid');
	Route::any('/uam/getuamuserroles', [App\Http\Controllers\Uam\UamDashboardController::class,'getuamuserroles'])->name('getuamuserroles');
	Route::any('/uam/uamadduser', [App\Http\Controllers\Uam\UamDashboardController::class,'uamadduser'])->name('uamadduser');
	Route::any('/uam/uamedituser', [App\Http\Controllers\Uam\UamDashboardController::class,'uamedituser'])->name('uamedituser');
	Route::any('/uam/uamsaveuserdeatils', [App\Http\Controllers\Uam\UamDashboardController::class,'uamsaveuserdeatils'])->name('uamsaveuserdeatils');
	Route::any('/uam/uamupdateuserstatus', [App\Http\Controllers\Uam\UamDashboardController::class,'uamupdateuserstatus'])->name('uamupdateuserstatus');
    Route::any('/admin/getuamuserroles', [App\Http\Controllers\Uam\UamDashboardController::class,'getuamuserroles'])->name('admingetuamuserroles');

    /*==============BRANCH REVIEWER ROLE =======================*/
	Route::any('monitoring/dashboard', [App\Http\Controllers\Bank\DashboardController::class,'dashboard'])->name('monitoring');


    /*==============MANCO ROLE =======================*/
    //Route::any('manco/dashboard', 'Bank\DashboardController@dashboard')->name('mancodashboard');

	Route::any('/admin/useractivitylog', [App\Http\Controllers\Admin\UserActivityLogController::class,'useractivitylog'])->name('useractivitylog');
	Route::any('/admin/activitylogs', [App\Http\Controllers\Admin\UserActivityLogController::class,'activitylogs'])->name('activitylogs');

	Route::any('/admin/apiservicelog', [App\Http\Controllers\Admin\ApiServiceLogController::class,'apiservicelog'])->name('apiservicelog');
	Route::any('/admin/apirequestlogs', [App\Http\Controllers\Admin\ApiServiceLogController::class,'apirequestlogs'])->name('apirequestlogs');

	Route::any('/admin/l1editlogs', [App\Http\Controllers\Admin\L1EditLogController::class,'l1editlogs'])->name('l1editlogs');
	Route::any('/admin/l1editlogstable', [App\Http\Controllers\Admin\L1EditLogController::class,'l1editlogstable'])->name('l1editlogstable');

	
	Route::any('/admin/apiqueuelog', [App\Http\Controllers\Admin\ApiQueueLogController::class,'apiqueuelog'])->name('apiqueuelog');
	Route::any('/admin/apiqueuelogtable', [App\Http\Controllers\Admin\ApiQueueLogController::class,'apiqueuelogtable'])->name('apiqueuelogtable');
	Route::any('/admin/updateapiqueue', [App\Http\Controllers\Admin\ApiQueueLogController::class,'updateapiqueue'])->name('updateapiqueue');



	Route::any('/admin/applicationsettings', [App\Http\Controllers\Admin\ApplicationSettingsController::class,'applicationsettings'])->name('applicationsettings');
	Route::any('/admin/updateSettings', [App\Http\Controllers\Admin\ApplicationSettingsController::class,'updateSettings'])->name('updateSettings');
	 // OneTimeTaskController From Admin
	Route::any('/admin/onetimetask', [App\Http\Controllers\Admin\OneTimeTaskController::class,'onetimetask'])->name('onetimetask');
	Route::any('/admin/resetaofcounter', [App\Http\Controllers\Admin\OneTimeTaskController::class,'resetaofcounter'])->name('resetaofcounter');

	Route::any('/admin/templates', [App\Http\Controllers\Admin\EmailSmsController::class,'templates'])->name('templates');
	Route::any('/admin/gettemplates', [App\Http\Controllers\Admin\EmailSmsController::class,'gettemplates'])->name('gettemplates');
	Route::any('/email/emailtemplate', [App\Http\Controllers\Admin\EmailSmsController::class,'emailtemplate'])->name('emailtemplate');
	Route::any('/admin/addtemplate', [App\Http\Controllers\Admin\EmailSmsController::class,'addtemplate'])->name('addtemplate');
	Route::any('/admin/savetemplate', [App\Http\Controllers\Admin\EmailSmsController::class,'savetemplate'])->name('savetemplate');
	Route::any('/admin/edittemplate', [App\Http\Controllers\Admin\EmailSmsController::class,'edittemplate'])->name('edittemplate');

	Route::any('channelid/getoaocolumnsdata', [App\Http\Controllers\ChannelId\DsaMasterController::class,'getoaocolumnsdata'])->name('getoaocolumnsdata');
	Route::any('channelid/addoaocolumndata', [App\Http\Controllers\ChannelId\DsaMasterController::class,'addoaocolumndata'])->name('addoaocolumndata');
	Route::any('channelid/saveoaocolumndata', [App\Http\Controllers\ChannelId\DsaMasterController::class,'saveoaocolumndata'])->name('saveoaocolumndata');

	
	 Route::any('channelid/mastertable',[App\Http\Controllers\ChannelId\DsaMasterController::class,'dsamastertable'])->name('dsamastertable');


	Route::any('channelid/apilogs',[App\Http\Controllers\ChannelId\DsaLogsController::class,'apilog'])->name('apilogs');
	Route::any('channelid/getapilogdata',[App\Http\Controllers\ChannelId\DsaLogsController::class,'getapilogdata'])->name('getapilogdata');

	Route::any('/admin/mastertables', [App\Http\Controllers\Admin\MasterTableController::class,'mastertables'])->name('mastertables');
	Route::any('/admin/getcolumnsbytable', [App\Http\Controllers\Admin\MasterTableController::class,'getcolumnsbytable'])->name('getcolumnsbytable');
	Route::any('/admin/mastertabledata', [App\Http\Controllers\Admin\MasterTableController::class,'mastertabledata'])->name('mastertabledata');
	Route::any('/admin/branchtabledata', [App\Http\Controllers\Admin\MasterTableController::class,'branchtabledata'])->name('branchtabledata');
	Route::any('/admin/addtabledata', [App\Http\Controllers\Admin\MasterTableController::class,'addtabledata'])->name('addtabledata');
	Route::any('/admin/addbranchtabledata', [App\Http\Controllers\Admin\MasterTableController::class,'addbranchtabledata'])->name('addbranchtabledata');
	Route::any('/admin/savecolumndata', [App\Http\Controllers\Admin\MasterTableController::class,'savecolumndata'])->name('adminsavecolumndata');
	Route::any('/admin/savebranchcolumndata', [App\Http\Controllers\Admin\MasterTableController::class,'savebranchcolumndata'])->name('savebranchcolumndata');

	Route::any('/admin/tdschemecode', [App\Http\Controllers\Admin\TdSchemeCodeController::class,'tdschemecode'])->name('tdschemecode');
	Route::any('/admin/getdschemecodelist', [App\Http\Controllers\Admin\TdSchemeCodeController::class,'getdschemecodelist'])->name('getdschemecodelist');
    // not use specific route below
	// Route::any('/admin/savingsschemecode', [App\Http\Controllers\Admin\SavingsschemecodeController::class,'savingsschemecode'])->name('savingsschemecode');
	// Route::any('/admin/getsavingsschemecode', [App\Http\Controllers\Admin\SavingsschemecodeController::class,'getsavingsschemecodelist'])->name('getsavingsschemecodelist');
	//not use specific route below
	// Route::any('/admin/country', [App\Http\Controllers\Admin\CountryController::class,'country'])->name('country');
	// Route::any('/admin/getcountry', [App\Http\Controllers\Admin\CountryController::class,'getcountrylist'])->name('getcountrylist');

	Route::any('/admin/branch', [App\Http\Controllers\Admin\BranchController::class,'branch'])->name('branch'); 
	// Not use in functionality
	// Route::any('/admin/getbranch', [App\Http\Controllers\Admin\BranchController::class,'getbranchlist'])->name('getbranchlist');
	Route::any('/admin/savebranchmapping', [App\Http\Controllers\Admin\BranchController::class,'savebranchmapping'])->name('savebranchmapping');
	Route::any('/admin/getcitiesbystate', [App\Http\Controllers\Admin\BranchController::class,'getcitiesbystate'])->name('getcitiesbystate');

	Route::any('bank/registerScreenFlow', [App\Http\Controllers\Bank\AddAccountController::class,'registerScreenFlow'])->name('registerScreenFlow');


	Route::any('bank/addaccount', [App\Http\Controllers\Bank\AddAccountController::class,'addaccount'])->name('addaccount');
	Route::any('bank/getschemecodebyaccounttype', [App\Http\Controllers\Bank\AddAccountController::class,'getschemecodebyaccounttype'])->name('getschemecodebyaccounttype');
	Route::any('bank/addapplicant', [App\Http\Controllers\Bank\AddAccountController::class,'addapplicant'])->name('addapplicant');
	Route::any('bank/panisvalid', [App\Http\Controllers\Bank\AddAccountController::class,'panisvalid'])->name('panisvalid');
	Route::any('bank/savepandetails', [App\Http\Controllers\Bank\AddAccountController::class,'savepandetails'])->name('savepandetails');
	Route::any('bank/panexists', [App\Http\Controllers\Bank\AddAccountController::class,'panexists'])->name('panexists');
	Route::any('bank/saveetbaccount', [App\Http\Controllers\Bank\AddAccountController::class,'saveetbaccount'])->name('saveetbaccount');
	Route::any('/bank/checkcurrentapi',[App\Http\Controllers\Bank\AddAccountController::class,'currentAccountApi'])->name('checkcurrentapi');

	Route::any('bank/saveetbcc', [App\Http\Controllers\Bank\AddAccountController::class,'saveetbcc'])->name('saveetbcc');
	Route::any('bank/getschemedata', [App\Http\Controllers\Bank\AddAccountController::class,'getschemedata'])->name('getschemedata');

	Route::any('bank/getgpadata', [App\Http\Controllers\Bank\AddAccountController::class,'getgpadata'])->name('getgpadata');
	Route::any('bank/delightKit', [App\Http\Controllers\Bank\AddAccountController::class,'delightKit'])->name('delightKit');

	//for testing purpose
	Route::any('bank/getbatchnumber', [App\Http\Controllers\Bank\AddAccountController::class,'getbatchnumber'])->name('getbatchnumber');

	Route::any('bank/getschemedatabyaccounttype', [App\Http\Controllers\Bank\AddAccountController::class,'getschemedatabyaccounttype'])->name('getschemedatabyaccounttype');

	Route::any('bank/addaccountnew', [App\Http\Controllers\Bank\AddAccountController::class,'addaccountnew'])->name('addaccountnew');
	Route::any('bank/saveaccountdetails', [App\Http\Controllers\Bank\AddAccountController::class,'saveaccountdetails'])->name('saveaccountdetails');
	Route::any('bank/addovddocuments', [App\Http\Controllers\Bank\AddAccountController::class,'addovddocuments'])->name('addovddocuments');

	Route::any('bank/getaddressdatabypincode', [App\Http\Controllers\Bank\AddAccountController::class,'getaddressdatabypincode'])->name('getaddressdatabypincode');
	
	//suman
	Route::any('bank/getnomineedetailsbyAccid', [App\Http\Controllers\Bank\AddAccountController::class,'getnomineedetailsbyAccid'])->name('getnomineedetailsbyAccid');

	Route::any('bank/addriskclassification', [App\Http\Controllers\Bank\AddAccountController::class,'addriskclassification'])->name('addriskclassification');
	Route::any('bank/riskclassificationrating', [App\Http\Controllers\Bank\AddAccountController::class,'riskclassificationrating'])->name('riskclassificationrating');
	Route::any('bank/categorisation', [App\Http\Controllers\Bank\AddAccountController::class,'categorisation'])->name('categorisation');

	Route::any('bank/saveriskdetails', [App\Http\Controllers\Bank\AddAccountController::class,'saveriskdetails'])->name('saveriskdetails');
	Route::any('bank/saveovddetails', [App\Http\Controllers\Bank\AddAccountController::class,'saveovddetails'])->name('saveovddetails');
	Route::any('bank/getifsccode', [App\Http\Controllers\Bank\AddAccountController::class,'getifsccode'])->name('getifsccode');
	Route::any('bank/addfinancialinfo', [App\Http\Controllers\Bank\AddAccountController::class,'addfinancialinfo'])->name('addfinancialinfo');
	Route::any('bank/savefinancialinfo', [App\Http\Controllers\Bank\AddAccountController::class,'savefinancialinfo'])->name('savefinancialinfo');
	// Route::any('bank/getaccountdetails', 'Bank\AddAccountController@getaccountdetails')->name('getaccountdetails');
	Route::any('bank/addnomineedetails', [App\Http\Controllers\Bank\AddAccountController::class,'addnomineedetails'])->name('addnomineedetails');
	Route::any('bank/savenomineedetails', [App\Http\Controllers\Bank\AddAccountController::class,'savenomineedetails'])->name('savenomineedetails');
	Route::any('bank/declaration', [App\Http\Controllers\Bank\AddAccountController::class,'declaration'])->name('declaration');
	Route::any('bank/applydigisign', [App\Http\Controllers\Bank\AddAccountController::class,'applydigisign'])->name('applydigisign');
	Route::any('bank/getaddress', [App\Http\Controllers\Bank\AddAccountController::class,'getaddress'])->name('getaddress');
	Route::any('bank/submission', [App\Http\Controllers\Bank\AddAccountController::class,'submission'])->name('submission');
	Route::any('bank/deleteimage', [App\Http\Controllers\Bank\AddAccountController::class,'deleteimage'])->name('deleteimage');

	Route::any('bank/checkdedupe', [App\Http\Controllers\Bank\AddAccountController::class,'checkdedupe'])->name('checkdedupe');
	Route::any('bank/checklivystatus', [App\Http\Controllers\Bank\AddAccountController::class,'checklivystatus'])->name('checklivystatus');
	#Route::any('bank/checkdedupestatus', 'Bank\AddAccountController@checkdedupestatus')->name('checkdedupestatus');

	Route::any('bank/encryptionpub', [App\Http\Controllers\Bank\AddAccountController::class,'encryptionpub'])->name('encryptionpub');

	Route::any('bank/ekycdetails', [App\Http\Controllers\Bank\AddAccountController::class,'ekycdetails'])->name('ekycdetails');

	Route::any('bank/dashboard', [App\Http\Controllers\Bank\DashboardController::class,'dashboard'])->name('bankdashboard');
	Route::any('bank/userapplications', [App\Http\Controllers\Bank\DashboardController::class,'userapplications'])->name('userapplications');
	Route::any('bank/fileupload', [App\Http\Controllers\Bank\SavingsController::class,'fileupload'])->name('fileupload');
	Route::any('bank/linkupload', [App\Http\Controllers\Bank\SavingsController::class,'linkupload'])->name('linkupload');
	Route::any('bank/level3update', [App\Http\Controllers\Bank\SavingsController::class,'level3update'])->name('level3update');

	//testing need to remove
	Route::any('bank/addcustomerdetails', [App\Http\Controllers\Bank\SavingsController::class,'addcustomerdetails'])->name('addcustomerdetails');
	Route::any('bank/savecustomerdetails', [App\Http\Controllers\Bank\SavingsController::class,'savecustomerdetails'])->name('savecustomerdetails');
	Route::any('bank/verifycustomerdetails', [App\Http\Controllers\Bank\SavingsController::class,'verifycustomerdetails'])->name('verifycustomerdetails');
	Route::any('bank/addovddocumenttabs', [App\Http\Controllers\Bank\AddAccountController::class,'addovddocumenttabs'])->name('addovddocumenttabs');
	Route::any('bank/submittonpc', [App\Http\Controllers\Bank\AddAccountController::class,'submittonpc'])->name('submittonpc');
	Route::any('bank/saveform', [App\Http\Controllers\Bank\AddAccountController::class,'saveform'])->name('saveform');

	Route::any('npc/dashboard', [App\Http\Controllers\NPC\DashboardController::class,'dashboard'])->name('npcdashboard');
	Route::any('npc/userapplications', [App\Http\Controllers\NPC\DashboardController::class,'userapplications'])->name('npcuserapplications');
	Route::any('npc/review', [App\Http\Controllers\NPC\ReviewController::class,'review'])->name('npcreview');
	Route::any('npc/savecomments', [App\Http\Controllers\NPC\ReviewController::class,'savecomments'])->name('savecomments');
	Route::any('npc/submittobank', [App\Http\Controllers\NPC\ReviewController::class,'submittobank'])->name('submittobank');
	Route::any('npc/entityreview', [App\Http\Controllers\NPC\ReviewController::class,'entityreview'])->name('entityreview');
	/*normal,priority,nr dashboard */
	Route::any('npc/nrdashboard', [App\Http\Controllers\NPC\DashboardController::class,'nrData'])->name('nrdashboard');
	
	Route::any('channelid/oaodashboard', [App\Http\Controllers\ChannelId\DashboardController::class,'oaodashboard'])->name('oaodashboard');
	Route::any('channelid/misoaodashboard', [App\Http\Controllers\ChannelId\MisDashboardController::class,'misoaodashboard'])->name('misoaodashboard');
	Route::any('channelid/emailsmstemplate', [App\Http\Controllers\ChannelId\EmailSmsController::class,'templates'])->name('channelid.emailsmstemplate');
	Route::any('/channelid/gettemplates', [App\Http\Controllers\ChannelId\EmailSmsController::class,'gettemplates'])->name('channelid.gettemplates');
	Route::any('/channelid/addtemplate', [App\Http\Controllers\ChannelId\EmailSmsController::class,'addtemplate'])->name('channelid.addtemplate');
	Route::any('/channelid/edittemplate', [App\Http\Controllers\ChannelId\EmailSmsController::class,'edittemplate'])->name('channelid.edittemplate');
	Route::any('/channelid/savetemplate', [App\Http\Controllers\ChannelId\EmailSmsController::class,'savetemplate'])->name('channelid.savetemplate');
	Route::any('/channelid/oaoReview', [App\Http\Controllers\ChannelId\DashboardController::class,'oaoReview'])->name('oaoReview');
	
	Route::any('channelid/fundReceived', [App\Http\Controllers\ChannelId\DashboardController::class,'fundReceived'])->name('fundReceived');
	Route::any('channelid/vkycDone', [App\Http\Controllers\ChannelId\DashboardController::class,'vkycDone'])->name('vkycDone');

	Route::any('/channelid/oaoapplications', [App\Http\Controllers\ChannelId\DashboardController::class,'oaoapplications'])->name('oaoapplications');
	Route::any('/channelid/misoaoapplications', [App\Http\Controllers\ChannelId\MisDashboardController::class,'oaoapplications'])->name('misoaoapplications');
	Route::any('channelid/updateOaoDetails', [App\Http\Controllers\ChannelId\DashboardController::class,'updateOaoDetails'])->name('updateOaoDetails');

	Route::any('npc/alreadyreview', [App\Http\Controllers\NPC\ReviewController::class,'alreadyreview'])->name('alreadyreview');
	
	Route::any('npc/getaddressdatabypincode', [App\Http\Controllers\NPC\ReviewController::class,'getaddressdatabypincode'])->name('npcgetaddressdatabypincode');
	#Route::any('npc/getifsccode', 'NPC\AddAccountController@getifsccode')->name('getifsccode');
	

	Route::any('npc/createcustomerid', [App\Http\Controllers\NPC\ReviewController::class,'createcustomerid'])->name('createcustomerid');
	Route::any('npc/checkfundingstatus', [App\Http\Controllers\NPC\ReviewController::class,'checkfundingstatus'])->name('checkfundingstatus');
	Route::any('npc/createaccountnumber', [App\Http\Controllers\NPC\ReviewController::class,'createaccountnumber'])->name('createaccountnumber');
	Route::any('npc/fundtransfer', [App\Http\Controllers\NPC\ReviewController::class,'fundtransfer'])->name('fundtransfer');
	Route::any('npc/generatequeryid', [App\Http\Controllers\NPC\ReviewController::class,'generatequeryid'])->name('generatequeryid');
	Route::any('npc/checkdedupestatus', [App\Http\Controllers\NPC\ReviewController::class,'checkdedupestatus'])->name('checkdedupestatus');
	Route::any('npc/checkdedupestatusall', [App\Http\Controllers\NPC\ReviewController::class,'checkdedupestatusall'])->name('checkdedupestatusall');
	Route::any('npc/markFormForQC', [App\Http\Controllers\NPC\ReviewController::class,'markFormForQC'])->name('markFormForQC');
	Route::any('npc/updateFieldValue', [App\Http\Controllers\NPC\ReviewController::class,'updateFieldValue'])->name('updateFieldValue');

	Route::any('archival/dashboard', [App\Http\Controllers\Archival\DashboardController::class,'dashboard'])->name('archivaldashboard');
    Route::any('archival/userapplications', [App\Http\Controllers\Archival\DashboardController::class,'userapplications'])->name('archivaluserapplications');
    Route::any('archival/addarchivalno', [App\Http\Controllers\Archival\ArchivalController::class,'addarchivalno'])->name('addarchivalno');
    Route::any('archival/archivallist', [App\Http\Controllers\Archival\ArchivalController::class,'archivallist'])->name('archivallist');
    Route::any('archival/savearchival', [App\Http\Controllers\Archival\ArchivalController::class,'savearchival'])->name('savearchival');
    Route::any('archival/updatearchivalno', [App\Http\Controllers\Archival\ArchivalController::class,'updatearchivalno'])->name('updatearchivalno');
    Route::any('archival/editarchivalno', [App\Http\Controllers\Archival\CreateBatchController::class,'editarchivalno'])->name('editarchivalno');
    Route::any('archival/aadhaarmasking', [App\Http\Controllers\Archival\AadhaarMaskingController::class,'display'])->name('aadhaarmasking');
    Route::any('archival/aadhaarmaskingupdate', [App\Http\Controllers\Archival\AadhaarMaskingController::class,'updateAadhaarMask'])->name('aadhaarmaskingupdate');


    Route::any('archival/aadhaarmaskingsave', [App\Http\Controllers\Archival\AadhaarMaskingController::class,'saveAadhaarMask'])->name('aadhaarmaskingsave');
    Route::any('archival/aadhaarmaskingsubmit', [App\Http\Controllers\Archival\AadhaarMaskingController::class,'submitAadhaarMask'])->name('aadhaarmaskingsubmit');


    Route::any('archival/archivalrecord', [App\Http\Controllers\Archival\ArchivalRecordController::class,'getarchivalrecord'])->name('getarchivalrecord');
    Route::any('archival/importarchivalrecord', [App\Http\Controllers\Archival\ArchivalRecordController::class,'importarchivalrecord'])->name('importarchivalrecord');

    Route::any('archival/allarchivalrecord', [App\Http\Controllers\Archival\ArchivalRecordController::class,'allarchivalrecord'])->name('allarchivalrecord');

    Route::any('archival/archivalrecords', [App\Http\Controllers\Archival\ArchivalRecordController::class,'archivalrecords'])->name('archivalrecords');
    Route::any('archival/editarchivalexcelrecord', [App\Http\Controllers\Archival\ArchivalRecordController::class,'editarchivalexcelrecord'])->name('editarchivalexcelrecord');
    Route::any('archival/savearchivalrecord', [App\Http\Controllers\Archival\ArchivalRecordController::class,'savearchivalrecord'])->name('savearchivalrecord');
	Route::any('archival/getpendingaadhaarmask', [App\Http\Controllers\Archival\AadhaarMaskingController::class,'getpendingaadhaarmask'])->name('getpendingaadhaarmask');

	Route::any('bank/review', [App\Http\Controllers\Bank\ReviewController::class,'review'])->name('bankreview');
	Route::any('bank/updatecolumn', [App\Http\Controllers\Bank\ReviewController::class,'updatecolumn'])->name('updatecolumn');
	Route::any('bank/createbatch', [App\Http\Controllers\Bank\CreateBatchController::class,'createbatch'])->name('createbatch');

	Route::any('bank/dispatchapplications', [App\Http\Controllers\Bank\CreateBatchController::class,'dispatchapplications'])->name('dispatchapplications');
	Route::any('bank/createbatchid', [App\Http\Controllers\Bank\CreateBatchController::class,'createbatchid'])->name('createbatchid');
	Route::any('bank/savebatch', [App\Http\Controllers\Bank\CreateBatchController::class,'savebatch'])->name('savebatch');

	Route::any('bank/addairwaybillno', [App\Http\Controllers\Bank\CreateBatchController::class,'addairwaybillno'])->name('addairwaybillno');
	Route::any('bank/editairwaybillno', [App\Http\Controllers\Bank\CreateBatchController::class,'editairwaybillno'])->name('editairwaybillno');
	Route::any('bank/updatedairwaybill', [App\Http\Controllers\Bank\CreateBatchController::class,'updatedairwaybill'])->name('updatedairwaybill');
	Route::any('bank/batchlist', [App\Http\Controllers\Bank\CreateBatchController::class,'batchlist'])->name('batchlist');
	Route::any('bank/saveairwaybillno', [App\Http\Controllers\Bank\CreateBatchController::class,'saveairwaybillno'])->name('saveairwaybillno');
	Route::any('bank/printairbatchid', [App\Http\Controllers\Bank\CreateBatchController::class,'printairbatchid'])->name('printairbatchid');

	Route::any('/inward/dashboard', [App\Http\Controllers\Inward\DashboardController::class,'dashboard'])->name('inwarddashboard');
	Route::any('/inward/batchapplications', [App\Http\Controllers\Inward\DashboardController::class,'batchapplications'])->name('batchapplications');
	Route::any('/inward/updateinward', [App\Http\Controllers\Inward\DashboardController::class,'updateinward'])->name('updateinward');
	Route::any('/inward/batchformapplications', [App\Http\Controllers\Inward\DashboardController::class,'batchformapplications'])->name('batchformapplications');
	Route::any('/inward/updateinwardstatus', [App\Http\Controllers\Inward\DashboardController::class,'updateinwardstatus'])->name('updateinwardstatus');
	Route::any('/inward/saveairwaybillno', [App\Http\Controllers\Inward\DashboardController::class,'saveairwaybillno'])->name('inwardsaveairwaybillno');


	// /*Maker INCIDENT*/
	// Route::any('/bank/makerindent', 'Bank\DashboardController@makerindent')->name('makerindent');
	// /*Branch Inventory*/
	// Route::any('/bank/branchinventory', 'Bank\DashboardController@branchinventory')->name('branchinventory');
	// /*Kit Inward*/
	// Route::any('/bank/kitinward', 'Bank\DashboardController@kitinward')->name('kitinward');
	// /*damage missing indent*/
	// Route::any('/bank/damagemissingkit', 'Bank\DashboardController@damagemissingkit')->name('damagemissingkit');

	//Route::any('/bank/aoftracking', 'Bank\AOFTrackerController@aoftracking')->name('aoftracking');
	Route::any('bank/aoftracking/{aof?}', [App\Http\Controllers\Bank\AOFTrackerController::class,'aoftracking'])->name('aoftracking');

	Route::any('/bank/trackingdetails', [App\Http\Controllers\Bank\AOFTrackerController::class,'trackingdetails'])->name('trackingdetails');
	Route::any('/bank/formdetails', [App\Http\Controllers\Bank\AOFTrackerController::class,'formdetails'])->name('formdetails');
	Route::any('/bank/abortform', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'abortform'])->name('abortform');
	Route::any('/bank/markformreject', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'markFormReject'])->name('markFormReject');

	Route::any('/bank/privilegeaccess', [App\Http\Controllers\NPC\PrivilegeAccessController::class,'privilegeaccess'])->name('privilegeaccess');
	Route::any('/bank/accountdetails', [App\Http\Controllers\NPC\PrivilegeAccessController::class,'accountdetails'])->name('accountdetails');
	Route::any('/npc/checktdaccountcreated', [App\Http\Controllers\NPC\PrivilegeAccessController::class,'checktdaccountcreated'])->name('checktdaccountcreated');

	Route::any('/management/dashboard', [App\Http\Controllers\Management\DashboardController::class,'dashboard'])->name('managementdashboard');
	Route::any('/management/dashboardv2', [App\Http\Controllers\Management\DashboardController::class,'dashboardv2'])->name('managementdashboardv2');
	Route::any('/management/processflow', [App\Http\Controllers\Management\DashboardController::class,'processflow'])->name('processflow');

	//============================management filtersss===========================================//
	//Route::any('/management/getchartdata', 'Management\DashboardController@getchartdata')->name('getchartdata');
	Route::any('/management/getfilterchartdata', [App\Http\Controllers\Management\DashboardController::class,'getfilterchartdata'])->name('getfilterchartdata');
	

	//===========================Exception Log==========================//
	Route::any('/admin/exception', [App\Http\Controllers\Admin\ExceptionController::class,'exception'])->name('exception');
	Route::any('/admin/getexceptionlist', [App\Http\Controllers\Admin\ExceptionController::class,'getexceptionlist'])->name('getexceptionlist');
	Route::any('/admin/getExLog', [App\Http\Controllers\Admin\ExceptionController::class,'getExLog'])->name('getExLog');
	Route::any('/admin/getFullExLog', [App\Http\Controllers\Admin\ExceptionController::class,'getFullExLog'])->name('getFullExLog');
	Route::any('/admin/debugInfo/{aof}', [App\Http\Controllers\Admin\ExceptionController::class,'debugInfo'])->name('debugInfo');
	Route::any('/admin/debugInfoTrace/{aof}', [App\Http\Controllers\Admin\ExceptionController::class,'debugInfoTrace'])->name('debugInfoTrace');
	Route::any('/admin/imagesdirect/{aof}', [App\Http\Controllers\Admin\ExceptionController::class,'imagesdirect'])->name('imagesdirect');
	Route::any('/admin/debugDBstruct/{code}/{schema}', [App\Http\Controllers\Admin\ExceptionController::class,'debugDBstruct'])->name('debugDBstruct');
	Route::any('/admin/debugMasterData/{code}/{table}/{records}', [App\Http\Controllers\Admin\ExceptionController::class,'debugMasterData'])->name('debugMasterData');	
	Route::any('/admin/debugInfoAmend/{crf}', [App\Http\Controllers\Admin\ExceptionController::class,'debugInfoAmend'])->name('debugInfoAmend');
	Route::any('/admin/checkGamTable/{custId}', [App\Http\Controllers\Admin\ExceptionController::class,'checkGamTable'])->name('checkGamTable');

	//===========================Email Sms Message Log==========================//
	Route::any('/admin/emailsmsmessages', [App\Http\Controllers\Admin\EmailSmsMessagesLogController::class,'emailsmsmessages'])->name('emailsmsmessages');
	Route::any('/admin/getemailsmsmessageslist', [App\Http\Controllers\Admin\EmailSmsMessagesLogController::class,'getemailsmsmessageslist'])->name('getemailsmsmessageslist');
	Route::any('/admin/getnotificationpending', [App\Http\Controllers\Admin\EmailSmsMessagesLogController::class,'getnotificationpending'])->name('getnotificationpending');


	//===========================Check ETB Customer Type(Normal/Call Center)==========================//
	Route::any('bank/checkEtbCustomerType', [App\Http\Controllers\Bank\AddAccountController::class,'checkEtbCustomerType'])->name('checkEtbCustomerType');

	//===============Privilege Update ===========================//

	Route::any('/bank/privilegeupdate', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'privilegeupdate'])->name('privilegeupdate');

	Route::any('/bank/privilegeupdateaccountdetails', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'privilegeupdateaccountdetails'])->name('privilegeupdateaccountdetails');

	Route::any('/bank/updatededupestatus', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatededupestatus'])->name('updatededupestatus');
	Route::any('/bank/updatecustomerid', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatecustomerid'])->name('updatecustomerid');
	Route::any('/bank/updateinternetbank', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updateinternetbank'])->name('updateinternetbank');
	Route::any('/bank/updatekyc', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatekyc'])->name('updatekyc');
	Route::any('/bank/updatefundinstatus', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatefundinstatus'])->name('updatefundinstatus');
	Route::any('/bank/updateaccountno', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updateaccountno'])->name('updateaccountno');
	Route::any('/bank/updateftrfundtransfer', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updateftrfundtransfer'])->name('updateftrfundtransfer');
	Route::any('/bank/updatesignaturestatus', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatesignaturestatus'])->name('updatesignaturestatus');
	Route::any('/bank/updatecardflagstatus', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatecardflagstatus'])->name('updatecardflagstatus');
	Route::any('/bank/updatenextrole', [App\Http\Controllers\NPC\PrivilegeUpdateController::class,'updatenextrole'])->name('updatenextrole');

	/*==================Call Center ========================*/

	Route::any('callcenter/dashboard', [App\Http\Controllers\Callcenter\CallCenterDashboardController::class,'dashboard'])->name('callcenterdashboard');
	Route::any('callcenter/callcenteruserapplications', [App\Http\Controllers\Callcenter\CallCenterDashboardController::class,'callcenteruserapplications'])->name('callcenteruserapplications');


	//=====================Maker ============================//
    Route::any('/maker/dashboard', [App\Http\Controllers\Maker\DashboardController::class,'dashboard'])->name('makerdashboard');
    Route::any('/maker/saveindent', [App\Http\Controllers\Maker\DashboardController::class,'saveindent'])->name('saveindent');
    Route::any('/maker/kitstatusbyscheme', [App\Http\Controllers\Maker\DashboardController::class,'kitstatusbyscheme'])->name('kitstatusbyscheme');
    Route::any('/maker/kitdetails', [App\Http\Controllers\Maker\DashboardController::class,'kitdetails'])->name('kitdetails');
    Route::any('/maker/kitdetailstable', [App\Http\Controllers\Maker\DashboardController::class,'kitdetailstable'])->name('kitdetailstable');
    Route::any('/maker/statusupdateapproval', [App\Http\Controllers\Maker\DashboardController::class,'statusupdateapproval'])->name('statusupdateapproval');
    Route::any('/maker/updatekitstatus', [App\Http\Controllers\Maker\DashboardController::class,'updatekitstatus'])->name('makerupdatekitstatus');
    //kit details table
    Route::any('/maker/kittable', [App\Http\Controllers\Maker\DashboardController::class,'kittable'])->name('kittable');
    #Route::any('/maker/kittabledetails', 'Maker\DashboardController@kitdetailstable')->name('kitdetailstable');
    Route::any('/maker/checkerkitdetailstable', [App\Http\Controllers\Maker\DashboardController::class,'checkerkitdetailstable'])->name('makercheckerkitdetailstable');
    //Maker INCIDENT
    Route::any('/maker/makerindent', [App\Http\Controllers\Maker\DashboardController::class,'makerindent'])->name('makerindent');
    //Branch Inventory
    Route::any('/maker/branchinventory', [App\Http\Controllers\Maker\DashboardController::class,'branchinventory'])->name('branchinventory');
    //Kit Inward
    Route::any('/maker/kitinward', [App\Http\Controllers\Maker\DashboardController::class,'kitinward'])->name('kitinward');
    //damage missing indent
    Route::any('/maker/damagemissingkit', [App\Http\Controllers\Maker\DashboardController::class,'damagemissingkit'])->name('damagemissingkit');
    //seek Approval request
    Route::any('/maker/requestApproval', [App\Http\Controllers\Maker\DashboardController::class,'requestApproval'])->name('requestApproval');
    //seek Approval table
    Route::any('/maker/seekapproval', [App\Http\Controllers\Maker\DashboardController::class,'seekapproval'])->name('seekapproval');
    //kit Count Approval table
    Route::any('/maker/getKitCountApproval', [App\Http\Controllers\Maker\DashboardController::class,'getKitCountApproval'])->name('getKitCountApproval');

    /////checker Role
    Route::any('/checker/dashboard', [App\Http\Controllers\Checker\DashboardController::class,'dashboard'])->name('checkerdashboard');
    //kit Count Approval table
    Route::any('/checker/checkerkitdetailstable', [App\Http\Controllers\Checker\DashboardController::class,'checkerkitdetailstable'])->name('checkerkitdetailstable');

    //submit Approval table
    Route::any('/checker/submitApproval', [App\Http\Controllers\Checker\DashboardController::class,'submitApproval'])->name('submitApproval');

    Route::any('/delightadmin/dashboard', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitCountApproval'])->name('kitCountApproval');
    Route::any('/delightadmin/kitcountapprovaltable', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitcountapprovaltable'])->name('kitcountapprovaltable');
    Route::any('/delightadmin/markapprover', [App\Http\Controllers\DelightAdmin\DashboardController::class,'approveKitRequest'])->name('approveKitRequest');
    Route::any('/delightadmin/updatedrstatus', [App\Http\Controllers\DelightAdmin\DashboardController::class,'updatedrstatus'])->name('updatedrstatus');
    Route::any('/delightadmin/kitdispatch', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitdispatch'])->name('kitdispatch');
    Route::any('/delightadmin/kitdispatchtable', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitdispatchtable'])->name('kitdispatchtable');
    Route::any('/delightadmin/updatekitstatus', [App\Http\Controllers\DelightAdmin\DashboardController::class,'updatekitstatus'])->name('updatekitstatus');
    Route::any('/delightadmin/branchinventory', [App\Http\Controllers\DelightAdmin\DashboardController::class,'branchinventory'])->name('kitinventory');
    Route::any('/delightadmin/inventorydetails', [App\Http\Controllers\DelightAdmin\DashboardController::class,'inventorydetails'])->name('inventorydetails');
    Route::any('/delightadmin/kitdetails', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitdetails'])->name('adminkitdetails');
    Route::any('/delightadmin/kitdetailstable', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitdetailstable'])->name('adminkitdetailstable');

    //DELIGHT ADMIN
    Route::any('/delightadmin/dashboard', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitCountApproval'])->name('delightadmindashboard');

    //Management AOF Mode Report
    Route::any('/management/modereport', [App\Http\Controllers\Management\ModeReportController::class,'modereport'])->name('modereport');
    Route::any('/management/getmodereport', [App\Http\Controllers\Management\ModeReportController::class,'getmodereport'])->name('getmodereport');	
	Route::any('/management/getreport/{type}', [App\Http\Controllers\Management\ModeReportController::class,'getreport'])->name('getreport');	

	//discrepancy report  discrepancyreport
	Route::any('/management/discrepancyreport', [App\Http\Controllers\Management\DiscrepancyReportController::class,'discrepancyreport'])->name('discrepancyreport');	
	Route::any('/management/getdiscrepancyreport', [App\Http\Controllers\Management\DiscrepancyReportController::class,'getdiscrepancyreport'])->name('getdiscrepancyreport');	

	//tat Report
    Route::any('/management/tatreport', [App\Http\Controllers\Management\ModeReportController::class,'tatreport'])->name('tatreport');
    Route::any('/management/tatreportdetails', [App\Http\Controllers\Management\ModeReportController::class,'tatreportdetails'])->name('tatreportdetails');
    ///addUserDetails (UAM)
    Route::any('/uam/addusertabledata', [App\Http\Controllers\Uam\UamDashboardController::class,'addusertabledata'])->name('addUserDetails');
	
	/*uam dashboard*/
	Route::any('/uam/l1Dashboard', [App\Http\Controllers\Uam\UamDashboardController::class,'npconeDashboard'])->name('L1Dashboard');
    Route::any('/uam/l2Dashboard', [App\Http\Controllers\Uam\UamDashboardController::class,'npctwoDashboard'])->name('L2Dashboard');
	Route::any('/uam/saveuser', [App\Http\Controllers\Uam\UamDashboardController::class,'saveUserdata'])->name('saveuser');
   
    //saveUserDetails(UAM)
	Route::any('/uam/savecolumndata', [App\Http\Controllers\Uam\UamDashboardController::class,'saveusercolumndata'])->name('savecolumndata');

	//check Kits generated By finacle
	Route::any('/delightadmin/checkgeneratedkits', [App\Http\Controllers\DelightAdmin\CheckKitGeneratedByFincale::class,'checkkitGenereated'])->name('checkkitGenereated');

	/* One Time Task */
	Route::any('/admin/genPdf/{aof?}', [App\Http\Controllers\Admin\ExceptionController::class,'genPdf'])->name('getPdf');

	Route::any('report/l3Report', [App\Http\Controllers\Reports\ReportController::class,'l3Report'])->name('l3Report');
	Route::any('/report/l3ReportDetails', [App\Http\Controllers\Reports\ReportController::class,'l3ReportDetails'])->name('l3ReportDetails');
	//Amended Flow
	
	//fetch customer detail with custid or account id details
	Route::any('bank/checkamendcustomer', [App\Http\Controllers\Amend\AmendController::class,'checkamendcustomer'])->name('checkamendcustomer');
	Route::any('bank/fetchdataperselectedid', [App\Http\Controllers\Amend\AmendController::class,'fetchdataperselectedid'])->name('fetchdataperselectedid');
	Route::any('bank/ameditemselected', [App\Http\Controllers\Amend\AmendController::class,'ameditemselected'])->name('ameditemselected');
	Route::any('bank/amendinput', [App\Http\Controllers\Amend\AmendController::class,'amendinput'])->name('amendinput');
    Route::any('bank/fetchdataforid', [App\Http\Controllers\Amend\AmendController::class,'fetchdataforid'])->name('fetchdataforid');
    Route::any('bank/getcustomeraccountdetails', [App\Http\Controllers\Amend\AmendController::class,'getcustomeraccountdetails'])->name('getcustomeraccountdetails');
    Route::any('bank/checkcustdataprocessing', [App\Http\Controllers\Amend\AmendController::class,'checkcustdataprocessing'])->name('checkcustdataprocessing');
    
	//insert data into table
	Route::any('bank/insertnewdata', [App\Http\Controllers\Amend\AmendController::class,'insertnewdata'])->name('insertnewdata');
    //pdf view template 
	Route::any('bank/amendform', [App\Http\Controllers\Amend\AmendController::class,'amendform'])->name('amendform');
    Route::any('bank/printrequestform', [App\Http\Controllers\Amend\AmendController::class,'printrequestform'])->name('printrequestform');
    //save crf document
    Route::any('bank/savecrfdocument', [App\Http\Controllers\Amend\AmendController::class,'savecrfdocument'])->name('savecrfdocument');
    //amendlist dashboard
    Route::any('bank/amenddashboard',[App\Http\Controllers\Amend\AmendDashboard::class,'amenddashboard'])->name('amenddashboard');
    Route::any('bank/amendapplication',[App\Http\Controllers\Amend\AmendDashboard::class,'amendapplication'])->name('amendapplication');

    //pincode selected fetc data 
    Route::any('bank/getdetailspincodeselected',[App\Http\Controllers\Amend\AmendController::class,'getdetailspincodeselected'])->name('getdetailspincodeselected');
    
    //amend L1 npc data form

    Route::any('amendnpc/dashboard',[App\Http\Controllers\AmendNpc\AmendNpcDashboardController::class,'dashboard'])->name('amendnpcdashboard');
    Route::any('amendnpc/amendapplicant',[App\Http\Controllers\AmendNpc\AmendNpcDashboardController::class,'amendapplicant'])->name('amendapplicant');
	Route::any('amendnpc/alreadyamendreview', [App\Http\Controllers\AmendNpc\AmendNpcDashboardController::class,'alreadyamendreview'])->name('alreadyamendreview');

    Route::any('amendnpc/amendL3update',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'amendL3update'])->name('amendL3update');

    Route::any('amendnpc/amendreview',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'amendreview'])->name('amendreview');
    Route::any('amendnpc/getamendaddressdatabypincode',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'getamendaddressdatabypincode'])->name('getamendaddressdatabypincode');
   Route::any('amendnpc/updateamendfieldvalue',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'updateAmendFieldValue'])->name('updateamendfieldvalue');
    Route::any('amendnpc/saveamendcomment',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'saveamendcomment'])->name('saveamendcomment');
    Route::any('amendnpc/amendnpcsubmit',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'amendnpcsubmit'])->name('amendnpcsubmit');
	Route::any('amendnpc/aadhaarmasking', [App\Http\Controllers\AmendNpc\AadhaarMaskingController::class,'display'])->name('amendaadhaarmasking');
	Route::any('amendnpc/aadhaarmaskingsave', [App\Http\Controllers\AmendNpc\AadhaarMaskingController::class,'saveAadhaarMask'])->name('aadhaarmaskingsave');

	Route::any('amendnpc/viewekycphoto/{ekyc_no}',[App\Http\Controllers\AmendNpc\AmendReviewController::class,'viewekycphoto'])->name('viewekycphoto');

    //amend Delete Image function

    Route::any('bank/amendDeleteImage',[App\Http\Controllers\Amend\AmendController::class,'amendDeleteImage'])->name('amendDeleteImage');

	//amendment tracking details 
	Route::any('bank/amendcrftracking',[App\Http\Controllers\Amend\AmendCRFTrackerController::class,'amendcrftracking'])->name('amendcrftracking');
	Route::any('bank/amendtrackingdetails',[App\Http\Controllers\Amend\AmendCRFTrackerController::class,'amendtrackingdetails'])->name('amendtrackingdetails');

	//27_02_2023 customerlist dropdown
	Route::any('bank/crfcustomerlist',[App\Http\Controllers\Amend\AmendCRFTrackerController::class,'crfcustomerlist'])->name('crfcustomerlist');

	//upload crf approval doccument 

	Route::any('bank/uploadcrfapproval',[App\Http\Controllers\Amend\AmendController::class,'uploadcrfapproval'])->name('uploadcrfapproval');

	//------------Pdf document save in temp folder----------------\\

	Route::any('bank/pdffileupload',[App\Http\Controllers\Bank\SavingsController::class,'uplodpdfdocument'])->name('pdffileupload');
	//------------Pdf download data --------------------------\\
	Route::any('bank/dowloadshowDoc/{pdfDocName?}',[App\Http\Controllers\Amend\AmendController::class,'dowloadshowDoc'])->name('dowloadshowDoc');
	// Route::any('bank/dowloadshowDoc','Amend\AmendController@dowloadshowDoc')->name('dowloadshowDoc');

	Route::any('/admin/encodeDebugUser/{uid}',[App\Http\Controllers\Admin\ExceptionController::class,'encodeDebugUser'])->name('encodeDebugUser');
	Route::any('/trespassed',[App\Http\Controllers\VersionController::class,'trespassed'])->name('trespassed');

	//-------------------- amend panis valid-------------------------\\
  Route::any('bank/amendpanisvalid',[App\Http\Controllers\Amend\AmendController::class,'amendpanisvalid'])->name('amendpanisvalid');

    // ---------------------amend flow check valid custid or not ---------------------\\
  Route::any('bank/checkvalidcustid',[App\Http\Controllers\Amend\AmendController::class,'checkvalidcustid'])->name('checkvalidcustid');

    // //-------------------check gender base title------------------\\
    // Route::any('bank/selectgendertitle','Amend\AmendController@selectgendertitle')->name('selectgendertitle');
	Route::any('bank/checkvaliddomain',[App\Http\Controllers\Amend\AmendController::class,'checkvaliddomain'])->name('checkvaliddomain');
	Route::any('bank/chkrestcountry',[App\Http\Controllers\Amend\AmendController::class,'chkrestcountry'])->name('chkrestcountry');
	Route::any('/admin/showoldforms/{days}',[App\Http\Controllers\Admin\ExceptionController::class,'showoldforms'])->name('showoldforms');
	Route::any('/admin/rejectoldforms/{days}',[App\Http\Controllers\Admin\ExceptionController::class,'rejectoldforms'])->name('rejectoldforms');
	Route::any('/admin/rejectoldETBforms/{days}',[App\Http\Controllers\Admin\ExceptionController::class,'rejectoldETBforms'])->name('rejectoldETBforms');
	Route::any('/admin/rejectNeverSentForms/{days}',[App\Http\Controllers\Admin\ExceptionController::class,'rejectNeverSentForms'])->name('rejectNeverSentForms');
 //    Route::any('bank/saveAmendOTP','Amend\AmendController@saveAmendOTP')->name('saveAmendOTP');
	// Route::get('bank/amendonline/{hashlinkid?}','Amend\AmendController@amendonline')->name('amendonline');

	//Mobile And Debit Pin 01-03-2023 report 
	Route::any('/amendnpc/servicerequestreport',[App\Http\Controllers\Reports\ReportController::class,'servicerequestreport'])->name('servicerequestreport');
	Route::any('/amendnpc/getservicerequestdata',[App\Http\Controllers\Reports\ReportController::class,'getservicerequestdata'])->name('getservicerequestdata');

	Route::any('/admin/amendapiqueuelog',[App\Http\Controllers\Admin\AmendApiQueueLogController::class,'amendapiqueuelog'])->name('amendapiqueuelog');
	Route::any('/admin/amendapiqueuelogtable',[App\Http\Controllers\Admin\AmendApiQueueLogController::class,'amendapiqueuelogtable'])->name('amendapiqueuelogtable');
	Route::any('/admin/updateamendapiqueue',[App\Http\Controllers\Admin\AmendApiQueueLogController::class,'updateamendapiqueue'])->name('updateamendapiqueue');


	

	Route::any('amend/checkvalidacctno',[App\Http\Controllers\Amend\AmendController::class,'checkvalidacctno'])->name('checkvalidacctno');
	// Route::any('amend/checkvalidaadhaarNo','Amend\AmendController@checkvalidaadhaarNo')->name('checkvalidaadhaarNo');

	//repayment api queue insert one time task

	Route::any('admin/repaymentApiinsertQueue/{formId}',[App\Http\Controllers\Admin\ExceptionController::class,'repaymentApiinsertQueue'])->name('repaymentApiinsertQueue');


	Route::any('/admin/uploadDebugInfo', [App\Http\Controllers\Admin\ExceptionController::class,'uploadDebugInfo'])->name('uploadDebugInfo');
	Route::any('/admin/debugTest', [App\Http\Controllers\Admin\ExceptionController::class,'debugTest'])->name('debugTest');
	Route::any('/admin/amenddebugTest', [App\Http\Controllers\Admin\ExceptionController::class,'amenddebugTest'])->name('amenddebugTest');

	Route::any('/extapi/uamapicall', [App\Http\Controllers\ExtApi\UAMApiController::class,'uamapicall'])->name('uamapicall');
	//create uam role for api 


	Route::any('/delightadmin/adminkitdetailshistory', [App\Http\Controllers\DelightAdmin\DashboardController::class,'adminkitdetailshistory'])->name('adminkitdetailshistory');
	Route::any('/delightadmin/kitdetailshistorytable', [App\Http\Controllers\DelightAdmin\DashboardController::class,'kitdetailshistorytable'])->name('kitdetailshistorytable');


	Route::any('/npc/checkimagemasking',[App\Http\Controllers\NPC\ReviewController::class,'checkimagemasking'])->name('checkimagemasking');
	Route::any('/apiqueuereport',[App\Http\Controllers\Reports\ReportController::class,'apiqueuereport'])->name('apiqueuereport');

	// added on 12-09-2024
	Route::any('archival/allimagemask', [App\Http\Controllers\Archival\AadhaarMaskingController::class,'allimagemask'])->name('allimagemask');
	Route::any('archival/displayimage',[App\Http\Controllers\Archival\AadhaarMaskingController::class,'displaymaskimage'])->name('displayimage');
	
	Route::any('amendnpc/all_amend_aadharmasking',[App\Http\Controllers\AmendNpc\AadhaarMaskingController::class,'all_amend_aadharmasking'])->name('all_amend_aadharmasking');
	Route::any('amendnpc/display_aadhar_amend',[App\Http\Controllers\AmendNpc\AadhaarMaskingController::class,'display_aadhar_amend'])->name('display_aadhar_amend');

	Route::any('/uam/UserUnDelete', [App\Http\Controllers\Uam\UamDashboardController::class,'UserUnDelete'])->name('UserUnDelete');
	Route::any('/uam/uamundeletereport',[App\Http\Controllers\Uam\UamDashboardController::class,'uamundeletereport'])->name('uamundeletereport');
	//Route::any('/etbntb_check_via_idovd', 'ETBNTB\checkIDOVDController@etbntb_check_via_idovd')->name('etbntb_check_via_idovd');
	Route::any('/npc/decrypt',[App\Http\Controllers\NPC\ReviewController::class,'decrypt'])->name('decrypt');
	Route::any("/bank/custamendrequestispending",[App\Http\Controllers\Bank\AddAccountController::class,'checkAmendEtbRequestPending']);
	Route::any('/etbntb_check_via_idovd', [App\Http\Controllers\ETBNTB\checkIDOVDController::class,'etbntb_check_via_idovd'])->name('etbntb_check_via_idovd');

	Route::any('/bank/ProofSelectedValidation',[App\Http\Controllers\Bank\AddAccountController::class,'ProofSelectedValidation'])->name('ProofSelectedValidation');

});
