@extends('layouts.app')
@section('content')
<?php 
use App\Helpers\CommonFunctions;
?>
<div class=" app-sett-background row row container-fluid">
  <div class="col-md-12">
    <h4 class="app-sett">Application Settings</h4>
    <div class="row appS-lables ">
      <div class="col-sm-2 appS-col-form-label mb-2">
        <h5>Label</h5>
      </div>
      <div class="col-sm-5">
        <h5>Value</h5>
      </div>
      <div class="col-sm-3">
        <h5>Comments</h5>
      </div>
      <div class="col-sm-1 text-center">
        <h5>Secure</h5>
      </div>
      <div class="col-sm-1">
        <h5>Action</h5>
      </div>
    </div>
    <form id="main" method="post" action="/" novalidate>
      @if(count($applicationSettings) > 0)
        @foreach($applicationSettings as $setting)
          <?php 
            $setting = (array) $setting;
            $type = "text";
            $class="fa-eye";
            $checked = '';
            $value = $setting['field_value'];
            if($setting['secure'] == 1){
              $type = "password";
              $class="fa-eye-slash";
              $checked="checked";
              $value = CommonFunctions::decrypt256($setting['field_value'],CommonFunctions::getrandomIV());
            }
          ?>
          <div class="form-group row">
            <label class="col-sm-2 appS-col-form-label">{{$setting['field_name']}} </label>
            <div class="col-sm-5 icon-text-box">

              <input type="{{$type}}" class="form-control-application" name="{{$setting['field_name']}}_value" id="{{$setting['field_name']}}_value" value="{{$value}}">
              @if($setting['secure'] == 1)
                <span class="fa-eye-main toggle_text" id="{{$setting['field_name']}}" data-placeholder="&#xf023;">
                  <i class="fa {{$class}}" aria-hidden="true"></i>
                </span>
              @endif

            </div>
            <div class="col-sm-3">
              <input type="text" class="form-control-application" name="{{$setting['field_name']}}_comments" id="{{$setting['field_name']}}_comments" value="{{$setting['comments']}}">
            </div>

            <div class="col-sm-1 appS-checkbox">
              <input class="app-checkbox" type="checkbox" name="{{$setting['field_name']}}_secure" id="{{$setting['field_name']}}_secure" {{$checked}}>
            </div>

            <div class="col-sm-1">
              <button type="button" class="btn btn-primary m-b-0 updateSettings" id="{{$setting['field_name']}}">Update</button>
            </div>
          </div>
        @endforeach
      @endif
    </form>
  </div>
</div>
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/admin.js') }}"></script>
@endpush