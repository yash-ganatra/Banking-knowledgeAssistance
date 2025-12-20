@extends('layouts.app')
@section('content')
<style>

/* Center website */
.main {
  max-width: 1000px;
  margin: auto;
}

h1 {
  font-size: 50px;
  word-break: break-all;
}

.row {
  margin: 8px -16px;
}

/* Add padding BETWEEN each column */
.row,
.row > .column {
  padding: 8px;
 
}

/* Create four equal columns that floats next to each other */
.column {
  float: left;
  width: 25%;
}

/* Clear floats after rows */ 
.row:after {
  content: "";
  display: table;
  clear: both;
}

/* Content */
.content {
  background-color: white;
  padding: 10px;
}

.modal-dialog {
      max-width: 800px;
      margin: 30px auto;
  }

.modal-body {
  position:relative;
  padding:0px;
}
.btn-close {
  position:absolute;
  right: 10px;
  top:0;
  z-index:999;
  font-size:2rem;
  font-weight: normal;
  color: #333333;
  opacity:1;
}


/* Image----*/
.image-holder{
    height: 200px;
}


/*Body of the Panel when it expands*/
.panel .panel-body {
    position: relative;
    padding: 0 !important;
    overflow: hidden;
    height: auto;
}

/*Image size and transition*/
.panel .panel-body a img {
    display: block;
    margin: 0;
    width: 100%;
    height: 150px;
    transition: all 0.5s;
    -moz-transition: all 0.5s;
    -webkit-transition: all 0.5s;
    -o-transition: all 0.5s;
}

/*Transform scale effect when you hover over*/
.panel .panel-body a.zoom:hover img {
    transform: scale(1.3);
    -ms-transform: scale(1.3);
    -webkit-transform: scale(1.3);
    -o-transform: scale(1.3);
    -moz-transform: scale(1.3);
}

/*Zoom Button*/
.panel .panel-body a.zoom span.overlay {
    position: absolute;
    top: 0;
    left: 0;
    visibility: hidden;
    height: 100%;
    width: 100%;
    background-color: #000;
    opacity: 0;
    transition: opacity .25s ease-out;
    -moz-transition: opacity .25s ease-out;
    -webkit-transition: opacity .25s ease-out;
    -o-transition: opacity .25s ease-out;
}

/*Zoom Button and Tint Overlay*/
.panel .panel-body a.zoom:hover span.overlay {
    display: block;
    visibility: visible;
    opacity: 0.55;
    -moz-opacity: 0.55;
    -webkit-opacity: 0.55;
    filter: alpha(opacity=65);
    -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=65)";
}

/*Zoom Button*/
.panel .panel-body a.zoom:hover span.overlay i {
    position: absolute;
    top: 45%;
    left: 0%;
    width: 100%;
    font-size: 2.25em;
    color: #fff !important;
    text-align: center;
    opacity: 1;
    -moz-opacity: 1;
    -webkit-opacity: 1;
    filter: alpha(opacity=1);
    -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=1)";
}
.content h4{
  height: 2em;
}
.content p{
  height: 3em;
  margin-top: 2.5em;
}
</style>
<div class="container">    
<div class="row">
   @foreach($items as $item) 
    <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 m-b-25">
        <div class="content panel panel-default info_page_min_height">
            <div class="panel-body">
                <a class="zoom infomodal" data-toggle="modal" data-target="#info_modal" data-title="{{$item->title}}" data-src="{{$item->src}}" data-type="{{$item->type}}">
                    @if($item->type == 'img')
                    <img src="{{$thumbs['imgThumb']}}">
                    @else
                    <img src="{{$thumbs['videoThumb']}}">
                    @endif
                </a>
                <div class="content">
                    <h4>{{$item->title}}</h4>
                    <p>{{$item->description}}</p>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!--Model For image -->
<div id="info_modal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Auditor Workflow</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <!-- <span aria-hidden="true">×</span> -->
                </button>
            </div>
            <div class="modal-body">
                <img class="img-fluid" src="" />
                <video class="embed-responsive-item" src="" id="video"  allowscriptaccess="always" allow="autoplay" controls="controls">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {    
    $('.infomodal').on('click', function(event) {
        var src = $(this).data('src'),
            title = $(this).data('title'),
            type = $(this).data('type'),
            css = {
                'maxWidth': $(window).width() - 100,
                'maxHeight': $(window).height() - 100
            };
        $(".modal-title").html(title);
        if(type == "img"){
            $("#info_modal").find('video').css('display','none');
        }else{
            $("#info_modal").find('img').css('display','none');
        }
        $("#info_modal").find(type).css('display','block');        
        $("#info_modal").find(type).css('width','-webkit-fill-available');        
        $("#info_modal").find(type).attr('src', src);
    });
});
</script>
@endpush