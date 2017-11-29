<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <tr>
        <th>Patient Id</th>
        <th>Medicaid ID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Date Of Birth</th>
        <th>Region</th>
        <th>Opt-in date</th>
        <th>Enrolled</th>
        <th>How Did You Hear</th>
        <th>Confirmed</th>
        <th>Patient Reports</th>
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
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]
                    }
                ]
            },

            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null, null, null, null, null, null, null, null
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
                        var _val = '<a href="/admin/patients/' + data[11] + '/edit_patients">' + data[iCol] + '</a>';
                        _val += '<a data_href="/admin/users/' + data[11] + '"class="remove_patient remove-option"><i class="icon_minus_alt"></i></a>';
                        return _val;
                    }
                },
                {
                    "aTargets": [2],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 2;

                        var _val = '<a href="/admin/patients/' + data[11] + '/edit_patients">' + data[iCol] + '</a>';
                        return _val;
                    }
                },
                {
                    "aTargets": [4],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 4;

                        var _val = '<a href="/admin/patients/' + data[11] + '/edit_patients">' + data[iCol] + '</a>';
                        return _val;
                    }
                },
                    @endif
                {
                    "aTargets": [11],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                        $(nTd).css('text-align', 'center');
                    },
                    "mRender": function (data, type, full) {
                        var _val = '<a href="/admin/patients/' + data[11] + '/report" class="btn btn-sm btn-primary" type="button">View Report</a>';
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

        var _href = " <?php echo URL::route('admin.programs.patients_list_csv', array($values['program_id'])) ?> ";
        $('<a class="DTTT_button" id="export_full_patients_list" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop


