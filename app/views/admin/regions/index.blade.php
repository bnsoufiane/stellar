<?php $user = \User::find(Sentry::getUser()->id); ?>

@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.regions.index') }}">Regions</a></li>
        </ul>
    </nav>
@stop


@section('content')
    @if ($user->isSysAdmin())<a href="{{ URL::route('admin.regions.create') }}" class="btn btn-sm btn-primary"
                                type="button">Add a New Region</a>
    <br/><br/>
    @endif

    <div class="container-fluid dropdowns_overflow">
        <div class="row">
            <div class="col-md-12">
                <table id="datatable_regions" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Insurance Company</th>
                        <th class="text-center">Programs</th>
                        <th class="text-center">Practice Groups</th>
                        <th class="text-center">Patients</th>
                        @if ($user->isSysAdmin())
                            <th class="text-center">Actions</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($regions as $region)
                        <tr>
                            <td>
                                <?php
                                $programs = $region->programs()->get();
                                $programs_str = '';
                                $programs_count = 0;
                                ?>

                                @foreach ($programs as $program)
                                    @if ($programs_str !== '')
                                        <?php $programs_str .= ', '?>
                                    @endif
                                    <?php $programs_str .= $program->name;
                                    $programs_count++;
                                    ?>
                                @endforeach
                                <?php $plural = ($programs_count == 1) ? '' : 's' ?>

                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.regions.edit', array($region->id)) }}"
                                       class="">{{$region->name}}</a>
                                @else
                                    {{$region->name}}
                                @endif

                                <br/>
                                {{"$programs_count program$plural: $programs_str"}}

                            </td>
                            <td>{{$region->insurance_company()->first()->name}}</td>
                            <td class="text-center">
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.regions.create_program', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">Add New</a>
                                    <a href="{{ URL::route('admin.regions.programs_roster', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">View/Edit</a>
                                @else
                                    <a href="{{ URL::route('admin.regions.programs_roster', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">View</a>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.regions.create_practice_group', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">Add New</a>
                                    <a href="{{ URL::route('admin.regions.practice_groups_roster', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">View/Edit</a>
                                @else
                                    <a href="{{ URL::route('admin.regions.practice_groups_roster', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">View</a>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($user->isSysAdmin())
                                    <a href="{{ URL::route('admin.regions.create_patients', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">Add New</a>
                                    <a href="{{ URL::route('admin.regions.patients_roster', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">View/Edit</a>
                                @else
                                    <a href="{{ URL::route('admin.regions.patients_roster', array($region->id)) }}"
                                       class="btn btn-sm btn-primary"
                                       type="button">View</a>
                                @endif
                            </td>
                            @if ($user->isSysAdmin())
                                <td class="text-center">

                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                                data-toggle="dropdown">
                                            Action <span class="caret"></span>
                                        </button>
                                        <ul role="menu" class="dropdown-menu pull-right">
                                            <li>
                                                <a href="{{ URL::route('admin.regions.edit', array($region->id)) }}">Edit
                                                    Region</a>
                                            </li>
                                            <li>
                                                <a href="{{ URL::route('admin.regions.destroy', array($region->id)) }}"
                                                   data-action="remove" id="delete_item_btn">Delete Region</a>
                                            </li>
                                            <li>
                                                <a class="import_doctors" href="javascript:void(0);"
                                                   region="{{$region->name}}" region_id="{{$region->id}}">Import
                                                    Doctors</a>
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
@stop



@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>

    <script>
        mytable = $('#datatable_regions').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null
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