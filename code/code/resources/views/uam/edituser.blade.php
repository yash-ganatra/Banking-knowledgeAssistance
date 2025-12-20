@extends('layouts.app')
@section('content')
<style type="text/css">
  .form-control:disabled:focus, .form-control[readonly]:focus{  border: 1px solid #ccc!important; }
</style>
<div class="pcoded-content">
  <div class="pcoded-inner-content">
    <!-- Main-body start -->
    <div class="main-body mx-1" style=" margin-left: -18%;">
      <div class="page-wrapper">
        <!-- Page body start -->
        <div class="page-body">
          <div class="row">
            <div class="col-sm-12">
              <!-- Basic Inputs Validation start -->
              <div class="card">
                <div class="card-block container">
                  @include('uam.userdetails')
        </div>
    </div>
<!-- Basic Inputs Validation end -->
@endsection
@push('scripts')
<script  src="{{ asset('custom/js/uam.js') }}"></script> 
<script type="text/javascript">
$(document).ready(function(){
  if($('#role').val() == 14){
    $('.role_type_list').removeClass('display-none');
  }
  addSelect2('userRole','Role',false);
  addSelect2('filter_type','Role Type',false);
  addSelect2('regionals','Regionals',false);
  addSelect2('zones','Zones',false);
  addSelect2('clusters','Clusters',false);
  //getUserRoles();
  selectedRole = $(".userRole").val();
});
</script>
@endpush