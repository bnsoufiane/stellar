@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.how_did_you_hear.index') }}">How Did You Hear</a>
            </li>
        </ul>
    </nav>
@stop


@section('content')
    {{ Form::model(NULL, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

    <div class="">
        @foreach($how_did_you_hears as $how_did_you_hear)
            <div class="input-group input-group-lg sepH_a @if ($errors->has('label')) has-error @endif">
                {{Form::hidden('label_ids[]', $how_did_you_hear->id)}}
                {{ Form::text('label[]', $how_did_you_hear->label, array('placeholder' => 'How Did You Hear', 'class' => 'form-control how_did_you_hears_input')) }}
                <a href="javascript:void(0);" class="actions_button add-option add_how_did_you_hear"><i
                            class="icon_plus_alt"></i></a>
                <a href="javascript:void(0);" class="actions_button remove-option remove_how_did_you_hear"><i
                            class="icon_minus_alt"></i></a>
                @if ($errors->has('label'))
                    <span class="help-block">{{ $errors->first('label') }}</span>
                @endif
            </div>
        @endforeach

        <div class="sepH_c text-right"></div>
        <a type="button" class="btn btn-link add_how_did_you_hear">Add a new How Did You Hear</a>

        <div class="form-group sepH_c">
            <button type="submit" class="btn btn-lg btn-primary">Save</button>
            <a href="{{ URL::route('admin.how_did_you_hear.index') }}" class="btn btn-lg btn-default ">Cancel</a>
        </div>
    {{ Form::close() }}
@stop
