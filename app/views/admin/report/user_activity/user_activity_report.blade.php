<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">User Activity Report</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <div class="container-fluid program_report user_activity_report">
        <div class="row">
            <div class="col-md-12">

                {{ Form::model($user, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}

                <div class="input-group input-group-lg sepH_a ">
                    {{ Form::label('admins', 'Select User : ', array('class' => 'control-label')) }}
                    {{Form::select('admins', $admins, Input::old('admins'), array('multiple'=>'multiple','name'=>'admins[]','class' => 'form-control select_multiple', 'size'=>10))}}
                    <a href="javascript:void(0);" id="select_all">Select All</a>
                </div>

                <div class="input-group input-group-lg sepH_a date_range_area">
                    {{ Form::label('date_range', 'Date Range : ', array('class' => 'control-label')) }}
                    {{ Form::text('date_range', Input::old('date_range'), array('class' => 'form-control daterange')) }}
                </div>

                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('report_type', 'Report Type : ', array('class' => 'control-label user_activity_report_type')) }}
                    {{ Form::radio('report_type', \Program::USER_ACTIVITY_REPORT_OUTREACH, true) }} Outreach Report<br>
                    {{ Form::radio('report_type', \Program::USER_ACTIVITY_REPORT_INCENTIVE) }} Incentive Report<br>
                    {{ Form::radio('report_type', \Program::USER_ACTIVITY_REPORT_ALL) }} All User Activity Report
                </div>

                <div class="form-group sepH_c view_report_area" style="display: none;">
                    <button type="submit" class="btn btn-lg btn-primary ">View Report</button>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
@stop



@section('scripts')

    <script src="{{asset('assets/lib/date-range-picker/js/moment.min.js')}}"></script>
    <script src="{{asset('assets/lib/date-range-picker/js/jquery.daterangepicker.js')}}"></script>

    <script>

        var daterange = $('.daterange').dateRangePicker({}).bind('datepicker-change', function (event, obj) {
            $('.view_report_area').show();
            //$('input[name="startDate"]').val(obj.date1);
            //$('input[name="endDate"]').val(obj.date2);
        });
    </script>
@stop