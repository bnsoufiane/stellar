<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <tr>
    <tr>
        <th class="text-center">Group ID</th>
        <th class="text-center">Name</th>
        <th class="text-center">Specialty</th>
        <th class="text-center">Phone</th>
        <th class="text-center">Fax</th>
        <th class="text-center">Address</th>
        <th class="text-center">City</th>
        <th class="text-center">State</th>
        <th class="text-center">Zip</th>
        <th class="text-center" style="min-width: 150px;">Doctors</th>
        <th class="text-center" style="min-width: 150px;">Actions</th>
    </tr>
    </tr>
    </thead>
    <tbody>
    </tbody>
</table>

@section('scripts')
    <script src="{{asset('assets/lib/DataTables/media/js/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/media/js/dataTables.bootstrap.js')}}"></script>
    <script src="{{asset('assets/lib/DataTables/extensions/TableTools/js/dataTables.tableTools.min.js')}}"></script>

    <script>
        mytable = $('table').DataTable({
            dom: 'T<"clear">lfrtip',
            tableTools: {
                "sSwfPath": "{{asset('assets/lib/DataTables/extensions/TableTools/swf/copy_csv_xls_pdf.swf')}}",
                "aButtons": [
                    {
                        "sExtends": "csv",
                        "sButtonText": "Export to Excel",
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                ]
            },

            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null, null, null, null, null, null, null
            ],

            "aoColumnDefs": [
                @if($values['isSysAdmin'])
                {
                    "aTargets": [0],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 0;
                        var _val = '<a href="/admin/regions/' + data[10] + '/practice_groups/' + data[9] + '/edit">' + data[iCol] + '</a>';
                        return _val;
                    }
                },
                {
                    "aTargets": [1],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 1;
                        var _val = '<a href="/admin/regions/' + data[10] + '/practice_groups/' + data[9] + '/edit">' + data[iCol] + '</a>';
                        return _val;
                    }
                },
                {
                    "aTargets": [10],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).attr('class', 'text-center');
                    },
                    "mRender": function (data, type, full) {
                        var _val = '<a href="/admin/regions/' + data[10] + '/practice_groups/' + data[9] + '/edit" class="btn btn-sm btn-primary" type="button" style="margin-right: 4px;">Edit</a>';
                        _val += '<a href="/admin/practice_groups/' + data[9] + '" class="btn btn-sm btn-primary" type="button" data-action="remove" id="delete_item_btn">Delete</a>';
                        return _val;
                    }
                },
                    @endif
                {
                    "aTargets": [9],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).attr('class', 'text-center');
                    },
                    "mRender": function (data, type, full) {
                        @if($values['isSysAdmin'])
                        var _val = '<a href="/admin/regions/' + data[10] + '/practice_groups/' + data[9] + '/create_doctor" class="btn btn-sm btn-primary" type="button" style="margin-right: 4px;">Add New</a>';
                        _val += '<a href="/admin/regions/' + data[10] + '/practice_groups/' + data[9] + '/doctors_roster" class="btn btn-sm btn-primary" type="button">View/Edit</a>';
                                @else
                                        var _val = '<a href="/admin/regions/' + data[10] + '/practice_groups/' + data[9] + '/doctors_roster" class="btn btn-sm btn-primary" type="button">View</a>';
                        @endif
                        return _val;
                    }
                }
            ],

        @foreach ($options as $k => $o)
        {{ json_encode($k) }}: {{ json_encode($o) }},
        @endforeach

        @foreach ($callbacks as $k => $o)
        {{ json_encode($k) }}: {{ $o }},
        @endforeach

        })
        ;

        var _href = " <?php echo URL::route('admin.regions.practice_groups_full_list_cvs', array($values['region_id'])) ?> ";
        $('<a class="DTTT_button" id="export_full_patients_list" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop


