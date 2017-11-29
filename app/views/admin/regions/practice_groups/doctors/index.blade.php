<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li>{{$insurance_company->name}}</li>
            <li>{{$region->name}}</li>
            <li>{{$practice_group->name}}</li>
            <li>Doctors List</li>
        </ul>
    </nav>
@stop


@section('content')
    @if ($user->isSysAdmin())
        <a href="{{ URL::route('admin.practice_groups.create_doctor', array($region_id, $practice_group_id)) }}"
           class="btn btn-sm btn-primary"
           type="button">Add a New Doctor</a>
        <br/><br/>
    @endif

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <table id="datatable_doctors" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>PCP ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        @if ($user->isSysAdmin())
                            <th class="text-center" style="min-width: 100px;">Actions</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($doctors as $doctor)
                        <tr>
                            <td>
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.doctors.edit', array($region_id, $practice_group_id, $doctor->id)) }}"
                                       class="">{{$doctor->pcp_id}}</a>
                                @else
                                    {{$doctor->pcp_id}}
                                @endif
                            </td>
                            <td>
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.doctors.edit', array($region_id, $practice_group_id, $doctor->id)) }}"
                                       class="">{{$doctor->first_name}}</a>
                                @else
                                    {{$doctor->first_name}}
                                @endif
                            </td>
                            <td>
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.doctors.edit', array($region_id, $practice_group_id, $doctor->id)) }}"
                                       class="">{{$doctor->last_name}}</a>
                                @else
                                    {{$doctor->last_name}}
                                @endif
                            </td>
                            @if ($user->isSysAdmin())
                                <td class="text-center">
                                    <a href="{{ URL::route('admin.doctors.edit', array($region_id, $practice_group_id, $doctor->id)) }}"
                                       class="btn btn-sm btn-primary" type="button">Edit</a>
                                    <a href="{{ URL::route('admin.doctors.destroy', array($doctor->id)) }}"
                                       class="btn btn-sm btn-primary" type="button"
                                       data-action="remove" id="delete_item_btn">Delete</a>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop



@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>

    <script>
        mytable = $('#datatable_doctors').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null
            ],
            dom: 'T<"clear">lfrtip',
            tableTools: {
                "sSwfPath": "{{asset('assets/lib/DataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf')}}",
                "aButtons": [
                    {
                        "sExtends": "csv",
                        "sButtonText": "Export to Excel",
                        "mColumns": [0, 1]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1]
                    }
                ]
            }
        });

    </script>
@stop