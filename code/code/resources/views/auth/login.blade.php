@php
    $versions = config('version.VERSIONS');
    $version = (array) current($versions);
    $versionName = $version['Version_Name'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <title>DCB - CUBE - AIS</title>
    <meta charset="utf-8">
    <meta name="cookie" content="{{mt_rand(100000,999999)}}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />   
    <!-- Favicon icon -->
    <!--<link rel="icon" href="{{ asset('assets/images/favicon.ico') }}" type="image/x-icon">-->
    <link rel="icon" href="{{ asset('assets/images/favicon.png') }}" type="image/png">
    <!-- Google font-->
    <!-- <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600" rel="stylesheet"> -->
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="{{ asset('components/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('custom/css/style-custom.min.css') }}">
</head>
<body class="fix-menu">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4 login-left">
            <div class="login-left-main">
                <div class="login-left-inn">
                                                
                    <div class="logo">
                       <img src="{{ asset('assets/images/dcb-logo.png') }}"> 
                       {{--  <img src="{{ asset('assets/images/factor.png') }}">  --}}   
                    </div>
                    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                        <ol class="carousel-indicators">
                            <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"></li>
                            <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></li>
                            <li data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <h5>Customer</h5>
                                <p style="font-size:0.8rem;">Building a good customer experience does not happen by accident. It happens by design.</p>
                            </div>
                            <div class="carousel-item">
                                <h5>Compliance</h5>
                                <p style="font-size:0.8rem;">Great customer service doesn’t mean customer is always right, it means customer is always honoured.</p>
                            </div>
                            <div class="carousel-item">
                                <h5>Completeness</h5>
                                <p style="font-size:0.8rem;">There are no traffic jams along the extra mile to travel with customer. Go for it!</p>
                            </div> 
                        </div>    
                    </div>
                    <div class="appinsource_footer">
                      
                        <h5>Powered By APPINSOURCE</h5>
                    </div>
                </div>
            </div>
        </div>
          <!-- <div class="col-md-8 pl-0 pr-0 login-right"> -->
        <div class="col-md-8 login-right" style="padding: 0px 0px 0px 0px;">
                                
            @if(isset($is_init))
                <form method="POST" class="login100-form validate-form" action="{{ route('init') }}">
                    @csrf
                    <div class="login-form d-flex align-items-center">
                        <div class="login-form-inn">
                            <h1>{{ __('CMP Initializing Key') }}</h1>
                            <div class="form-group">
                                <label for="init_key">Init Key</label>
                                <input type="password" class="form-control" id="init_key" name="init_key" placeholder="Enter Init Key" required>
                              </div>
                            <button type="submit" class="btn btn-primary">{{ __('Intialize') }}</button>
                        </div>
                    </div>
                </form>
            @else
                <form method="POST" class="login100-form validate-form" id="loginForm" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="login-form d-flex align-items-center">                  
                        
                        <div class="login-form-inn">
                        
                            <div class="cube animation animation--infinite animation--spin">
                              <div class="cube__side cube__side--front">Customer</div>
                              <div class="cube__side cube__side--back">Competent</div>
                              <div class="cube__side cube__side--top">Complete</div>
                              <div class="cube__side cube__side--bottom">Communicate</div>
                              <div class="cube__side cube__side--left">Clarity</div>
                              <div class="cube__side cube__side--right">Compliant</div>
                           </div>
                        
                            <h1>&nbsp;&nbsp; C U<span style='color:lightgrey;'>.</span>B E <span style="font-size: small;">v.{{$versionName}}</span></h1>
                            <h6>Login </h6>
                            <!-- <h1>{{ __('CUBE Login') }}</h1> -->
                            <!-- <div class="form-group"> -->
                                <div class="form-group mb-3">
                                <label for="username">Domain ID</label>
                                <input type="text" class="form-control username my-1" name="username" placeholder="Enter Domain ID" required autocomplete="off" >
                            </div>
                            <!-- <div class="form-group"> -->
                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input type="password" class="form-control my-1" id="password" name="password" placeholder="Enter password" required autocomplete="off">
                            </div>
                            @if($errors->any())
                                <h4 class="errors_login">{{$errors->first()}}</h4>
                            @endif
                            <button type="submit" class="btn btn-primary" id="submit_login">{{ __('Login') }}</button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
    <!-- Required Jquery -->
    <script  src="{{ asset('components/jquery/js/jquery.min.js') }}"></script>
    <script  src="{{ asset('components/popper.js/js/popper.min.js') }}"></script>
    <script  src="{{ asset('components/bootstrap/js/bootstrap.min.js') }}"></script>
    <script  src="{{ asset('assets/js/crypto-js.js') }}"></script>
    <script src="{{ asset('custom/js/util.js') }}"></script>
    <script type="text/javascript">
    // function encrypt(string, key) {
    //     var encryptMethod = "AES-256-CBC";
    //     var encryptMethodLength = parseInt(encryptMethod.match(/\d+/)[0]);
    //     var iv = CryptoJS.lib.WordArray.random(16);// the reason to be 16, please read on `encryptMethod` property.

    //     var salt = CryptoJS.lib.WordArray.random(256);
    //     var iterations = 999;
    //     encryptMethodLength = (encryptMethodLength/4);// example: AES number is 256 / 4 = 64
    //     var hashKey = CryptoJS.PBKDF2(key, salt, {'hasher': CryptoJS.algo.SHA512, 'keySize': (encryptMethodLength/8), 'iterations': iterations});

    //     var encrypted = CryptoJS.AES.encrypt(string, hashKey, {'mode': CryptoJS.mode.CBC, 'iv': iv});
    //     var encryptedString = CryptoJS.enc.Base64.stringify(encrypted.ciphertext);

    //     var output = {
    //         'ciphertext': encryptedString,
    //         'iv': CryptoJS.enc.Hex.stringify(iv),
    //         'salt': CryptoJS.enc.Hex.stringify(salt),
    //         'iterations': iterations
    //     };
    //     return CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(JSON.stringify(output)));
    // }// encrypt

    window.history.forward();

    window.history.pushState(null, document.title, location.href);
    window.addEventListener('popstate', function (event)
    {
        history.pushState(null, document.title, location.href);
    });

    window.history.replaceState(null, null, window.location.href);


    $(document).ready(function() {
        window.history.forward();
        window.history.pushState(null, "", window.location.href);        
        window.onpopstate = function() {
            window.history.pushState(null, "", window.location.href);
        };

        $("body").on("click","#submit_login",function(){
            var password = $("#password").val();
            password = encrypt(password,$('meta[name="cookie"]').attr('content'));
            password += '='+$('meta[name="cookie"]').attr('content');
            password += paddingsalt($('meta[name="cookie"]').attr('content'));
            $("#password").val(password);
            /*console.log(password);
            return false;*/
            $("#loginForm").submit();
        });

        const usr = document.getElementsByClassName('username');
        usr[0].onpaste = e => e.preventDefault();

        const pwd = document.getElementById('password');
        pwd.onpaste = e => e.preventDefault();
    });  

    // function paddingsalt(hash)
    // {
    //     var text = "";
    //     var length = 5;
    //     var possible = "0123456789";
       
    //     for (var i = 0; i < length; i++)
    //         text += possible.charAt(Math.floor(Math.random() * possible.length));
       
    //     var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
       
    //     for (var i = 0; i < length; i++)
    //         text += possible.charAt(Math.floor(Math.random() * possible.length));

    //     return text;
    // }
    </script>

    <script>

     $(document).bind("contextmenu",function(e) {
         e.preventDefault();
        });
        $(document).keydown(function(e){
            if(e.which === 123){
               return false;
            }
       });

    document.onkeydown = function (e) {
        if (event.keyCode == 123) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'i'.charCodeAt(0))) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && (e.keyCode == 'C'.charCodeAt(0) || e.keyCode == 'c'.charCodeAt(0))) {
            return false;
        }
        if (e.ctrlKey && e.shiftKey && (e.keyCode == 'J'.charCodeAt(0) || e.keyCode == 'j'.charCodeAt(0))) {
            return false;
        }
        if (e.ctrlKey && (e.keyCode == 'U'.charCodeAt(0) || e.keyCode == 'u'.charCodeAt(0))) {
            return false;
        }
        if (e.ctrlKey && (e.keyCode == 'S'.charCodeAt(0) || e.keyCode == 's'.charCodeAt(0))) {
            return false;
        }
    }
</script>
</body>
</html>