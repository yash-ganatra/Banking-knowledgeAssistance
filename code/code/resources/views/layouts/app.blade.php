<?php
    use App\Helpers\CommonFunctions;
    use Carbon\Carbon;
    
    if(Session::get('role') != ''){
        $role = Session::get('role');
        $lastLogin = Session::get('lastLogin');
        if (Session::get('lastLogin') == '') {
            $lastLogin = '-1';
        }
        $redirecturl = CommonFunctions::getDashboardUrl($role);
    }
    $_usr = Session::get('userId');
    $_usrTime =  Cache::get('CG_is_logged_in');
?>

@php   
    
    
    $block_debug = true;
    if(env('APP_SETUP') != 'DEV'){ 
      
        if(Cache::get('_ALLOW_THIS_USER_DEBUG') == Session::get('userId')){
            $block_debug = false;
        }
        if(Cache::get('_ALLOW_THIS_USER_DEBUG') == 'ALL'){
            $block_debug = false;
        }
   }else{
        $block_debug = false;
   } 

@endphp

@if($block_debug)

<script>

    // Copyright (C) AppInSource Technologies - Chetan Ganatra - 21 March 2024
    
    console.log('INDEV...{{$block_debug}}...{{Session::get("userId")}}');
    window._sessionT = +new Date();        
    window._dbgTmrCG;

    const devtools = {            isOpen: false,            orientation: undefined,        };

    const threshold = 170;

    const emitEvent = (isOpen, orientation) => {
        globalThis.dispatchEvent(new globalThis.CustomEvent('devtoolschange', {
            detail: { isOpen, orientation, },
        }));
    };

    const main = ({emitEvents = true} = {}) => {
        const widthThreshold = globalThis.outerWidth - globalThis.innerWidth > threshold;
        const heightThreshold = globalThis.outerHeight - globalThis.innerHeight > threshold;
        const orientation = widthThreshold ? 'vertical' : 'horizontal';

        if (
            !(heightThreshold && widthThreshold)
            && ((globalThis.Firebug && globalThis.Firebug.chrome && globalThis.Firebug.chrome.isInitialized) || widthThreshold || heightThreshold)
        ) {
            if ((!devtools.isOpen || devtools.orientation !== orientation) && emitEvents) {
                emitEvent(true, orientation);
            }
            devtools.isOpen = true;                devtools.orientation = orientation;
        } else {
            if (devtools.isOpen && emitEvents) {
                emitEvent(false, undefined);
            }
            devtools.isOpen = false;                devtools.orientation = undefined;
        }
    };

    main({emitEvents: false});
    
    clearInterval();        setInterval(main, 3000);

    window.addEventListener('devtoolschange', event => {
        if(event.detail.isOpen){            
            //alert('CG.CL.1');
            setTimeout(function(){
                window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/logout';
            }, 4000);        
        
            window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/trespassed';        

            //var diag = window.open("", "Unauthorized Access!", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=500,height=200,top="+(screen.height/3)+",left="+(screen.width/3));
            //diag.document.body.innerHTML = "<div style='text-align: center; font-family: Arial;'><h3 style='color:#f00';>Unauthorized tampering detected!</h3><hr>User ID and attempt logged for Audit. <br><br>Ref. URL: "+window.location.href+" <br><br>Disabling user account...</div>";                        
        }
    });

    document.addEventListener('contextmenu', (e) => e.preventDefault());

    function ctrlShiftKey(e, keyCode) {
        return e.ctrlKey && e.shiftKey && e.keyCode === keyCode.charCodeAt(0);
    }

    document.onkeydown = (e) => {
        // Disable F12, Ctrl + Shift + I, Ctrl + Shift + J, Ctrl + U
        if (
            event.keyCode === 123 ||
            ctrlShiftKey(e, 'I') ||
            ctrlShiftKey(e, 'J') ||
            ctrlShiftKey(e, 'C') ||
            (e.ctrlKey && e.keyCode === 'U'.charCodeAt(0))
        )
        return false;
    };

    function cutLe(){
        //alert('CG.CL.3');
        setTimeout(function(){
                window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/logout';
            }, 4000);        
        
        window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/trespassed';        
    }

</script>
@endif


