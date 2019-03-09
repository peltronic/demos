@extends('layouts.admin.main')
@section('body-class') @parent()widgets create @stop()

@section('sidebar')
@include('admin.base._minorNav',['routes'=>$minor_nav['routes']])
@endsection

@section('content')
<div class="subcontainer-view">

    <section class="row">
        <article class="col-sm-12">
            <h1>{{$pageheading or "Create New"}}</h1>
        </article>
    </section>

    <section class="row">
        <article class="col-sm-12">
            <div class="" id="form-createWidget">
                {!! Form::model(null,['route' => ['admin.widgets.store'],'method'=>'POST','class'=>'tag-basicForm']) !!}
                @include('admin.widgets._form',['errors'=>$errors])
                {!! Form::close() !!}
            </div>
        </article>
    </section>

</div>

@endsection

@push('blade_inlinejs')
<script type="text/javascript">

$(document).ready(function() {
});

</script>  
@endpush  
