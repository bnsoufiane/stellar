@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.phones.index') }}">Phones</a></li>
        </ul>
    </nav>
@stop


@section('content')
    {{ Form::model(NULL, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

    <div class="">
    @foreach($phones as $phone)
        <div class="input-group input-group-lg sepH_a @if ($errors->has('phone_number')) has-error @endif">
            {{Form::hidden('phone_number_ids[]', $phone->id)}}
            {{ Form::text('phone_number[]', $phone->phone_number, array('placeholder' => 'Phone Number', 'class' => 'form-control phones_input phone_mask')) }}
            <a href="javascript:void(0);" class="actions_button add-option add_phone"><i class="icon_plus_alt"></i></a>
            <a href="javascript:void(0);" class="actions_button remove-option remove_phone"><i class="icon_minus_alt"></i></a>
            @if ($errors->has('phone_number'))
                <span class="help-block">{{ $errors->first('phone_number') }}</span>
            @endif
        </div>
    @endforeach

    <div class="sepH_c text-right"></div>
        <a type="button" class="btn btn-link add_phone">Add a new phone number</a>
        <div class="form-group sepH_c">
        <button type="submit" class="btn btn-lg btn-primary">Save</button>
        <a href="{{ URL::route('admin.phones.index') }}" class="btn btn-lg btn-default ">Cancel</a>
    </div>
    {{ Form::close() }}
@stop