<!DOCTYPE html>
<html lang="en">
<head>
    <title>DCB - CUBE - AIS</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta charset="utf-8" name="base_url" content="{{ URL::to('/') }}">
    <meta name="cookie" content="{{Cookie::get('token')}}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Favicon icon -->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/bootstrap.min.css') }}">
    <!-- Select 2 css -->
    <link rel="stylesheet" href="{{ asset('components/select2/css/select2.min.css') }}" />
    <!-- Date & Time Pickers-->
    <link href="{{ asset('components/bootstrap-datepicker/css/bootstrap-datepicker.css') }}" rel="stylesheet">
    <link href="{{ asset('pages/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/css/jquery-confirm.css') }}" rel="stylesheet">
    <!-- Cropper css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('components/cropper/css/cropper.css') }}">
    <!-- font awesome icon -->
    <link href="{{ asset('components/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet" type="text/css">
    <!-- Notification.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/pages/notification/notification.css') }}">
    <!-- ico font -->
    <!-- <link rel="stylesheet" type="text/css" href="{{ asset('icon/icofont/css/icofont.css') }}"> -->
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/buttons.dataTables.min.css') }}">
    <!--<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/maker-style.css') }}">-->
    <link rel="stylesheet" type="text/css" href="{{ asset('custom/css/style-custom.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('custom/css/style_custom_flow.css') }}">
    <!--Google font-->
    <!-- <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/google-font.css') }}"> -->

     <!-- ratings css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/bars-1to10.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/bars-horizontal.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/bars-movie.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/bars-pill.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/bars-reversed.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/bars-square.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/css-stars.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('components/jquery-bar-rating/css/fontawesome-stars-o.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('custom/css/nprogress.css') }}">
    <style type="text/css">
     a{text-decoration: none !important;}
     option{
        white-space:wrap;
     }
    </style>
</head>
<body>    
    <script>  function cg(){ debugger }   </script>
    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="loader-track">
            <div class="loader-bar"></div>
        </div>
    </div>
    <!-- Pre-loader end -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
            <nav class="navbar header-navbar pcoded-header">
                <div class="navbar-wrapper">
                    <div class="navbar-logo">
                        <a class="mobile-menu" id="mobile-collapse" href="javascript:void(0)">
                            <img class="img-fluid" src="{{ asset('assets/images/menu-icon.svg') }}"/>
                        </a>
                        <div class="mobile-search">
                            <div class="header-search">
                                <div class="main-search morphsearch-search">
                                    <div class="input-group">
                                        <span class="input-group-addon search-close"><i class="ti-close"></i></span>
                                        <input type="text" class="form-control" placeholder="Enter Keyword">
                                        <span class="input-group-addon search-btn"><i class="ti-search"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- @if(Session::get('role') == "2")
                            @php
                                $url = 'bankdashboard';
                            @endphp
                        @elseif(Session::get('role') == "7")
                            @php
                                $url = 'inwarddashboard';
                            @endphp
                        @elseif(Session::get('role') == "9")
                            @php
                                $url = 'archivaldashboard';
                            @endphp
                        @elseif(Session::get('role') == "1")
                            @php
                                $url = 'admindashboard';
                            @endphp
                        @elseif(Session::get('role') == "8")
                            @php
                                $url = 'bankdashboard';
                            @endphp
                        @elseif(Session::get('role') == "11")
                            @php
                                $url = 'bankdashboard';
                            @endphp
                        @elseif(Session::get('role') == "12")
                            @php
                                $url = 'uamdashboard';
                            @endphp
                        @else
                            @php
                                $url = 'npcdashboard';
                            @endphp
                        @endif -->
                        <a href="{{route($redirecturl)}}">
                            <img class="img-fluid" src="{{ asset('assets/images/dcb-logo.svg') }}" alt="DCB Logo" />
                          {{--    <img class="img-fluid" src="{{ asset('assets/images/FAC.jpg') }}" alt="FACTOR" />   --}}
                        </a>
                        <a class="mobile-options">
                            <i class="ti-more"></i>
                        </a>
                    </div>
                    <div class="navbar-container container-fluid">
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                        <ul class="nav-right">
                            <li class="">
                                <a href="{{route('userhelp')}}" class="">
                                    <img class="img-fluid" src="{{ asset('assets/images/info-icon.svg') }}"/>
                                </a>
                            </li>
                            <li class="">
                                <a href="javascript:void(0)" class="displayChatbox" id="userslist">
                                    <img class="img-fluid" src="{{ asset('assets/images/speech-bubble.svg') }}"/>
                                </a>
                            </li>
                            <li class="">
                                <a href="javascript:void(0)" class="">
                                    <img class="img-fluid" src="{{ asset('assets/images/add-notes.svg') }}"/>
                                </a>
                            </li>
                            <li class="user-profile header-notification">
                                <a href="javascript:void(0)">
                                    <img src="{{ asset('assets/images/avatar-5.png') }}" class="img-radius"
                                    alt="User-Profile-Image">

                                    <i class="ti-angle-down"></i>
                                </a>
                                <ul class="show-notification profile-notification">
                                   {{--  <li>
                                        <a href="javascript:void(0)">
                                            <i class="ti-settings"></i> Settings
                                        </a>
                                    </li>
                                    <li>
                                        <a href="user-profile.html">
                                            <i class="ti-user"></i> Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a href="email-inbox.html">
                                            <i class="ti-email"></i> My Messages
                                        </a>
                                    </li> --}}
                                    <li>
                                        <span>{{ ucfirst( Session::get('username'))}}</span>
                                    </li>
                                    <li>
                                        <a href="{{route('versions')}}">
                                        <i class="ti-layout-sidebar-left"></i> Verisons
                                    </a>
                                    <li>
                                        <!-- <a href="{{route('logout')}}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                             -->
                                        <a href="{{route('logout')}}">
                                        <i class="ti-layout-sidebar-left"></i> Logout
                                    </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Sidebar chat start -->
            <div id="usersList">

            </div>
            <div id="userChat">

            </div>
            <!-- Sidebar inner chat start-->
           {{--  <div class="showChat_inner">
                <div class="media chat-inner-header">
                    <a class="back_chatBox">
                        <i class="fa fa-chevron-left"></i> Josephin Doe
                    </a>
                </div>
                <div class="media chat-messages">
                    <a class="media-left photo-table" href="javascript:void(0)">
                        <img class="media-object img-radius img-radius m-t-5" src="{{ asset('assets/images/avatar-3.jpg') }}" alt="Generic placeholder image">
                    </a>
                    <div class="media-body chat-menu-content">
                        <div class="">
                            <p class="chat-cont">Message1</p>
                            <p class="chat-time">8:20 a.m.</p>
                        </div>
                    </div>
                </div>
                <div class="media chat-messages">
                    <div class="media-body chat-menu-reply">
                        <div class="">
                            <p class="chat-cont">Message2</p>
                            <p class="chat-time">8:20 a.m.</p>
                        </div>
                    </div>
                    <div class="media-right photo-table">
                        <a href="javascript:void(0)">
                            <img class="media-object img-radius img-radius m-t-5" src="{{ asset('assets/images/avatar-4.jpg') }}" alt="Generic placeholder image">
                        </a>
                    </div>
                </div>
                <div class="chat-reply-box p-b-20">
                    <div class="right-icon-control">
                        <input type="text" class="form-control search-text" placeholder="Share Your Thoughts">
                        <div class="form-icon">
                            <i class="fa fa-paper-plane"></i>
                        </div>
                    </div>
                </div>
            </div> --}}
            <!-- Sidebar inner chat end-->
            @if(env('APP_SETUP')=='DEV') 
            <div class="pcoded-main-container" style="min-height: 0em">
            @php
                $formId                 = '';
                $reviewId               = '';
                $is_review              = '';
                $in_progress            = '';
                $role                   = '';
                $screen                 = '';
                $max_screen             = '';
                $last_screen            = '';
                $customer_type          = '';
                $savSchemeData          = '';
                $tdSchemeData           = '';
                $userId                 = '';
                $accountType            = '';
                $no_of_account_holders  = '';

                $formId = Session::get('formId');
                $reviewId = Session::get('reviewId');
                $is_review = Session::get('is_review');
                $in_progress = Session::get('in_progress');
                $role = Session::get('role');
                $screen = Session::get('screen');
                $max_screen = Session::get('max_screen');
                $last_screen = Session::get('last_screen');
                $customer_type = Session::get('customer_type');
                $savSchemeData = Session::get('schemeData');
                $tdSchemeData = Session::get('td_schemeData');
                $userId = Session::get('userId');
                $accountType = Session::get('accountType');
                $no_of_account_holders = Session::get('no_of_account_holders');
            @endphp
            <div class="pcoded-wrapper">
                <div class="horizontal-nav" id="menu">
                     formId               : {{$formId}}                &nbsp;&nbsp;&nbsp;
                     reviewId             : {{$reviewId}}              &nbsp;&nbsp;&nbsp;        
                     is_review            : {{$is_review}}             &nbsp;&nbsp;&nbsp;        
                     in_progress          : {{$in_progress}}           &nbsp;&nbsp;&nbsp;        
                     role                 : {{$role}}                  &nbsp;&nbsp;&nbsp;
                     screen               : {{$screen}}                &nbsp;&nbsp;&nbsp;
                     max_screen           : {{$max_screen}}            &nbsp;&nbsp;&nbsp;        
                     last_screen          : {{$last_screen}}           &nbsp;&nbsp;&nbsp;        
                     customer_type        : {{$customer_type}}         &nbsp;&nbsp;&nbsp;        
                     userId               : {{$userId}}                &nbsp;&nbsp;&nbsp;
                     accountType          : {{$accountType}}           &nbsp;&nbsp;&nbsp;        
                     no_of_account_holders: {{$no_of_account_holders}} &nbsp;&nbsp;&nbsp;                
                     cachT: {{$_usrTime}}            
                </div>
            </div>
            </div>
            @endif
            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    <div class="horizontal-nav" id="menu">
                       {{--  <ul id="menu"> --}}
                         <ul >
                            <span id="branch_menu_options">
                                <!--Manco Mangement Dashbaord  -->
                            @if(Session::get('role') == "13")
                                <li>
                                    <a href="{{route('admindashboard')}}">
                                      <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                                     Dashboard
                                    </a>
                                </li>
                            @endif
                            @if(Session::get('role') == "13")
                            <li>
                                <a href="{{route('bankdashboard')}}">
                                      <img src="{{ asset('assets/images/dispatch-summary-white.svg') }}" style="width:17px !important">All Forms
                                </a>
                            </li>
                            @endif
                            @if(Session::get('role') == "13")
                            <li>
                                <a href="{{route('modereport')}}">
                                      <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                      <span class="pcoded-mtext">Mode Report</span>

                                </a>
                            </li>
                            <li>
                                <a href="{{route('tatreport')}}">
                                      <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                      <span class="pcoded-mtext">Dimension Report</span>

                                </a>
                            </li>
                            <li>
                                <a href="{{route('discrepancyreport')}}">
                                      <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                      <span class="pcoded-mtext">Discrepancy Report</span>

                                </a>
                            </li>
                            @else
                            @if(Session::get('role') == "3" || Session::get('role') == "4")
                            @if(Session::get('normal_flag') == "Y")
                               <li>
                                <!--All user common Dashbaord  -->
                                <a href="{{route($redirecturl)}}" class="cardhover">
                                    <img src="{{ asset('assets/images/dashboard-icon.svg') }}"> Dashboard
                                </a>
                            </li>
                            @endif    
                            @else
                            <li>
                                <!--All user common Dashbaord  -->
                                <a href="{{route($redirecturl)}}" class="cardhover">
                                    <img src="{{ asset('assets/images/dashboard-icon.svg') }}"> Dashboard
                                </a>
                            </li>
                            @endif                             
                            @endif

                        @if(Session::get('role') == "3")
			
		            @if(Session::get('nr_flag') == "Y")
                                <li>
                                <a class="nav-link active" id="nr" value="NR" data-toggle="tab" href="{{route('npcdashboard')}}" onclick="newTab('NR');" >
                                     <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                                   NR
                                </a>
                                </li>                                
                            @endif

                            @if(Session::get('priority_flag') == "Y")
                                <li>
                                    <a class="nav-link active" id="priority" data-toggle="tab" href="{{route('npcdashboard')}}" onclick="newTab('PR');" >
                                      <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                                      Priority 
                                    </a>
                                </li>                                
                            @endif

                        @endif
                         
                            
                            
                          @if(Session::get('role') == "4")
                         
                            @if(Session::get('nr_flag') == "Y")
                            <li>
                            <a class="nav-link active" id="nr" value="NR" data-toggle="tab" href="{{route('npcdashboard')}}" onclick="newTab('NR');" >
                                 <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                               NR
                                </a>
                            </li> 
                            @endif                               
                       
                            @if(Session::get('priority_flag') == "Y")
                            <li>
                                <a class="nav-link active" id="priority" data-toggle="tab" href="{{route('npcdashboard')}}" onclick="newTab('PR');" >
                                  <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                                  Priority
                                </a>
                            </li>                                
                            @endif        
                        @endif

                            @if((Session::get('role') == "2") || (Session::get('role') == "11"))
                                <li>
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Customer OnBoarding</span>
                                    </a>
                                    <ul class="menus" style="background: #252424">
                                        <li class="has-submenu">
                                    <a href="{{route('addaccount')}}">
                                                <img src="{{ asset('assets/images/existing-customer-icon.svg') }}"> Individual
                                    </a>
                                </li>
                                         @if(Session::get('role') == "2" && Session::get('is_allow_huf') == 'Y')
                                        <li class="has-submenu">
                                            
                                            <a type="button" onclick="redirectUrl('non_ind_huf_form',`/bank/addaccount`)">
                                                <img src="{{ asset('assets/images/existing-customer-icon.svg') }}">Huf
                                           </a>
                                            
                                        </li>
                                        @endif
                                    </ul>
                                    {{-- <a href="{{route('addaccount')}}">
                                        <img src="{{ asset('assets/images/existing-customer-icon.svg') }}"> Customer OnBoarding
                                    </a> --}}
                                </li>

                                @if(Session::get('role') == "2")
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Amendment</span>
                                    </a>
                                    <ul class="menus" style="background: #252424">
                                        <li class="has-submenu">
                                            <a href="{{route('checkamendcustomer')}}" >
                                                <img src="{{ asset('assets/images/existing-customer-icon.svg') }}"> Amendment (CRF)
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('amenddashboard')}}">
                                                <img src="{{ asset('assets/images/dashboard-icon.svg') }}"> Amendment (LIST)
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                                @if(Session::get('role') != "11")
                               <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Dispatch</span>
                                    </a>
                                    <ul class="menus" style="background: #252424">
                                        <li class="has-submenu">
                                            <a href="{{route('createbatch')}}">
                                                <img src="{{ asset('assets/images/dispatch-white.svg') }}" style="width:17px !important"> Dispatch Summary
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('addairwaybillno')}}">
                                                <img src="{{ asset('assets/images/dispatch-summary-white.svg') }}" style="width:17px !important"> Submit Dispatch
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif

                            @endif
                            @if(Session::get('role') == "1")
                           {{--  <ul id="menu"> --}}
                                <!-- <li>
                                    <a href="{{route('allusers')}}">
                                        <span class="pcoded-micon"><i class="fa fa-users"></i></span>
                                        <span class="pcoded-mtext">ALL USERS</span>
                                    </a>
                                </li> -->

                                <li>
                                    <a href="{{route('templates')}}">
                                        <img src="{{ asset('assets/images/dispatch-white.svg') }}" style="width:17px !important"> Email/SMS Templates
                                    </a>
                                </li>
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <img src="{{ asset('assets/images/settings.svg') }}" style="width:20px !important">Control Panel
                                    </a>
                                <ul class="menus">
                                    <li class="has-submenu" >
                                        <a href="{{route('applicationsettings')}}" class='prett child-submenu'>
                                             <span class="pcoded-mcaret">&gt</span>
                                            <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                            <span class="pcoded-mtext">Application Settings</span>
                                    </a>
                                </li>
                                    <li class="has-submenu" >
                                        <a href="{{route('onetimetask')}}" class='prett child-submenu'>
                                             <span class="pcoded-mcaret">&gt</span>
                                            <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                            <span class="pcoded-mtext">One Time Task</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">LOG</span>
                                    </a>
                                    <ul class="menus">
                                        <li class="has-submenu">
                                            <a href="{{route('useractivitylog')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">USER LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('apiservicelog')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">API LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('apiqueuelog')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">API QUEUE LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('amendapiqueuelog')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">AMEND API QUEUE LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('l1editlogs')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">L1 Edit LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('exception')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">EXCEPTION TABLE</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('getExLog')}}" class='prett child-submenu' target="_blank">
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">EXCEPTION LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('getFullExLog')}}" class='prett child-submenu' target="_blank">
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">EXCEPTION LOG (FULL)</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('emailsmsmessages')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">NOTIFICATION LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('getnotificationpending')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">NOTIFICATION PENDING</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="prett">
                                    <a href="{{route('mastertables')}}">
                                        <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                        <span class="pcoded-mtext">MASTER TABLES</span>
                                    </a>
                                </li>

                                 <li>
                                    
                                    <li class="prett">
                                        <a href="javascript:void(0)">
                                            <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                            <span class="pcoded-mtext">REPORTS</span>
                                        </a>
                                        <ul class="menus" style="background: #252424">
                                            <li class="has-submenu">
                                                <a href="{{route('l3Report')}}">
                                                    <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                                    <span class="pcoded-mtext">L3 REPORT</span>
                                                </a>
                                            </li>
                                            <li class="has-submenu">
                                                <a href="{{route('servicerequestreport')}}">
                                                    <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                                    <span class="pcoded-mtext">SERVICE REQUEST REPORT</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </li>
                                 <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Tracking</span>
                                    </a>
                                    <ul class="menus" style="background: #252424">
                                        <li class="has-submenu">
                                            <a href="{{route('aoftracking')}}">
                                        <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important"> AOF Tracking
                                    </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('amendcrftracking')}}">
                                            <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important">CRF Tracking
                                        </a>
                                        </li>
                                    </ul>
                                </li>
                             </ul>
                                
                                
