@extends('admin.layouts.base')

<?php

$admins_names = '';
foreach ($admins as $admin) {

    if ($admins_names != '') {
        $admins_names .= ', ';
    }

    $admins_names .= $admin->full_name;
}
?>

@section('title'){{"$admins_names, Activity Report"}}@stop

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="#">Report</a></li>
        </ul>
    </nav>
@stop


@section('content')

    <div class="container-fluid export_report_page">
        <div class="row">
            <div class="col-md-12">

                @if($input['report_type'] == \Program::USER_ACTIVITY_REPORT_OUTREACH)
                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_user_activity_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('admins', $admins)
->setCustomValues('input', $input)
->setCustomValues('admins_names', $admins_names)
->render('admin/report/user_activity/show_user_activity_report_outreach_datatable') }}

                @elseif($input['report_type'] == \Program::USER_ACTIVITY_REPORT_INCENTIVE)
                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_user_activity_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('admins', $admins)
->setCustomValues('input', $input)
->setCustomValues('admins_names', $admins_names)
->render('admin/report/user_activity/show_user_activity_report_incentive_datatable') }}

                @else

                    {{ Datatable::table()
->setUrl(route('admin.reports.generate_user_activity_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('admins', $admins)
->setCustomValues('input', $input)
->setCustomValues('admins_names', $admins_names)
->render('admin/report/user_activity/show_user_activity_report_all_datatable') }}


                @endif

            </div>
        </div>
    </div>
@stop

