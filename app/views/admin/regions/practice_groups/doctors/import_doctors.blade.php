@extends('admin.layouts.base_iframe')

@section('content')
    <div class="page-head">
        <h4 class="orange_color">Regions: Import Doctors</h4>
    </div>

    <div class="cl-mcont" style="padding: 6px 0 0 0;">

        <div class="block-flat no-padding">
            <div class="content">

                <form role="form">

                    <div class="dropzone import_doctors" data-upload-extensions="csv,xls,xlsx"
                         style="padding: 15px 8px 5px 20px">
                        <div class="btn btn-md btn-success btn-rad">Choose file</div>
                        <input type="hidden" name="imported_file" rv-value="model:imported_file"/>
                    </div>
                </form>
                <br/><br/>

                <div class="import_cancel_buttons" style="display: none;">
                    <button class="btn btn-primary add_imported_doctors" region_id="{{$region->id}}" type="submit">Add
                        Doctors
                    </button>
                    <a class="btn btn-default cancel_importing" name="cancel" value="cancel">Cancel Import</a>
                </div>
                <br/>

                <div class="form-group list-component" id="imported-doctors"
                     style="display: none; overflow: auto !important;">

                    <table class="table table-striped table-bordered" width="150%">
                        <thead>
                        <tr>
                            <th>Group ID</th>
                            <th>Doctor ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>

                    </table>

                    <br/><br/>

                </div>

            </div>
        </div>
    </div>
@stop