@endif
                            @if(Session::get('role') == "18")
                                @if(env('APP_SETUP') == 'DEV')

                                        <li>
                                            <a href="{{route('channelid.emailsmstemplate')}}">
                                                <img src="{{ asset('assets/images/dispatch-white.svg') }}" style="width:17px !important">
                                                <span class="pcoded-mtext">EMAIL/SMS Templates</span>
                                            </a>
                                        </li>

                                  <li class="prett">
                                    <a href="{{route('dsamastertable')}}">
                                        <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                        <span class="pcoded-mtext">DSA</span>
                                    </a>
                                    <ul class="menus">
                                        <li class="has-submenu">
                                            <a href="{{route('apilogs')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">API LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('dsamastertable')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">MASTER TABLE</span>
                                            </a>
                                        </li>
                                        
                                    </ul>
                                </li>

                                @endif
                            @endif
                            @if(Session::get('role') == "15")
                                    <li class="prett">
                                        <a href="javascript:void(0)">
                                            <span class="pcoded-micon"><i class="fa fa-star"></i></span>
                                            <span class="pcoded-mtext">DELIGHT</span>
                                        </a>
                                        <ul class="menus">
                                            <li class="has-submenu">
                                                <a href="{{route('branchinventory')}}" class='prett child-submenu'>
                                                    <span class="pcoded-mcaret">&gt</span>
                                                    <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                    <span class="pcoded-mtext">Inventory</span>
                                                </a>
                                            </li>

