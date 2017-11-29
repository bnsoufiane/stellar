<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')

@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>Programs List</li>
        </ul>
    </nav>
@stop


@section('content')
<div id="pp_import">
    <span class="heading_text">{{$insurance_company->name}} / {{$region->name}}</span><br/><br/>
    @if ($user->isSysAdmin())
        <a href="{{ URL::route('admin.regions.create_program', $region_id) }}" class="btn btn-sm btn-primary"
           type="button">Add a New Program</a><br/><br/>
    @endif

    <div class="container-fluid dropdowns_overflow">
        <div class="row">
            <div class="col-md-12">
                <table id="datatable_programs" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>

                        <th>Name</th>
                        <th>Abbreviation</th>
                        <th>Type</th>
                        <th style="width: 50%">Notes</th>
                        <th>Contact Frequency</th>
                        <th>Visit Requirement</th>
                        @if ($user->isSysAdmin())
                            <th>Actions</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($programs as $program)
                        <tr>
                            <td>
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.programs.edit', array($region_id, $program->id)) }}"
                                       class="">{{$program->name}}</a>
                                    <br/>{{number_format($program->patients()->count())}}
                                @else
                                    {{$program->name}}
                                    <br/>{{number_format($program->patients()->count())}}
                                @endif
                            </td>
                            <td>{{$program->abbreviation}}</td>
                            <td>{{$program->type()}}</td>
                            <td>{{$program->notes}}</td>
                            <td>{{$program->contact_frequency()}}</td>
                            <td>{{$program->visit_requirement()}}</td>
                            @if ($user->isSysAdmin())
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                                data-toggle="dropdown">
                                            Action <span class="caret"></span>
                                        </button>
                                        <ul role="menu" class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ URL::route('admin.programs.edit', array($region_id, $program->id)) }}">Edit
                                                    Program</a>
                                            </li>
                                            <li>
                                                <a href="{{ URL::route('admin.programs.destroy', array($program->id)) }}"
                                                   data-action="remove" id="delete_item_btn">Delete Program</a>
                                            </li>
                                            @if(($program->type != \Program::TYPE_PREGNANCY) &&
                                            ($program->type != \Program::TYPE_WC15_AHC) &&
                                            ($program->type != \Program::TYPE_WC15_KF))

   
    @if(($program->type == \Program::TYPE_POSTPARTUM) && (in_array($program->region_id,$region_array) ))
     <li>
                                                    <a class="import_visit_dates" href="javascript:void(0);"
                                                       insurance_company="{{$insurance_company->name}}"
                                                       region="{{$region->name}}" program="{{$program->name}}"
                                                       region_id="{{$region_id}}" program_id="{{$program->id}}">Resume paused import</a>
    </li>
    @else
      <li>
                                                    <a class="import_visit_dates" href="javascript:void(0);"
                                                       insurance_company="{{$insurance_company->name}}"
                                                       region="{{$region->name}}" program="{{$program->name}}"
                                                       region_id="{{$region_id}}" program_id="{{$program->id}}">Import Visit Dates</a>
                                                </li>


    @endif
   
    @endif                                          
                                            <li>
                                                <a href="{{ URL::route('admin.programs.patients_list_csv', array($program->id)) }}">Export
                                                    Patients</a>
                                            </li>
                                            <li>
                                                <a href="{{ URL::route('admin.programs.patients_list', array($program->id)) }}">View
                                                    All Patients</a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
@stop



@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>

    <script>

        mytable = $('#datatable_programs').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null, null
            ],
            dom: 'T<"clear">lfrtip',
            tableTools: {
                "sSwfPath": "{{asset('assets/lib/DataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf')}}",
                "aButtons": [
                    {
                        "sExtends": "csv",
                        "sButtonText": "Export to Excel",
                        "mColumns": [0, 1, 2, 3, 4]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1, 2, 3, 4]
                    }
                ]
            }
        });

    </script>
@stop