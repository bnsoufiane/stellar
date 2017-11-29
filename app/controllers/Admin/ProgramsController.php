<?php

namespace Admin;

use View;

class ProgramsController extends BaseController
{

    public function index($region_id)
    {
        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();

        $programs = $region->programs()->get();

        $pause_data = \DB::select(\DB::Raw("SELECT region_id, count(id) AS record_count FROM pp_import_pause_data GROUP BY region_id"));

        $pp_data_array = [];

        foreach ($pause_data as $key => $value) {
            $pp_data_array[$value->region_id] = $value->record_count;
        }
        $region_array = array_keys($pp_data_array);


        $this->layout->content = View::make('admin/programs/index')
            ->with('programs', $programs)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('region_array', $region_array)
            ->with('region_id', $region_id);
    }

    public function create($region_id)
    {
        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();

        $types_array = array(\Program::TYPE_OTHER => 'Other', \Program::TYPE_A1C => 'A1C',
            \Program::TYPE_PREGNANCY => 'Pregnancy', \Program::TYPE_POSTPARTUM => 'Postpartum',
            \Program::TYPE_WC15_AHC => 'WC15-AHC', \Program::TYPE_WC15_KF => 'WC15-KF', \Program::TYPE_FIRST_TRIMESTER => 'First Trimester');
        $periods_table = array(\Program::PER_WEEK => 'Week', \Program::PER_MONTH => 'Month', \Program::PER_YEAR => 'Year');
        $all_practice_groups = $region->practiceGroups()->get();

        $this->layout->content = View::make('admin/programs/create')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('practice_groups', array())
            ->with('all_practice_groups', $all_practice_groups)
            ->with('program', new \Program())
            ->with('region_id', $region_id)
            ->with('types_array', $types_array)
            ->with('periods_table', $periods_table)
            ->with('route', 'admin.programs.store');
    }