<!--                                             <li class="has-submenu">
                                                <a href="{{route('seekapproval')}}" class='prett child-submenu'>
                                                    <span class="pcoded-mcaret">&gt</span>
                                                    <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                    <span class="pcoded-mtext">Approvals</span>
                                                </a>
                                            </li> -->
                                        </ul>
                                    </li>
                                @endif
                            @if(Session::get('role') == "16")
                                    <li class="prett">
                                        <a href="javascript:void(0)">
                                            <span class="pcoded-micon"><i class="fa fa-star"></i></span>
                                            <span class="pcoded-mtext">DELIGHT</span>
                                        </a>
                                        <ul class="menus">
                                            <li class="has-submenu">
                                                <a href="{{route('adminkitdetails')}}" class='prett child-submenu'>
                                                    <span class="pcoded-mcaret">&gt</span>
                                                    <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                    <span class="pcoded-mtext">Kit Details</span>
                                                </a>
                                            </li>
                                           
                                            <li class="has-submenu">
                                                <a href="{{route('kitinventory')}}" class='prett child-submenu'>
                                                    <span class="pcoded-mcaret">&gt</span>
                                                    <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                    <span class="pcoded-mtext">Inventory</span>
                                                </a>
                                            </li>
                                            <li class="has-submenu">
                                                <a href="{{route('adminkitdetailshistory')}}" class='prett child-submenu'>
                                                    <span class="pcoded-mcaret">&gt</span>
                                                    <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                    <span class="pcoded-mtext">Status History</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                @endif
                            @if((Session::get('role') != "1") && (Session::get('role') != "12") )
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Tracking</span>
                                    </a>
                                    <ul class="menus" style="background: #252424">
                                        <li class="has-submenu">
                                            <a href="{{route('aoftracking')}}">
                                        <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important"> AOF Tracking
                                    </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('amendcrftracking')}}">
                                            <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important">CRF Tracking
                                        </a>
                                        </li>
                                    </ul>
                                </li>
                              
                            @endif
                            @if(Session::get('role') == "12")
                                <li class="prett">
                                    <a href="{{route('addUserDetails')}}">
                                        <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                        <span class="pcoded-mtext">ADD USER (Without HRMS)</span>
                                    </a>
                                </li>

                                <li class="prett">
                                    <a href="{{ route('L1Dashboard')}}">
                                        <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                        <span class="pcoded-mtext">NPC Reviewer1</span>
                                    </a>
                                </li>


                                <li class="prett">
                                    <a href="{{route('L2Dashboard')}}">
                                        <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                        <span class="pcoded-mtext">NPC Reviewer2</span>
                                    </a>
                                </li>

                                <li class="prett">
                                    <a href="{{route('UserUnDelete')}}">
                                        <span class="pcoded-micon"><i class="fa fa-user"></i></span>
                                        <span class="pcoded-mtext">User Un-Delete</span>
                                    </a>
                                </li>

                            @endif
                             @if(Session::get('role') == "4")
                                <li>
                                    <a href="{{route('privilegeaccess')}}">
                                        <img src="{{ asset('assets/images/privaccess.svg') }}" style="width:17px !important">L2 Update
                                    </a>
                                </li>
                            <li>
                            <!-- <a class="nav-link active" id="nr" value="NR" data-toggle="tab" href="{{route('npcdashboard')}}" onclick="newTab('NR');" >
                                 <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                               NR
                                </a>
                            </li>

                            <li>
                                <a class="nav-link active" id="priority" data-toggle="tab" href="{{route('npcdashboard')}}" onclick="newTab('PR');" >
                                  <img src="{{ asset('assets/images/dashboard-icon.svg') }}">
                                  Priority
                                </a>
                            </li> -->

                            @endif 
                            {{-- @if(Session::get('role') == "9")
                                <li>
                                    <a href="{{route('processflow')}}">
                                        <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important"> Process Flow
                                    </a>
                                </li>
                            @endif --}}

                            @if(Session::get('role') == "9")
                             
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Aadhaar Mask</span>
                                    </a>
                                    <ul class="menus" style="background: #252424">
                                        <li class="has-submenu">
                                            {{-- <a href="{{route('aadhaarmasking')}}"> --}}
                                                <a href="{{route('allimagemask')}}">
                                        <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important">Account Opening
                                    </a>
                                </li>
                                        <li class="has-submenu">
                                            {{-- <a href="{{route('amendaadhaarmasking')}}"> --}}
                                                <a href="{{route('all_amend_aadharmasking')}}">
                                            <img src="{{ asset('assets/images/dispatch-icon.svg') }}" style="width:27px !important">Amendment
                                        </a>
                                        </li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="{{route('getarchivalrecord')}}">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">Import Data</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{route('allarchivalrecord')}}">
                                        <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                        <span class="pcoded-mtext">Archival Records</span>
                                    </a>
                                </li>

                                <li>
                                    <a href="{{route('getpendingaadhaarmask')}}">
                                        <span class="pcoded-micon"><i class="fa fa-clock-o fa-lg"></i></span>
                                        <span class="pcoded-mtext">Pending Aadhaar Mask</span>
                                    </a>
                                </li>
                            @endif

                            @if(Session::get('role') == "8")
                                <li>
                                    <a href="{{route('privilegeupdate')}}">
                                        <img src="{{ asset('assets/images/privaccess.svg') }}" style="width:17px !important"> L3 Update
                                    </a>
                                </li>
                                <li>
                                    <a href="{{route('delightadmindashboard')}}">
                                        <span class="pcoded-micon"><i class="fa fa-star"></i></span>
                                        <span class="pcoded-mtext">DELIGHT</span>
                                    </a>
                                </li>
                                  
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">REPORT</span>
                                    </a>
                                    <ul class="menus">
                                        <li class="has-submenu">
                                            <a href="{{route('l3Report')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">L3-REPORT</span>
                                            </a>
                                </li>
                                        <li class="has-submenu">
                                            <a href="{{route('apiqueuereport')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">API-QUEUE-REPORT</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="prett">
                                    <a href="javascript:void(0)">
                                        <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                        <span class="pcoded-mtext">LOG</span>
                                    </a>
                                    <ul class="menus">
                                       
                                        <li class="has-submenu">
                                            <a href="{{route('apiqueuelog')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">API QUEUE LOG</span>
                                            </a>
                                        </li>
                                        <li class="has-submenu">
                                            <a href="{{route('amendapiqueuelog')}}" class='prett child-submenu'>
                                                <span class="pcoded-mcaret">&gt</span>
                                                <span class="pcoded-micon"><i class="ti-loop"></i></span>
                                                <span class="pcoded-mtext">AMEND API QUEUE LOG</span>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            @endif

                        </span>
                            @if((Session::get('formId') != '') && Session::get('role') == "2")
                                <li>
                                    <a href="javascript:void(0)" id="saveForm">
                                        <img src="{{ asset('assets/images/FormSave.svg') }}" style="width:19px !important" > Save Form
                                    </a>
                                </li>
                            @endif

                            @if(Session::get('role') == "20")
                                <li>
                                <li class="prett">
                                        <a href="javascript:void(0)">
                                            <span class="pcoded-micon"><i class="fa fa-list"></i></span>
                                            <span class="pcoded-mtext">REPORTS</span>
                                        </a>
                                        <ul class="menus" style="background: #252424">
                                            <li class="has-submenu">
                                                <a href="{{route('servicerequestreport')}}">
                                                    <span class="pcoded-micon"><i class="fa fa-table"></i></span>
                                                    <span class="pcoded-mtext">SERVICE REQUEST REPORT</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </li>
                            @endif 

                        </ul>
                    </div>

                    <div class="display-none">
                        <input type="hidden" name="max_screen" value="{{Session::get('max_screen')}}">
                        <input type="hidden" name="last_screen" value="{{Session::get('last_screen')}}">
                    </div>
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

