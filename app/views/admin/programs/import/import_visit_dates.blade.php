@extends('admin.layouts.base_iframe')

@section('content')
    <div id="pp_import">
    <div class="page-head">
        <h4 class="orange_color">Regions: Import Visitr Dates</h4>
    </div>

    <div class="cl-mcont" style="padding: 6px 0 0 0;">

        <div class="block-flat no-padding">
            <div class="content">
                <form role="form">
                    <?php
                    $current_year = date("Y");
                    ?>
                    @if($program->type == \Program::TYPE_POSTPARTUM)
                    @if((isset($pause_data_count)) && ($pause_data_count <> 0))

                  
                    @else
                      <div class="input-group input-group-lg sepH_a">
                        {{ Form::label('date_of_service', 'Date Of Service: ', array('class' => 'control-label nopaddingtop')) }}
                        {{Form::selectRange('date_of_service', ($current_year-2), ($current_year+1), $current_year)}}
                    </div>


                    @endif
                    @else
                     <div class="input-group input-group-lg sepH_a">
                        {{ Form::label('date_of_service', 'Date Of Service: ', array('class' => 'control-label nopaddingtop')) }}
                        {{Form::selectRange('date_of_service', ($current_year-2), ($current_year+1), $current_year)}}
                    </div>
                    @endif

                    @if($program->type == \Program::TYPE_A1C)
                        <div class="input-group input-group-lg sepH_a">
                            {{ Form::label('metric', 'Metric: ', array('class' => 'control-label nopaddingtop')) }}
                            {{Form::select('metric', array(\Program::METRIC_URINE => 'Urine', \Program::METRIC_BLOOD => 'Blood', \Program::METRIC_EYE => 'Eye', \Program::METRIC_BLOOD_AND_URINE => 'Blood & Urine'
                            , \Program::METRIC_URINE_EYE => 'Urine & Eye'
                            , \Program::METRIC_BLOOD_EYE => 'Blood & Eye'
                            , \Program::METRIC_BLOOD_URINE_EYE => 'Blood, Urine, Eye'), Input::old('metric'))}}
                        </div>
                    @endif

     @if(($program->type == \Program::TYPE_POSTPARTUM) && (in_array($program->region_id,$region_array) ))

         <div region_id="{{$region->id}}" class="pp_paused_import" id="pp_paused_import"
                         style="padding: 15px 8px 5px 20px">
                        <div class="btn btn-md btn-warning btn-rad">Click here to proceed paused import!</div>
                        
                    </div>

     @else

      <div class="dropzone import_visit_dates_drop_zone"
                         data-upload-extensions="csv,xls,xlsx"
                         style="padding: 15px 8px 5px 20px">
                        <div class="btn btn-md btn-warning btn-rad">Proceed</div>
                        <input type="hidden" name="imported_file" rv-value="model:imported_file"/>
                    </div>

     @endif
                   
                </form>
                <br/><br/>

                <div class="form-group list-component" id="imported-visit-dates" style="display: none">
               
                @if ($program->type == \Program::TYPE_POSTPARTUM)
                    <button class="btn btn-primary add_imported_visit_dates_post_partum" region_id="{{$region->id}}" program="{{$program->name}}"
                            program_id="{{$program->id}}" type="submit">Add
                        Patients
                    </button>
                    <a class="btn btn-default cancel_importing" name="cancel" value="cancel">Cancel Import</a>
                @else
                 <button class="btn btn-primary add_imported_visit_dates" region_id="{{$region->id}}" program="{{$program->name}}"
                            program_id="{{$program->id}}" type="submit">Add
                        Patients
                    </button>
                    <a class="btn btn-default cancel_importing" name="cancel" value="cancel">Cancel Import</a>

                @endif
                    <br/><br/>

                    <table class="table table-striped table-bordered" width="150%">
                        <thead>
                        <tr>
                            <th>Patient ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Date Of Service</th>
                            <th>Doctor ID</th>
                            <th>Incentive Type</th>
                            <th>Incentive Value</th>
                            <th>Incentive Code</th>
                            <th>Incentive Date</th>
                            <th>Notes</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>
    </div>
    
@stop