    public function store()
    {
        $input = \Input::all();

        $validator = \Program::validate($input);

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.create_program', array($input['region_id']))
                ->withInput()
                ->withErrors($validator);
        } else {
            $region = \Region::find($input['region_id']);
            $program = \Program::create($input);
            $program->region_id = $input['region_id'];
            $program->region()->associate($region);

            if (\Input::get('practice_groups_id') == null) {
                $practice_groups_ids = array();
            } else {
                $practice_groups_ids = \Input::get('practice_groups_id');
            }
            $program->practice_groups()->sync($practice_groups_ids);

            $program->save();

            return \Redirect::route('admin.regions.index')
                ->with('success', 'A program has been successfully created.');
        }
    }

    public function show()
    {
        return 'show';
    }

    public function edit($region_id, $program_id)
    {
        $types_array = array(\Program::TYPE_OTHER => 'Other', \Program::TYPE_A1C => 'A1C',
            \Program::TYPE_PREGNANCY => 'Pregnancy', \Program::TYPE_POSTPARTUM => 'Postpartum',
            \Program::TYPE_WC15_AHC => 'WC15-AHC', \Program::TYPE_WC15_KF => 'WC15-KF', \Program::TYPE_FIRST_TRIMESTER => 'First Trimester');
        $periods_table = array(\Program::PER_WEEK => 'Week', \Program::PER_MONTH => 'Month', \Program::PER_YEAR => 'Year');
        $program = \Program::find($program_id);

        $region = \Region::find($region_id);
        $insurance_company = $region->insurance_company()->first();
        $practice_groups = $program->practice_groups()->get();
        $all_practice_groups = $region->practiceGroups()->get();

        $this->layout->content = View::make('admin/programs/edit')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('practice_groups', $practice_groups)
            ->with('all_practice_groups', $all_practice_groups)
            ->with('program', $program)
            ->with('region_id', $region_id)
            ->with('types_array', $types_array)
            ->with('periods_table', $periods_table)
            ->with('route', array('admin.programs.update', $program_id))
            ->with('method', 'PUT');
    }

    public function update($program_id)
    {
        $input = \Input::all();
        $validator = \Program::validate($input, $program_id);

        if ($validator->fails()) {
            return \Redirect::route('admin.programs.edit', array($input['region_id'], $program_id))
                ->withInput()
                ->withErrors($validator);
        } else {
            $program = \Program::find($program_id);
            $program->fill($input);


            if (\Input::get('practice_groups_id') == null) {
                $practice_groups_ids = array();
            } else {
                $practice_groups_ids = \Input::get('practice_groups_id');
            }
            $program->practice_groups()->sync($practice_groups_ids);

            $program->save();

            return \Redirect::route('admin.regions.programs_roster', $input['region_id'])
                ->with('success', 'A program has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $program = \Program::find($id);

        if (!$program) {
            \App::abort(404);
        }
        \DB::table('patient_program')
            ->where('program_id', '=', $id)
            ->delete();
        $program->delete();

        return array('ok' => 1);
    }

    public function program_reports()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_regions_as_key_value_array();

        $programs = array();
        foreach ($regions as $key => $value) {
            $region = \Region::find($key);
            $programs = $region->get_programs_as_key_value_array();

            break;
        }

        $this->layout->content = View::make('admin/report/programs/program_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.programs.generate_report')
            ->with('method', 'GET');
    }

    public function generate_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        if ($input["kept_appt"] == 'y') {
            $result = \DB::table('patient_program_visits')->where('program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->whereBetween(\DB::raw('CAST(actual_visit_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial');

        } else {
            $first_date_of_year = date('Y-01-01 00:00:00', strtotime($date_ranges[1]));
            $last_date_of_year = date('Y-12-31 23:59:59', strtotime($date_ranges[1]));

            $result = \DB::table('users')
                ->join('patient_program', 'users.id', '=', 'patient_program.patient_id')
                ->leftJoin('patient_program_visits', 'users.id', '=', 'patient_program_visits.patient_id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereRaw("( scheduled_visit_date is null or CAST(scheduled_visit_date AS DATE) BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "' )")
                ->whereRaw("( actual_visit_date is null or actual_visit_date not BETWEEN '" . $first_date_of_year . "' and '" . $last_date_of_year . "' )")
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'actual_visit_date', 'scheduled_visit_date', 'incentive_type', 'gift_card_serial');
        }

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('scheduled_visit_date', function ($model) use (&$isSysAdmin) {
                    return \Helpers::format_date_display($model->scheduled_visit_date);
                })
                ->addColumn('actual_visit_date', function ($model) use (&$isSysAdmin) {
                    return \Helpers::format_date_display($model->actual_visit_date);
                })
                ->showColumns('incentive_type', 'gift_card_serial')
                ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->orderColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')
                ->make();
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $this->layout->content = View::make('admin/report/programs/show_program_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_report_csv()
    {

        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        if ($input["kept_appt"] == 'y') {
            $result = \DB::table('patient_program_visits')->where('program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->whereBetween('actual_visit_date', array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')
                ->get();

        } else {
            $first_date_of_year = date('Y-01-01 00:00:00', strtotime($date_ranges[1]));
            $last_date_of_year = date('Y-12-31 23:59:59', strtotime($date_ranges[1]));

            $result = \DB::table('users')
                ->join('patient_program', 'users.id', '=', 'patient_program.patient_id')
                ->leftJoin('patient_program_visits', 'users.id', '=', 'patient_program_visits.patient_id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereRaw("( scheduled_visit_date is null or CAST(scheduled_visit_date AS DATE) BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "' )")
                ->whereRaw("( actual_visit_date is null or actual_visit_date not BETWEEN '" . $first_date_of_year . "' and '" . $last_date_of_year . "' )")
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'scheduled_visit_date', 'actual_visit_date', 'incentive_type', 'gift_card_serial')->get();
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation Patient has " . ($input["kept_appt"] == 'y' ? '' : 'not ') . "kept appointment " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Scheduled Visit Date', 'Actual Visit Date', 'Incentive Type', 'Incentive Code');
        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->scheduled_visit_date = ($item->scheduled_visit_date !== null) ? date_format(date_create($item->scheduled_visit_date), 'm/d/Y') : 'Not Available';
            $item->actual_visit_date = ($item->actual_visit_date !== null) ? date_format(date_create($item->actual_visit_date), 'm/d/Y') : 'Not Available';
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';

            $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->scheduled_visit_date", "$item->actual_visit_date", "$item->incentive_type", "$item->gift_card_serial");

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patient_visits($patient_id, $program_id, $program_instance_id = 0)
    {
        $visit_date_verified_obj = \VisitDateVerified::all();
        $visit_date_verified = [];
        foreach ($visit_date_verified_obj as $item) {
            $visit_date_verified[$item->id] = $item->title;
        }
        $visit_date_verified = array(null => "--Select A value--") + $visit_date_verified;

        $member_completed_required_visit_dates_obj = \MemberCompletedRequiredVisitDate::all();
        $member_completed_required_visit_dates = [];
        foreach ($member_completed_required_visit_dates_obj as $item) {
            $member_completed_required_visit_dates[$item->id] = $item->title;
        }
        $member_completed_required_visit_dates = array(null => "--Select A value--") + $member_completed_required_visit_dates;


        $how_did_you_hear_obj = \HowDidYouHear::all();
        $how_did_you_hear = [];
        foreach ($how_did_you_hear_obj as $item) {
            $how_did_you_hear[$item->id] = $item->label;
        }
        $how_did_you_hear = array(null => "--Select A value--") + $how_did_you_hear;

        //$region = \Region::find($region_id);
        $patient = \User::find($patient_id);
        $program = \Program::find($program_id);
        $patient_program = $program->patient_program($patient_id);


        if ($program->type == \Program::TYPE_PREGNANCY) {

            if ($program_instance_id != 0) {
                $program_instance = \DB::table('pregnancies')
                    ->where('id', '=', $program_instance_id)
                    ->first();
            } else {
                $program_instance = \DB::table('pregnancies')
                    ->where('patient_id', '=', $patient_id)
                    ->where('program_id', '=', $program->id)
                    ->where('open', '=', true)
                    ->orderBy('created_at', "desc")
                    ->first();
            }

            $last_closed_pregnancy = \DB::table('pregnancies')
                ->where('patient_id', '=', $patient_id)
                ->where('program_id', '=', $program->id)
                ->where('open', '=', false)
                ->orderBy('created_at', "desc")
                ->first();

            $previous_pregnancies = \DB::table('pregnancies')
                ->where('patient_id', '=', $patient_id)
                ->where('program_id', '=', $program->id)
                ->where('open', '=', false)
                ->orderBy('created_at', "desc")
                ->get();

        } else if ($program->type == \Program::TYPE_FIRST_TRIMESTER) {
            $program_instance = \DB::table('first_trimesters')
                ->where('patient_id', '=', $patient_id)
                ->where('program_id', '=', $program->id)
                ->where('open', '=', true)
                ->orderBy('created_at', "desc")
                ->first();
        } else if ($program->type == \Program::TYPE_POSTPARTUM) {
            $post_partum_instances = \DB::table('post_partums')
                ->where('patient_id', '=', $patient_id)
                ->where('program_id', '=', $program->id)
                ->orderBy('created_at', "desc")
                ->orderBy('delivery_date', "desc")
                ->get();

            if ($program_instance_id != 0) {
                $program_instance = \DB::table('post_partums')
                    ->where('id', '=', $program_instance_id)
                    ->first();

            } else {
                if (count($post_partum_instances) != 0) {
                    $program_instance = $post_partum_instances[0];
                }
            }
        }

        //$program->patient_notes = $program->patient_notes($patient_id)->patient_notes;
        $program->patient_notes = $patient_program->patient_notes;
        $program->date_added = \Helpers::format_date_display($patient_program->date_added);
        $program->due_date = \Helpers::format_date_display($patient_program->due_date);
        $program->delivery_date = \Helpers::format_date_display($patient_program->delivery_date);
        if (($program->type == \Program::TYPE_WC15_AHC) || ($program->type == \Program::TYPE_WC15_KF)) {
            $program->delivery_date = \Helpers::format_date_display($patient->date_of_birth);
        }
        $program->postpartum_start = \Helpers::format_date_display($patient_program->postpartum_start);
        $program->postpartum_end = \Helpers::format_date_display($patient_program->postpartum_end);
        $program->birth_weight = $patient_program->birth_weight;
        $program->pediatrician_id = $patient_program->pediatrician_id;
        $program->confirmed = $patient_program->confirmed;
        $program->mother_k2yc = $patient_program->mother_k2yc;
        $program->discontinue = $patient_program->discontinue;
        $program->discontinue_reason = $patient_program->discontinue_reason_id;
        $program->discontinue_date = \Helpers::format_date_display($patient_program->discontinue_date);
        $program->gestational_age = $patient_program->gestational_age;
        $program->enrolled_by = $patient_program->enrolled_by;
        $program->member_completed_required_visit_dates = $patient_program->member_completed_required_visit_dates;
        $program->how_did_you_hear = $patient_program->how_did_you_hear;
        $program->primary_insurance = $patient_program->primary_insurance;

        if (($program->type == \Program::TYPE_PREGNANCY || $program->type == \Program::TYPE_FIRST_TRIMESTER) && $program_instance != null) {
            $program->open = $program_instance->open;
            $program->patient_notes = $program_instance->patient_notes;
            $program->date_added = \Helpers::format_date_display($program_instance->date_added);
            $program->due_date = \Helpers::format_date_display($program_instance->due_date);
            $program->discontinue = $program_instance->discontinue;
            $program->discontinue_reason = $program_instance->discontinue_reason_id;
            $program->discontinue_date = \Helpers::format_date_display($program_instance->discontinue_date);
        }

        if ($program->type == \Program::TYPE_PREGNANCY && $program_instance != null) {
            $program->enrolled_by = $program_instance->enrolled_by;
            $program->eligible_for_gift_incentive = $program_instance->eligible_for_gift_incentive;
            $program->eligible_date = \Helpers::format_date_display($program_instance->eligible_date);
            $program->member_completed_required_visit_dates = $program_instance->member_completed_required_visit_dates;
            $program->cribs_quantity = $program_instance->cribs_quantity;
            $program->eligibility_notes = $program_instance->eligibility_notes;
            $program->delivery_date = \Helpers::format_date_display($program_instance->delivery_date);
            $program->birth_weight = $program_instance->birth_weight;
            $program->pediatrician_id = $program_instance->pediatrician_id;
            $program->gestational_age = $program_instance->gestational_age;
            $program->primary_insurance = $program_instance->primary_insurance;
            $program->confirmed = $program_instance->confirmed;

        } else if ($program->type == \Program::TYPE_FIRST_TRIMESTER && $program_instance != null) {
            $program->enrolled_by = $program_instance->enrolled_by;
            $program->first_trimester_start = \Helpers::format_date_display($program_instance->first_trimester_start);
            $program->first_trimester_end = \Helpers::format_date_display($program_instance->first_trimester_end);
            $program->how_did_you_hear = $program_instance->how_did_you_hear;

            $program->postpartum_start = $program->first_trimester_start;
            $program->postpartum_end = $program->first_trimester_end;
        }

        if ($program->type == \Program::TYPE_POSTPARTUM && isset($program_instance) && $program_instance != null) {
            $program->program_instance_id = $program_instance->id;
            $program->patient_notes = $program_instance->patient_notes;
            $program->delivery_date = \Helpers::format_date_display($program_instance->delivery_date);
            $program->birth_weight = $program_instance->birth_weight;
            $program->gestational_age = $program_instance->gestational_age;
            $program->pediatrician_id = $program_instance->pediatrician_id;
            $program->postpartum_start = \Helpers::format_date_display($program_instance->postpartum_start);
            $program->postpartum_end = \Helpers::format_date_display($program_instance->postpartum_end);
        }


        $previous_contacts = $patient->previous_contacts($program->id, !empty($program_instance) ? $program_instance->id : false);
        $actual_visits = $patient->actual_visits($program->id, !empty($program_instance) ? $program_instance->id : false);
        $manual_outreaches = $patient->manual_outreaches($program->id, !empty($program_instance) ? $program_instance->id : false);

        $outreach_codes = \OutreachCode::orderBy('code_name')->get()->lists('code_name', 'id');
        $outreach_codes = array("0" => "--Select A value--") + $outreach_codes;

        $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . date('Y')));
        $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . date('Y')));

        $scheduled_visit_date_for_current_year = \DB::table('patient_program_visits')
            ->where('patient_id', '=', $patient_id)
            ->where('program_id', '=', $program_id)
            ->whereBetween('scheduled_visit_date', array($first_date_of_year, $last_date_of_year))
            ->first();

        if ($scheduled_visit_date_for_current_year != null) {
            $program->scheduled_visit_date = \Helpers::format_date_display($scheduled_visit_date_for_current_year->scheduled_visit_date);
            $program->scheduled_visit_date_notes = $scheduled_visit_date_for_current_year->scheduled_visit_date_notes;
        }

        if (($program->type == \Program::TYPE_WC15_AHC) || ($program->type == \Program::TYPE_WC15_KF)) {
            $discontinue_tracking_reasons_obj = \DiscontinueTrackingWC15Reason::all();
        } else {
            $discontinue_tracking_reasons_obj = \DiscontinueTrackingReason::all();
        }

        $discontinue_tracking_reasons = [];
        foreach ($discontinue_tracking_reasons_obj as $discontinue_tracking_reason) {
            $discontinue_tracking_reasons[$discontinue_tracking_reason->id] = $discontinue_tracking_reason->reason;
        }

        if ($program->type == \Program::TYPE_PREGNANCY || $program->type == \Program::TYPE_POSTPARTUM) {
            $total_incentives = \DB::select(\DB::Raw("SELECT SUM(incentive_value) as total_incentives
FROM patient_program_visits join users on users.id = patient_program_visits.patient_id
WHERE gift_card_returned <> 1 and actual_visit_date BETWEEN '$first_date_of_year' and '$last_date_of_year'
    and patient_id = " . $patient_id . " and incentive_value is not null GROUP by patient_id"));

        } else {
            $total_incentives = \DB::select(\DB::Raw("SELECT SUM(incentive_value) as total_incentives
FROM patient_program_visits join users on users.id = patient_program_visits.patient_id
WHERE
    gift_card_returned <> 1
    and actual_visit_date BETWEEN '$first_date_of_year' and '$last_date_of_year' and incentive_value is not null
    and patient_id = " . $patient_id . " GROUP by patient_id"));
        }

        $total_incentives = count($total_incentives) ? $total_incentives[0]->total_incentives : 0;

        if ($program->type == \Program::TYPE_PREGNANCY || $program->type == \Program::TYPE_POSTPARTUM) {
            $incentives_list = \DB::select(\DB::Raw("SELECT name, actual_visit_date, incentive_type, incentive_value, gift_card_serial,
 incentive_date_sent, gift_card_returned, incentive_returned_date, gift_card_returned_notes
FROM patient_program_visits
JOIN programs on programs.id=patient_program_visits.program_id
WHERE patient_id = " . $patient_id . " and actual_visit_date BETWEEN '$first_date_of_year' and '$last_date_of_year' and incentive_value is not null and incentive_date_sent is not null"));
        } else {
            $incentives_list = \DB::select(\DB::Raw("SELECT name, actual_visit_date, incentive_type, incentive_value, gift_card_serial,
 incentive_date_sent, gift_card_returned, incentive_returned_date, gift_card_returned_notes
FROM patient_program_visits
JOIN programs on programs.id=patient_program_visits.program_id
WHERE actual_visit_date BETWEEN '$first_date_of_year' and '$last_date_of_year' and incentive_value is not null and incentive_date_sent is not null
    and patient_id = " . $patient_id));
        }

        if (($program->type == \Program::TYPE_PREGNANCY || $program->type == \Program::TYPE_FIRST_TRIMESTER)
            && (!isset($program_instance) || $program_instance == null)
        ) {
            $program = \Program::find($program_id);
            $actual_visits = array();
        }


        $this->layout->content = View::make('admin/regions/patients/patient_visits')
            //->with('region', $region)
            ->with('patient', $patient)
            ->with('program', $program)
            ->with('discontinue_tracking_reasons', $discontinue_tracking_reasons)
            ->with('visit_date_verified', $visit_date_verified)
            ->with('member_completed_required_visit_dates', $member_completed_required_visit_dates)
            ->with('how_did_you_hear', $how_did_you_hear)
            ->with('previous_contacts', $previous_contacts)
            ->with('actual_visits', $actual_visits)
            ->with('outreach_codes', $outreach_codes)
            ->with('manual_outreaches', $manual_outreaches)
            ->with('total_incentives', $total_incentives)
            ->with('incentives_list', $incentives_list)
            ->with('pregnancy', isset($program_instance) ? $program_instance : null)
            ->with('program_instance', isset($program_instance) ? $program_instance : null)
            ->with('last_closed_pregnancy', isset($last_closed_pregnancy) ? $last_closed_pregnancy : null)
            ->with('previous_pregnancies', isset($previous_pregnancies) ? $previous_pregnancies : null)
            ->with('post_partum_instances', isset($post_partum_instances) ? $post_partum_instances : null)
            ->with('first_trimester', isset($program_instance) ? $program_instance : null)
            ->with('route', 'admin.programs.add_patient_actual_visit')
            ->with('method', 'PUT');
    }

    public function add_new_pregnancy($patient_id, $program_id, $open = true)
    {
        $pregnancy = \DB::table('pregnancies')
            ->where('patient_id', '=', $patient_id)
            ->where('program_id', '=', $program_id)
            ->where('open', '=', true)
            ->orderBy('created_at', "desc")
            ->first();

        if ($pregnancy == null) {
            \DB::table('pregnancies')->insert(
                array('patient_id' => $patient_id, 'program_id' => $program_id,
                    'open' => $open,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
        }

        return \DB::table('pregnancies')
            ->where('patient_id', '=', $patient_id)
            ->where('program_id', '=', $program_id)
            ->where('open', '=', $open)
            ->orderBy('created_at', "desc")
            ->first();

//        return \Redirect::route('admin.programs.patient_visits', array($patient_id, $program_id))
//            ->with('success', 'A new pregnancy has been successfully created.');

    }


    public function add_new_post_partum_instance($patient_id, $program_id, $pregnancy = null)
    {

        if ($pregnancy != null) {
            \DB::table('post_partums')->insert(
                array('patient_id' => $patient_id, 'program_id' => $program_id,
                    'pregnancy_id' => $pregnancy->id,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));

            return \DB::table('post_partums')
                ->where('pregnancy_id', '=', $pregnancy->id)
                ->first();

        } else {
            \DB::table('post_partums')->insert(
                array('patient_id' => $patient_id, 'program_id' => $program_id,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));

//            return \DB::table('post_partums')
//                ->where('patient_id', '=', $patient_id)
//                ->where('program_id', '=', $program_id)
//                ->orderBy('created_at', "desc")
//                ->first();

            return \Redirect::route('admin.programs.patient_visits', array($patient_id, $program_id))
                ->with('success', 'A new Post Partum Instance has been successfully created.');
        }


//        return \Redirect::route('admin.programs.patient_visits', array($patient_id, $program_id))
//            ->with('success', 'A new pregnancy has been successfully created.');

    }

    public function add_new_first_trimester($patient_id, $program_id)
    {
        $pregnancy = \DB::table('pregnancies')
            ->where('patient_id', '=', $patient_id)
            ->orderBy('created_at', "desc")
            ->first();

        $first_trimesters = \DB::table('first_trimesters')
            ->where('patient_id', '=', $patient_id)
            ->where('program_id', '=', $program_id)
            ->where('open', '=', true)
            ->orderBy('created_at', "desc")
            ->first();

        if ($first_trimesters == null) {
            \DB::table('first_trimesters')->insert(
                array('patient_id' => $patient_id, 'program_id' => $program_id,
                    'pregnancy_id' => ($pregnancy != null) ? $pregnancy->id : 0,
                    'open' => true,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
        }

        return \Redirect::route('admin.programs.patient_visits', array($patient_id, $program_id))
            ->with('success', 'A new first trimester instance has been successfully created.');
    }

    public function add_patient_actual_visit()
    {
        $input = \Input::all();
        $program = \Program::where('id', $input['program_id'])->first();

        if ($program->type == \Program::TYPE_PREGNANCY) {

            $this->add_patient_actual_visit_for_pregnancy_program($input, $program, $input['program_instance_id']);

        } else if ($program->type == \Program::TYPE_FIRST_TRIMESTER) {

            $this->add_patient_actual_visit_for_first_trimester_program($input, $program);

        } else if (($program->type == \Program::TYPE_WC15_AHC) || ($program->type == \Program::TYPE_WC15_KF)) {

            $this->add_patient_actual_visit_for_wc15_program($input, $program);

        } else {
            $incentive_save_status = $this->add_patient_actual_visit_for_other_programs($input, $program);
            if ($incentive_save_status == "false") {
                return \Redirect::back()->with('alert', 'Incentive data can not be saved as allowed limit already reached!');
            } else if ($incentive_save_status == "blank") {
                return \Redirect::back()->with('alert', 'Incentive fields are not filled properly');
            }
        }

        if (($program->type == \Program::TYPE_WC15_AHC) || ($program->type == \Program::TYPE_WC15_KF)) {
            return \Redirect::route('admin.programs.patient_visits', array($input['patient_id'], $input['program_id']))
                ->with('success', 'An actual visit date has been successfully added.');
        } else {
            return \Redirect::route('admin.regions.patients_roster', array($program->region->id))
                ->with('success', 'An actual visit date has been successfully added.');
        }
    }

    private function add_patient_actual_visit_for_pregnancy_program($input, $program, $program_instance_id = 0)
    {
        if (empty($input['old_scheduled_visit_ids'])) {
            $input['old_scheduled_visit_ids'] = array();
        }
        if (empty($input['scheduled_visit_ids'])) {
            $input['scheduled_visit_ids'] = array();
        }

        $old_scheduled_visit_ids = $input['old_scheduled_visit_ids'];
        $scheduled_visit_ids = $input['scheduled_visit_ids'];

        $deleted_scheduled_visits = array_diff($old_scheduled_visit_ids, $scheduled_visit_ids);

        if ($program_instance_id != 0) {
            $pregnancy = \DB::table('pregnancies')
                ->where('id', '=', $program_instance_id)
                ->first();
        } else {
            $pregnancy = \DB::table('pregnancies')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('open', '=', true)
                ->orderBy('created_at', "desc")
                ->first();
        }


        if ($pregnancy == null) {
            $pregnancy = $this->add_new_pregnancy($input['patient_id'], $input['program_id'], empty($input['delivery_date']));

        }

        if (!empty($input['delivery_date']) || isset($input['discontinue'])) {

            if (!empty($input['delivery_date'])) {
                $this->assign_member_to_post_partum_program($input, $program, $pregnancy);

                $this->assign_member_to_wc15_program($input, $program, $pregnancy->id);
            }

            $open_pregnancy = \DB::table('pregnancies')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('open', '=', true)
                ->orderBy('created_at', "desc")
                ->first();

            if (!empty($open_pregnancy) && $open_pregnancy->id == $program_instance_id) {
                \DB::table('pregnancies')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])->where('open', '=', true)
                    ->update(array('open' => false));
            }

            \DB::commit();
        }

        try {
            \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                ->update(array('patient_notes' => $input['patient_notes'],
                    'date_added' => \Helpers::format_date_DB($input['date_added']),
                    'due_date' => \Helpers::format_date_DB($input['due_date']),
                    'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                    'birth_weight' => $input['birth_weight'],
                    'pediatrician_id' => $input['pediatrician_id'],
                    'discontinue' => isset($input['discontinue']) ? true : false,
                    'discontinue_reason_id' => $input['discontinue_reason'],
                    'discontinue_date' => \Helpers::format_date_DB($input['discontinue_date']),
                    'gestational_age' => floatval($input['gestational_age']),
                    'enrolled_by' => $input['enrolled_by'],
                    'member_completed_required_visit_dates' => !empty($input['member_completed_required_visit_dates']) ? $input['member_completed_required_visit_dates'] : 0,
                    'how_did_you_hear' => !empty($input['how_did_you_hear']) ? $input['how_did_you_hear'] : 0,
                    'primary_insurance' => isset($input['primary_insurance']) ? true : false,
                    'confirmed' => isset($input['confirmed']) ? true : false,
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ));

            \DB::table('pregnancies')->where('id', '=', $pregnancy->id)
                ->update(array(
                    'patient_notes' => $input['patient_notes'],
                    'date_added' => \Helpers::format_date_DB($input['date_added']),
                    'due_date' => \Helpers::format_date_DB($input['due_date']),
                    'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                    'birth_weight' => $input['birth_weight'],
                    'pediatrician_id' => $input['pediatrician_id'],
                    'discontinue' => isset($input['discontinue']) ? true : false,
                    'discontinue_reason_id' => $input['discontinue_reason'],
                    'discontinue_date' => \Helpers::format_date_DB($input['discontinue_date']),
                    'gestational_age' => floatval($input['gestational_age']),
                    'enrolled_by' => $input['enrolled_by'],
                    'eligible_for_gift_incentive' => isset($input['eligible_for_gift_incentive']) ? true : false,
                    'eligible_date' => \Helpers::format_date_DB($input['eligible_date']),
                    'member_completed_required_visit_dates' => !empty($input['member_completed_required_visit_dates']) ? $input['member_completed_required_visit_dates'] : 0,
                    'cribs_quantity' => $input['cribs_quantity'],
                    'eligibility_notes' => $input['eligibility_notes'],
                    'primary_insurance' => isset($input['primary_insurance']) ? true : false,
                    'confirmed' => isset($input['confirmed']) ? true : false,
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ));

            \DB::commit();
        } catch (\Exception $e) {
            die($e->getMessage());
        }

        if (!empty($input['sign_up'])) {

            $sign_up_record = \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('program_instance_id', '=', $pregnancy->id)
                ->where('sign_up', true)
                ->first();

            if ($sign_up_record) {
                \DB::table('patient_program_visits')
                    ->where('patient_id', '=', $input['patient_id'])
                    ->where('program_id', '=', $input['program_id'])
                    ->where('program_instance_id', '=', $pregnancy->id)
                    ->where('sign_up', true)
                    ->update(
                        array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                            'program_instance_id' => $pregnancy->id,
                            'actual_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                            'scheduled_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                            'incentive_type' => $input['sign_up_incentive_type'],
                            'incentive_value' => !empty($input['sign_up_incentive_value']) ? str_replace("$", "", $input['sign_up_incentive_value']) : 0,
                            'gift_card_serial' => $input['sign_up_gift_card_serial'],
                            'incentive_date_sent' => \Helpers::format_date_DB($input['sign_up_incentive_date']),
                            'visit_notes' => $input['sign_up_notes'],
                            'sign_up' => true,
                            'manually_added' => true,
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            } else {
                \DB::table('patient_program_visits')->insert(
                    array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                        'program_instance_id' => $pregnancy->id,
                        'actual_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                        'scheduled_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                        'incentive_type' => $input['sign_up_incentive_type'],
                        'incentive_value' => !empty($input['sign_up_incentive_value']) ? str_replace("$", "", $input['sign_up_incentive_value']) : 0,
                        'gift_card_serial' => $input['sign_up_gift_card_serial'],
                        'incentive_date_sent' => \Helpers::format_date_DB($input['sign_up_incentive_date']),
                        'visit_notes' => $input['sign_up_notes'],
                        'sign_up' => true,
                        'manually_added' => true,
                        'created_by' => \Sentry::getUser()->id,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            }


//            \DB::table('patient_program_visits')
//                ->where('patient_id', '=', $input['patient_id'])
//                ->where('program_id', '=', $input['program_id'])
//                ->where('program_instance_id', '=', $pregnancy->id)
//                ->where('sign_up', true)
//                ->delete();

        }


        //delete "deleted visit ids" from patient_program_visits and manual_outreaches tables
        if (count($deleted_scheduled_visits)) {
            \DB::table('patient_program_visits')
                ->whereIn('id', $deleted_scheduled_visits)
                ->where('sign_up', '<>', true)
                ->delete();

            \DB::table('manual_outreaches')
                ->whereIn('patient_program_visits_id', $deleted_scheduled_visits)
                ->delete();
        }

        $scheduled_visits_rows = $this->construct_scheduled_visits_records($input);

        $this->save_scheduled_visits_records($scheduled_visits_rows, $input, $program->type, $pregnancy);
    }

    private function add_patient_actual_visit_for_first_trimester_program($input, $program)
    {
        $first_trimester = \DB::table('first_trimesters')
            ->where('patient_id', '=', $input['patient_id'])
            ->where('program_id', '=', $input['program_id'])
            ->where('open', '=', true)
            ->orderBy('created_at', "desc")
            ->first();

        if (isset($input['manual_outreach'])) {
            for ($i = 0; $i < count($input['manual_outreach']); $i++) {

                \DB::table('manual_outreaches')->insert(
                    array('patient_id' => $input['patient_id'],
                        'program_id' => $input['program_id'],
                        'program_instance_id' => (!empty($first_trimester)) ? $first_trimester->id : 0,
                        'outreach_date' => \Helpers::format_date_DB($input['manual_outreach_date'][$i]),
                        'outreach_code' => $input['manual_outreach_code'][$i],
                        'outreach_notes' => $input['manual_outreach_notes'][$i],
                        'outreach_metric' => isset($input['manual_outreach_metric'][$i]) ? $input['manual_outreach_metric'][$i] : null,
                        'created_by' => \Sentry::getUser()->id,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                );
            }
        }

        //$postpartum_start = date('Y-m-d', strtotime("+21 days", $input['delivery_date']));
        //$postpartum_end = date('Y-m-d', strtotime("+56 days", $input['delivery_date']));

        try {
            \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                ->update(array(
                    'enrolled_by' => $input['enrolled_by'],
                    'date_added' => \Helpers::format_date_DB($input['date_added']),
                    'due_date' => \Helpers::format_date_DB($input['due_date']),
                    'postpartum_start' => \Helpers::format_date_DB($input['postpartum_start']),
                    'postpartum_end' => \Helpers::format_date_DB($input['postpartum_end']),
                    'discontinue' => isset($input['discontinue']) ? true : false,
                    'discontinue_reason_id' => $input['discontinue_reason'],
                    'discontinue_date' => \Helpers::format_date_DB($input['discontinue_date']),
                    'how_did_you_hear' => !empty($input['how_did_you_hear']) ? $input['how_did_you_hear'] : 0,
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ));

            \DB::table('first_trimesters')->where('id', '=', $first_trimester->id)
                ->update(array(
                    'patient_notes' => $input['patient_notes'],
                    'sign_up' => \Helpers::format_date_DB($input['sign_up']),
                    'enrolled_by' => $input['enrolled_by'],
                    'date_added' => \Helpers::format_date_DB($input['date_added']),
                    'due_date' => \Helpers::format_date_DB($input['due_date']),
                    'first_trimester_start' => \Helpers::format_date_DB($input['postpartum_start']),
                    'first_trimester_end' => \Helpers::format_date_DB($input['postpartum_end']),
                    'discontinue' => isset($input['discontinue']) ? true : false,
                    'discontinue_reason_id' => $input['discontinue_reason'],
                    'discontinue_date' => \Helpers::format_date_DB($input['discontinue_date']),
                    'how_did_you_hear' => !empty($input['how_did_you_hear']) ? $input['how_did_you_hear'] : 0,
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ));

            \DB::commit();

        } catch (\Exception $e) {
            die($e->getMessage());
        }

        if (!empty($input['actual_visit_date'])) {

            $actual_visit_date_for_current_instance = \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('program_instance_id', '=', $first_trimester->id)
                ->first();

            if (count($actual_visit_date_for_current_instance) > 0) {

                \DB::table('patient_program_visits')
                    ->where('patient_id', '=', $input['patient_id'])
                    ->where('program_id', '=', $input['program_id'])
                    ->where('program_instance_id', '=', $first_trimester->id)
                    ->update(
                        array('patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'program_instance_id' => $first_trimester->id,
                            'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                            'visit_date_verified_by' => $input['visit_date_verified_by'],
                            'julian_date' => (int)$input['julian_date'],
                            'doctor_id' => $input['doctor_id'],
                            'incentive_type' => $input['incentive_type'],
                            'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                            'gift_card_serial' => $input['gift_card_serial'],
                            'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                            'visit_notes' => $input['visit_notes'],
                            'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                            'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                            'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                            'manually_added' => isset($input['manually_added']) ? true : false,
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            } else {
                \DB::table('patient_program_visits')->insert(
                    array('patient_id' => $input['patient_id'],
                        'program_id' => $input['program_id'],
                        'program_instance_id' => $first_trimester->id,
                        'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                        'visit_date_verified_by' => $input['visit_date_verified_by'],
                        'julian_date' => (int)$input['julian_date'],
                        'doctor_id' => $input['doctor_id'],
                        'incentive_type' => $input['incentive_type'],
                        'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                        'gift_card_serial' => $input['gift_card_serial'],
                        'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                        'visit_notes' => $input['visit_notes'],
                        'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                        'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                        'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                        'manually_added' => isset($input['manually_added']) ? true : false,
                        'created_by' => \Sentry::getUser()->id,
                        'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            }

            \DB::table('first_trimesters')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])->where('open', '=', true)
                ->update(array('open' => false));

        }
    }

    private function assign_member_to_post_partum_program($input, $program, $pregnancy)
    {
        $patient = \User::where('id', '=', $input['patient_id'])->first();
        $region = $program->region;
        $all_region_programs = $region->programs()->get();

        foreach ($all_region_programs as $prg) {
            if ($prg->type == \Program::TYPE_POSTPARTUM) {
                try {
                    $patient->programs()->sync(array($prg->id), false);

                    $pp_instance = \DB::table('post_partums')
                        ->where('pregnancy_id', '=', $pregnancy->id)
                        ->first();

                    if ($pp_instance == null) {
                        $pp_instance = $this->add_new_post_partum_instance($input['patient_id'], $prg->id, $pregnancy);
                    }

                    $postpartum_start = date('Y-m-d', strtotime("+21 days", strtotime($input['delivery_date'])));
                    $postpartum_end = date('Y-m-d', strtotime("+56 days", strtotime($input['delivery_date'])));

                    \DB::table('patient_program')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $prg->id)
                        ->where('postpartum_start', '=', Null)
                        ->where('postpartum_end', '=', Null)
                        ->update(array('patient_notes' => $input['patient_notes'],
                            'date_added' => \Helpers::format_date_DB($input['date_added']),
                            'due_date' => \Helpers::format_date_DB($input['due_date']),
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'discontinue' => isset($input['discontinue']) ? true : false,
                            'discontinue_reason_id' => $input['discontinue_reason'],
                            'discontinue_date' => \Helpers::format_date_DB($input['discontinue_date']),
                            'gestational_age' => floatval($input['gestational_age']),
                            'postpartum_start' => $postpartum_start,
                            'postpartum_end' => $postpartum_end,
                            'enrolled_by' => $input['enrolled_by'],
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                    \DB::table('patient_program')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $prg->id)
                        ->update(array(
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'enrolled_by' => $input['enrolled_by'],
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));


                    \DB::table('post_partums')
                        ->where('id', '=', $pp_instance->id)
                        ->where('postpartum_start', '=', Null)
                        ->where('postpartum_end', '=', Null)
                        ->update(array('patient_notes' => $input['patient_notes'],
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'pediatrician_id' => $input['pediatrician_id'],
                            'postpartum_start' => $postpartum_start,
                            'postpartum_end' => $postpartum_end,
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                    \DB::table('post_partums')
                        ->where('id', '=', $pp_instance->id)
                        ->update(array(
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                } catch (\Exception $e) {
                    echo "<pre>";
                    print_r($e->getMessage());
                    echo "</pre>";
                    die();
                }
            }
        }
    }

    private function assign_member_to_wc15_program($input, $program, $pregnancy_id)
    {
        $patient = \User::where('id', '=', $input['patient_id'])->first();
        $region = $program->region;
        $all_region_programs = $region->programs()->get();

        foreach ($all_region_programs as $prg) {
            if (($prg->type == \Program::TYPE_WC15_AHC) || ($prg->type == \Program::TYPE_WC15_KF)) {
                try {
                    $child = $this->create_new_child_patient($input, $region, $patient, $pregnancy_id);

                    $child->programs()->sync(array($prg->id), false);

                    \DB::table('patient_program')
                        ->where('patient_id', '=', $child->id)
                        ->where('program_id', '=', $prg->id)
                        ->update(array(
                            /*'date_added' => \Helpers::format_date_DB($input['date_added']),*/
                            'due_date' => \Helpers::format_date_DB($input['due_date']),
                            'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                            'birth_weight' => $input['birth_weight'],
                            'pediatrician_id' => $input['pediatrician_id'],
                            'gestational_age' => floatval($input['gestational_age']),
                            'enrolled_by' => $input['enrolled_by'],
                            'mother_k2yc' => true,
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                } catch (\Exception $e) {

                }
            }
        }
    }

    private function create_new_child_patient($input, $region, $mother, $pregnancy_id)
    {
        $child_patient = \User::findTempChildPatient($mother, $pregnancy_id);

        $patient_data = array();
        $patient_data["username"] = $mother->username . "-TEMP-1";
        $patient_data["date_of_birth"] = \Helpers::format_date_DB($input['delivery_date']);
        $patient_data["address1"] = $mother->address1;
        $patient_data["address2"] = $mother->address2;
        $patient_data["city"] = $mother->city;
        $patient_data["state"] = $mother->state;
        $patient_data["zip"] = $mother->zip;
        $patient_data["county"] = $mother->county;
        $patient_data["phone1"] = $mother->phone1;
        $patient_data["trac_phone"] = $mother->trac_phone;
        $patient_data["email"] = $mother->email;
        $patient_data["password"] = \Hash::make('curotec');

        if ($child_patient == null) {
            try {
                $user = \User::create($patient_data);
                $user->region()->associate($region);
                $user->insurance_company()->associate($region->insurance_company()->first());
                $user->addGroup(\Sentry::findGroupByName("Patient"));
                $user->mother_id = $mother->id;
                $user->pregnancy_id = $pregnancy_id;

                $user->save();
            } catch (\Exception $e) {
                \App::abort(500, 'error creating a child patient');
            }

        } else {
            $user = \User::find($child_patient->id);
            /*
            try {
                $patient_data["username"] = $child_patient->username;
                \DB::beginTransaction();

                $user = \User::find($child_patient->id);
                $user->fill($patient_data);
                $user->mother_id = $mother->id;
                $user->save();

                \DB::commit();
            } catch (\Exception $e) {
                \App::abort(500, 'error updating a child patient');
            }
            */
        }

        return $user;
    }

    private function add_patient_actual_visit_for_wc15_program($input, $program)
    {
        if (empty($input['old_scheduled_visit_ids'])) {
            $input['old_scheduled_visit_ids'] = array();
        }
        if (empty($input['scheduled_visit_ids'])) {
            $input['scheduled_visit_ids'] = array();
        }

        $old_scheduled_visit_ids = $input['old_scheduled_visit_ids'];
        $scheduled_visit_ids = $input['scheduled_visit_ids'];

        $deleted_scheduled_visits = array_diff($old_scheduled_visit_ids, $scheduled_visit_ids);

        try {
            \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                ->update(array('patient_notes' => $input['patient_notes'],
                    'date_added' => \Helpers::format_date_DB($input['date_added']),
                    'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                    'birth_weight' => $input['birth_weight'],
                    'confirmed' => isset($input['confirmed']) ? true : false,
                    'mother_k2yc' => isset($input['mother_k2yc']) ? true : false,
                    'discontinue' => isset($input['discontinue']) ? true : false,
                    'discontinue_reason_id' => $input['discontinue_reason'],
                    'discontinue_date' => \Helpers::format_date_DB($input['discontinue_date']),
                    'gestational_age' => floatval($input['gestational_age']),
                    'how_did_you_hear' => !empty($input['how_did_you_hear']) ? $input['how_did_you_hear'] : 0,
                    'enrolled_by' => $input['enrolled_by'],
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ));

            \DB::table('users')->where('id', '=', $input['patient_id'])
                ->update(array(
                    'date_of_birth' => \Helpers::format_date_DB($input['delivery_date']),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                ));

        } catch (\Exception $e) {
        }

        //*
        if (!empty($input['sign_up'])) {

            $sign_up_visit = \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('sign_up', true)
                ->first();

            if (count($sign_up_visit) == 0) {

                $values_to_insert = array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                    'actual_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                    'scheduled_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                    'visit_notes' => $input['sign_up_notes'],
                    'sign_up' => true,
                    'created_by' => \Sentry::getUser()->id,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString());

                if (($program->type == \Program::TYPE_WC15_KF)) {
                    $values_to_insert['incentive_type'] = !empty($input['sign_up_incentive_type']) ? $input['sign_up_incentive_type'] : null;
                    $values_to_insert['incentive_value'] = !empty($input['sign_up_incentive_value']) ? str_replace("$", "", $input['sign_up_incentive_value']) : 0;
                    $values_to_insert['gift_card_serial'] = !empty($input['sign_up_gift_card_serial']) ? $input['sign_up_gift_card_serial'] : null;
                    $values_to_insert['incentive_date_sent'] = \Helpers::format_date_DB($input['sign_up_incentive_date']);

                    $values_to_insert['gift_card_returned'] = isset($input['sign_up_gift_card_returned']) ? true : false;
                    $values_to_insert['incentive_returned_date'] = \Helpers::format_date_DB($input['sign_up_gift_card_returned_date']);
                    $values_to_insert['gift_card_returned_notes'] = $input['sign_up_gift_card_returned_notes'];
                    $values_to_insert['manually_added'] = isset($input['sign_up_manually_added']) ? true : false;
                }

                \DB::table('patient_program_visits')->insert($values_to_insert);

                $patient_program_visits_id = \DB::getPdo()->lastInsertId();

                if (isset($input['sign_up_manual_outreach'])) {
                    for ($i = 0; $i < count($input['sign_up_manual_outreach']); $i++) {

                        \DB::table('manual_outreaches')->insert(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'patient_program_visits_id' => $patient_program_visits_id,
                                'outreach_date' => \Helpers::format_date_DB($input['sign_up_manual_outreach_date'][$i]),
                                'outreach_code' => $input['sign_up_manual_outreach_code'][$i],
                                'outreach_notes' => $input['sign_up_manual_outreach_notes'][$i],
                                'created_by' => \Sentry::getUser()->id,
                                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                        );
                    }
                }
            } else {

                $values_to_insert = array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                    'actual_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                    'scheduled_visit_date' => \Helpers::format_date_DB($input['sign_up']),
                    'visit_notes' => $input['sign_up_notes'],
                    'sign_up' => true,
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString());

                if (($program->type == \Program::TYPE_WC15_KF)) {
                    $values_to_insert['incentive_type'] = !empty($input['sign_up_incentive_type']) ? $input['sign_up_incentive_type'] : null;
                    $values_to_insert['incentive_value'] = !empty($input['sign_up_incentive_value']) ? str_replace("$", "", $input['sign_up_incentive_value']) : 0;
                    $values_to_insert['gift_card_serial'] = !empty($input['sign_up_gift_card_serial']) ? $input['sign_up_gift_card_serial'] : null;
                    $values_to_insert['incentive_date_sent'] = \Helpers::format_date_DB($input['sign_up_incentive_date']);

                    $values_to_insert['gift_card_returned'] = isset($input['sign_up_gift_card_returned']) ? true : false;
                    $values_to_insert['incentive_returned_date'] = \Helpers::format_date_DB($input['sign_up_gift_card_returned_date']);
                    $values_to_insert['gift_card_returned_notes'] = $input['sign_up_gift_card_returned_notes'];
                    $values_to_insert['manually_added'] = isset($input['sign_up_manually_added']) ? true : false;
                }


                \DB::table('patient_program_visits')
                    ->where('id', '=', $sign_up_visit->id)
                    ->update($values_to_insert);

                $patient_program_visits_id = $sign_up_visit->id;

                if (isset($input['sign_up_manual_outreach'])) {
                    for ($i = 0; $i < count($input['sign_up_manual_outreach']); $i++) {

                        \DB::table('manual_outreaches')->insert(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'patient_program_visits_id' => $patient_program_visits_id,
                                'outreach_date' => \Helpers::format_date_DB($input['sign_up_manual_outreach_date'][$i]),
                                'outreach_code' => $input['sign_up_manual_outreach_code'][$i],
                                'outreach_notes' => $input['sign_up_manual_outreach_notes'][$i],
                                'created_by' => \Sentry::getUser()->id,
                                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                        );
                    }
                }
            }

        }

        //delete "deleted visit ids" from patient_program_visits and manual_outreaches tables
        if (count($deleted_scheduled_visits)) {
            \DB::table('patient_program_visits')
                ->whereIn('id', $deleted_scheduled_visits)
                ->where('sign_up', '<>', true)
                ->delete();

            \DB::table('manual_outreaches')
                ->whereIn('patient_program_visits_id', $deleted_scheduled_visits)
                ->delete();
        }

        if (!empty($input['scheduled_visit_ids'])) {
            $scheduled_visits_rows = $this->construct_scheduled_visits_records($input);

            $this->save_scheduled_visits_records($scheduled_visits_rows, $input, $program->type);
        } else if (!empty($input['sign_up']) && !count($deleted_scheduled_visits) && !isset($input['discontinue'])) {
            $this->calculate_estimated_visit_dates($input, $program->type);
        }

        //*/
    }

    private function construct_scheduled_visits_records($input)
    {
        $scheduled_visits_rows = [];
        if (!empty($input['scheduled_visit_ids'])) {
            for ($i = 0; $i < count($input['scheduled_visit_ids']); $i++) {

                $id = $input['scheduled_visit_ids'][$i];

                $scheduled_visits_row = [];
                $scheduled_visits_row['id'] = $id;
                $scheduled_visits_row['scheduled_visit'] = \Helpers::format_date_DB($input['scheduled_visit_' . $id]);
                $scheduled_visits_row['actual_visit'] = isset($input['actual_visit_' . $id]) ? \Helpers::format_date_DB($input['actual_visit_' . $id]) : null;

                $scheduled_visits_row['visit_date_verified_by'] = !empty($input['visit_date_verified_by_' . $id]) ? $input['visit_date_verified_by_' . $id] : 0;
                $scheduled_visits_row['julian_date'] = isset($input['julian_date_' . $id]) ? (int)$input['julian_date_' . $id] : null;
                $scheduled_visits_row['doctor_id'] = isset($input['doctor_id_' . $id]) ? $input['doctor_id_' . $id] : '';
                $scheduled_visits_row['incentive_type'] = isset($input['incentive_type_' . $id]) ? $input['incentive_type_' . $id] : '';
                $scheduled_visits_row['incentive_value'] = !empty($input['incentive_value_' . $id]) ? str_replace("$", "", $input['incentive_value_' . $id]) : 0;
                $scheduled_visits_row['gift_card_serial'] = isset($input['gift_card_serial_' . $id]) ? $input['gift_card_serial_' . $id] : '';
                $scheduled_visits_row['incentive_date'] = isset($input['incentive_date_' . $id]) ? \Helpers::format_date_DB($input['incentive_date_' . $id]) : null;
                $scheduled_visits_row['visit_notes'] = !empty($input['visit_notes_' . $id]) ? $input['visit_notes_' . $id] : '';
                $scheduled_visits_row['gift_card_returned'] = isset($input['gift_card_returned_' . $id]) ? true : false;
                $scheduled_visits_row['incentive_returned_date'] = isset($input['gift_card_returned_date_' . $id]) ? \Helpers::format_date_DB($input['gift_card_returned_date_' . $id]) : null;
                $scheduled_visits_row['gift_card_returned_notes'] = !empty($input['gift_card_returned_notes_' . $id]) ? $input['gift_card_returned_notes_' . $id] : '';
                $scheduled_visits_row['manually_added'] = isset($input['manually_added']) ? true : false;

                $scheduled_visits_rows[] = $scheduled_visits_row;
            }
        }

        return $scheduled_visits_rows;

    }

    private function save_scheduled_visits_records($scheduled_visits_rows, $input, $program_type, $pregnancy = false)
    {
        foreach ($scheduled_visits_rows as $scheduled_visits_row) {
            //if (strpos($scheduled_visits_row['id'], 'new_visit') !== false && !empty($scheduled_visits_row['scheduled_visit'])) {
            if (strpos($scheduled_visits_row['id'], 'new_visit') !== false) {

                $index = $scheduled_visits_row['id'];

                $values_to_insert = array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                    'program_instance_id' => ($pregnancy != false) ? $pregnancy->id : 0,
                    'actual_visit_date' => $scheduled_visits_row['actual_visit'],
                    'visit_date_verified_by' => $scheduled_visits_row['visit_date_verified_by'],
                    'julian_date' => $scheduled_visits_row['julian_date'],
                    'estimated_date' => $scheduled_visits_row['scheduled_visit'],
                    'doctor_id' => $scheduled_visits_row['doctor_id'],
                    'incentive_type' => $scheduled_visits_row['incentive_type'],
                    'incentive_value' => $scheduled_visits_row['incentive_value'],
                    'gift_card_serial' => $scheduled_visits_row['gift_card_serial'],
                    'incentive_date_sent' => $scheduled_visits_row['incentive_date'],
                    'visit_notes' => $scheduled_visits_row['visit_notes'],
                    'gift_card_returned' => $scheduled_visits_row['gift_card_returned'],
                    'incentive_returned_date' => $scheduled_visits_row['incentive_returned_date'],
                    'gift_card_returned_notes' => $scheduled_visits_row['gift_card_returned_notes'],
                    'manually_added' => $scheduled_visits_row['manually_added'],
                    'created_by' => \Sentry::getUser()->id,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString());

                if (($program_type != \Program::TYPE_WC15_AHC) || ($program_type != \Program::TYPE_WC15_KF)) {
                    $values_to_insert['scheduled_visit_date'] = $scheduled_visits_row['scheduled_visit'];
                }


                \DB::table('patient_program_visits')->insert($values_to_insert);

                $patient_program_visits_id = \DB::getPdo()->lastInsertId();

                if (isset($input['manual_outreach_' . $index])) {
                    for ($i = 0; $i < count($input['manual_outreach_' . $index]); $i++) {

                        \DB::table('manual_outreaches')->insert(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'program_instance_id' => ($pregnancy != false) ? $pregnancy->id : 0,
                                'patient_program_visits_id' => $patient_program_visits_id,
                                'outreach_date' => \Helpers::format_date_DB($input['manual_outreach_date_' . $index][$i]),
                                'outreach_code' => $input['manual_outreach_code_' . $index][$i],
                                'outreach_notes' => $input['manual_outreach_notes_' . $index][$i],
                                'created_by' => \Sentry::getUser()->id,
                                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                        );
                    }
                }
            } else {
                $index = $scheduled_visits_row['id'];

                \DB::table('patient_program_visits')
                    ->where('id', $index)
                    ->update(
                        array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                            'program_instance_id' => ($pregnancy != false) ? $pregnancy->id : 0,
                            'actual_visit_date' => $scheduled_visits_row['actual_visit'],
                            'visit_date_verified_by' => $scheduled_visits_row['visit_date_verified_by'],
                            'julian_date' => $scheduled_visits_row['julian_date'],
                            'scheduled_visit_date' => $scheduled_visits_row['scheduled_visit'],
                            'doctor_id' => $scheduled_visits_row['doctor_id'],
                            'incentive_type' => $scheduled_visits_row['incentive_type'],
                            'incentive_value' => $scheduled_visits_row['incentive_value'],
                            'gift_card_serial' => $scheduled_visits_row['gift_card_serial'],
                            'incentive_date_sent' => $scheduled_visits_row['incentive_date'],
                            'visit_notes' => $scheduled_visits_row['visit_notes'],
                            'gift_card_returned' => $scheduled_visits_row['gift_card_returned'],
                            'incentive_returned_date' => $scheduled_visits_row['incentive_returned_date'],
                            'gift_card_returned_notes' => $scheduled_visits_row['gift_card_returned_notes'],
                            'manually_added' => $scheduled_visits_row['manually_added'],
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));

                if (isset($input['manual_outreach_' . $index])) {
                    for ($i = 0; $i < count($input['manual_outreach_' . $index]); $i++) {

                        \DB::table('manual_outreaches')->insert(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'program_instance_id' => ($pregnancy != false) ? $pregnancy->id : 0,
                                'patient_program_visits_id' => $index,
                                'outreach_date' => \Helpers::format_date_DB($input['manual_outreach_date_' . $index][$i]),
                                'outreach_code' => $input['manual_outreach_code_' . $index][$i],
                                'outreach_notes' => $input['manual_outreach_notes_' . $index][$i],
                                'created_by' => \Sentry::getUser()->id,
                                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                        );
                    }
                }

            }
        }
    }


    private function construct_scheduled_visits_records_for_A1C_program($input)
    {
        $scheduled_visits_rows = [];
        if (!empty($input['scheduled_visit_ids'])) {
            for ($i = 0; $i < count($input['scheduled_visit_ids']); $i++) {

                $id = $input['scheduled_visit_ids'][$i];

                $scheduled_visits_row = [];
                $scheduled_visits_row['id'] = $id;
                $scheduled_visits_row['metric'] = isset($input['metric_' . $id]) ? $input['metric_' . $id] : 0;
                $scheduled_visits_row['scheduled_visit'] = \Helpers::format_date_DB($input['scheduled_visit_date_' . $id]);
                $scheduled_visits_row['scheduled_visit_date_notes'] = !empty($input['scheduled_visit_date_notes_' . $id]) ? $input['scheduled_visit_date_notes_' . $id] : '';

                $scheduled_visits_rows[] = $scheduled_visits_row;
            }
        }

        return $scheduled_visits_rows;

    }

    private function save_scheduled_visits_records_for_A1C_program($scheduled_visits_rows, $input)
    {
        foreach ($scheduled_visits_rows as $scheduled_visits_row) {
            //if (strpos($scheduled_visits_row['id'], 'new_visit') !== false && !empty($scheduled_visits_row['scheduled_visit'])) {
            if (strpos($scheduled_visits_row['id'], 'new_visit') !== false) {

                $values_to_insert = array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                    'metric' => $scheduled_visits_row['metric'],
                    'scheduled_visit_date' => $scheduled_visits_row['scheduled_visit'],
                    'scheduled_visit_date_notes' => $scheduled_visits_row['scheduled_visit_date_notes'],

                    'created_by' => \Sentry::getUser()->id,
                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString());

                \DB::table('patient_program_visits')->insert($values_to_insert);


            } else {
                $index = $scheduled_visits_row['id'];

                \DB::table('patient_program_visits')
                    ->where('id', $index)
                    ->update(
                        array('patient_id' => $input['patient_id'], 'program_id' => $input['program_id'],
                            'metric' => $scheduled_visits_row['metric'],
                            'scheduled_visit_date' => $scheduled_visits_row['scheduled_visit'],
                            'scheduled_visit_date_notes' => $scheduled_visits_row['scheduled_visit_date_notes'],
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
            }
        }
    }


    private function calculate_estimated_visit_dates($input, $program_type)
    {
        $mos_2 = date('Y-m-d', strtotime("+60 days", strtotime($input['delivery_date'])));
        $mos_4 = date('Y-m-d', strtotime("+120 days", strtotime($input['delivery_date'])));
        $mos_6 = date('Y-m-d', strtotime("+180 days", strtotime($input['delivery_date'])));
        $mos_9 = date('Y-m-d', strtotime("+270 days", strtotime($input['delivery_date'])));
        $mos_12 = date('Y-m-d', strtotime("+360 days", strtotime($input['delivery_date'])));
        $mos_15 = date('Y-m-d', strtotime("+450 days", strtotime($input['delivery_date'])));

        $input['scheduled_visit_ids'] = [];

        if (strtotime($input['sign_up']) < strtotime($mos_2)) {
            array_push($input['scheduled_visit_ids'], 'new_visit_mos_2');
            $input['scheduled_visit_new_visit_mos_2'] = $mos_2;
        }

        if (strtotime($input['sign_up']) < strtotime($mos_4)) {
            array_push($input['scheduled_visit_ids'], 'new_visit_mos_4');
            $input['scheduled_visit_new_visit_mos_4'] = $mos_4;
        }

        if (strtotime($input['sign_up']) < strtotime($mos_6)) {
            array_push($input['scheduled_visit_ids'], 'new_visit_mos_6');
            $input['scheduled_visit_new_visit_mos_6'] = $mos_6;
        }

        if (strtotime($input['sign_up']) < strtotime($mos_9)) {
            array_push($input['scheduled_visit_ids'], 'new_visit_mos_9');
            $input['scheduled_visit_new_visit_mos_9'] = $mos_9;
        }

        if (strtotime($input['sign_up']) < strtotime($mos_12)) {
            array_push($input['scheduled_visit_ids'], 'new_visit_mos_12');
            $input['scheduled_visit_new_visit_mos_12'] = $mos_12;
        }

        if (strtotime($input['sign_up']) < strtotime($mos_15)) {
            array_push($input['scheduled_visit_ids'], 'new_visit_mos_15');
            $input['scheduled_visit_new_visit_mos_15'] = $mos_15;
        }


        $scheduled_visits_rows = $this->construct_scheduled_visits_records($input);

        $this->save_scheduled_visits_records($scheduled_visits_rows, $input, $program_type);
    }

    private function add_patient_actual_visit_for_other_programs($input, $program)
    {
        if (isset($input['manual_outreach'])) {
            for ($i = 0; $i < count($input['manual_outreach']); $i++) {

                if ($program->type == \Program::TYPE_POSTPARTUM) {
                    \DB::table('manual_outreaches')->insert(
                        array('patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'program_instance_id' => $input['program_instance_id'],
                            'outreach_date' => \Helpers::format_date_DB($input['manual_outreach_date'][$i]),
                            'outreach_code' => $input['manual_outreach_code'][$i],
                            'outreach_notes' => $input['manual_outreach_notes'][$i],
                            'outreach_metric' => isset($input['manual_outreach_metric'][$i]) ? $input['manual_outreach_metric'][$i] : null,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                    );
                } else {
                    \DB::table('manual_outreaches')->insert(
                        array('patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'outreach_date' => \Helpers::format_date_DB($input['manual_outreach_date'][$i]),
                            'outreach_code' => $input['manual_outreach_code'][$i],
                            'outreach_notes' => $input['manual_outreach_notes'][$i],
                            'outreach_metric' => isset($input['manual_outreach_metric'][$i]) ? $input['manual_outreach_metric'][$i] : null,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString())
                    );
                }
            }
        }

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            //$postpartum_start = date('Y-m-d', strtotime("+21 days", $input['delivery_date']));
            //$postpartum_end = date('Y-m-d', strtotime("+56 days", $input['delivery_date']));


            try {
                \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                    ->update(array(
                        'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                        'postpartum_start' => \Helpers::format_date_DB($input['postpartum_start']),
                        'postpartum_end' => \Helpers::format_date_DB($input['postpartum_end']),
                        'birth_weight' => $input['birth_weight'],
                        'gestational_age' => floatval($input['gestational_age']),
                        'pediatrician_id' => $input['pediatrician_id'],
                        'patient_notes' => $input['patient_notes']
                    ));

                \DB::table('post_partums')
                    ->where('id', '=', $input['program_instance_id'])
                    ->update(array(
                        'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                        'postpartum_start' => \Helpers::format_date_DB($input['postpartum_start']),
                        'postpartum_end' => \Helpers::format_date_DB($input['postpartum_end']),
                        'birth_weight' => $input['birth_weight'],
                        'gestational_age' => floatval($input['gestational_age']),
                        'pediatrician_id' => $input['pediatrician_id'],
                        'patient_notes' => $input['patient_notes']
                    ));

            } catch (\Exception $e) {

            }

            // update pregnancy program information
            $patient = \User::where('id', '=', $input['patient_id'])->first();
            $region = $program->region;
            $all_region_programs = $region->programs()->get();

            foreach ($all_region_programs as $prg) {
                if ($prg->type == \Program::TYPE_PREGNANCY) {

                    try {
                        \DB::table('patient_program')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $prg->id)
                            ->update(array(
                                'delivery_date' => \Helpers::format_date_DB($input['delivery_date']),
                                'birth_weight' => $input['birth_weight'],
                                'pediatrician_id' => $input['pediatrician_id'],
                                'gestational_age' => floatval($input['gestational_age']),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                            ));
                        // TODO: if a PP instance is updated => update the pregnancy instance as well

                    } catch (\Exception $e) {

                    }
                }
            }


        } else {

            try {
                \DB::table('patient_program')->where('patient_id', '=', $input['patient_id'])->where('program_id', '=', $input['program_id'])
                    ->update(array('patient_notes' => $input['patient_notes']));
            } catch (\Exception $e) {

            }

        }

        // retrieve current year scheduled/actual visit
        $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . date('Y')));
        $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . date('Y')));

        if ($program->type == \Program::TYPE_A1C) {
            if (empty($input['metric'])) {
                $scheduled_visit_date_for_current_year = null;
            } else {
                $scheduled_visit_date_for_current_year = \DB::table('patient_program_visits')
                    ->where('patient_id', '=', $input['patient_id'])
                    ->where('program_id', '=', $input['program_id'])
                    ->where('metric', '=', $input['metric'])
                    ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                    ->first();
            }
        } else if ($program->type == \Program::TYPE_POSTPARTUM) {
            $scheduled_visit_date_for_current_year = \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->where('program_instance_id', '=', $input['program_instance_id'])
                ->first();

        } else {
            $scheduled_visit_date_for_current_year = \DB::table('patient_program_visits')
                ->where('patient_id', '=', $input['patient_id'])
                ->where('program_id', '=', $input['program_id'])
                ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                ->first();
        }

        if (!empty($input['actual_visit_date'])) {

            if ($scheduled_visit_date_for_current_year != null) {
                if ($program->type == \Program::TYPE_A1C) {
                    // matching function to match existing doctor with the entered doctor_id
                    /*
                    $doctor_id = null;
                    if (isset($input['doctor_id'])) {
                        $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'])->first();
                        if ($doctor) {
                            $doctor_id = $doctor->id;
                        }
                    }
                    //*/


                    \DB::table('patient_program_visits')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $input['program_id'])
                        ->where('metric', '=', $input['metric'])
                        ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                        ->update(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                'doctor_id' => $input['doctor_id'],
                                'incentive_type' => $input['incentive_type'],
                                'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                'gift_card_serial' => $input['gift_card_serial'],
                                'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                'metric' => $input['metric'],
                                'visit_notes' => $input['visit_notes'],
                                'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                                'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                                'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                                'manually_added' => isset($input['manually_added']) ? true : false,
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
                } else if ($program->type == \Program::TYPE_POSTPARTUM) {

                    \DB::table('patient_program_visits')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $input['program_id'])
                        ->where('program_instance_id', '=', $input['program_instance_id'])
                        ->update(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                'doctor_id' => $input['doctor_id'],
                                'incentive_type' => $input['incentive_type'],
                                'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                'gift_card_serial' => $input['gift_card_serial'],
                                'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                'visit_notes' => $input['visit_notes'],
                                'manually_added' => isset($input['manually_added']) ? true : false,
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));

                } else {
                    // matching function to match existing doctor with the entered doctor_id
                    /*
                    $doctor_id = null;
                    if (isset($input['doctor_id'])) {
                        $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'])->first();
                        if ($doctor) {
                            $doctor_id = $doctor->id;
                        }
                    }
                    //*/
                    //code for update
                    if ($program->abbreviation == 'Cribs for Kids') {
                        $incentive_null = \DB::table('patient_program_visits')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $input['program_id'])
                            ->where('is_add_on_incentive', '=', 0)
                            ->whereRaw(' (scheduled_visit_date between ? and ? ) ', array($first_date_of_year, $last_date_of_year))
                            ->where(function ($query) {
                                $query->whereNull('actual_visit_date')
                                    ->orWhere('actual_visit_date', '=', '0000-00-00 00:00:00');
                            })
                            ->count();

                        $incentive_initial_count = \DB::table('patient_program_visits')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $input['program_id'])
                            ->where('is_add_on_incentive', '=', 0)
                            ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                            ->count();

                        if ($incentive_initial_count == 0) {
                            $add_on_flag = 0;
                        } else {
                            $add_on_flag = 1;
                        }

                        $incentive_addon_count = \DB::table('patient_program_visits')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $input['program_id'])
                            ->where('is_add_on_incentive', '=', 1)
                            ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                            ->count();

                        //fetching region id for given prog
                        $data_for_region = \DB::table('programs')
                            ->where('id', '=', $input['program_id'])
                            ->first();
                        $region_id = $data_for_region->region_id;

                        $prog_type = \program::TYPE_PREGNANCY;

                        //fetching K2YC prog id for given region 

                        $data_for_programa = \DB::table('programs')
                            ->where('region_id', '=', $region_id)
                            ->where('type', "=", $prog_type)
                            ->first();
                        $prog_id = $data_for_programa->id;


                        $preg_data = \DB::table('pregnancies')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $prog_id)
                            //  ->where('open', '=', true)
                            ->orderBy('created_at', "desc")
                            ->first();

                        if ($preg_data != null) {
                            $allowed_crib_incentives = $preg_data->cribs_quantity;
                        } else {
                            $allowed_crib_incentives = 0;
                        }

                        if ($incentive_null == 1) {
                            \DB::table('patient_program_visits')
                                ->where('patient_id', '=', $input['patient_id'])
                                ->where('program_id', '=', $input['program_id'])
                                ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                                ->where(function ($query) {
                                    $query->whereNull('actual_visit_date')
                                        ->orWhere('actual_visit_date', '=', '0000-00-00 00:00:00');
                                })
                                ->update(
                                    array('patient_id' => $input['patient_id'],
                                        'program_id' => $input['program_id'],
                                        'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                        'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                        'doctor_id' => $input['doctor_id'],
                                        'incentive_type' => $input['incentive_type'],
                                        'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                        'gift_card_serial' => $input['gift_card_serial'],
                                        'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                        'visit_notes' => $input['visit_notes'],
                                        'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                                        'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                                        'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                                        'manually_added' => isset($input['manually_added']) ? true : false,
                                        'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
                        } else {


                            if ((($incentive_initial_count) + ($incentive_addon_count)) < $allowed_crib_incentives) {
                                if ((!empty($input['incentive_type'])) && (!empty($input['incentive_value'])) && (!empty($input['gift_card_serial'])) && (!empty($input['incentive_date_sent']))) {
                                    \DB::table('patient_program_visits')->insert(
                                        array('patient_id' => $input['patient_id'],
                                            'program_id' => $input['program_id'],
                                            'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                            'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                            'doctor_id' => $input['doctor_id'],
                                            'incentive_type' => $input['incentive_type'],
                                            'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                            'gift_card_serial' => $input['gift_card_serial'],
                                            'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                            'is_add_on_incentive' => $add_on_flag,
                                            'visit_notes' => $input['visit_notes'],
                                            'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                                            'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                                            'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                                            'manually_added' => isset($input['manually_added']) ? true : false,
                                            'created_by' => \Sentry::getUser()->id,
                                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
                                } else {
                                    return "blank";
                                }
                            } else {
                                return "false";
                            }
                        }
                    } else {
                        \DB::table('patient_program_visits')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $input['program_id'])
                            ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                            ->update(
                                array('patient_id' => $input['patient_id'],
                                    'program_id' => $input['program_id'],
                                    'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                    'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                    'doctor_id' => $input['doctor_id'],
                                    'incentive_type' => $input['incentive_type'],
                                    'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                    'gift_card_serial' => $input['gift_card_serial'],
                                    'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                    'visit_notes' => $input['visit_notes'],
                                    'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                                    'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                                    'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                                    'manually_added' => isset($input['manually_added']) ? true : false,
                                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
                    }
                }
            } else {
                if ($program->type == \Program::TYPE_A1C) {
                    // matching function to match existing doctor with the entered doctor_id
                    /*
                    $doctor_id = null;
                    if (isset($input['doctor_id'])) {
                        $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'])->first();
                        if ($doctor) {
                            $doctor_id = $doctor->id;
                        }
                    }
                    //*/


                    \DB::table('patient_program_visits')->insert(
                        array('patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                            'doctor_id' => $input['doctor_id'],
                            'incentive_type' => $input['incentive_type'],
                            'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                            'gift_card_serial' => $input['gift_card_serial'],
                            'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                            'metric' => $input['metric'],
                            'visit_notes' => $input['visit_notes'],
                            'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                            'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                            'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                            'manually_added' => isset($input['manually_added']) ? true : false,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));

                } else if ($program->type == \Program::TYPE_POSTPARTUM) {

                    \DB::table('patient_program_visits')->insert(
                        array('patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'program_instance_id' => $input['program_instance_id'],
                            'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                            'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                            'doctor_id' => $input['doctor_id'],
                            'incentive_type' => $input['incentive_type'],
                            'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                            'gift_card_serial' => $input['gift_card_serial'],
                            'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                            'visit_notes' => $input['visit_notes'],
                            'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                            'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                            'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                            'manually_added' => isset($input['manually_added']) ? true : false,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));

                } else {
                    // matching function to match existing doctor with the entered doctor_id
                    /*
                    $doctor_id = null;
                    if (isset($input['doctor_id'])) {
                        $doctor = \Doctor::where('pcp_id', '=', $input['doctor_id'])->first();
                        if ($doctor) {
                            $doctor_id = $doctor->id;
                        }
                    }
                    //*/
                    if ($program->abbreviation == 'Cribs for Kids') {

                        $incentive_initial_count = \DB::table('patient_program_visits')
                            ->where('patient_id', '=', $input['patient_id'])
                            ->where('program_id', '=', $input['program_id'])
                            ->where('is_add_on_incentive', '=', 0)
                            ->whereRaw('( (scheduled_visit_date between ? and ? ) or ( actual_visit_date between ? and ? ) )', array($first_date_of_year, $last_date_of_year, $first_date_of_year, $last_date_of_year))
                            ->count();

                        if ($incentive_initial_count == 0) {
                            $add_on_flag = 0;
                        } else {
                            $add_on_flag = 1;
                        }

                        if ((!empty($input['incentive_type'])) && (!empty($input['incentive_value'])) && (!empty($input['gift_card_serial'])) && (!empty($input['incentive_date_sent']))) {
                            \DB::table('patient_program_visits')->insert(
                                array('patient_id' => $input['patient_id'],
                                    'program_id' => $input['program_id'],
                                    'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                    'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                    'doctor_id' => $input['doctor_id'],
                                    'incentive_type' => $input['incentive_type'],
                                    'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                    'gift_card_serial' => $input['gift_card_serial'],
                                    'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                    'visit_notes' => $input['visit_notes'],
                                    'is_add_on_incentive' => $add_on_flag,
                                    'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                                    'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                                    'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                                    'manually_added' => isset($input['manually_added']) ? true : false,
                                    'created_by' => \Sentry::getUser()->id,
                                    'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                    'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
                        } else {
                            return "blank";
                        }
                    } else {
                        \DB::table('patient_program_visits')->insert(
                            array('patient_id' => $input['patient_id'],
                                'program_id' => $input['program_id'],
                                'actual_visit_date' => \Helpers::format_date_DB($input['actual_visit_date']),
                                'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                'doctor_id' => $input['doctor_id'],
                                'incentive_type' => $input['incentive_type'],
                                'incentive_value' => !empty($input['incentive_value']) ? str_replace("$", "", $input['incentive_value']) : 0,
                                'gift_card_serial' => $input['gift_card_serial'],
                                'incentive_date_sent' => \Helpers::format_date_DB($input['incentive_date_sent']),
                                'visit_notes' => $input['visit_notes'],
                                'gift_card_returned' => isset($input['gift_card_returned']) ? true : false,
                                'incentive_returned_date' => isset($input['gift_card_returned_date']) ? \Helpers::format_date_DB($input['gift_card_returned_date']) : null,
                                'gift_card_returned_notes' => isset($input['gift_card_returned_notes']) ? $input['gift_card_returned_notes'] : null,
                                'manually_added' => isset($input['manually_added']) ? true : false,
                                'created_by' => \Sentry::getUser()->id,
                                'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()));
                    }
                }
            }

        } else if ($program->type != \Program::TYPE_A1C && !empty($input['scheduled_visit_date'])) {

            if ($scheduled_visit_date_for_current_year != null) {

                if ($program->type == \Program::TYPE_POSTPARTUM) {

                    \DB::table('patient_program_visits')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $input['program_id'])
                        ->where('program_instance_id', '=', $input['program_instance_id'])
                        ->update(
                            array(
                                'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                'scheduled_visit_date_notes' => isset($input['scheduled_visit_date_notes']) ? $input['scheduled_visit_date_notes'] : null,
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                            ));

                } else {
                    \DB::table('patient_program_visits')
                        ->where('patient_id', '=', $input['patient_id'])
                        ->where('program_id', '=', $input['program_id'])
                        ->whereBetween('scheduled_visit_date', array($first_date_of_year, $last_date_of_year))
                        ->update(
                            array(
                                'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                                'scheduled_visit_date_notes' => isset($input['scheduled_visit_date_notes']) ? $input['scheduled_visit_date_notes'] : null,
                                'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                            ));
                }

            } else {

                if ($program->type == \Program::TYPE_POSTPARTUM) {

                    \DB::table('patient_program_visits')->insert(
                        array(
                            'patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'program_instance_id' => $input['program_instance_id'],
                            'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                            'scheduled_visit_date_notes' => isset($input['scheduled_visit_date_notes']) ? $input['scheduled_visit_date_notes'] : null,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));

                } else {

                    \DB::table('patient_program_visits')->insert(
                        array(
                            'patient_id' => $input['patient_id'],
                            'program_id' => $input['program_id'],
                            'scheduled_visit_date' => \Helpers::format_date_DB($input['scheduled_visit_date']),
                            'scheduled_visit_date_notes' => isset($input['scheduled_visit_date_notes']) ? $input['scheduled_visit_date_notes'] : null,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => \Carbon\Carbon::now()->toDateTimeString(),
                            'updated_at' => \Carbon\Carbon::now()->toDateTimeString()
                        ));
                }
            }

        }


        if ($program->type == \Program::TYPE_A1C) {

            $scheduled_visits_rows = $this->construct_scheduled_visits_records_for_A1C_program($input);

            $this->save_scheduled_visits_records_for_A1C_program($scheduled_visits_rows, $input);
        }

    }

    public function import_visit_dates($region_id, $program_id)
    {
        $region = \Region::find($region_id);
        $program = \Program::find($program_id);


        $pause_data = \DB::select(\DB::Raw("SELECT region_id, count(id) AS record_count FROM pp_import_pause_data GROUP BY region_id"));

        $pp_data_array = [];

        foreach ($pause_data as $key => $value) {
            $pp_data_array[$value->region_id] = $value->record_count;
        }
        $region_array = array_keys($pp_data_array);

        $this->layout = \View::make('admin.layouts.base_iframe');

        $this->layout->content = View::make('admin/programs/import/import_visit_dates')
            ->with('region', $region)
            ->with('program', $program)
            ->with('route', array('admin.index'))
            ->with('region_array', $region_array);

    }


    public function store_imported_visit_dates()
    {
        if (strpos(getcwd(), 'public') !== false) {
            $baselink = 'uploads/';
        } else {
            $baselink = 'public/uploads/';
        }

        $filename = $baselink . (\Input::get('file'));

        $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        unset($data[0]);
        $data = array_values($data);

        $program = \DB::table('programs')->where('id', '=', \Input::get('program'))->first();
        $region = \Region::find($program->region_id);

        $usernames = [];
        $drs_ids = [];
        $rows_count = 10; //($program->type == \Program::TYPE_POSTPARTUM) ? 11 : 10;

        // fetch all usernames for all rows, build an array import_data containing data to be imported
        $import_data = array();
        foreach ($data as $row) {
            $rowData = str_getcsv($row, ",", '"');

            if (count($rowData) == $rows_count && !empty($rowData[0])) {

                $usernames[] = $rowData[0];

                $item = array();
                $item['first_name'] = trim($rowData[1]);
                $item['last_name'] = trim($rowData[2]);
                $item['actual_visit_date'] = trim($rowData[3]);
                $item['doctor_id'] = trim($rowData[4]);
                $drs_ids[] = trim($rowData[4]);
                $item['incentive_type'] = trim($rowData[5]);
                $item['incentive_value'] = trim($rowData[6]);
                $item['gift_card_serial'] = str_replace(".00", "", trim($rowData[7]));
                $item['incentive_date_sent'] = trim($rowData[8]);
                $item['visit_notes'] = trim($rowData[9]);
                //$item['delivery_date'] = trim($rowData[10]);

                $import_data[$rowData[0]] = $item;
            }
        }


        // find usernames ( to filter out ) that already have an actual visit date set for the current year
        $date_of_service = \Input::get('date_of_service');

        $first_date_of_year = date('Y-m-d 00:00:00', strtotime("first day of january " . $date_of_service));
        $last_date_of_year = date('Y-m-d 23:59:59', strtotime("last day of december " . $date_of_service));

        $metric_type = \Program::METRIC_NULL;
        if ($program->type == \Program::TYPE_A1C) {
            $metric_type = \Input::get('metric');
        }

        $already_set_actual_visit_date = \DB::table('patient_program_visits')
            ->select('users.username')
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->where('program_id', '=', \Input::get('program'))
            ->where('metric', '=', $metric_type)
            ->whereIn('users.username', $usernames)
            ->whereBetween('actual_visit_date', array($first_date_of_year, $last_date_of_year))
            ->get();

        $already_set_actual_visit_date_usernames = [];
        foreach ($already_set_actual_visit_date as $item) {
            $already_set_actual_visit_date_usernames[] = $item->username;
        }

        $already_existing_scheduled_visit_date = \DB::table('patient_program_visits')
            ->select('users.username', 'patient_program_visits.id')
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->where('program_id', '=', \Input::get('program'))
            ->where('metric', '=', $metric_type)
            ->whereIn('users.username', $usernames)
            ->whereBetween('scheduled_visit_date', array($first_date_of_year, $last_date_of_year))
            ->get();

        $already_existing_scheduled_visit_date_rows_ids = [];
        foreach ($already_existing_scheduled_visit_date as $item) {
            $already_existing_scheduled_visit_date_rows_ids[$item->username] = $item->id;
        }

        $patients_list = \User::whereIn('username', $usernames)->where('region_id', '=', $program->region_id)->get();

        // get the doctors from the DB to a key value array
        $doctors = \Doctor::whereIn('pcp_id', $drs_ids)->lists('id', 'pcp_id');
        // deduce not matching doctors
        $not_matching_doctors = array_unique(array_diff($drs_ids, array_keys($doctors)));

        if (!$region->no_limits) {
            // retrive the total incentives for the current yer of service for each user
            $result = \DB::select(\DB::Raw("SELECT username, SUM(incentive_value) as total_incentives
            FROM patient_program_visits join users on users.id = patient_program_visits.patient_id
            WHERE
            gift_card_returned <> 1
            and actual_visit_date BETWEEN '$first_date_of_year' and '$last_date_of_year'
            and username in( " . implode(',', $usernames) . " )
            GROUP by patient_id"));

            $total_incentives_above_annual_limit = [];
            foreach ($result as $item) {
                if (($item->total_incentives + $import_data[$item->username]['incentive_value']) > $region->annual_incentive_limit) {
                    $total_incentives_above_annual_limit[$item->username] = $item->total_incentives;
                }
            }
        } else {
            $total_incentives_above_annual_limit = [];
        }

        \DB::beginTransaction();
        $existing_items = [];

        foreach ($patients_list as $patient) {

            $existing_items[] = $patient->username;
            // exclude usernames that already have an actual visit date set
            if (in_array($patient->username, $already_set_actual_visit_date_usernames)) {
                continue;
            }

            if (!$region->no_limits) {
                // exclude patients who will go above their annual incentive limit
                if (in_array($patient->username, $total_incentives_above_annual_limit)) {
                    continue;
                }
            }

            /*
            if (in_array($import_data[$patient->username]['doctor_id'], $not_matching_doctors)) {
                continue;
            }
            */

            if (array_key_exists($patient->username, $already_existing_scheduled_visit_date_rows_ids)) {
                $rowId = $already_existing_scheduled_visit_date_rows_ids[$patient->username];
            } else {
                $rowId = 0;
            }

            $actual_visit_date = strtotime($import_data[$patient->username]['actual_visit_date']);
            $incentive_type = $import_data[$patient->username]['incentive_type'];
            $incentive_value = $import_data[$patient->username]['incentive_value'];
            $gift_card_serial = $import_data[$patient->username]['gift_card_serial'];
            $incentive_date_sent = '';
            if ($import_data[$patient->username]['incentive_date_sent'] != '') {
                $incentive_date_sent = \Helpers::format_date_DB(trim($import_data[$patient->username]['incentive_date_sent']));
            }
            $visit_notes = $import_data[$patient->username]['visit_notes'];
            /*
            $delivery_date = '';
            if ($import_data[$patient->username]['delivery_date'] != '') {
                $delivery_date = date('Y-m-d', strtotime(trim($import_data[$patient->username]['delivery_date'])));
            }
            */
            $created_at = \Carbon\Carbon::now()->toDateTimeString();

            $actual_visit_date = date('Y-m-d', $actual_visit_date);

            try {

                if ($rowId == 0) {

                    \DB::table('patient_program_visits')->insert(
                        array('patient_id' => $patient->id,
                            'program_id' => $program->id,
                            'actual_visit_date' => $actual_visit_date,
                            'incentive_type' => $incentive_type,
                            'incentive_value' => $incentive_value,
                            'gift_card_serial' => $gift_card_serial,
                            'incentive_date_sent' => $incentive_date_sent,
                            'doctor_id' => isset($doctors[$import_data[$patient->username]['doctor_id']]) ? $doctors[$import_data[$patient->username]['doctor_id']] : null,
                            'metric' => $metric_type,
                            'visit_notes' => $visit_notes,
                            //'delivery_date' => $delivery_date,
                            'created_by' => \Sentry::getUser()->id,
                            'created_at' => $created_at,
                            'updated_at' => $created_at));
                } else {
                    \DB::table('patient_program_visits')
                        ->where('patient_program_visits.id', '=', $rowId)
                        ->update(
                            array('patient_id' => $patient->id,
                                'program_id' => $program->id,
                                'actual_visit_date' => $actual_visit_date,
                                'incentive_type' => $incentive_type,
                                'incentive_value' => $incentive_value,
                                'gift_card_serial' => $gift_card_serial,
                                'incentive_date_sent' => $incentive_date_sent,
                                'doctor_id' => isset($doctors[$import_data[$patient->username]['doctor_id']]) ? $doctors[$import_data[$patient->username]['doctor_id']] : null,
                                'metric' => $metric_type,
                                'visit_notes' => $visit_notes,
                                //'delivery_date' => $delivery_date,
                                'updated_at' => $created_at));
                }

            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        \DB::commit();
        // find not matching usernames : usernames in the import file that don't exist in the DB
        $nonExistentItemsUsernames = array_diff($usernames, $existing_items);

        $nonExistentItems = [];
        foreach ($nonExistentItemsUsernames as $username) {
            $item = array();

            $item['last_name'] = $import_data[$username]['last_name'];
            $item['first_name'] = $import_data[$username]['first_name'];

            $nonExistentItems[] = $item;
        }

        // build notifications messages to tell the admin which rows aren't existing and which rows have their actual
        // visit dates already set
        $alreadySetItems = [];
        foreach ($already_set_actual_visit_date_usernames as $username) {
            $item = array();

            $item['last_name'] = $import_data[$username]['last_name'];
            $item['first_name'] = $import_data[$username]['first_name'];

            $alreadySetItems[] = $item;
        }
        $message = '';
        if (count($nonExistentItems) > 0) {
            $message .= 'These patients do not exist for this region: <br/>';
            foreach ($nonExistentItems as $item) {
                $message .= ($item['last_name'] . ', ' . $item['first_name'] . '<br/>');
            }
            $message .= '<br/>';
        }

        if (count($alreadySetItems) > 0) {
            $message .= 'These patients already have an actual visit date set: <br/>';
            foreach ($alreadySetItems as $item) {
                $message .= ($item['last_name'] . ', ' . $item['first_name'] . '<br/>');
            }
            $message .= '<br/>';
        }

        if (count($not_matching_doctors) > 0) {
            $message .= 'These doctors IDs do not exist: <br/>';
            foreach ($not_matching_doctors as $item) {
                $message .= ($item . '<br/>');
            }
        }


        if (count($total_incentives_above_annual_limit) > 0) {
            $message .= 'The incentive value for these patients will take them over their annual limit: <br/>';
            foreach ($total_incentives_above_annual_limit as $key => $value) {
                $message .= ($import_data[$key]['last_name'] . ', ' . $import_data[$key]['first_name'] . '<br/>');
            }
        }


        if (count($nonExistentItems) > 0 || count($alreadySetItems) > 0 || count($not_matching_doctors) > 0 || count($total_incentives_above_annual_limit) > 0) {

            // Create a csv file based on $message Begin
            if (strpos(getcwd(), 'public') !== false) {
                $baselink = 'uploads/';
            } else {
                $baselink = 'public/uploads/';
            }
            $filename = $baselink . uniqid() . ".csv";

            $delimiter = ",";
            $f = fopen("$filename", 'w');

            if (count($nonExistentItems) > 0) {
                $line = array("These patients do not exist for this region:");
                fputcsv($f, $line, $delimiter);
                foreach ($nonExistentItems as $item) {
                    $str = $item['last_name'] . ', ' . $item['first_name'];
                    fputcsv($f, array("$str"), $delimiter);
                }
            }

            if (count($alreadySetItems) > 0) {
                $line = array("These patients already have an actual visit date set:");
                fputcsv($f, $line, $delimiter);
                foreach ($alreadySetItems as $item) {
                    $str = $item['last_name'] . ', ' . $item['first_name'];
                    fputcsv($f, array("$str"), $delimiter);
                }
            }

            if (count($not_matching_doctors) > 0) {
                $line = array("These doctors IDs do not exist:");
                fputcsv($f, $line, $delimiter);
                foreach ($not_matching_doctors as $item) {
                    fputcsv($f, array("$item"), $delimiter);
                }
            }

            if (count($total_incentives_above_annual_limit) > 0) {
                $line = array("The incentive value for these patients will take them over their annual limit:");
                fputcsv($f, $line, $delimiter);
                foreach ($total_incentives_above_annual_limit as $key => $value) {
                    $str = $import_data[$key]['last_name'] . ', ' . $import_data[$key]['first_name'];
                    fputcsv($f, array("$str"), $delimiter);
                }
            }

            fclose($f);

            // Create a csv file based on $message End
            //return \Response::make(array('ok' => json_encode($message)), 201);
            return \Response::make(array('ok' => $message, 'result_file' => str_replace("public/", "", $filename)), 201);
        } else {
            return \Response::make(array('ok' => true), 201);
        }
    }

    public function patients_list_csv($id)
    {
        $program = \Program::find($id);
        if ($program == null) {
            return $this->patients_list_csv_no_program_found();
        } else if ($program->type == \Program::TYPE_PREGNANCY) {
            return $this->patients_list_csv_pregnancy_program($program);
        } else if (($program->type == \Program::TYPE_WC15_AHC) || ($program->type == \Program::TYPE_WC15_KF)) {
            return $this->patients_list_csv_wc15_program($program);
        } else {
            return $this->patients_list_csv_other_programs($program);
        }
    }

    private function patients_list_csv_no_program_found()
    {

        $filename = "no program found.csv";
        $f = fopen('php://memory', 'w');

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patients_list_csv_other_programs($program)
    {
        $patients = \DB::table('users')
            ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
            ->where('patient_program.program_id', '=', $program->id)
            ->join('regions', 'regions.id', '=', 'users.region_id')
            ->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                'email', 'abbreviation');

        $delimiter = ",";
        //$filename = $program->region->name . " - " . $program->name . " - Patients.csv";
        $filename = "STELLAR " . $program->region->abbreviation . " $program->abbreviation Member Roster " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of Birth',
            'Sex', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'County', 'Phone', 'Trac Phone', 'Email',
            'Region');
        fputcsv($f, $line, $delimiter);

        $patients->chunk(2000, function ($patients) use ($f, $delimiter) {
            foreach ($patients as $patient) {

                $patient->date_of_birth = \Helpers::format_date_display($patient->date_of_birth);

                $line = array("$patient->username", "$patient->medicaid_id", "$patient->first_name",
                    "$patient->middle_initial", "$patient->last_name", "$patient->date_of_birth", "$patient->sex",
                    "$patient->address1", "$patient->address2", "$patient->city", "$patient->state",
                    "$patient->zip", "$patient->county", "$patient->phone1", "$patient->trac_phone",
                    "$patient->email", "$patient->abbreviation");

                fputcsv($f, $line, $delimiter);

            };
        });

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patients_list_csv_wc15_program($program)
    {
        $patients = \DB::table('users')
            ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
            ->where('patient_program.program_id', '=', $program->id)
            ->join('regions', 'regions.id', '=', 'users.region_id')
            ->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'patient_program.how_did_you_hear')
            ->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                'email', 'abbreviation', 'how_did_you_hears.label', 'confirmed', 'date_added');

        $delimiter = ",";
        //$filename = $program->region->name . " - " . $program->name . " - Patients.csv";
        $filename = "STELLAR " . $program->region->abbreviation . " $program->abbreviation Member Roster " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of Birth',
            'Sex', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'County', 'Phone', 'Trac Phone', 'Email',
            'Region', 'How Did You Hear', 'Confirmed', 'Opt-in Date');
        fputcsv($f, $line, $delimiter);

        $totalMembers = 0;
        $totalConfirmed = 0;
        $totalNonConfirmed = 0;

        $patients->chunk(2000, function ($patients) use ($f, $delimiter, &$totalMembers, &$totalConfirmed, &$totalNonConfirmed) {
            foreach ($patients as $patient) {

                $totalMembers++;
                if ($patient->confirmed) {
                    $totalConfirmed++;
                } else {
                    $totalNonConfirmed++;
                }

                $patient->date_of_birth = \Helpers::format_date_display($patient->date_of_birth);
                $patient->date_added = \Helpers::format_date_display($patient->date_added);
                $patient->confirmed = $patient->confirmed ? 'Y' : 'N';

                $line = array("$patient->username", "$patient->medicaid_id", "$patient->first_name",
                    "$patient->middle_initial", "$patient->last_name", "$patient->date_of_birth", "$patient->sex",
                    "$patient->address1", "$patient->address2", "$patient->city", "$patient->state",
                    "$patient->zip", "$patient->county", "$patient->phone1", "$patient->trac_phone",
                    "$patient->email", "$patient->abbreviation", "$patient->label", "$patient->confirmed",
                    "$patient->date_added");

                fputcsv($f, $line, $delimiter);
            };
        });


        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        fputcsv($f, $line, $delimiter);

        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', 'Total Members', $totalMembers, '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', 'Confirmed', $totalConfirmed, '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', 'NonConfirmed', $totalNonConfirmed, '', '', '', '');
        fputcsv($f, $line, $delimiter);


        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patients_list_csv_pregnancy_program($program)
    {
        $patients = \DB::table('users')
            //->join('pregnancies', 'pregnancies.patient_id', '=', 'users.id')
            ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
            ->leftjoin('pregnancies', 'pregnancies.id', '=', \DB::raw('(
                      SELECT
                        pregnancies.id
                      FROM
                        pregnancies
                      WHERE
                        pregnancies.patient_id = users.id
                      ORDER BY
                        pregnancies.created_at
                      DESC
                    LIMIT 1
                    )'))
            ->join('regions', 'regions.id', '=', 'users.region_id')
            ->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'pregnancies.how_did_you_hear')
            ->where('patient_program.program_id', '=', $program->id)
            ->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'email',
                'abbreviation', 'pregnancies.date_added', 'pregnancies.enrolled_by', 'pregnancies.delivery_date', 'pregnancies.discontinue_date', 'how_did_you_hears.label');

        $delimiter = ",";
        //$filename = $program->region->name . " - " . $program->name . " - Patients.csv";
        $filename = "STELLAR " . $program->region->abbreviation . " $program->abbreviation Member Roster " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of Birth',
            'Sex', 'Address 1', 'Address 2', 'City', 'State', 'Zip', 'County', 'Phone1', 'Trac Phone', 'Email',
            'Region', 'Opt-in Date', 'Enrolled', 'Delivery Date', 'Discontinue Date', 'How Did You Hear');
        fputcsv($f, $line, $delimiter);

        $patients->chunk(2000, function ($patients) use ($f, $delimiter) {
            foreach ($patients as $patient) {

                $patient->date_of_birth = \Helpers::format_date_display($patient->date_of_birth);
                $patient->date_added = \Helpers::format_date_display($patient->date_added);
                if ($patient->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $patient->enrolled_by = 'HC';
                } else if ($patient->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $patient->enrolled_by = 'Stellar';
                } else {
                    $patient->enrolled_by = 'Undefined';
                }
                $patient->delivery_date = \Helpers::format_date_display($patient->delivery_date);
                $patient->discontinue_date = \Helpers::format_date_display($patient->discontinue_date);

                $line = array("$patient->username", "$patient->medicaid_id", "$patient->first_name",
                    "$patient->middle_initial", "$patient->last_name", "$patient->date_of_birth", "$patient->sex",
                    "$patient->address1", "$patient->address2", "$patient->city", "$patient->state",
                    "$patient->zip", "$patient->county", "$patient->phone1", "$patient->trac_phone",
                    "$patient->email", "$patient->abbreviation", "$patient->date_added",
                    "$patient->enrolled_by", "$patient->delivery_date", "$patient->discontinue_date",
                    "$patient->label");

                fputcsv($f, $line, $delimiter);

            };
        });

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function patients_list($id)
    {
        $program = \Program::find($id);

        $user = \User::find(\Sentry::getUser()->id);
        $isSysAdmin = $user->isSysAdmin();

        $query = \DB::table('users')
            ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
            ->join('regions', 'regions.id', '=', 'users.region_id')
            ->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'patient_program.how_did_you_hear')
            ->where('patient_program.program_id', '=', $program->id)
            ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'abbreviation', 'users.id as user_id', 'date_added', 'enrolled_by', 'how_did_you_hears.label', 'confirmed');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($query)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('date_of_birth', function ($model) use (&$isSysAdmin) {
                    return date("m/d/Y", strtotime($model->date_of_birth));
                })
                ->showColumns('abbreviation')
                ->addColumn('date_added', function ($model) use (&$isSysAdmin) {
                    return date("m/d/Y", strtotime($model->date_added));
                })
                ->addColumn('enrolled_by', function ($model) {
                    if ($model->enrolled_by == \Program::ENROLLED_BY_HC) {
                        return 'HC';
                    } else if ($model->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                        return 'Stellar';
                    } else {
                        return 'Undefined';
                    }
                })
                ->showColumns('label')
                ->addColumn('confirmed', function ($model) {
                    if ($model->confirmed) {
                        return 'Y';
                    } else {
                        return 'N';
                    }
                })
                ->showColumns('user_id')
                ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->orderColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth')
                ->make();
        }

        $this->layout->content = View::make('admin/programs/patients')
            ->with('isSysAdmin', $isSysAdmin)
            ->with('program_id', $program->id);

    }

    public function get_patients($id)
    {
        $program = \Program::find($id);

        return $program->get_patients_as_key_value_array();
    }

}
