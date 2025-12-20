
<div style='text-align: center; font-family: Arial; width: 60%; margin-left:20%; margin-top:5%;'>
    <div>
        <br>
        <h3 style='color:#ae2217'>Unauthorized tampering detected!</h3>
        <hr>
        User attempt to tamper page found and logged for Audit. <br><br>
        User: <span style='color:#ae2217'>{{$user}} </span>| Machine IP: <span style='color:#ae2217'> {{$ip}}</span>    <br><br>        
    </div>
</div>  

<script>
    setTimeout(function(){
            window.location = window.location.origin+'/'+window.location.href.split('/')[3]+'/logout';
        }, 4000);
</script>                      