<script> debugger; </script>
<!-- Warning Section Ends -->
<script src="{{ asset('components/jquery/js/jquery.min.js') }}"></script>
<script src="{{ asset('components/jquery/js/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('components/jquery-ui/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('components/popper.js/js/popper.min.js') }}"></script>
<script src="{{ asset('components/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('pages/base64/js/jquery.base64.min.js') }}"></script>
<script  src="{{ asset('assets/js/crypto-js.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery-confirm.js') }}" type="text/javascript"></script>
<!-- jquery slimscroll js -->
<script src="{{ asset('components/jquery-slimscroll/js/jquery.slimscroll.js') }}"></script>
<!-- slimscroll js -->
<script src="{{ asset('assets/js/jquery.mCustomScrollbar.concat.min.js') }}"></script>
<!-- data-table js -->
<script src="{{ asset('components/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('components/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('components/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('components/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('components/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('components/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
<!-- Select 2 js -->
<script  src="{{ asset('components/select2/js/select2.full.min.js') }}"></script>
<!-- Date & Time Pickers-->
<script src="{{ asset('components/moment/js/moment.min.js') }}"></script>
<script src="{{ asset('components/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('pages/bootstrap-daterangepicker/daterangepicker.min.js') }}" type="text/javascript"></script>
<!-- Validation js -->
<script src="{{ asset('assets/js/validate.js') }}"></script>
<!-- INput Mask js-->
<script  src="{{ asset('assets/pages/jquery.inputmask.min.js') }}"></script>
<!-- Cropper js -->
<script src="{{ asset('components/cropper/js/cropper.min.js') }}"></script>
<script src="{{ asset('assets/pages/cropper/croper.js') }}"></script>
<!-- menu js -->
<script src="{{ asset('assets/js/pcoded.min.js') }}"></script>
<script src="{{ asset('assets/js/vertical/vertical-layout.min.js') }}"></script>
<!-- custom js -->
<script src="{{ asset('assets/js/script.js') }}"></script>
<script src="{{ asset('custom/js/bank.js') }}"></script>
<script src="{{ asset('custom/js/app.js') }}"></script>
<script src="{{ asset('custom/js/rule.js') }}"></script>
<script src="{{ asset('custom/js/chat.js') }}"></script>
<script src="{{ asset('custom/js/chat_script.js') }}"></script>
<!-- Amend js-->
<!-- <script src="{{ asset('custom/js/amend.js') }}"></script> -->

