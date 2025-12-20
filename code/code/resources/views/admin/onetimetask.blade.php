@php
    $activeYearButton = false;
    if(isset($activeYearButton)){
        $activeYearButton = $activeButton;
    }
@endphp
@extends('layouts.app')
@section('content')
<div class = "row">
		<div class="col-sm-12 mx-auto m-3">
      <div class="card">
        <div class="card-body">
            <div class="timeline-item-heading">
                <div class="lable-heading content-blck-1 content-blck-update-privileges-1">ACTIVITY</div>
                <div class="lable-heading content-blck-2 content-blck-update-privileges-2">COMMENTS</div>
                <div class="lable-heading content-blck-3 content-blck-update-privileges-3" >ACTION</div>
            </div>

            <div class="content-blck-1 content-blck-update-privileges-1 content-blck-tl m-2">
                Reset AOF Counter
            </div>

            <div class="content-blck-2 content-blck-update-privileges-2 content-blck-tl col-md-2 m-2">
              <center>
              	<input type = "text" class="form-control resetAofComment" name="resetAofComment" id="resetAofComment">
              </center>
            </div>

            <div class="content-blck-3 content-blck-update-privileges-4 content-blck-tl m-2" style="padding-left: 74px;">
              <center>
                @if($activeYearButton)
	               <button type = "button" id="resetAofCounter" class="btn btn-primary resetAofCounter">Submit</button>
                @else
                  <button type = "button" class="btn btn-primary" disabled="" >Done</button>
                @endif
              </center>
            </div>
       </div>
      </div>
    </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/onetimetask.js') }}"></script>
<script type="text/javascript">

</script>
@endpush

