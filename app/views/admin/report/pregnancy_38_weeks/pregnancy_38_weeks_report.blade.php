<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Pregnancy Reports</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <div class="container-fluid pregnancy_report">
        <div class="row">
            <div class="col-md-12">

                {{ Form::model($user, array('route' => $route, 'method' => isset($method) ? $method : 'POST')) }}
                <div class="input-group input-group-lg sepH_a insurance_companies_area">
                    {{ Form::label('insurance_company', 'Select Insurance Company : ', array('class' => 'control-label')) }}
                    {{Form::select('insurance_company', $insurance_companies, Input::old('insurance_company'), array('class' => 'form-control'))}}
                </div>


                <div class="input-group input-group-lg sepH_a regions_area">
                    {{ Form::label('region', 'Select Region : ', array('class' => 'control-label')) }}
                    {{Form::select('region', $regions, Input::old('region'), array('class' => 'form-control'))}}
                </div>

                <div class="input-group input-group-lg sepH_a">
                    {{ Form::label('pregnancy_38_week_report_type', 'Report Type : ', array('class' => 'control-label pregnancy_38_report_type')) }}

                    {{ Form::radio('pregnancy_38_week_report_type', \Program::PREGNANCY_38_WEEK_REPORT_All_Visits, true) }}
                    38 Week - All Visits Report<br>
                    {{ Form::radio('pregnancy_38_week_report_type', \Program::PREGNANCY_38_WEEK_REPORT_Date_Only) }}
                    38 Week - Date Only Report<br>

                    {{ Form::radio('pregnancy_38_week_report_type', \Program::PREGNANCY_36_WEEK_REPORT_All_Visits) }}
                    36 Week - All Visits Report<br>
                    {{ Form::radio('pregnancy_38_week_report_type', \Program::PREGNANCY_36_WEEK_REPORT_Date_Only) }}
                    36 Week - Date Only Report<br>
                </div>

                <div class="form-group sepH_c view_report_area">
                    <button type="submit" class="btn btn-lg btn-primary do_not_disable_on_submit">View Report</button>
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
            $('.pregnancy_report_type_area').show();
            //$('input[name="startDate"]').val(obj.date1);
            //$('input[name="endDate"]').val(obj.date2);
        });
    </script>
@stop