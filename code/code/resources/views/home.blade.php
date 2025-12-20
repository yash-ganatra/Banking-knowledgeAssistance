@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif
                    You are logged in!
                    <img class="img-fluid" id="foo" src="{{ asset('uploads/Landing.png') }}" alt="DCB Bank" 
                                                                    width ="500" height ="500"/>
                </div>
                <button onclick="rotateFoo()">rotate</button>
                <div class="card-body" id="responseTableDiv">
                    <table id="userApplicationsTable">
                        <thead>
                            <th>ID</th>
                            <th>USER_NAME</th>
                            <th>APPLICATION_ID</th>
                            <th>SENT_ON</th>
                            <th>STATUS</th>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    function rotateFoo(){
        var angle = ($('#foo').data('angle') + 90) || 90;
        $('#foo').css({'transform': 'rotate(' + angle + 'deg)'});
        $('#foo').data('angle', angle);
    }
</script>
@endpush
