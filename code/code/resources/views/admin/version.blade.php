
@extends('layouts.app')
@section('content')
<div class="pcoded-content1">
    <div class="pcoded-inner-content1">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                  <div class="page-body page-body-top mb-3">
                    <div class="row">
					 <div class="card table-top col-md-12">                                            
                        <div class="card-block table-border-style card-block-padding">
                            <div class="table-responsive">
                            	<table class="table table-custom col-md-8 mx-auto" id="" >
                                    <thead>
                                        <tr>
                                            <th>VERSION NO</th>
                                            <th>VERSION DATE</th>
                                            <th>VERSION DESCRIPTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    		   @foreach($versions as $version)
                                                    <tr>
                                                        <td style="width: 5%;">{{$version->Version_Name}}</td>
                                                        <td style="width: 5%;">{{$version->Version_Date}}</td>
                                                        <td valign="top" style="padding-right: 1; width: 5%;">
                                                            <ol style="padding-right: 56px;">                                               
                                                            @foreach($version->Version_Description as $lineItem)
                                                                <li style="list-style-position: inside; text-align: left; padding-left: 152px;" class="mx-auto" >{{$lineItem}}</li>
                                                            @endforeach
                                                            </ol>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

					                    </div>


@endsection
@push('scripts')
<script type="text/javascript">

     
</script>
@endpush