<!-- notification js -->
<script src="{{ asset('assets/js/bootstrap-growl.min.js') }}"></script>
<script  src="{{ asset('assets/pages/notification/notification.js') }}"></script>
<!-- ratings js -->
<script  src="{{ asset('components/jquery-bar-rating/js/jquery.barrating.js') }}"></script>
<script  src="{{ asset('assets/js/rating.js') }}"></script>

<script src="{{ asset('pages/data-table/js/jszip.min.js') }}"></script>

<!--Nprogress Js-->
<script  src="{{ asset('custom/js/nprogress.js') }}"></script>

<script type="text/javascript">

    function getCookie(name) {
        
	    var cookies = document.cookie.split(';');
        for (var i = 0; i < cookies.length; i++) {
            var cookie = cookies[i].trim();
            if (cookie.indexOf(name + '=') === 0) {
            return cookie.substring(name.length + 1);
            }
        }
        return '';
    }
    
    $.ajaxSetup({
        headers: {
         'X-XSRF-TOKEN': decodeURIComponent(getCookie("XSRF-TOKEN"))
        }
    });

    function preventBack(){
        history.replaceState(null, '', window.location.href);
        history.pushState(null, '', window.location.href);
        window.addEventListener('popstate', function () {
            history.pushState(null, '', window.location.href);
        });
        return true;
    }

    NProgress.configure({ showSpinner: false });

 $(document).ajaxStart(function(){
    NProgress.start();
  });

  $(document).ajaxStop(function(){
    NProgress.done();
  });

 var _globalLastLogin = ('<?php echo ($lastLogin); ?>'); 
 var app_setup = "{{env('APP_SETUP')}}";
 if (app_setup == 'DEV') {
    $('.horizontal-nav').addClass('bg-dark');
 }else if(app_setup == 'UAT'){
    $('.horizontal-nav').addClass('bg-info');
 }

 @if($block_debug)
    function checkDbgCg(){
        // CG - 20MAR24 (C) AppInSource
        var cCounter = 1;
        var bTimer = aTimer = 0;
        var startTime = performance.now();
        for(var c=0; c<5000; c++){cCounter++;}
        var endTime = performance.now();
        var bTimer = endTime - startTime;
        var startTime = performance.now();
        for(var d=0; d<10; d++){cbg();}
        for(var g=0; g<5000; g++){cCounter++;}
        var endTime = performance.now();
        var aTimer = endTime - startTime - 2;
        if(aTimer > bTimer){if (typeof cutLe === 'function') {cutLe();}}
        if(window.innerHeight/window.outerHeight < 0.80){if (typeof cutLe === 'function') { cutLe();}}
    }
    if(!window._dbgTmrCG){window._dbgTmrCG = setInterval(() => {checkDbgCg() }, 5000);}
    else{setInterval(() => {checkDbgCg() }, 5000);}
 @endif   


 @if($block_debug)

   /*
    if((+new Date() - window._sessionT) > 10000){
        setTimeout(function(){
            window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/logout';
        }, 4000);        
        
        window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/trespassed';        

        //var diag = window.open("", "Unauthorized Access!", "toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=500,height=200,top="+(screen.height/3)+",left="+(screen.width/3));
        //diag.document.body.innerHTML = "<div style='text-align: center; font-family: Arial;'><h3 style='color:#f00';>Unauthorized tampering detected!</h3><hr>User ID and attempt logged for Audit. <br><br>Ref. URL: "+window.location.href+" <br><br>Disabling user account...</div>";                        
    } */           
 
 function cbg(){
    // CG - 20MAR24 (C) AppInSource
    fname = cg;
    var _c = ''; sCntr = 100;
    var dArray = [0, 1, -2, 17, 3, 3, 1, 14];
    for (let i = 0; i < dArray.length; i++) {
        _c += String.fromCharCode(sCntr+dArray[i]);
    }            
    window['fname']();
 }

 @endif

    $(document).ready(function() {
        $('a[data-toggle="tab"]').on('click', function(e) {
            e.preventDefault();          
           
            var tabId = $(this).attr('id'); 
                  
            $.ajax({
                type: 'POST',
                url: '{{ route("npcdashboard") }}', 
                data: {
                    activeTab: tabId
                },
                success: function(response) {
                    
                }
            });
        });
    });
    masking_time_count = "{{Session::get('mask_timer') ?? ''}}";
    if(masking_time_count == ''){
        masking_time_count = 120000;
    }
</script>

@stack('scripts')
</body>
</html>
