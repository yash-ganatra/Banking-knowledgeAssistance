<div class="row">
    <div class="col-md-2">
        <p class="lable-cus">Verify Crf</p>
    </div>
    <div class="col-md-3">
        <p class="lable-cus">Customer ID</p>
        <p class="lable-green">{{$masterDetails['customer_id']}}</p>
    </div>
    <div class="col-md-2">
        <p class="lable-cus">CRF Number:</p>
        <p class="lable-green">
                <span> {{$crfNumber}} </span>
        </p>
    </div>
    @if($role == 21 || $role == 22)
        <div class="col-md-2">
            <p class="lable-cus">Review Form Iteration</p>
            <p class="lable-green" id="iteration">{{$reviewIteration}}</p>
        </div>
    @endif  
</div>