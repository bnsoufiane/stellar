<?php $current_user_id = Sentry::getUser()->id; ?>

@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.users.index') }}">Users</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <a href="{{ URL::route('admin.users.create') }}" class="btn btn-sm btn-primary" type="button">Add a New User</a>
    <br/><br/>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <table id="datatable_users" class="table table-striped table-bordered" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Admin ID</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td><a href="{{ URL::route('admin.users.edit', array($user->id)) }}"
                                   class="">{{$user->last_name}}, {{$user->first_name}}</a></td>
                            <td><a href="{{ URL::route('admin.users.edit', array($user->id)) }}"
                                   class="">{{$user->username}}</a></td>
                            <td>{{$user->role()}}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                            data-toggle="dropdown">
                                        Action <span class="caret"></span>
                                    </button>
                                    <ul role="menu" class="dropdown-menu pull-right">
                                        <li><a href="{{ URL::route('admin.users.edit', array($user->id)) }}">Edit
                                                User</a></li>
                                        @if($current_user_id !== $user->id)
                                        <li><a href="{{ URL::route('admin.users.destroy', array($user->id)) }}"
                                               data-action="remove" id="delete_item_btn">Delete User</a></li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
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
        mytable = $('#datatable_users').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null
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