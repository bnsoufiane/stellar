<?php

namespace Admin;

use View;

class PostpartumimportController extends BaseController
{

    public function resume_import($region_id)
    {
        $this->layout = \View::make('admin.programs.import.pp_import_visit_dates')
            ->with('region_id', $region_id)
            ->with('message', '');

    }

    /**
     * It will save all import CSV data into 'pp_import_csv_data' table and also log into 'pp_import_pause_data'
     */

    public function store_imported_visit_dates_post_partum()
    {


        //decide if any paused import exist
        $input_program = \Input::get('program');
        $program = \DB::table('programs')->where('id', '=', $input_program)->first();
        $region_id = $program->region_id;

        $user_id = \Sentry::getUser()->id;


        $paused_db_count = \DB::table('pp_import_pause_data')
            ->where('region_id', "=", $region_id)
            ->count();

        if ($paused_db_count > 0) //if pause data exist then skip import and process paused
        {
            $this->layout = \View::make('admin.programs.import.pp_import_visit_dates')
                ->with('message', '')
                ->with('region_id', $region_id);

        } else //if no puase data then lets import data get saved into table for processing
        {
            if (strpos(getcwd(), 'public') !== false) {
                $baselink = 'uploads/';
            } else {
                $baselink = 'public/uploads/';

            }

            $filename = $baselink . (\Input::get('file'));


            $date_of_service = \Input::get('date_of_service');


            $insert_data = \DB::table('pp_import_pause_data')->insert(
                array('filename' => $filename,
                    'date_of_service' => $date_of_service,
                    'program' => $input_program,
                    'region_id' => $region_id,
                    'user_id' => $user_id,
                    'row_imported' => 0));

            $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            unset($data[0]);
            $data = array_values($data);

            $rows_count = 10;

            // fetch all usernames for all rows, build an array import_data containing data to be imported
            $import_data = array();
            foreach ($data as $row) {
                $rowData = str_getcsv($row, ",", '"');

                if (count($rowData) == $rows_count && !empty($rowData[0])) {

                    \DB::table('pp_import_csv_data')->insert(
                        array('username' => trim($rowData[0]),
                            'first_name' => trim($rowData[1]),
                            'last_name' => trim($rowData[2]),
                            'actual_visit_date' => trim($rowData[3]),
                            'doctor_id' => trim($rowData[4]),
                            'incentive_type' => trim($rowData[5]),
                            'incentive_value' => trim($rowData[6]),
                            'gift_card_serial' => str_replace(".00", "", trim($rowData[7])),
                            'incentive_date_sent' => trim($rowData[8]),
                            'region_id' => $region_id,
                            'user_id' => $user_id,
                            'visit_notes' => trim($rowData[9])));


                }
            }

            //now lets call view which will eventually process saved data
            $this->layout = \View::make('admin.programs.import.pp_import_visit_dates')
                ->with('region_id', $region_id)
                ->with('message', '');


        }


    }

    /**
     * This function would act on AJAX requests. Save imported records if user opted to save
     */

