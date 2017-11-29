<table id="{{ $id }}" class="{{ $class }}">
    <thead>
    <tr>
        <th>Insurance Company: {{$values['insurance_company']->name}}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th>Region: {{$values['region']->name}}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th>Program: {{$values['program']->name}}</th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
        <th></th>
    </tr>
    <tr>
        <th>Patient Id</th>
        <th>Medicaid ID</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Last Name</th>
        <th>Scheduled Visit Date</th>
        <th>Actual Visit Date</th>
        <th>Incentive Type</th>
        <th>Incentive Code</th>
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
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    {
                        "sExtends": "pdf",
                        "sButtonText": "Export to PDF",
                        "mColumns": [0, 1, 2, 3, 4, 5, 6, 7]
                    }
                ]
            },

            paging: true,
            "iDisplayLength": 25,
            "stateSave": true,
            columns: [
                null, null, null, null, null, null, null, null, null
            ],
            "fnServerParams": function (aoData) {
                aoData.push(
                        {name: "insurance_company", value: {{$values['input']['insurance_company']}}},
                        {name: "region", value: {{$values['input']['region']}}},
                        {name: "program", value: {{$values['input']['program']}}},
                        {name: "date_range", value: "{{$values['input']['date_range']}}"},
                        {name: "kept_appt", value: "{{$values['input']['kept_appt']}}"}
                )
            },
            "aoColumnDefs": [
                {
                    "aTargets": [5],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 5;
                        if (data[iCol] === null) {
                            return 'Not Available';
                        }

                        var date = new Date(data[iCol]);
                        date = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
                        return date;
                    }
                },
                {
                    "aTargets": [6],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 6;
                        if (data[iCol] === null) {
                            return 'Not Available';
                        }

                        var date = new Date(data[iCol]);
                        date = (date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear();
                        return date;
                    }
                },
                {
                    "aTargets": [7],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 7;
                        if (data[iCol] === null) {
                            return 'Not Available';
                        }

                        return data[iCol];
                    }
                },
                {
                    "aTargets": [8],
                    "mData": null,
                    "fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
                    },
                    "mRender": function (data, type, full) {
                        var iCol = 8;
                        if (data[iCol] === null) {
                            return 'Not Available';
                        }

                        return data[iCol];
                    }
                },
            ],


        @foreach ($options as $k => $o)
        {{ json_encode($k) }}: {{ json_encode($o) }},
        @endforeach

        @foreach ($callbacks as $k => $o)
        {{ json_encode($k) }}: {{ $o }},
        @endforeach

        })
        ;

        var data = {
            'insurance_company': "{{$values['input']['insurance_company']}}",
            'region': "{{$values['input']['region']}}",
            'program': "{{$values['input']['program']}}",
            'date_range': "{{$values['input']['date_range']}}",
            'kept_appt': "{{$values['input']['kept_appt']}}"
        }


        var url = [];
        for (var d in data)
            url.push(encodeURIComponent(d) + "=" + encodeURIComponent(data[d]));
        url = url.join("&");

        var _href = " <?php echo URL::route('admin.programs.generate_report_csv') ?>?" + url;
        $('<a class="DTTT_button" id="program_report_csv" href="' + _href + '"><span>Export full list</span></a>').prependTo('.DTTT_container');

    </script>

@stop

