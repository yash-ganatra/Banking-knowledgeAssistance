@extends('layouts.app')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('custom/css/maker-style.css') }}">
<div id="kit-detail-table-div">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body page-body-top">
                    <div class="row filter drop-down-top filtergrid ">
                        <div class="col-md-3">
                            {!! Form::select('branchId', $branches, '', array('class'=>'form-control branchId','id'=>'branchId',
                                        'name'=>'branchId','placeholder'=>'Select Branch Id')) !!}
                        </div>
                        <div id="inventoryDetails" class="w-100">
                        </div>
                    </div>
                </div>
                <!-- Page-body end -->
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
    <script  src="{{ asset('custom/js/delightadmin.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
           addSelect2('branchId','Branch ID',false);
        });
    </script>
@endpush