    public function store_imported_visit_dates_post_partum_ajax($region_id)
    {
        //getting saved pause data
        $pause_data = \DB::table('pp_import_pause_data')
            ->where('region_id', "=", $region_id)
            ->orderBy('id', 'desc')
            ->first();
        $program_input = $pause_data->program;

        $program = \DB::table('programs')->where('id', '=', $program_input)->first();
        $region = \Region::find($program->region_id);


        $date_of_service = $pause_data->date_of_service;
        $metric_type = \Program::METRIC_NULL;

        // calculate first and last date of the year
        $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . $date_of_service));
        $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . $date_of_service));

        //get vital row param from ajax request, would determine record to be updted

        $row_id_import_data = \Input::get('row_id_import_data');
        $row_id_existing_data = \Input::get('row_id_existing_data');
        $user_action = \Input::get('user_action');
        $pp_id = \Input::get('pp_id');


        $import_row = \DB::table('pp_import_csv_data')
            ->where('id', '=', $row_id_import_data)
            ->where('region_id', "=", $region_id)
            ->first();

        $member_found = \DB::table('users')
            ->where('username', '=', $import_row->username)
            ->where('region_id', '=', $region_id)
            ->first();

        if ($member_found) {
            $id_patient = $member_found->id;
        }


        if ($row_id_import_data != 0 && $member_found) {

            $actual_visit_date = \Carbon::parse($import_row->actual_visit_date);
            $actual_visit_date_timestamp = $actual_visit_date->toDateTimeString();

            $incentive_date = \Carbon::parse($import_row->incentive_date_sent);
            $incentive_date_timestamp = $incentive_date->toDateTimeString();

            $updated_at = \Carbon\Carbon::now()->toDateTimeString();

            if ($user_action == 'save') // if user choose to save
            {
                if ($row_id_existing_data != "") {

                    \DB::table('patient_program_visits')
                        ->where('id', '=', $row_id_existing_data)
                        ->update(
                            array('program_id' => $program->id,
                                'actual_visit_date' => $actual_visit_date_timestamp,
                                'incentive_type' => $import_row->incentive_type,
                                'incentive_value' => $import_row->incentive_value,
                                'gift_card_serial' => $import_row->gift_card_serial,
                                'incentive_date_sent' => $incentive_date_timestamp,
                                'doctor_id' => isset($import_row->doctor_id) ? $import_row->doctor_id : null,
                                'metric' => $metric_type,
                                'visit_notes' => $import_row->visit_notes,
                                'created_by' => \Sentry::getUser()->id,
                                'updated_at' => $updated_at));

                    $updated_row = \DB::table('patient_program_visits')
                        ->where('id', '=', $row_id_existing_data)
                        ->first();
                    // dd($updated_row);

                    \DB::table('post_partums')
                        ->where('id', '=', $updated_row->program_instance_id)
                        ->update(array('closed' => 1));
                } else {
                    \DB::table('patient_program_visits')
                        ->insert(
                            array('program_id' => $program->id,
                                'patient_id' => $id_patient,
                                'program_instance_id' => $pp_id,
                                'actual_visit_date' => $actual_visit_date_timestamp,
                                'incentive_type' => $import_row->incentive_type,
                                'incentive_value' => $import_row->incentive_value,
                                'gift_card_serial' => $import_row->gift_card_serial,
                                'incentive_date_sent' => $incentive_date_timestamp,
                                'doctor_id' => isset($import_row->doctor_id) ? $import_row->doctor_id : null,
                                'metric' => $metric_type,
                                'visit_notes' => $import_row->visit_notes,
                                'created_by' => \Sentry::getUser()->id,
                                'updated_at' => $updated_at));

                    \DB::table('post_partums')
                        ->where('id', '=', $pp_id)
                        ->update(array('closed' => 1));
                }

            } else //if user cancel importing row
            {

                $import_row_array = (array)$import_row;
                unset($import_row_array['id']);
                unset($import_row_array['status']);

                $import_row_array['reason'] = "User canceled import for this record";
                \DB::table('pp_import_summary_data')
                    ->insert($import_row_array);

            }

        }

        //now when single record is processed, lets update pause data table


        \DB::table('pp_import_pause_data')
            ->where('id', '=', $pause_data->id)
            ->update(array('row_imported' => $row_id_import_data));


        // if its last row that got imported, lets move to summary report else process next record

        $last_row_csv_data = \DB::table('pp_import_csv_data')
            ->find(\DB::table('pp_import_csv_data')->where('region_id', '=', $region_id)->max('id'));
        if (($last_row_csv_data->id) == $row_id_import_data) {

            $this->generate_summary_report_post_partum_import($region_id);

        } else {

            $this->process_imported_dates_post_partum($region_id);

        }


    }

    /*
    This function will loop through saved CSV data and if valid open post partum found, it will fetch view for further user action else log into summery data
     */


    public function process_imported_dates_post_partum($region_id)
    {
        //getting pause data

        $pause_record = \DB::table('pp_import_pause_data')
            ->where('region_id', '=', $region_id)
            ->orderBy('id', 'desc')
            ->first();
        $remaining_record_count = \DB::table('pp_import_csv_data')
            ->where('region_id', '=', $region_id)
            ->where('id', '>', ($pause_record->row_imported))
            ->count();
        //loop through all remaining data
        for ($i = 0; $i < $remaining_record_count; $i++) {
            $pause_data = \DB::table('pp_import_pause_data')
                ->where('region_id', '=', $region_id)
                ->orderBy('id', 'desc')
                ->first();
            $program_input = $pause_data->program;

            $program = \DB::table('programs')->where('id', '=', $program_input)->first();
            $region = \Region::find($program->region_id);


            $date_of_service = $pause_data->date_of_service;
            $metric_type = \Program::METRIC_NULL;

            $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . $date_of_service));
            $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . $date_of_service));

            $import_row = \DB::table('pp_import_csv_data')
                ->where('region_id', '=', $region_id)
                ->where('id', '=', ($pause_data->row_imported + 1))
                ->first();
            $import_row_array = (array)$import_row;
            unset($import_row_array['id']);
            unset($import_row_array['status']);
            $member_id = $import_row->username;


            //checking if member is valid

            $member_found = \DB::table('users')
                ->where('username', '=', $member_id)
                ->where('region_id', '=', $region_id)
                ->first();
            $total_found_member = count($member_found);

            if ($total_found_member > 0) {
                $id_patient = $member_found->id;

            }

            if ($total_found_member < 1) {

                $import_row_array['reason'] = "Not a valid member that exists";
                $import_row_array['region_id'] = $region_id;

                \DB::table('pp_import_summary_data')
                    ->insert($import_row_array);
                //updat pause data
                \DB::table('pp_import_pause_data')
                    ->where('region_id', '=', $region_id)
                    ->where('row_imported', '=', $pause_data->row_imported)
                    ->update(array('row_imported' => ($pause_data->row_imported + 1)));

            } else {
                //check member for the region reached incentive limit
                if (!$region->no_limits) {
                    // retrive the total incentives for the current yer of service for each user
                    $find_incentive = \DB::select(\DB::Raw("SELECT SUM(incentive_value) as total_incentives
                    FROM patient_program_visits WHERE patient_id = '$id_patient' and gift_card_returned <> 1
                    and actual_visit_date BETWEEN '$first_date_of_year' and '$last_date_of_year'"));
                    $total_earned_incentive = $find_incentive[0]->total_incentives;
                    // dd($find_incentive[0]);
                    if ($total_earned_incentive == NULL) {
                        $total_earned_incentive = 0;
                    }

                    $total_incentives = $total_earned_incentive + ($import_row->incentive_value);
                    //dd($total_incentives);
                    // if incentive limit reached, lets not import data and rather log in summery
                    if ($region->annual_incentive_limit < $total_incentives) {
                        $import_row_array['reason'] = "The incentive value for these patients will take them over their annual limit";
                        $import_row_array['region_id'] = $region_id;

                        \DB::table('pp_import_summary_data')
                            ->insert($import_row_array);
                        //update pause data
                        \DB::table('pp_import_pause_data')
                            ->where('region_id', '=', $region_id)
                            ->where('row_imported', '=', $pause_data->row_imported)
                            ->update(array('row_imported' => ($pause_data->row_imported + 1)));
                        //if no incentive limit reached, lets fetch open pp and send view to user to process record
                    } else {

                        $open_pp = \DB::select(\DB::Raw("SELECT t2.delivery_date as delivery_date, t1.id as id, t2.id as pp_id, t1.scheduled_visit_date as scheduled_visit_date, t1.patient_id as patient_id, IF((t2.delivery_date < CURDATE() - INTERVAL 56 DAY),
               'Expired', 'Not expired') AS expiry FROM post_partums t2 LEFT JOIN  patient_program_visits t1 ON t1.program_instance_id = t2.id and t1.program_id ='$program_input' WHERE t2.patient_id = '$id_patient' and t2.closed <> 1 "));
                        //dd($open_pp);
                        if (count($open_pp) == 0) {
                            $import_row_array['reason'] = "No record found with open PP for this member";
                            $import_row_array['region_id'] = $region_id;
                            \DB::table('pp_import_summary_data')
                                ->insert($import_row_array);
                            //updat pause data
                            \DB::table('pp_import_pause_data')
                                ->where('region_id', '=', $region_id)
                                ->where('row_imported', '=', $pause_data->row_imported)
                                ->update(array('row_imported' => ($pause_data->row_imported + 1)));

                        } else {
                            $this->layout = \View::make('admin.programs.import.post_partum_import_ajax')
                                ->with('open_pp', $open_pp)
                                ->with('region_id', $region_id)
                                ->with('import_row', $import_row);
                            break;

                        }
                    }

                } else {
                    $open_pp = \DB::select(\DB::Raw("SELECT t2.delivery_date as delivery_date, t1.id as id, t2.id as pp_id, t1.scheduled_visit_date as scheduled_visit_date, t1.patient_id as patient_id, IF((t2.delivery_date < CURDATE() - INTERVAL 56 DAY),
               'Expired', 'Not expired') AS expiry FROM post_partums t2 LEFT JOIN  patient_program_visits t1 ON t1.program_instance_id = t2.id and t1.program_id ='$program_input'  WHERE t2.patient_id = '$id_patient' and t2.closed <> 1 "));
                    //dd($open_pp);
                    if (count($open_pp) == 0) {
                        $import_row_array['reason'] = "No record found with open PP for this member";
                        $import_row_array['region_id'] = $region_id;
                        \DB::table('pp_import_summary_data')
                            ->insert($import_row_array);
                        //updat pause data
                        \DB::table('pp_import_pause_data')
                            ->where('region_id', '=', $region_id)
                            ->where('row_imported', '=', $pause_data->row_imported)
                            ->update(array('row_imported' => ($pause_data->row_imported + 1)));

                    } else {
                        $this->layout = \View::make('admin.programs.import.post_partum_import_ajax')
                            ->with('open_pp', $open_pp)
                            ->with('region_id', $region_id)
                            ->with('import_row', $import_row);
                        break;

                    }

                }

            }


            $total_import_rows = \DB::table('pp_import_csv_data')
                ->where('region_id', '=', $region_id)
                ->count();
            $last_row_imported = \DB::table('pp_import_pause_data')
                ->where('region_id', '=', $region_id)
                ->orderBy('id', 'desc')
                ->first();
            //if this was the last record in process then lets move to summery report
            if ($last_row_imported->row_imported == $total_import_rows) {

                $this->generate_summary_report_post_partum_import($region_id);

            }


        }

    }

    /**
     * generate summary report based on logged data
     */

    public function generate_summary_report_post_partum_import($region_id)
    {
        // generate csv
        $result = \DB::table('pp_import_summary_data')
            ->where('region_id', '=', $region_id)
            ->get();

        if (strpos(getcwd(), 'public') !== false) {
            $baselink = 'uploads/';
        } else {
            $baselink = 'public/uploads/';
        }
        $filename = $baselink . uniqid() . ".csv";

        $delimiter = ",";
        $f = fopen("$filename", 'w');

        $line = array('MemberID', 'MemberFirstName', 'MemberLastName', 'DOS', 'Reason why not imported');
        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {

            $line = array("$item->username", "$item->first_name", "$item->last_name",
                "$item->actual_visit_date", "$item->reason"
            );


            fputcsv($f, $line, $delimiter);
        }

        fclose($f);

        $this->layout = \View::make('admin.programs.import.post_partum_import_ajax')
            ->with('finished', true)
            ->with('result_file', str_replace("public/", "", $filename));

        \DB::table('pp_import_pause_data')->where('region_id', '=', $region_id)->delete();
        \DB::table('pp_import_csv_data')->where('region_id', '=', $region_id)->delete();
        \DB::table('pp_import_summary_data')->where('region_id', '=', $region_id)->delete();


    }


}