@extends('admin.layouts.base')


@section('breadcrumbs')
    <nav id="breadcrumbs">
        <ul>
            <li><a href="{{ URL::route('index') }}">Home</a></li>
            <li><a href="{{ URL::route('admin.insurance_companies.index') }}">Insurance Companies</a></li>
        </ul>
    </nav>
@stop


@section('content')
    <a href="{{ URL::route('admin.insurance_companies.create') }}" class="btn btn-sm btn-primary" type="button">Add a
        New Insurance Company</a><br/><br/>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <table id="datatable_insurance_companies" class="table table-striped table-bordered" cellspacing="0"
                       width="100%">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($insurance_companies as $insurance_company)
                        <tr>
                            <td>
                                <a href="{{ URL::route('admin.insurance_companies.edit', array($insurance_company->id)) }}"
                                   class="">{{$insurance_company->name}}</a></td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle"
                                            data-toggle="dropdown">
                                        Action <span class="caret"></span>
                                    </button>
                                    <ul role="menu" class="dropdown-menu pull-right">
                                        <li>
                                            <a href="{{ URL::route('admin.insurance_companies.edit', array($insurance_company->id)) }}">Edit
                                                Insurance Company</a></li>
                                        <li>
                                            <a href="{{ URL::route('admin.insurance_companies.destroy', array($insurance_company->id)) }}"
                                               data-action="remove" id="delete_item_btn" >Delete Insurance Company</a>
                                        </li>
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
        mytable = $('#datatable_insurance_companies').DataTable({
            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null
            ],
            dom: 'T<"clear">lfrtip',
            tableTools: {
                "sSwfPath": "{{asset('assets/lib/DataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf')}}",
                "aButtons": [
                    {
                        "sExtends": "csv",
                        "sButtonText": "Export to Excel",
                        "mColumns": [0]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0]
                    }
                ]
            }
        });

    </script>
@stop