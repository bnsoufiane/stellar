<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <tr>
        <th>PCP ID</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Practice Group</th>
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
                        "mColumns": [0, 1, 2, 3]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1, 2, 3]
                    }
                ]
            },

            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null
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
                        var _val = '<a href="/admin/regions/' + data[6] + '/practice_groups/' + data[5] + '/doctors/' + data[4] + '/edit">' + data[iCol] + '</a>';
                        //_val += '<a data_href="/admin/users/' + data[15] + '"class="remove_patient remove-option"><i class="icon_minus_alt"></i></a>';
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
                        var _val = '<a href="/admin/regions/' + data[6] + '/practice_groups/' + data[5] + '/doctors/' + data[4] + '/edit">' + data[iCol] + '</a>';
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
                        var _val = '<a href="/admin/regions/' + data[6] + '/practice_groups/' + data[5] + '/doctors/' + data[4] + '/edit">' + data[iCol] + '</a>';
                        return _val;
                    }
                }
                @endif
        ],

        @foreach ($options as $k => $o)
        {{ json_encode($k) }}: {{ json_encode($o) }},
        @endforeach

        @foreach ($callbacks as $k => $o)
        {{ json_encode($k) }}: {{ $o }},
        @endforeach

        })
        ;

        var _href = " <?php echo URL::route('admin.regions.all_doctors_csv', array($values['region_id'])) ?> ";
        $('<a class="DTTT_button" id="export_full_patients_list" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop


