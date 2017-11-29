@extends('admin.layouts.base')

@section('title'){{"$insurance_company->name, $region->name, $program->name - Program Patient Report"}}@stop

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

                {{ Datatable::table()
->setUrl(route('admin.reports.generate_scheduled_visit_report'))
->setOptions('ordering', false)
->setId('datatable_patients_roster')
->setCustomValues('insurance_company', $insurance_company)
->setCustomValues('region', $region)
->setCustomValues('program', $program)
->setCustomValues('input', $input)
->render('admin/report/scheduled_visits/show_scheduled_visit_report_datatable') }}

            </div>
        </div>
    </div>
@stop

