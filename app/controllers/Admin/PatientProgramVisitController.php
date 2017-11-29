<?php

namespace Admin;

use View;

class PatientProgramVisitController extends BaseController
{

    public function edit($patient_id, $program_id, $patient_program_visit_id)
    {
        $patient_program_visit = \PatientProgramVisit::find($patient_program_visit_id);
        $program = \Program::where('id', $program_id)->first();

        if (!$patient_program_visit) {
            \App::abort(404);
        }
        $patient_program_visit->actual_visit_date = \Helpers::format_date_display($patient_program_visit->actual_visit_date);
        $patient_program_visit->incentive_date_sent = \Helpers::format_date_display($patient_program_visit->incentive_date_sent);
        $patient_program_visit->incentive_returned_date = \Helpers::format_date_display($patient_program_visit->incentive_returned_date);

        $this->layout->content = View::make('admin/regions/patients/patient_program_visits/edit')
            ->with('patient_program_visit', $patient_program_visit)
            ->with('patient_id', $patient_id)
            ->with('program_id', $program_id)
            ->with('program', $program)
            ->with('route', array('admin.patient_program_visits.update', $patient_program_visit_id))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        $input['actual_visit_date'] = \Helpers::format_date_DB($input['actual_visit_date']);
        $input['incentive_date_sent'] = \Helpers::format_date_DB($input['incentive_date_sent']);
        $input['incentive_value'] = str_replace("$", "", $input['incentive_value']);
        $input['gift_card_returned'] = (!empty($input['gift_card_returned']) && $input['gift_card_returned'] == true) ? 1 : 0;

        if (!empty($input['incentive_returned_date'])) {
            $input['incentive_returned_date'] = \Helpers::format_date_DB($input['incentive_returned_date']);
        }

        $patient_program_visit = \PatientProgramVisit::find($id);
        $patient_program_visit->fill($input);
        $patient_program_visit->save();

        return \Redirect::route('admin.programs.patient_visits', array($patient_program_visit->patient_id, $patient_program_visit->program_id))
            ->with('success', 'A patient visit has been successfully updated.');
    }

    public function destroy($id)
    {
        $patient_program_visit = \PatientProgramVisit::find($id);

        if (!$patient_program_visit) {
            \App::abort(404);
        }

        $patient_program_visit->delete();
        return array('ok' => 1);
    }

    public function scheduled_visit_report()
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

        $this->layout->content = View::make('admin/report/scheduled_visits/scheduled_visit_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_scheduled_visit_report')
            ->with('method', 'GET');
    }


    private function scheduled_visit_report_query($program_type, $input, $date_ranges)
    {
        if ($program_type == \Program::TYPE_PREGNANCY) {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->Join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
                    manual_outreaches.id FROM manual_outreaches WHERE
                    manual_outreaches.patient_id = patient_program_visits.patient_id
                    AND manual_outreaches.program_id = patient_program_visits.program_id
                    ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereRaw("( actual_visit_date = ? OR actual_visit_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->whereBetween(\DB::raw('CAST(scheduled_visit_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial',
                    'users.last_name', 'users.date_of_birth', 'date_added', 'scheduled_visit_date',
                    'outreach_date', 'code_name', 'outreach_notes', 'actual_visit_date')
                ->orderBy('patient_program_visits.id');

        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->Join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw("(SELECT manual_outreaches.id FROM manual_outreaches WHERE manual_outreaches.patient_id = patient_program_visits.patient_id AND manual_outreaches.program_id = patient_program_visits.program_id and `manual_outreaches`.`outreach_date` >= DATE_FORMAT( patient_program_visits.scheduled_visit_date, '%Y-01-01' ) and `manual_outreaches`.`outreach_date` <= patient_program_visits.scheduled_visit_date ORDER BY outreach_date DESC LIMIT 1 ) "))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereRaw("( actual_visit_date = ? OR actual_visit_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->whereBetween(\DB::raw('CAST(scheduled_visit_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial',
                    'users.last_name', 'users.date_of_birth', 'scheduled_visit_date', 'outreach_date',
                    'code_name', 'outreach_notes', 'actual_visit_date')
                ->orderBy('patient_program_visits.id');
        }

        return $result;

    }

    public function generate_scheduled_visit_report()
    {
        $input = \Input::all();
        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $result = $this->scheduled_visit_report_query($program->type, $input, $date_ranges);

        if (\Datatable::shouldHandle()) {

            if ($program->type == \Program::TYPE_PREGNANCY) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
                    })
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'scheduled_visit_date', 'actual_visit_date',
                        'outreach_date', 'code_name', 'outreach_notes'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->orderColumns('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'scheduled_visit_date', 'actual_visit_date',
                        'outreach_date', 'code_name', 'outreach_notes')
                    ->make();
            } else {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'scheduled_visit_date', 'actual_visit_date',
                        'outreach_date', 'code_name', 'outreach_notes'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->orderColumns('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'scheduled_visit_date', 'actual_visit_date',
                        'outreach_date', 'code_name', 'outreach_notes')
                    ->make();
            }
        }

        $this->layout->content = View::make('admin/report/scheduled_visits/show_scheduled_visit_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_scheduled_visit_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation Scheduled Visit Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_PREGNANCY) {
            $line = array('Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name',
                'Last Name', 'Date Of Birth', 'Enroll Date', 'Scheduled Visit Date', 'Actual Visit Date',
                'Outreach Date', 'Outreach Code', 'Outreach Notes');
        } else {
            $line = array('Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name',
                'Last Name', 'Date Of Birth', 'Scheduled Visit Date', 'Actual Visit Date',
                'Outreach Date', 'Outreach Code', 'Outreach Notes');
        }

        fputcsv($f, $line, $delimiter);

        $result = $this->scheduled_visit_report_query($program->type, $input, $date_ranges)->get();

        foreach ($result as $item) {
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->metric = \User::metric_toString($item->metric);

            if ($program->type == \Program::TYPE_PREGNANCY) {
                $item->date_added = \Helpers::format_date_display($item->date_added);

                $line = array("$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name",
                    "$item->middle_initial", "$item->last_name", "$item->date_of_birth", "$item->date_added",
                    "$item->scheduled_visit_date", "$item->actual_visit_date", "$item->outreach_date",
                    "$item->code_name", "$item->outreach_notes");
            } else {
                $line = array("$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name",
                    "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->scheduled_visit_date", "$item->actual_visit_date", "$item->outreach_date",
                    "$item->code_name", "$item->outreach_notes");
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/
    }


    public function incentive_report()
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

        $this->layout->content = View::make('admin/report/incentives/incentive_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_incentive_report')
            ->with('method', 'GET');
    }

    private function incentive_report_query_old($program_type, $input, $date_ranges, $report_version)
    {
        // Let's get what date to be filtered - DOS or incentive
        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_NEW)) {
            $report_date = 'incentive_date_sent';

        }
        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD_DOS) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_NEW_DOS)) {
            $report_date = 'actual_visit_date';
        }

        if ($program_type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users as users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->leftjoin('post_partums', 'post_partums.id', '=', 'patient_program_visits.program_instance_id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
                    manual_outreaches.id FROM manual_outreaches WHERE
                    manual_outreaches.patient_id = patient_program_visits.patient_id
                    AND manual_outreaches.program_id = patient_program_visits.program_id
                    ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->leftJoin('member_completed_required_visit_dates', 'patient_program.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(' . $report_date . ' AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.last_name',
                    'users.middle_initial', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county',
                    'users.phone1', 'users.trac_phone', 'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date',
                    'code_name', 'outreach_notes', 'admin.username as creator_username', 'actual_visit_date', 'doctor_id',
                    'visit_notes', 'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial'
                    , 'member_completed_required_visit_dates.title', \DB::raw("'' as cribs_quantity"), 'manually_added',
                    'post_partums.delivery_date', 'post_partums.gestational_age', 'post_partums.birth_weight');
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->leftjoin('pregnancies', 'pregnancies.id', '=', 'patient_program_visits.program_instance_id')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
                    manual_outreaches.id FROM manual_outreaches WHERE
                    manual_outreaches.patient_id = patient_program_visits.patient_id
                    AND manual_outreaches.program_id = patient_program_visits.program_id
                    ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->leftJoin('member_completed_required_visit_dates', 'patient_program.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(' . $report_date . ' AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.last_name',
                    'users.middle_initial', 'users.date_of_birth', 'users.sex', 'users.address1', 'users.address2',
                    'users.city', 'users.state', 'users.zip', 'users.county', 'users.phone1', 'users.trac_phone',
                    'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'admin.username as creator_username', 'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type',
                    'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'member_completed_required_visit_dates.title',
                    'cribs_quantity', 'manually_added');
        }

        return $result;

    }

    private function incentive_report_query_new($program_type, $input, $date_ranges, $report_version)
    {
        // Let's get what date to be filtered - DOS or incentive
        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_NEW)) {
            $report_date = 'incentive_date_sent';

        }
        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD_DOS) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_NEW_DOS)) {
            $report_date = 'actual_visit_date';
        }
        if ($program_type == \Program::TYPE_PREGNANCY) {
            $result = \DB::table('patient_program_visits')
                ->join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->join('pregnancies', 'pregnancies.id', '=', 'patient_program_visits.program_instance_id')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->leftJoin('member_completed_required_visit_dates', 'patient_program.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(' . $report_date . ' AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                //->whereBetween('outreach_date', array($date_ranges[0], $date_ranges[1]))
                ->select('patient_program_visits.id as incentive_id', 'metric', 'users.username', 'users.medicaid_id',
                    'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county', 'users.phone1',
                    'users.trac_phone', 'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username', 'actual_visit_date', 'doctor_id', 'visit_notes',
                    'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial',
                    'member_completed_required_visit_dates.title', 'cribs_quantity', 'manually_added')
                ->orderBy('patient_program_visits.id');

        } else if ($program_type == \Program::TYPE_A1C) {

            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->join('pregnancies', 'pregnancies.id', '=', 'patient_program_visits.program_instance_id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftJoin('manual_outreaches', function ($join) use (&$input, &$date_ranges) {
                    $join->on(\DB::raw(" (`manual_outreaches`.`patient_id` = `patient_program_visits`.`patient_id`
	and `manual_outreaches`.`program_id` = " . $input["program"] . "
	and `manual_outreaches`.`outreach_date` >= DATE_FORMAT(
		patient_program_visits.actual_visit_date,
		'%Y-01-01'
	) and `manual_outreaches`.`outreach_date` <= patient_program_visits.actual_visit_date
	 and manual_outreaches.outreach_metric = patient_program_visits.metric)"), \DB::raw(''), \DB::raw(''));
                })
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->leftJoin('member_completed_required_visit_dates', 'patient_program.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(' . $report_date . ' AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                //->whereBetween('outreach_date', array($date_ranges[0], $date_ranges[1]))
                ->select('patient_program_visits.id as incentive_id', 'metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county', 'users.phone1',
                    'users.trac_phone', 'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username', 'actual_visit_date', 'doctor_id', 'visit_notes',
                    'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial',
                    'member_completed_required_visit_dates.title', 'cribs_quantity', 'manually_added')
                ->orderBy('patient_program_visits.id');

        } else if ($program_type == \Program::TYPE_POSTPARTUM) {

            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->Join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->leftjoin('post_partums', 'post_partums.id', '=', 'patient_program_visits.program_instance_id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftJoin('manual_outreaches', function ($join) use (&$input, &$date_ranges) {
                    $join->on(\DB::raw(" (`manual_outreaches`.`patient_id` = `patient_program_visits`.`patient_id`
	and `manual_outreaches`.`program_id` = " . $input["program"] . "
	and `manual_outreaches`.`program_instance_id` = `post_partums`.`id`
	)"), \DB::raw(''), \DB::raw(''));
                })
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->leftJoin('member_completed_required_visit_dates', 'patient_program.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(' . $report_date . ' AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                //->whereBetween('outreach_date', array($date_ranges[0], $date_ranges[1]))
                ->select('patient_program_visits.id as incentive_id', 'metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county', 'users.phone1',
                    'users.trac_phone', 'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username', 'actual_visit_date', 'doctor_id', 'visit_notes',
                    'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial',
                    'member_completed_required_visit_dates.title', \DB::raw("'' as cribs_quantity"), 'manually_added',
                    'post_partums.delivery_date', 'post_partums.gestational_age', 'post_partums.birth_weight')
                ->orderBy('patient_program_visits.id');
        } else {

            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->Join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'patient_program_visits.patient_id')
                        ->on('patient_program.program_id', '=', 'patient_program_visits.program_id');
                })
                ->join('pregnancies', 'pregnancies.id', '=', 'patient_program_visits.program_instance_id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftJoin('manual_outreaches', function ($join) use (&$input, &$date_ranges) {
                    $join->on(\DB::raw(" (`manual_outreaches`.`patient_id` = `patient_program_visits`.`patient_id`
	and `manual_outreaches`.`program_id` = " . $input["program"] . "
	and `manual_outreaches`.`outreach_date` >= DATE_FORMAT(
		patient_program_visits.actual_visit_date,
		'%Y-01-01'
	) and `manual_outreaches`.`outreach_date` <= patient_program_visits.actual_visit_date)"), \DB::raw(''), \DB::raw(''));
                })
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->leftJoin('member_completed_required_visit_dates', 'patient_program.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(' . $report_date . ' AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                //->whereBetween('outreach_date', array($date_ranges[0], $date_ranges[1]))
                ->select('patient_program_visits.id as incentive_id', 'metric', 'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county', 'users.phone1',
                    'users.trac_phone', 'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username', 'actual_visit_date', 'doctor_id', 'visit_notes',
                    'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial',
                    'member_completed_required_visit_dates.title', 'cribs_quantity', 'manually_added')
                ->orderBy('patient_program_visits.id');
        }

        return $result;

    }

    public function generate_incentive_report()
    {

        /*
        ->leftJoin('patient_program_visits', function ($join) use (&$input) {
        $join->on('patient_program_visits.patient_id', '=', 'patient_program.patient_id')
            ->where('patient_program_visits.program_id', '=', $input["program"]);
            })
        */

        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $report_version = $input['report_version'];

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD_DOS)) {
            $result = $this->incentive_report_query_old($program->type, $input, $date_ranges, $report_version);
        } else {
            $result = $this->incentive_report_query_new($program->type, $input, $date_ranges, $report_version);
        }

        if ($program->type == \Program::TYPE_POSTPARTUM) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes', 'creator_username')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->showColumns('title', 'cribs_quantity')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->showColumns('gestational_age', 'birth_weight')
                    ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name',
                        'users.middle_initial', 'users.last_name', 'users.sex', 'users.address1',
                        'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county',
                        'users.phone1', 'users.trac_phone', 'admin.username'
                        , \DB::Raw("CONCAT(`users`.`first_name`, ' ', `users`.`last_name`)"), \DB::Raw("CONCAT(`users`.`last_name`, ' ', `users`.`first_name`)"))
                    ->make();
            }
        } else {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes', 'creator_username')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->showColumns('title', 'cribs_quantity')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name',
                        'users.middle_initial', 'users.last_name', 'users.sex', 'users.address1',
                        'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county',
                        'users.phone1', 'users.trac_phone', 'admin.username', 'gift_card_serial'
                        , \DB::Raw("CONCAT(`users`.`first_name`, ' ', `users`.`last_name`)"), \DB::Raw("CONCAT(`users`.`last_name`, ' ', `users`.`first_name`)"))
                    ->make();
            }
        }

        $this->layout->content = View::make('admin/report/incentives/show_incentive_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_incentive_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $report_version = $input['report_version'];

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD_DOS)) {
            $result = $this->incentive_report_query_old($program->type, $input, $date_ranges, $report_version)->get();
        } else {
            $result = $this->incentive_report_query_new($program->type, $input, $date_ranges, $report_version)->get();
        }

        $delimiter = ",";
        $version = ($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD) ? 'Old version' : 'New Version';
        if (($report_version == \Program::INCENTIVE_REPORT_VERSION_OLD) OR ($report_version == \Program::INCENTIVE_REPORT_VERSION_NEW)) {
            $report_filter = '(incentive date)';
        } else {
            $report_filter = '(DOS)';
        }
        $filename = "STELLAR $region->abbreviation $program->abbreviation Incentive Report $version $report_filter " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $line = array('Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes', 'Created By',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'Gift', 'Quantity', 'E-script',
                'Delivery Date', 'Gestational Age', 'Birth Weight');
        } else {
            $line = array('Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes', 'Created By',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'Gift', 'Quantity', 'E-script');
        }

        fputcsv($f, $line, $delimiter);

        $last_incentive_id = null;
        $total_incentives = 0;

        foreach ($result as $item) {

            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }
            $item->manually_added = ($item->manually_added) ? "Y" : "N";

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
            }

            if ($report_version == \Program::INCENTIVE_REPORT_VERSION_NEW) {
                if ($item->incentive_id == $last_incentive_id) {
                    $item->actual_visit_date = null;
                    $item->doctor_id = null;
                    $item->visit_notes = null;
                    $item->incentive_type = null;
                    $item->incentive_value = null;
                    $item->incentive_date_sent = null;
                    $item->gift_card_serial = null;
                    $item->manually_added = null;
                    $item->delivery_date = null;
                    $item->gestational_age = null;
                    $item->birth_weight = null;
                } else {
                    $last_incentive_id = $item->incentive_id;
                    $total_incentives++;
                }
            } else {
                $total_incentives++;
            }

            $item->incentive_value = $item->incentive_value ? "$" . $item->incentive_value : null;

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $line = array("$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->creator_username", "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes",
                    "$item->incentive_type", "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial",
                    "$item->title", "$item->cribs_quantity", "$item->manually_added", "$item->delivery_date",
                    "$item->gestational_age", "$item->birth_weight"
                );
            } else {
                $line = array("$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes", "$item->creator_username",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial",
                    "$item->title", "$item->cribs_quantity", "$item->manually_added"
                );
            }

            fputcsv($f, $line, $delimiter);
        }


        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        fputcsv($f, $line, $delimiter);

        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', "Total Incentives", "$total_incentives", '', '', '', '');
        fputcsv($f, $line, $delimiter);

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function member_roster_report()
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
            $programs = $region->get_programs_with_types();

            break;
        }

        $this->layout->content = View::make('admin/report/member_roster/member_roster_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_member_roster_report')
            ->with('method', 'GET');
    }

    private function member_roster_report_query($program, $input, $all_dates_flag, $date_ranges)
    {
        if (($program->type != \Program::TYPE_PREGNANCY && $program->type != \Program::TYPE_WC15_AHC &&
                $program->type != \Program::TYPE_WC15_KF) || $input['report_version'] == \Program::MEMBER_ROSTER_REPORT_MEMBER_ROSTER
        ) {
            $result = \DB::table('users')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $program->id)
                ->join('regions', 'regions.id', '=', 'users.region_id');

            if ($program->type == \Program::TYPE_PREGNANCY) {

                $result = $result->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'patient_program.how_did_you_hear');

                if (!$all_dates_flag) {
                    $result = $result->whereBetween(\DB::raw('CAST(date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]));
                }

                $result = $result->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'email', 'regions.name as region', 'date_added', 'enrolled_by', 'delivery_date', 'discontinue_date', 'how_did_you_hears.label as how_did_you_hear');

            } else if ($program->type == \Program::TYPE_WC15_AHC || $program->type == \Program::TYPE_WC15_KF) {
                $result = $result->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'patient_program.how_did_you_hear');

                if (!$all_dates_flag) {
                    $result = $result->whereBetween(\DB::raw('CAST(date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]));
                }

                $result = $result->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'email', 'regions.name as region', 'date_added', 'enrolled_by', 'discontinue_date',
                    'how_did_you_hears.label as how_did_you_hear');
            } else {
                $result = $result->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'email', 'regions.name as region');
            }
        } else {

            if ($program->type == \Program::TYPE_WC15_AHC || $program->type == \Program::TYPE_WC15_KF) {
                $result = \DB::table('users')
                    ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                    ->leftjoin('pregnancies', 'pregnancies.patient_id', '=', 'users.id')
                    ->where('patient_program.program_id', '=', $program->id)
                    ->join('regions', 'regions.id', '=', 'users.region_id')
                    ->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'pregnancies.how_did_you_hear');

                if (!$all_dates_flag) {
                    $result = $result->whereBetween(\DB::raw('CAST(patient_program.date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]));
                }

                $result = $result->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'email', 'regions.name as region', 'patient_program.date_added', 'patient_program.enrolled_by',
                    'patient_program.discontinue_date', 'how_did_you_hears.label as how_did_you_hear');
            } else {
                $result = \DB::table('users')
                    ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                    ->leftjoin('pregnancies', 'pregnancies.patient_id', '=', 'users.id')
                    ->where('patient_program.program_id', '=', $program->id)
                    ->join('regions', 'regions.id', '=', 'users.region_id')
                    ->leftJoin('how_did_you_hears', 'how_did_you_hears.id', '=', 'pregnancies.how_did_you_hear');

                if (!$all_dates_flag) {
                    $result = $result->whereBetween(\DB::raw('CAST(pregnancies.date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]));
                }

                $result = $result->select('username', 'medicaid_id', 'last_name', 'first_name', 'middle_initial', 'date_of_birth',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'email', 'regions.name as region', 'pregnancies.date_added', 'pregnancies.enrolled_by', 'pregnancies.delivery_date', 'pregnancies.discontinue_date', 'how_did_you_hears.label as how_did_you_hear');
            }
        }


        return $result;
    }

    public function generate_member_roster_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);

        $all_dates_flag = !empty($input['all_dates']);
        if ($program->type == \Program::TYPE_PREGNANCY && !$all_dates_flag && count($date_ranges) < 2) {
            return 'You must select all dates flag or select a date range';
        }

        if (count($date_ranges) > 1) {
            $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
            $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        }

        $report_version = $input['report_version'];

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);


        $result = $this->member_roster_report_query($program, $input, $all_dates_flag, $date_ranges);

        if (\Datatable::shouldHandle()) {
            $query = \Datatable::query($result)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('date_of_birth', function ($model) {
                    return \Helpers::format_date_display($model->date_of_birth);
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'email', 'region');

            if ($program->type == \Program::TYPE_PREGNANCY || $program->type == \Program::TYPE_WC15_AHC || $program->type == \Program::TYPE_WC15_KF) {
                $query->addColumn('date_added', function ($model) {
                    return \Helpers::format_date_display($model->date_added);
                })
                    ->addColumn('enrolled_by', function ($model) {
                        if ($model->enrolled_by == \Program::ENROLLED_BY_HC) {
                            return 'HC';
                        } else if ($model->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                            return 'Stellar';
                        } else {
                            return 'Undefined';
                        }
                    });

                if ($program->type == \Program::TYPE_PREGNANCY) {
                    $query->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    });
                }

                $query->addColumn('discontinue_date', function ($model) {
                    return \Helpers::format_date_display($model->discontinue_date);
                })
                    ->showColumns('how_did_you_hear');
            }
            $query->searchColumns('users.username', 'users.medicaid_id', 'users.first_name',
                'users.middle_initial', 'users.last_name'
                , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"));

            return $query->make();
        }

        $this->layout->content = View::make('admin/report/member_roster/show_member_roster_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_member_roster_report_csv()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);

        $all_dates_flag = isset($input['all_dates']);
        if ($program->type == \Program::TYPE_PREGNANCY && !$all_dates_flag && count($date_ranges) < 2) {
            return 'You must select all dates flag or select a date range';
        }

        if (count($date_ranges) > 1) {
            $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
            $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $result = $this->member_roster_report_query($program, $input, $all_dates_flag, $date_ranges)->get();

        $delimiter = ",";

        $report_version = ($input['report_version'] == \Program::MEMBER_ROSTER_REPORT_MEMBER_ROSTER) ? "Member Roster Report" : "Member Encounter Report New Version";
        $filename = "STELLAR $region->abbreviation $program->abbreviation $report_version " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_PREGNANCY) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Email', 'Region'
            , 'Opt-in Date', 'Enrolled', 'Delivery Date', 'Discontinue Date', 'How Did You Hear');
        } else if ($program->type == \Program::TYPE_WC15_AHC || $program->type == \Program::TYPE_WC15_KF) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Email', 'Region'
            , 'Opt-in Date', 'Enrolled', 'Discontinue Date', 'How Did You Hear');
        } else {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Email', 'Region');
        }

        fputcsv($f, $line, $delimiter);

        $last_incentive_id = null;

        foreach ($result as $item) {
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);

            if ($program->type == \Program::TYPE_PREGNANCY || $program->type == \Program::TYPE_WC15_AHC || $program->type == \Program::TYPE_WC15_KF) {

                $item->date_added = \Helpers::format_date_display($item->date_added);
                if ($program->type == \Program::TYPE_PREGNANCY) {
                    $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
                }
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }
                if ($program->type == \Program::TYPE_PREGNANCY) {
                    $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                        "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                        "$item->county", "$item->phone1", "$item->trac_phone", "$item->email", "$item->region", "$item->date_added",
                        "$item->enrolled_by", "$item->delivery_date", "$item->discontinue_date", "$item->how_did_you_hear"
                    );
                } else {
                    $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                        "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                        "$item->county", "$item->phone1", "$item->trac_phone", "$item->email", "$item->region", "$item->date_added",
                        "$item->enrolled_by", "$item->discontinue_date", "$item->how_did_you_hear"
                    );
                }

            } else {
                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->email", "$item->region"
                );
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function no_incentives_report()
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

        $this->layout->content = View::make('admin/report/no_incentives/no_incentives_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_no_incentives_report')
            ->with('method', 'GET');
    }

    private function no_incentives_report_query($input, $date_ranges)
    {
        $result = \DB::table('users')
            ->join('patient_program', 'users.id', '=', 'patient_program.patient_id')
            ->where('patient_program.program_id', '=', $input["program"])
            ->whereRaw(" users.id not in (
		SELECT
			patient_id
		FROM
			patient_program_visits
		WHERE
			patient_program_visits.program_id = " . $input["program"] . "
			and CAST(incentive_date_sent AS DATE) BETWEEN '" . $date_ranges[0] . "'
			and '" . $date_ranges[1] . "'
	) ")
            ->select('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county', 'users.phone1', 'users.trac_phone');

        return $result;

    }

    public function generate_no_incentives_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);

        $year = $input["incentives_year"];
        $date_ranges[0] = date('Y-m-d 00:00:00', strtotime("first day of january " . $year));
        $date_ranges[1] = date('Y-m-d 23:59:59', strtotime("last day of december " . $year));
        //$date_ranges[0] = date('Y-m-d', strtotime("first day of january " . $year));
        //$date_ranges[1] = date('Y-m-d', strtotime("last day of december " . $year));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $result = $this->no_incentives_report_query($input, $date_ranges);

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('date_of_birth', function ($model) {
                    return \Helpers::format_date_display($model->date_of_birth);
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial',
                    'users.last_name', 'users.sex', 'users.address1', 'users.address2', 'users.city',
                    'users.state', 'users.zip', 'users.county', 'users.phone1', 'users.trac_phone'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();
        }

        $this->layout->content = View::make('admin/report/no_incentives/show_no_incentives_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_no_incentives_report_csv()
    {
        $input = \Input::all();
        $year = $input["incentives_year"];
        $date_ranges[0] = date('Y-m-d 00:00:00', strtotime("first day of january " . $year));
        $date_ranges[1] = date('Y-m-d 23:59:59', strtotime("last day of december " . $year));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $result = $this->no_incentives_report_query($input, $date_ranges)->get();

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation No Incentives Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);


        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
            'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone');

        fputcsv($f, $line, $delimiter);

        //$last_incentive_id = null;
        //$total_incentives = 0;

        foreach ($result as $item) {
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);

            //$total_incentives++;


            $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                "$item->county", "$item->phone1", "$item->trac_phone");

            fputcsv($f, $line, $delimiter);
        }


        /*
        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        fputcsv($f, $line, $delimiter);

        $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
            "Total Incentives", "$total_incentives", '', '', '', '');
        fputcsv($f, $line, $delimiter);
        */

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function patient_outreach_report()
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

        $this->layout->content = View::make('admin/report/patient_outreach/patient_outreach_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_patient_outreach_report')
            ->with('method', 'GET');
    }

    private function patient_outreach_report_query($program_type, $input, $date_ranges)
    {
        if ($program_type == \Program::TYPE_PREGNANCY) {

            $result = \DB::table('manual_outreaches')
                ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->where('manual_outreaches.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(outreach_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip',
                    'users.county', 'users.phone1', 'users.trac_phone', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username');

        } else if ($program_type == \Program::TYPE_A1C) {

            $result = \DB::table('manual_outreaches')
                ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
                ->join('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->where('manual_outreaches.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(outreach_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip',
                    'users.county', 'users.phone1', 'users.trac_phone', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username');

        } else if ($program_type == \Program::TYPE_POSTPARTUM) {

            $result = \DB::table('manual_outreaches')
                ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
                ->Join('patient_program', function ($join) {
                    $join->on('patient_program.patient_id', '=', 'manual_outreaches.patient_id')
                        ->on('patient_program.program_id', '=', 'manual_outreaches.program_id');
                })
                ->leftjoin('post_partums', 'post_partums.id', '=', 'manual_outreaches.program_instance_id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->where('manual_outreaches.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(outreach_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name',
                    'users.date_of_birth', 'users.sex', 'users.address1', 'users.address2', 'users.city', 'users.state',
                    'users.zip', 'users.county', 'users.phone1', 'users.trac_phone', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username',
                    'post_partums.delivery_date', 'post_partums.gestational_age', 'post_partums.birth_weight');


        } else {

            $result = \DB::table('manual_outreaches')
                ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
                ->where('manual_outreaches.program_id', '=', $input["program"])
                ->whereBetween(\DB::raw('CAST(outreach_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name', 'users.date_of_birth', 'users.sex',
                    'users.address1', 'users.address2', 'users.city', 'users.state', 'users.zip',
                    'users.county', 'users.phone1', 'users.trac_phone', 'outreach_date', 'code_name',
                    'outreach_notes', 'admin.username as creator_username');
        }

        return $result;

    }

    public function generate_patient_outreach_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $result = $this->patient_outreach_report_query($program->type, $input, $date_ranges);

        if ($program->type == \Program::TYPE_POSTPARTUM) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes', 'creator_username')
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->showColumns('gestational_age', 'birth_weight')
                    ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name',
                        'users.middle_initial', 'users.last_name', 'users.sex', 'users.address1',
                        'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county',
                        'users.phone1', 'users.trac_phone', 'code_name', 'outreach_notes',
                        'admin.username', 'gestational_age', 'birth_weight'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes', 'creator_username')
                    ->searchColumns('users.username', 'users.medicaid_id', 'users.first_name',
                        'users.middle_initial', 'users.last_name', 'users.sex', 'users.address1',
                        'users.address2', 'users.city', 'users.state', 'users.zip', 'users.county',
                        'users.phone1', 'users.trac_phone', 'code_name', 'outreach_notes',
                        'admin.username'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        }

        $this->layout->content = View::make('admin/report/patient_outreach/show_patient_outreach_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_patient_outreach_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        $result = $this->patient_outreach_report_query($program->type, $input, $date_ranges)->get();

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation Patient Outreach Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name',
                'Middle Name', 'Last Name', 'Date of birth', 'Sex', 'Address1', 'Address2',
                'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Outreach Date',
                'Outreach Code', 'Outreach Notes', 'Created By',
                'Delivery Date', 'Gestational Age', 'Birth Weight');
        } else {
            $line = array('Patient ID', 'Medicaid ID', 'First Name',
                'Middle Name', 'Last Name', 'Date of birth', 'Sex', 'Address1', 'Address2',
                'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Outreach Date',
                'Outreach Code', 'Outreach Notes', 'Created By');
        }

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
            }

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $line = array("$item->username", "$item->medicaid_id",
                    "$item->first_name", "$item->middle_initial", "$item->last_name",
                    "$item->date_of_birth", "$item->sex", "$item->address1", "$item->address2",
                    "$item->city", "$item->state", "$item->zip", "$item->county", "$item->phone1",
                    "$item->trac_phone", "$item->outreach_date", "$item->code_name",
                    "$item->outreach_notes", "$item->creator_username", "$item->delivery_date",
                    "$item->gestational_age", "$item->birth_weight"
                );
            } else {
                $line = array("$item->username", "$item->medicaid_id",
                    "$item->first_name", "$item->middle_initial", "$item->last_name",
                    "$item->date_of_birth", "$item->sex", "$item->address1", "$item->address2",
                    "$item->city", "$item->state", "$item->zip", "$item->county", "$item->phone1",
                    "$item->trac_phone", "$item->outreach_date", "$item->code_name",
                    "$item->outreach_notes", "$item->creator_username",
                );
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function quarterly_incentive_report()
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

        $this->layout->content = View::make('admin/report/quarterly_incentives/quarterly_incentive_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_quarterly_incentive_report')
            ->with('method', 'GET');
    }

    public function generate_quarterly_incentive_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $result = \DB::table('patient_program_visits as ppv')
            ->join('users', 'ppv.patient_id', '=', 'users.id')
            ->join('programs', 'ppv.program_id', '=', 'programs.id')
            ->leftjoin(\DB::raw('( select manual_outreaches.patient_id, manual_outreaches.program_id, Max(id) id from
			manual_outreaches group by manual_outreaches.patient_id, manual_outreaches.program_id ) mo ')
                , function ($join) {
                    $join->on('mo.patient_id', '=', 'ppv.patient_id')
                        ->on('mo.program_id', '=', 'ppv.program_id');
                })
            ->leftjoin(\DB::raw('manual_outreaches m'), 'm.id', '=', 'mo.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'm.outreach_code')
            ->leftjoin(\DB::raw(" (SELECT patient_id, SUM(incentive_value) as total_incentives FROM patient_program_visits WHERE gift_card_returned <> 1 and incentive_value is not null and CAST(incentive_date_sent AS DATE) BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "' GROUP BY patient_id ) o"), function ($join) {
                $join->on('ppv.patient_id', '=', 'o.patient_id');
            })
            ->whereIn('ppv.program_id', array_keys($region->get_programs_as_key_value_array()))
            ->whereBetween(\DB::raw('CAST(incentive_date_sent AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select('name', 'metric', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'gift_card_returned', 'manually_added', 'total_incentives');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('name')
                ->addColumn('metric', function ($model) {
                    return \User::metric_toString($model->metric);
                })
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('date_of_birth', function ($model) {
                    return \Helpers::format_date_display($model->date_of_birth);
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                ->addColumn('scheduled_visit_date', function ($model) {
                    return \Helpers::format_date_display($model->scheduled_visit_date);
                })
                ->showColumns('scheduled_visit_date_notes')
                ->addColumn('outreach_date', function ($model) {
                    return \Helpers::format_date_display($model->outreach_date);
                })
                ->showColumns('code_name', 'outreach_notes')
                ->addColumn('actual_visit_date', function ($model) {
                    return \Helpers::format_date_display($model->actual_visit_date);
                })
                ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                ->addColumn('incentive_date_sent', function ($model) {
                    return \Helpers::format_date_display($model->incentive_date_sent);
                })
                ->showColumns('gift_card_serial')
                ->addColumn('gift_card_returned', function ($model) {
                    if ($model->gift_card_returned) {
                        return 'Y';
                    } else {
                        return 'N';
                    }
                })
                ->addColumn('manually_added', function ($model) {
                    if ($model->manually_added) {
                        return 'Y';
                    } else {
                        return 'N';
                    }
                })
                ->showColumns('total_incentives')
                ->searchColumns('name', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'scheduled_visit_date_notes', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                    'incentive_type', 'incentive_value', 'gift_card_serial', 'manually_added'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();
        }


        $this->layout->content = View::make('admin/report/quarterly_incentives/show_quarterly_incentive_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('input', $input);
        //*/
    }

    public function generate_quarterly_incentive_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $result = \DB::table('patient_program_visits as ppv')
            ->join('users', 'ppv.patient_id', '=', 'users.id')
            ->join('programs', 'ppv.program_id', '=', 'programs.id')
            ->leftjoin(\DB::raw('( select manual_outreaches.patient_id, manual_outreaches.program_id, Max(id) id from
			manual_outreaches group by manual_outreaches.patient_id, manual_outreaches.program_id ) mo ')
                , function ($join) {
                    $join->on('mo.patient_id', '=', 'ppv.patient_id')
                        ->on('mo.program_id', '=', 'ppv.program_id');
                })
            ->leftjoin(\DB::raw('manual_outreaches m'), 'm.id', '=', 'mo.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'm.outreach_code')
            ->leftjoin(\DB::raw(" (SELECT patient_id, SUM(incentive_value) as total_incentives FROM patient_program_visits WHERE gift_card_returned <> 1 and incentive_value is not null and CAST(incentive_date_sent AS DATE) BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "' GROUP BY patient_id ) o"), function ($join) {
                $join->on('ppv.patient_id', '=', 'o.patient_id');
            })
            ->whereIn('ppv.program_id', array_keys($region->get_programs_as_key_value_array()))
            ->whereBetween(\DB::raw('CAST(incentive_date_sent AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select('name', 'metric', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'gift_card_returned', 'manually_added', 'total_incentives')
            ->get();

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation Quarterly Incentive Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        $line = array('Program', 'Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
            'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
            'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
            'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
            'Incentive Date', 'Incentive Code', 'Gift Card Returned', 'E-script', 'Cumulative YTD Incentives');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }
            $item->incentive_value = "$" . $item->incentive_value;
            $item->gift_card_returned = ($item->gift_card_returned) ? "Y" : "N";
            $item->manually_added = ($item->manually_added) ? "Y" : "N";

            if ($item->total_incentives == null) {
                $item->total_incentives = 0;
            }
            $item->total_incentives = "$" . $item->total_incentives;

            $line = array("$item->name", "$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial",
                "$item->gift_card_returned", "$item->manually_added", "$item->total_incentives"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function pregnancy_report()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_pregnancy_regions_as_key_value_array();

        $this->layout->content = View::make('admin/report/pregnancy/pregnancy_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('route', 'admin.reports.generate_pregnancy_report')
            ->with('method', 'GET');
    }

    private function pregnancy_report_query($pregnancy_report_type, $input, $date_ranges, $all_dates_flag)
    {
        if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OPT_IN) {
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                });

            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                'date_added', 'enrolled_by', 'primary_insurance');

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->leftjoin('patient_program_visits', 'pregnancies.id', '=', 'patient_program_visits.program_instance_id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by',
                    'primary_insurance', \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = pregnancies.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"))
                ->orderBy('users.id');

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE) {
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5');

            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                'reason', 'discontinue_date', 'date_added', 'enrolled_by', 'primary_insurance');

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE_WITH_OUTREACHES) {
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->leftjoin('patient_program_visits', 'patient_program_visits.id', '=', \DB::raw('(SELECT
                    patient_program_visits.id FROM patient_program_visits WHERE
                    patient_program_visits.patient_id = pregnancies.patient_id
                    AND patient_program_visits.program_id = pregnancies.program_id
                    ORDER BY patient_program_visits.actual_visit_date DESC LIMIT 1)')
                )
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5');
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason', 'discontinue_date',
                'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by'
                , 'primary_insurance', \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = pregnancies.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"));

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY_OUTREACH) {

            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'users.id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('delivery_date', '<>', '0000-00-00 00:00:00')
                ->where('delivery_date', '<>', 'NULL');
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'delivery_date'
                , 'scheduled_visit_date', 'scheduled_visit_date_notes', 'actual_visit_date', 'doctor_id',
                'visit_notes', 'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by', 'primary_insurance');


        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY) {
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('delivery_date', '<>', '0000-00-00 00:00:00')
                ->where('delivery_date', '<>', 'NULL');
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                'delivery_date', 'date_added', 'enrolled_by', 'primary_insurance');
        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_BLANK_PREGNANCIES) {

            $result_attached_to_preg_program_with_no_pregnancies_instances = \DB::table('users')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->join('programs', 'patient_program.program_id', '=', 'programs.id')
                ->join('regions', 'regions.id', '=', 'programs.region_id')
                ->leftJoin('pregnancies', function ($join) use (&$input) {
                    $join->on('patient_program.patient_id', '=', 'pregnancies.patient_id')
                        ->on('patient_program.program_id', '=', 'pregnancies.program_id');
                })
                ->where('programs.type', '=', \Program::TYPE_PREGNANCY)
                ->whereNull('pregnancies.patient_id')
                ->select('users.username', 'users.first_name', 'users.last_name', 'users.date_of_birth', 'regions.name',
                    'patient_program.date_added', 'patient_program.due_date', 'patient_program.enrolled_by');

            $result_blank_pregnancies_instances = \DB::table('users')
                ->join('pregnancies', 'pregnancies.patient_id', '=', 'users.id')
                ->join('programs', 'pregnancies.program_id', '=', 'programs.id')
                ->join('regions', 'regions.id', '=', 'programs.region_id')
                ->where('programs.type', '=', \Program::TYPE_PREGNANCY)
                ->whereNull('pregnancies.date_added')
                ->select('users.username', 'users.first_name', 'users.last_name', 'users.date_of_birth', 'regions.name',
                    'pregnancies.date_added', 'pregnancies.due_date', 'pregnancies.enrolled_by');

            $result = $result_attached_to_preg_program_with_no_pregnancies_instances->unionAll($result_blank_pregnancies_instances);

        }

        return $result;

    }

    public function generate_pregnancy_report()
    {
        $input = \Input::all();
        $all_dates_flag = !empty($input['all_dates']);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) > 1) {
            $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
            $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        }

        if (count($date_ranges) < 2 && !$all_dates_flag) {
            return 'No date range selected';
        }

        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));
        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;

        $pregnancy_report_type = $input['pregnancy_report_type'];

        $result = $this->pregnancy_report_query($pregnancy_report_type, $input, $date_ranges, $all_dates_flag);

        if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OPT_IN) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('primary_insurance', function ($model) {
                        if ($model->primary_insurance) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OUTREACH) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('primary_insurance', function ($model) {
                        if ($model->primary_insurance) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->showColumns('reason')
                    ->addColumn('discontinue_date', function ($model) {
                        return \Helpers::format_date_display($model->discontinue_date);
                    })
                    ->addColumn('primary_insurance', function ($model) {
                        if ($model->primary_insurance) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'reason'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE_WITH_OUTREACHES) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->showColumns('reason')
                    ->addColumn('discontinue_date', function ($model) {
                        return \Helpers::format_date_display($model->discontinue_date);
                    })
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('primary_insurance', function ($model) {
                        if ($model->primary_insurance) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY_OUTREACH) {
            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('primary_insurance', function ($model) {
                        if ($model->primary_insurance) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'doctor_id',
                        'visit_notes', 'incentive_type', 'incentive_value', 'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();

            }


        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('primary_insurance', function ($model) {
                        if ($model->primary_insurance) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'sex',
                        'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_BLANK_PREGNANCIES) {

            $input = \Input::all();
            $all_dates_flag = !empty($input['all_dates']);
            $date_ranges = explode(" to ", $input["date_range"]);
            if (count($date_ranges) > 1) {
                $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
                $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
            }

            $program = \DB::table('programs')
                ->where('region_id', '=', $input["region"])
                ->where('type', '=', \Program::TYPE_PREGNANCY)
                ->first();
            $input["program"] = $program->id;

            $pregnancy_report_type = $input['pregnancy_report_type'];
            $result = $this->pregnancy_report_query($pregnancy_report_type, $input, $date_ranges, $all_dates_flag)->get();

            $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
            $region = \Region::find($input["region"]);

            $delimiter = ",";
            $report_name = "Blank Pregnancies Report";
            $filename = "STELLAR $region->abbreviation $program->abbreviation $report_name " . \Helpers::today_date_report_name() . ".csv";

            $f = fopen('php://memory', 'w');

            $line = array("Insurance Company: $insurance_company->name", '', '', '');
            fputcsv($f, $line, $delimiter);
            $line = array("Region: $region->name", '', '', '');
            fputcsv($f, $line, $delimiter);
            $line = array("Program: $program->name", '', '', '');
            fputcsv($f, $line, $delimiter);

            $line = array('Member ID', 'First Name', 'Last Name', 'Date of birth',
                'Region', 'Optin Date', 'Due Date', 'Enrolled');

            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                $line = array("$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                    "$item->name", "$item->date_added", "$item->due_date", "$item->enrolled_by"
                );

                fputcsv($f, $line, $delimiter);
            }


            fseek($f, 0);
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');
            fpassthru($f);

            die();

        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $this->layout->content = View::make('admin/report/pregnancy/show_pregnancy_report')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_pregnancy_report_csv()
    {
        $input = \Input::all();
        $all_dates_flag = !empty($input['all_dates']);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2 && !$all_dates_flag) {
            return 'No date range selected';
        }
        if (count($date_ranges) > 1) {
            $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
            $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        }

        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;

        $pregnancy_report_type = $input['pregnancy_report_type'];
        $result = $this->pregnancy_report_query($pregnancy_report_type, $input, $date_ranges, $all_dates_flag)->get();

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $delimiter = ",";

        if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OPT_IN) {
            $report_name = "Active Patient Opt-in Report";

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $report_name = "Active Patient Outreach Report";

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE) {
            $report_name = "Discontinue Report";

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE_WITH_OUTREACHES) {
            $report_name = "Discontinue Report With Outreaches";

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY_OUTREACH) {
            $report_name = "Delivery Outreach Report";

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY) {
            $report_name = "Delivery Report";
        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_BLANK_PREGNANCIES) {
            $report_name = "Blank Pregnancies Report";
        }

        $filename = "STELLAR $region->abbreviation $program->abbreviation $report_name " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OPT_IN) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name',
                'Date of birth', 'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Due Date', 'Opt-in Date', 'Enrolled', 'Primary Insurance');

            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->primary_insurance) {
                    $item->primary_insurance = 'Y';
                } else {
                    $item->primary_insurance = 'N';
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date", "$item->date_added", "$item->enrolled_by", "$item->primary_insurance"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", count($result), '', '', '');
            fputcsv($f, $line, $delimiter);

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Primary Insurance', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->primary_insurance) {
                    $item->primary_insurance = 'Y';
                } else {
                    $item->primary_insurance = 'N';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date", "$item->date_added", "$item->enrolled_by",
                    "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added", "$item->primary_insurance"
                , "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);


        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Discontinue Reason', 'Discontinue Date', 'Primary Insurance');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                if ($item->primary_insurance) {
                    $item->primary_insurance = 'Y';
                } else {
                    $item->primary_insurance = 'N';
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->reason", "$item->discontinue_date", "$item->primary_insurance"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '');
            fputcsv($f, $line, $delimiter);

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DISCONTINUE_WITH_OUTREACHES) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Discontinue Reason', 'Discontinue Date',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Primary Insurance', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->primary_insurance) {
                    $item->primary_insurance = 'Y';
                } else {
                    $item->primary_insurance = 'N';
                }
                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->reason", "$item->discontinue_date",
                    "$item->scheduled_visit_date", "$item->scheduled_visit_date_notes", "$item->outreach_date",
                    "$item->code_name", "$item->outreach_notes", "$item->actual_visit_date", "$item->doctor_id",
                    "$item->visit_notes", "$item->incentive_type", "$item->incentive_value", "$item->incentive_date_sent",
                    "$item->gift_card_serial", "$item->manually_added", "$item->primary_insurance", "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY_OUTREACH) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled',
                'Delivery Date', 'Scheduled Visit Date', 'Scheduled Visit Notes', 'Actual Visit Date', 'Doctor ID',
                'Actual Visit Notes', 'Incentive Type', 'Incentive Amount', 'Incentive Date', 'Incentive Code', 'E-script',
                'Primary Insurance');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->primary_insurance) {
                    $item->primary_insurance = 'Y';
                } else {
                    $item->primary_insurance = 'N';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->delivery_date",
                    "$item->scheduled_visit_date", "$item->scheduled_visit_date_notes", "$item->actual_visit_date",
                    "$item->doctor_id", "$item->visit_notes", "$item->incentive_type", "$item->incentive_value",
                    "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added",
                    "$item->primary_insurance"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);


        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Delivery Date', 'Primary Insurance');

            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->primary_insurance) {
                    $item->primary_insurance = 'Y';
                } else {
                    $item->primary_insurance = 'N';
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->delivery_date", "$item->primary_insurance"
                );

                fputcsv($f, $line, $delimiter);
            }
        }


        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function pregnancy_report_38_weeks()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_pregnancy_regions_as_key_value_array();

        $this->layout->content = View::make('admin/report/pregnancy_38_weeks/pregnancy_38_weeks_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('route', 'admin.reports.generate_pregnancy_report_38_weeks_csv')
            ->with('method', 'GET');
    }

    private function pregnancy_report_38_weeks_query($input)
    {
        //dd($input);
        if ($input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_38_WEEK_REPORT_All_Visits
            || $input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_38_WEEK_REPORT_Date_Only
        ) {
            $date_diff = 14;
        } else {
            $date_diff = 28;
        }

        if ($input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_38_WEEK_REPORT_All_Visits
            || $input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_36_WEEK_REPORT_All_Visits
        ) {

            return \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.program_instance_id', '=', 'pregnancies.id')
                        ->where('patient_program_visits.actual_visit_date', '<>', '0000-00-00 00:00:00')
                        ->where('patient_program_visits.program_id', '=', $input["program"])
                        ->where('patient_program_visits.actual_visit_date', '<>', 'NULL');
                })
                ->leftJoin('member_completed_required_visit_dates', 'pregnancies.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'address1', 'address2',
                    'city', 'state', 'zip', 'phone1', 'date_added', 'enrolled_by', 'due_date',
                    \DB::Raw(" DATE_SUB(due_date, INTERVAL " . $date_diff . " DAY) as week_38_date"),
                    'scheduled_visit_date', 'scheduled_visit_date_notes', 'actual_visit_date', 'doctor_id',
                    'visit_notes', 'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial',
                    'member_completed_required_visit_dates.title', 'cribs_quantity', 'primary_insurance'
                )
                ->orderBy('users.username');
        } else {
            return \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->leftJoin('member_completed_required_visit_dates', 'pregnancies.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'address1', 'address2',
                    'city', 'state', 'zip', 'phone1', 'date_added', 'enrolled_by', 'due_date',
                    \DB::Raw(" DATE_SUB(due_date, INTERVAL " . $date_diff . " DAY) as week_38_date"),
                    'member_completed_required_visit_dates.title', 'cribs_quantity', 'primary_insurance'
                )
                ->orderBy('users.username');
        }

    }

    public function generate_pregnancy_report_38_weeks_csv()
    {
        $input = \Input::all();

        if ($input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_38_WEEK_REPORT_All_Visits
            || $input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_36_WEEK_REPORT_All_Visits
        ) {
            return $this->generate_pregnancy_report_36_38_weeks_csv_all_visits($input);
        } else {
            return $this->generate_pregnancy_report_36_38_weeks_csv_date_only($input);
        }
    }

    public function generate_pregnancy_report_36_38_weeks_csv_all_visits($input)
    {
        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;

        $result = $this->pregnancy_report_38_weeks_query($input)->get();

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $delimiter = ",";

        if ($input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_38_WEEK_REPORT_All_Visits) {
            $week_number = 38;
        } else {
            $week_number = 36;
        }
        $filename = "STELLAR $region->abbreviation $program->abbreviation $week_number Week - All Visits Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);


        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Address1', 'Address2', 'City',
            'State', 'Zip', 'Phone1', 'Opt-in Date', 'Enrolled', 'Due Date', "$week_number week Date",
            'Scheduled Visit Date', 'Scheduled Visit Notes', 'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes',
            'Incentive Type', 'Incentive Amount', 'Incentive Date', 'Incentive Code',
            'Member Completed Required Visit Dates', 'Quantity', 'Primary Insurance');

        fputcsv($f, $line, $delimiter);

        $last_patient_username = null;
        $total_patients = 0;

        foreach ($result as $item) {
            $item->due_date = \Helpers::format_date_display($item->due_date);
            $item->week_38_date = \Helpers::format_date_display($item->week_38_date);
            $item->date_added = \Helpers::format_date_display($item->date_added);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }
            $item->incentive_value = "$" . $item->incentive_value;

            if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                $item->enrolled_by = 'HC';
            } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                $item->enrolled_by = 'Stellar';
            } else {
                $item->enrolled_by = 'Undefined';
            }

            if ($item->primary_insurance) {
                $item->primary_insurance = 'Y';
            } else {
                $item->primary_insurance = 'N';
            }

            if ($item->username != $last_patient_username) {

                $last_patient_username = $item->username;
                $total_patients++;
            }

            $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->address1",
                "$item->address2", "$item->city", "$item->state", "$item->zip", "$item->phone1",
                "$item->date_added", "$item->enrolled_by", "$item->due_date", "$item->week_38_date",
                "$item->scheduled_visit_date", "$item->scheduled_visit_date_notes",
                "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial",
                "$item->title", "$item->cribs_quantity", "$item->primary_insurance"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }

    public function generate_pregnancy_report_36_38_weeks_csv_date_only($input)
    {

        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;

        $result = $this->pregnancy_report_38_weeks_query($input)->get();

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $delimiter = ",";

        if ($input["pregnancy_38_week_report_type"] == \Program::PREGNANCY_38_WEEK_REPORT_Date_Only) {
            $week_number = 38;
        } else {
            $week_number = 36;
        }

        $filename = "STELLAR $region->abbreviation $program->abbreviation $week_number Week - Date Only Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);


        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name',
            'Address1', 'Address2', 'City', 'State', 'Zip', 'Phone1', 'Opt-in Date', 'Enrolled', 'Due Date',
            "$week_number week Date", 'Member Completed Required Visit Dates', 'Quantity', 'Primary Insurance');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->due_date = \Helpers::format_date_display($item->due_date);
            $item->week_38_date = \Helpers::format_date_display($item->week_38_date);
            $item->date_added = \Helpers::format_date_display($item->date_added);

            if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                $item->enrolled_by = 'HC';
            } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                $item->enrolled_by = 'Stellar';
            } else {
                $item->enrolled_by = 'Undefined';
            }

            if ($item->primary_insurance) {
                $item->primary_insurance = 'Y';
            } else {
                $item->primary_insurance = 'N';
            }

            $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial",
                "$item->last_name", "$item->address1", "$item->address2", "$item->city", "$item->state",
                "$item->zip", "$item->phone1", "$item->date_added", "$item->enrolled_by", "$item->due_date",
                "$item->week_38_date", "$item->title", "$item->cribs_quantity", "$item->primary_insurance"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function first_trimester_report()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_first_trimester_regions_as_key_value_array();

        $this->layout->content = View::make('admin/report/first_trimester/first_trimester_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('route', 'admin.reports.generate_first_trimester_report')
            ->with('method', 'GET');
    }

    private function first_trimester_report_query($pregnancy_report_type, $input, $date_ranges)
    {
        if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OPT_IN) {
            $result = \DB::table('first_trimesters')
                ->join('users', 'first_trimesters.patient_id', '=', 'users.id')
                ->where('first_trimesters.program_id', '=', $input["program"])
                ->where('first_trimesters.open', '=', 1)
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->whereBetween(\DB::raw('CAST(date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                    'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                    'date_added', 'enrolled_by');

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $result = \DB::table('first_trimesters')
                ->join('users', 'first_trimesters.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'first_trimesters.patient_id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('first_trimesters.program_id', '=', $input["program"])
                ->where('first_trimesters.open', '=', 1)
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by'
                    , \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = first_trimesters.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"))
                ->orderBy('users.id');

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE) {
            $result = \DB::table('first_trimesters')
                ->join('users', 'first_trimesters.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'first_trimesters.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('first_trimesters.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5')
                ->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason', 'discontinue_date',
                    'date_added', 'enrolled_by');

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE_WITH_OUTREACHES) {
            $result = \DB::table('first_trimesters')
                ->join('users', 'first_trimesters.patient_id', '=', 'users.id')
                ->leftjoin('patient_program_visits', 'patient_program_visits.id', '=', \DB::raw('(SELECT
                    patient_program_visits.id FROM patient_program_visits WHERE
                    patient_program_visits.patient_id = first_trimesters.patient_id
                    AND patient_program_visits.program_id = first_trimesters.program_id
                    ORDER BY patient_program_visits.actual_visit_date DESC LIMIT 1)')
                )
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('discontinue_tracking_reasons', 'first_trimesters.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('first_trimesters.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5')
                ->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason', 'discontinue_date',
                    'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by'
                    , \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = first_trimesters.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"));
        }

        return $result;

    }

    public function generate_first_trimester_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));
        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_FIRST_TRIMESTER)
            ->first();
        $input["program"] = $program->id;

        $pregnancy_report_type = $input['pregnancy_report_type'];

        $result = $this->first_trimester_report_query($pregnancy_report_type, $input, $date_ranges);

        if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OPT_IN) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OUTREACH) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->showColumns('reason')
                    ->addColumn('discontinue_date', function ($model) {
                        return \Helpers::format_date_display($model->discontinue_date);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'reason'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE_WITH_OUTREACHES) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->showColumns('reason')
                    ->addColumn('discontinue_date', function ($model) {
                        return \Helpers::format_date_display($model->discontinue_date);
                    })
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $this->layout->content = View::make('admin/report/first_trimester/show_first_trimester_report')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_first_trimester_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_FIRST_TRIMESTER)
            ->first();
        $input["program"] = $program->id;

        $pregnancy_report_type = $input['pregnancy_report_type'];
        $result = $this->first_trimester_report_query($pregnancy_report_type, $input, $date_ranges)->get();

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $delimiter = ",";

        if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OPT_IN) {
            $report_name = "Active Patient Opt-in Report";

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $report_name = "Active Patient Outreach Report";

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE) {
            $report_name = "Discontinue Report";

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE_WITH_OUTREACHES) {
            $report_name = "Discontinue Report With Outreaches";

        }

        $filename = "STELLAR $region->abbreviation $program->abbreviation $report_name " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OPT_IN) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled');

            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date", "$item->date_added", "$item->enrolled_by"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", count($result), '', '', '');
            fputcsv($f, $line, $delimiter);

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date", "$item->date_added", "$item->enrolled_by",
                    "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                , "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);


        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Discontinue Reason', 'Discontinue Date');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->reason", "$item->discontinue_date"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '');
            fputcsv($f, $line, $delimiter);

        } else if ($pregnancy_report_type == \Program::FIRST_TRIMESTER_REPORT_DISCONTINUE_WITH_OUTREACHES) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Discontinue Reason', 'Discontinue Date',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->reason", "$item->discontinue_date",
                    "$item->scheduled_visit_date", "$item->scheduled_visit_date_notes", "$item->outreach_date",
                    "$item->code_name", "$item->outreach_notes", "$item->actual_visit_date", "$item->doctor_id",
                    "$item->visit_notes", "$item->incentive_type", "$item->incentive_value", "$item->incentive_date_sent",
                    "$item->gift_card_serial", "$item->manually_added", "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);

        }


        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function returned_gift_card_report()
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

        $this->layout->content = View::make('admin/report/returned_gift_card/returned_gift_card_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_returned_gift_card_report')
            ->with('method', 'GET');
    }

    public function generate_returned_gift_card_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $all_dates_flag = !empty($input['all_dates']);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2 && !$all_dates_flag) {
            return 'No date range selected';
        }
        if (count($date_ranges) > 1) {
            $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
            $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
            //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));
        }


        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->leftjoin('post_partums', 'post_partums.id', '=', 'patient_program_visits.program_instance_id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', '1');
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(incentive_returned_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('metric', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'manually_added', 'post_partums.delivery_date',
                'post_partums.gestational_age', 'post_partums.birth_weight', 'incentive_returned_date', 'gift_card_returned_notes');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('delivery_date', function ($model) {
                        return \Helpers::format_date_display($model->delivery_date);
                    })
                    ->showColumns('gestational_age', 'birth_weight')
                    ->addColumn('incentive_returned_date', function ($model) {
                        return \Helpers::format_date_display($model->incentive_returned_date);
                    })
                    ->showColumns('gift_card_returned_notes')
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'gift_card_serial', 'manually_added',
                        'gestational_age', 'birth_weight', 'gift_card_returned_notes'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', 1);
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(incentive_returned_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }

            $result = $result->select('metric', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'manually_added', 'incentive_returned_date', 'gift_card_returned_notes');

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->addColumn('metric', function ($model) {
                        return \User::metric_toString($model->metric);
                    })
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('incentive_returned_date', function ($model) {
                        return \Helpers::format_date_display($model->incentive_returned_date);
                    })
                    ->showColumns('gift_card_returned_notes')
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes', 'doctor_id', 'visit_notes',
                        'incentive_type', 'incentive_value', 'gift_card_serial', 'manually_added',
                        'gift_card_returned_notes'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        }

        $this->layout->content = View::make('admin/report/returned_gift_card/show_returned_gift_card_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_returned_gift_card_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);

        if (count($date_ranges) > 1) {
            $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
            $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
            //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));
        }

        $all_dates_flag = !empty($input['all_dates']);

        if (count($date_ranges) < 2 && !$all_dates_flag) {
            return 'No date range selected';
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \Program::find($input["program"]);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $result = \DB::table('patient_program_visits')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                ->leftjoin('post_partums', 'post_partums.id', '=', 'patient_program_visits.program_instance_id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('patient_program', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', '1');
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(incentive_returned_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('metric', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'manually_added', 'post_partums.delivery_date',
                'post_partums.gestational_age', 'post_partums.birth_weight', 'incentive_returned_date', 'gift_card_returned_notes')
                ->get();
        } else {
            $result = \DB::table('patient_program_visits')
                ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
                //->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
                ->leftjoin('manual_outreaches', 'manual_outreaches.id', '=', \DB::raw('(SELECT
			manual_outreaches.id FROM manual_outreaches WHERE
			manual_outreaches.patient_id = patient_program_visits.patient_id
            AND manual_outreaches.program_id = patient_program_visits.program_id
            ORDER BY outreach_date DESC LIMIT 1)'))
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program_visits.program_id', '=', $input["program"])
                ->where('gift_card_returned', '=', '1');
            if (!$all_dates_flag) {
                $result = $result->whereBetween(\DB::raw('CAST(incentive_returned_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result = $result->select('metric', 'username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'scheduled_visit_date',
                'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                'incentive_date_sent', 'gift_card_serial', 'manually_added', 'incentive_returned_date', 'gift_card_returned_notes')
                ->get();
        }

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation Returned Gift Card Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        if ($program->type == \Program::TYPE_POSTPARTUM) {
            $line = array('Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script',
                'Delivery Date', 'Gestational Age', 'Birth Weight', 'Incentive Returned Date', 'Gift Card Returned Notes');
        } else {
            $line = array('Metric', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Incentive Returned Date', 'Gift Card Returned Notes');
        }

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }
            $item->incentive_value = "$" . $item->incentive_value;
            $item->manually_added = ($item->manually_added) ? "Y" : "N";
            $item->incentive_returned_date = \Helpers::format_date_display($item->incentive_returned_date);

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $item->delivery_date = \Helpers::format_date_display($item->delivery_date);
            }

            if ($program->type == \Program::TYPE_POSTPARTUM) {
                $line = array("$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added",
                    "$item->delivery_date", "$item->gestational_age", "$item->birth_weight",
                    "$item->incentive_returned_date", "$item->gift_card_returned_notes"
                );
            } else {
                $line = array("$item->metric", "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                , "$item->incentive_returned_date", "$item->gift_card_returned_notes"
                );
            }

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function outreach_codes_report()
    {
        $outreach_codes = \OutreachCode::orderBy('code_name')->get()->lists('code_name', 'id');
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

        $this->layout->content = View::make('admin/report/outreach_codes/outreach_codes_report')
            ->with('outreach_codes', $outreach_codes)
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('programs', $programs)
            ->with('route', 'admin.reports.generate_outreach_codes_report')
            ->with('method', 'GET');
    }

    public function generate_outreach_codes_report()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $input['outreach_codes'] = isset($input['outreach_codes']) ? $input['outreach_codes'] : array();

        $result = \DB::table('manual_outreaches')
            ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
            ->where('manual_outreaches.program_id', '=', $input["program"])
            ->whereIn('manual_outreaches.outreach_code', $input['outreach_codes'])
            ->whereBetween(\DB::raw('CAST(outreach_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'outreach_date', 'code_name', 'outreach_notes');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('date_of_birth', function ($model) {
                    return \Helpers::format_date_display($model->date_of_birth);
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                ->addColumn('outreach_date', function ($model) {
                    return \Helpers::format_date_display($model->outreach_date);
                })
                ->showColumns('code_name', 'outreach_notes')
                ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'sex',
                    'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'code_name', 'outreach_notes'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();
        }

        $this->layout->content = View::make('admin/report/outreach_codes/show_outreach_codes_report')
            //->with('outreach_codes', $outreach_codes)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
        //*/
    }

    public function generate_outreach_codes_report_csv()
    {
        $input = \Input::all();
        $program = \Program::find($input["program"]);
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $input['outreach_codes'] = isset($input['outreach_codes']) ? explode(",", $input['outreach_codes']) : array();

        $result = \DB::table('manual_outreaches')
            ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
            ->where('manual_outreaches.program_id', '=', $input["program"])
            ->whereIn('manual_outreaches.outreach_code', $input['outreach_codes'])
            ->whereBetween(\DB::raw('CAST(outreach_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'outreach_date', 'code_name', 'outreach_notes')
            ->get();

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation Outreach Code Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
            'Gender', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone', 'Trac Phone',
            'Outreach Date', 'Outreach Code', 'Outreach Notes');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);

            $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                "$item->county", "$item->phone1", "$item->trac_phone", "$item->outreach_date",
                "$item->code_name", "$item->outreach_notes"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function user_activity_report()
    {

        $admins = \User::getAllAdminsAndClients();

        $this->layout->content = View::make('admin/report/user_activity/user_activity_report')
            ->with('admins', $admins)
            ->with('route', 'admin.reports.generate_user_activity_report')
            ->with('method', 'GET');
    }

    public function generate_user_activity_report()
    {
        $input = \Input::all();
        if (gettype($input["admins"]) == "string") {
            $input["admins"] = json_decode($input["admins"]);
        }

        if ($input['report_type'] == \Program::USER_ACTIVITY_REPORT_OUTREACH) {
            return $this->generate_user_activity_report_outreach($input);
        } else if ($input['report_type'] == \Program::USER_ACTIVITY_REPORT_INCENTIVE) {
            return $this->generate_user_activity_report_incentive($input);
        } else {
            return $this->generate_user_activity_report_all_csv($input);
        }
    }

    private function generate_user_activity_report_outreach($input)
    {
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $admins = \User::find($input["admins"]);

        $result = \DB::table('manual_outreaches')
            ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
            ->join('programs', 'manual_outreaches.program_id', '=', 'programs.id')
            ->join('regions', 'programs.region_id', '=', 'regions.id')
            ->join('insurance_companies', 'regions.insurance_company_id', '=', 'insurance_companies.id')
            ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
            ->whereIn('created_by', $input["admins"])
            ->whereBetween(\DB::raw('CAST(manual_outreaches.created_at AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select(\DB::raw("CONCAT(admin.first_name, ' ', admin.last_name) as admin_full_name"),
                'insurance_companies.name as insurance_company', 'regions.name as region',
                'programs.name as program', 'users.username', 'users.medicaid_id', 'users.first_name',
                'users.middle_initial', 'users.last_name', 'outreach_date', 'code_name', 'outreach_notes',
                'manual_outreaches.created_at');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('admin_full_name', 'insurance_company', 'region', 'program', 'username',
                    'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('outreach_date', function ($model) {
                    return \Helpers::format_date_display($model->outreach_date);
                })
                ->showColumns('code_name', 'outreach_notes')
                ->addColumn('created_at', function ($model) {
                    return \Helpers::format_date_display($model->created_at);
                })
                ->searchColumns('insurance_company', 'region', 'program', 'username', 'medicaid_id', 'first_name',
                    'middle_initial', 'last_name', 'code_name', 'outreach_notes'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();
        }

        $this->layout->content = View::make('admin/report/user_activity/show_user_activity_report')
            //->with('patients', $result)
            ->with('admins', $admins)
            ->with('input', $input);
    }

    private function generate_user_activity_report_incentive($input)
    {
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $admins = \User::find($input["admins"]);

        $result = \DB::table('patient_program_visits')
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->join('programs', 'patient_program_visits.program_id', '=', 'programs.id')
            ->join('regions', 'programs.region_id', '=', 'regions.id')
            ->join('insurance_companies', 'regions.insurance_company_id', '=', 'insurance_companies.id')
            ->leftjoin('users as admin', 'patient_program_visits.created_by', '=', 'admin.id')
            ->whereIn('created_by', $input["admins"])
            ->whereBetween(\DB::raw('CAST(patient_program_visits.created_at AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select(\DB::raw("CONCAT(admin.first_name, ' ', admin.last_name) as admin_full_name"),
                'insurance_companies.name as insurance_company', 'regions.name as region', 'programs.name as program',
                'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name',
                'incentive_type', 'gift_card_serial', 'incentive_date_sent', 'patient_program_visits.created_at');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('admin_full_name', 'insurance_company', 'region', 'program', 'username', 'medicaid_id',
                    'first_name', 'middle_initial', 'last_name', 'incentive_type', 'gift_card_serial')
                ->addColumn('incentive_date_sent', function ($model) {
                    return \Helpers::format_date_display($model->incentive_date_sent);
                })
                ->addColumn('created_at', function ($model) {
                    return \Helpers::format_date_display($model->created_at);
                })
                ->searchColumns('insurance_company', 'region', 'program', 'username', 'medicaid_id', 'first_name',
                    'middle_initial', 'last_name', 'incentive_type', 'gift_card_serial'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();
        }


        $this->layout->content = View::make('admin/report/user_activity/show_user_activity_report')
            //->with('patients', $result)
            ->with('admins', $admins)
            ->with('input', $input);
    }


    public function generate_user_activity_report_csv()
    {
        $input = \Input::all();

        if (gettype($input["admins"]) == "string") {
            $input["admins"] = json_decode($input["admins"]);
        }

        if ($input['report_type'] == \Program::USER_ACTIVITY_REPORT_OUTREACH) {
            return $this->generate_user_activity_report_outreach_csv($input);
        } else {
            return $this->generate_user_activity_report_incentive_csv($input);
        }
    }

    private function generate_user_activity_report_outreach_csv($input)
    {
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $admins = \User::find($input["admins"]);

        $result = \DB::table('manual_outreaches')
            ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
            ->join('programs', 'manual_outreaches.program_id', '=', 'programs.id')
            ->join('regions', 'programs.region_id', '=', 'regions.id')
            ->join('insurance_companies', 'regions.insurance_company_id', '=', 'insurance_companies.id')
            ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
            ->whereIn('created_by', $input["admins"])
            ->whereBetween(\DB::raw('CAST(manual_outreaches.created_at AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select(\DB::raw("CONCAT(admin.first_name, ' ', admin.last_name) as admin_full_name"),
                'insurance_companies.name as insurance_company', 'regions.name as region', 'programs.name as program',
                'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name',
                'outreach_date', 'code_name', 'outreach_notes', 'manual_outreaches.created_at')
            ->get();

        $delimiter = ",";
        $filename = "STELLAR User Activity - Outreach Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('User', 'Insurance Company', 'Region', 'Program', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name',
            'Outreach Date', 'Outreach Code', 'Outreach Notes', 'Outreach Added Date');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->created_at = \Helpers::format_date_display($item->created_at);

            $line = array("$item->admin_full_name", "$item->insurance_company", "$item->region", "$item->program",
                "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial",
                "$item->last_name", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                "$item->created_at"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }

    private function generate_user_activity_report_incentive_csv($input)
    {
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $admins = \User::find($input["admins"]);

        $result = \DB::table('patient_program_visits')
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->join('programs', 'patient_program_visits.program_id', '=', 'programs.id')
            ->join('regions', 'programs.region_id', '=', 'regions.id')
            ->join('insurance_companies', 'regions.insurance_company_id', '=', 'insurance_companies.id')
            ->whereIn('created_by', $input["admins"])
            ->leftjoin('users as admin', 'patient_program_visits.created_by', '=', 'admin.id')
            ->whereBetween(\DB::raw('CAST(patient_program_visits.created_at AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select(\DB::raw("CONCAT(admin.first_name, ' ', admin.last_name) as admin_full_name"),
                'insurance_companies.name as insurance_company', 'regions.name as region', 'programs.name as program',
                'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial', 'users.last_name',
                'incentive_type', 'gift_card_serial', 'incentive_date_sent', 'patient_program_visits.created_at')
            ->get();

        $delimiter = ",";
        $filename = "STELLAR User Activity - Incentive Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('User', 'Insurance Company', 'Region', 'Program', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name',
            'Incentive Type', 'Gift Card Serial', 'Incentive Date Sent', 'Incentive Added Date');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->created_at = \Helpers::format_date_display($item->created_at);
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }


            $line = array("$item->admin_full_name", "$item->insurance_company", "$item->region", "$item->program",
                "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial",
                "$item->last_name", "$item->incentive_type", "$item->gift_card_serial", "$item->incentive_date_sent",
                "$item->created_at"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }

    private function generate_user_activity_report_all_csv($input)
    {
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $admins = \User::find($input["admins"]);

        $outreaches = \DB::table('manual_outreaches')
            ->join('users', 'manual_outreaches.patient_id', '=', 'users.id')
            ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
            ->join('programs', 'manual_outreaches.program_id', '=', 'programs.id')
            ->join('regions', 'programs.region_id', '=', 'regions.id')
            ->join('insurance_companies', 'regions.insurance_company_id', '=', 'insurance_companies.id')
            ->leftjoin('users as admin', 'manual_outreaches.created_by', '=', 'admin.id')
            ->whereIn('created_by', $input["admins"])
            ->whereBetween(\DB::raw('CAST(manual_outreaches.created_at AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select(\DB::raw("CONCAT(admin.first_name, ' ', admin.last_name) as admin_full_name"),
                'insurance_companies.name as insurance_company', 'regions.name as region', 'programs.name as program',
                'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial',
                'users.last_name', 'outreach_date', 'code_name', 'outreach_notes',
                'manual_outreaches.created_at as outreach_created_at', \DB::raw("'' as incentive_type"),
                \DB::raw("'' as gift_card_serial"), \DB::raw("'' as incentive_date_sent"), \DB::raw("'' as incentive_created_at"));

        $incentives = \DB::table('patient_program_visits')
            ->join('users', 'patient_program_visits.patient_id', '=', 'users.id')
            ->join('programs', 'patient_program_visits.program_id', '=', 'programs.id')
            ->join('regions', 'programs.region_id', '=', 'regions.id')
            ->join('insurance_companies', 'regions.insurance_company_id', '=', 'insurance_companies.id')
            ->leftjoin('users as admin', 'patient_program_visits.created_by', '=', 'admin.id')
            ->whereIn('created_by', $input["admins"])
            ->whereBetween(\DB::raw('CAST(patient_program_visits.created_at AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select(\DB::raw("CONCAT(admin.first_name, ' ', admin.last_name) as admin_full_name"),
                'insurance_companies.name as insurance_company', 'regions.name as region', 'programs.name as program',
                'users.username', 'users.medicaid_id', 'users.first_name', 'users.middle_initial',
                'users.last_name', \DB::raw("'' as outreach_date"), \DB::raw("'' as code_name"),
                \DB::raw("'' as outreach_notes"), \DB::raw("'' as outreach_created_at"), 'incentive_type',
                'gift_card_serial', 'incentive_date_sent',
                'patient_program_visits.created_at as incentive_created_at');

        $result = $outreaches->unionAll($incentives)->get();

        $delimiter = ",";
        $filename = "STELLAR All User Activity Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('User', 'Insurance Company', 'Region', 'Program', 'Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name',
            'Outreach Date', 'Outreach Code', 'Outreach Notes', 'Outreach Datestamp',
            'Incentive Type', 'Gift Card Serial', 'Incentive Date Sent', 'Incentive Datestamp');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
            $item->outreach_created_at = \Helpers::format_date_display($item->outreach_created_at);
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            $item->incentive_created_at = \Helpers::format_date_display($item->incentive_created_at);
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }

            $line = array("$item->admin_full_name", "$item->insurance_company", "$item->region", "$item->program",
                "$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial",
                "$item->last_name", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                "$item->outreach_created_at", "$item->incentive_type", "$item->gift_card_serial",
                "$item->incentive_date_sent", "$item->incentive_created_at"
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();


    }


    public function wc15_report()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_wc15_regions_as_key_value_array();

        $this->layout->content = View::make('admin/report/wc15/wc15_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('route', 'admin.reports.generate_wc15_report')
            ->with('method', 'GET');
    }

    private function wc15_report_query($wc15_report_type, $input, $date_ranges)
    {
        if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OPT_IN) {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->where('confirmed', '=', '1')
                ->whereBetween(\DB::raw('CAST(date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                    'date_of_birth', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1',
                    'trac_phone', 'due_date', 'date_added', 'enrolled_by');

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OPT_IN) {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->where(function ($query) {
                    $query->whereNull('confirmed')
                        ->orWhere('confirmed', '=', 0);
                })
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                    'date_of_birth', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1',
                    'trac_phone', 'due_date', 'date_added', 'enrolled_by');

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OUTREACH) {

            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'patient_program.patient_id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->where('confirmed', '=', '1')
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by'
                    , \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = patient_program.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"))
                ->orderBy('users.id');

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OUTREACH) {

            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'patient_program.patient_id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })
                ->where(function ($query) {
                    $query->whereNull('confirmed')
                        ->orWhere('confirmed', '=', 0);
                })
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'scheduled_visit_date',
                    'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by'
                    , \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = patient_program.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"))
                ->orderBy('users.id');

        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE) {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_wc15_reasons', 'patient_program.discontinue_reason_id', '=', 'discontinue_tracking_wc15_reasons.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason', 'discontinue_date',
                    'date_added', 'enrolled_by');

        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE_WITH_OUTREACHES) {
            $result = \DB::table('patient_program')
                ->join('users', 'patient_program.patient_id', '=', 'users.id')
                ->leftjoin('patient_program_visits', 'patient_program_visits.id', '=', \DB::raw('(SELECT
                    patient_program_visits.id FROM patient_program_visits WHERE
                    patient_program_visits.patient_id = patient_program.patient_id
                    AND patient_program_visits.program_id = patient_program.program_id
                    ORDER BY patient_program_visits.actual_visit_date DESC LIMIT 1)')
                )
                ->leftjoin('manual_outreaches', 'manual_outreaches.patient_program_visits_id', '=', 'patient_program_visits.id')
                ->leftjoin('outreach_codes', 'outreach_codes.id', '=', 'manual_outreaches.outreach_code')
                ->join('discontinue_tracking_wc15_reasons', 'patient_program.discontinue_reason_id', '=', 'discontinue_tracking_wc15_reasons.id')
                ->where('patient_program.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
                ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                    'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason', 'discontinue_date',
                    'scheduled_visit_date', 'scheduled_visit_date_notes', 'outreach_date', 'code_name', 'outreach_notes',
                    'actual_visit_date', 'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                    'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by'
                    , \DB::Raw("( SELECT min(scheduled_visit_date) FROM patient_program_visits where patient_id = patient_program.patient_id and program_id = " . $input["program"] . " and actual_visit_date is null) as next_scheduled_visit"));

        }
        return $result;

    }

    public function generate_wc15_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));
        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where(function ($query) {
                $query->where('type', '=', \Program::TYPE_WC15_AHC)
                    ->orWhere('type', '=', \Program::TYPE_WC15_KF);
            })
            ->first();

        $input["program"] = $program->id;

        $wc15_report_type = $input['pregnancy_report_type'];

        $result = $this->wc15_report_query($wc15_report_type, $input, $date_ranges);

        if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OPT_IN) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OUTREACH) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OPT_IN) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OUTREACH) {
            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }

        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->showColumns('reason')
                    ->addColumn('discontinue_date', function ($model) {
                        return \Helpers::format_date_display($model->discontinue_date);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                        'reason'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE_WITH_OUTREACHES) {

            if (\Datatable::shouldHandle()) {
                return \Datatable::query($result)
                    ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                    ->addColumn('date_of_birth', function ($model) {
                        return \Helpers::format_date_display($model->date_of_birth);
                    })
                    ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                    ->addColumn('due_date', function ($model) {
                        return \Helpers::format_date_display($model->due_date);
                    })
                    ->addColumn('date_added', function ($model) {
                        return \Helpers::format_date_display($model->date_added);
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
                    ->showColumns('reason')
                    ->addColumn('discontinue_date', function ($model) {
                        return \Helpers::format_date_display($model->discontinue_date);
                    })
                    ->addColumn('scheduled_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->scheduled_visit_date);
                    })
                    ->showColumns('scheduled_visit_date_notes')
                    ->addColumn('outreach_date', function ($model) {
                        return \Helpers::format_date_display($model->outreach_date);
                    })
                    ->showColumns('code_name', 'outreach_notes')
                    ->addColumn('actual_visit_date', function ($model) {
                        return \Helpers::format_date_display($model->actual_visit_date);
                    })
                    ->showColumns('doctor_id', 'visit_notes', 'incentive_type', 'incentive_value')
                    ->addColumn('incentive_date_sent', function ($model) {
                        return \Helpers::format_date_display($model->incentive_date_sent);
                    })
                    ->showColumns('gift_card_serial')
                    ->addColumn('manually_added', function ($model) {
                        if ($model->manually_added) {
                            return 'Y';
                        } else {
                            return 'N';
                        }
                    })
                    ->addColumn('next_scheduled_visit', function ($model) {
                        return \Helpers::format_date_display($model->next_scheduled_visit);
                    })
                    ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                        'sex', 'address1', 'address2',
                        'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'reason',
                        'scheduled_visit_date_notes', 'code_name', 'outreach_notes',
                        'doctor_id', 'visit_notes', 'incentive_type', 'incentive_value',
                        'gift_card_serial', 'manually_added'
                        , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                    ->make();
            }
        }

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $this->layout->content = View::make('admin/report/wc15/show_wc15_report')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('program', $program)
            ->with('input', $input);
    }

    public function generate_wc15_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where(function ($query) {
                $query->where('type', '=', \Program::TYPE_WC15_AHC)
                    ->orWhere('type', '=', \Program::TYPE_WC15_KF);
            })
            ->first();
        $input["program"] = $program->id;

        $wc15_report_type = $input['pregnancy_report_type'];
        $result = $this->wc15_report_query($wc15_report_type, $input, $date_ranges)->get();

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $delimiter = ",";

        if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OPT_IN) {
            $report_name = "Active Confirmed - Opt-in Report";

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OUTREACH) {
            $report_name = "Active Confirmed - Outreach Report";

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OPT_IN) {
            $report_name = "Active NonConfirmed - Opt-in Report";

        } else if ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OUTREACH) {
            $report_name = "Active NonConfirmed - Outreach Report";

        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE) {
            $report_name = "Discontinued Report";

        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE_WITH_OUTREACHES) {
            $report_name = "Discontinued - Outreach Report";
        }

        $filename = "STELLAR $region->abbreviation $program->abbreviation $report_name " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);

        if (($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OPT_IN)
            || ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OPT_IN)
        ) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth', 'Sex', 'Address1',
                'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date', 'Opt-in Date', 'Enrolled');

            fputcsv($f, $line, $delimiter);

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date", "$item->date_added", "$item->enrolled_by"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", count($result), '', '', '');
            fputcsv($f, $line, $delimiter);

        } else if (($wc15_report_type == \Program::WC15_REPORT_ACTIVE_CONFIRMED_OUTREACH)
            || ($wc15_report_type == \Program::WC15_REPORT_ACTIVE_NONCONFIRMED_OUTREACH)
        ) {
            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name',
                'Last Name', 'Date of birth', 'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip',
                'County', 'Phone1', 'TracPhone', 'Due Date', 'Opt-in Date', 'Enrolled',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code',
                'Outreach Notes', 'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type',
                'Incentive Amount', 'Incentive Date', 'Incentive Code', 'E-script', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date", "$item->date_added", "$item->enrolled_by",
                    "$item->scheduled_visit_date",
                    "$item->scheduled_visit_date_notes", "$item->outreach_date", "$item->code_name", "$item->outreach_notes",
                    "$item->actual_visit_date", "$item->doctor_id", "$item->visit_notes", "$item->incentive_type",
                    "$item->incentive_value", "$item->incentive_date_sent", "$item->gift_card_serial", "$item->manually_added"
                , "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);


        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Discontinue Reason', 'Discontinue Date');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->reason", "$item->discontinue_date"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '');
            fputcsv($f, $line, $delimiter);

        } else if ($wc15_report_type == \Program::WC15_REPORT_DISCONTINUE_WITH_OUTREACHES) {

            $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
                'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone1', 'TracPhone', 'Due Date',
                'Opt-in Date', 'Enrolled', 'Discontinue Reason', 'Discontinue Date',
                'Scheduled Visit Date', 'Scheduled Visit Notes', 'Outreach Date', 'Outreach Code', 'Outreach Notes',
                'Actual Visit Date', 'Doctor ID', 'Actual Visit Notes', 'Incentive Type', 'Incentive Amount',
                'Incentive Date', 'Incentive Code', 'E-script', 'Next Scheduled Visit');

            fputcsv($f, $line, $delimiter);

            $last_patient_username = null;
            $total_patients = 0;

            foreach ($result as $item) {
                $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
                $item->due_date = \Helpers::format_date_display($item->due_date);
                $item->date_added = \Helpers::format_date_display($item->date_added);
                $item->discontinue_date = \Helpers::format_date_display($item->discontinue_date);
                $item->scheduled_visit_date = \Helpers::format_date_display($item->scheduled_visit_date);
                $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
                $item->outreach_date = \Helpers::format_date_display($item->outreach_date);
                $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
                $item->incentive_type = ($item->incentive_type !== null) ? $item->incentive_type : 'Not Available';
                $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
                if ($item->gift_card_serial != 'Not Available') {
                    $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
                }
                $item->incentive_value = "$" . $item->incentive_value;
                $item->manually_added = ($item->manually_added) ? "Y" : "N";
                $item->next_scheduled_visit = \Helpers::format_date_display($item->next_scheduled_visit);

                if ($item->enrolled_by == \Program::ENROLLED_BY_HC) {
                    $item->enrolled_by = 'HC';
                } else if ($item->enrolled_by == \Program::ENROLLED_BY_STELLAR) {
                    $item->enrolled_by = 'Stellar';
                } else {
                    $item->enrolled_by = 'Undefined';
                }

                if ($item->username != $last_patient_username) {

                    $last_patient_username = $item->username;
                    $total_patients++;
                }

                $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial", "$item->last_name", "$item->date_of_birth",
                    "$item->sex", "$item->address1", "$item->address2", "$item->city", "$item->state", "$item->zip",
                    "$item->county", "$item->phone1", "$item->trac_phone", "$item->due_date",
                    "$item->date_added", "$item->enrolled_by", "$item->reason", "$item->discontinue_date",
                    "$item->scheduled_visit_date", "$item->scheduled_visit_date_notes", "$item->outreach_date",
                    "$item->code_name", "$item->outreach_notes", "$item->actual_visit_date", "$item->doctor_id",
                    "$item->visit_notes", "$item->incentive_type", "$item->incentive_value", "$item->incentive_date_sent",
                    "$item->gift_card_serial", "$item->manually_added", "$item->next_scheduled_visit"
                );

                fputcsv($f, $line, $delimiter);
            }

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', "Total Members", $total_patients, '', '', '', '', '', '',);
            fputcsv($f, $line, $delimiter);

        }


        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/

    }


    public function cumulative_incentive_report()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_regions_as_key_value_array();

        $this->layout->content = View::make('admin/report/cumulative_incentive_report/cumulative_incentive_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('route', 'admin.reports.generate_cumulative_incentive_report')
            ->with('method', 'GET');
    }

    private function cumulative_incentive_report_query($cumulative_incentive_report_type, $input, $date_ranges)
    {
        if ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE) {

            $result = \DB::select(\DB::Raw("SELECT
    region_of_program.name AS programs_region,
    region_of_member.name AS members_region,
	programs.name,
	metric,
	users.medicaid_id,
	users.username,
	users.first_name,
	users.last_name,
	users.date_of_birth,
	users.sex,
	actual_visit_date,
	incentive_type,
	incentive_value,
	incentive_date_sent,
	gift_card_serial,
	total_incentives
FROM
	patient_program_visits ppv
	join users on users.id = ppv.patient_id
	JOIN programs on ppv.program_id = programs.id
	JOIN
      regions AS region_of_program ON(
        region_of_program.id = programs.region_id
      )
    JOIN
      regions AS region_of_member ON(
        region_of_member.id = users.region_id
      )
	JOIN (
		SELECT
			patient_id,
			SUM(incentive_value) as total_incentives
		FROM
			patient_program_visits
		WHERE gift_card_returned <> 1
	        and incentive_value is not null
	        and incentive_date_sent BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "'	   
		GROUP BY
			patient_id
	) o ON ppv.patient_id = o.patient_id
WHERE
	users.region_id = '" . $input['region'] . "'
	and programs.region_id = '" . $input['region'] . "'
	and gift_card_returned <> 1
	and incentive_value is not null
	and incentive_date_sent BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "'	
ORDER BY
	users.username"));

        } else if ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS) {

            $result = \DB::select(\DB::Raw("SELECT
    region_of_program.name AS programs_region,
    region_of_member.name AS members_region,
    programs.name,
    metric,
    users.medicaid_id,
    users.username,
    users.first_name,
    users.last_name,
    users.date_of_birth,
    users.sex,
    actual_visit_date,
    incentive_type,
    incentive_value,
    incentive_date_sent,
    gift_card_serial,
    total_incentives
FROM
    patient_program_visits ppv
    join users on users.id = ppv.patient_id
    JOIN programs on ppv.program_id = programs.id
    JOIN
      regions AS region_of_program ON(
        region_of_program.id = programs.region_id
      )
    JOIN
      regions AS region_of_member ON(
        region_of_member.id = users.region_id
      )
    JOIN (
        SELECT
            patient_id,
            SUM(incentive_value) as total_incentives
        FROM
            patient_program_visits
        WHERE gift_card_returned <> 1
            and incentive_value is not null
            and actual_visit_date BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "'
        GROUP BY
            patient_id
    ) o ON ppv.patient_id = o.patient_id
WHERE
    users.region_id = '" . $input['region'] . "'
    and programs.region_id = '" . $input['region'] . "'
    and gift_card_returned <> 1
    and incentive_value is not null   
    and actual_visit_date BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "'
ORDER BY
    users.username"));

        } else if ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) {
            $result = \DB::select(\DB::Raw("SELECT
    region_of_program.name AS programs_region,
    region_of_member.name AS members_region,
	programs.name,
	metric,
	users.medicaid_id,
	users.username,
	users.first_name,
	users.last_name,
	users.date_of_birth,
	users.sex,
	actual_visit_date,
	incentive_type,
	incentive_value,
	incentive_date_sent,
	gift_card_serial,
	gift_card_returned
FROM
	patient_program_visits ppv
	join users on users.id = ppv.patient_id
	JOIN programs on ppv.program_id = programs.id
	JOIN
      regions AS region_of_program ON(
        region_of_program.id = programs.region_id
      )
    JOIN
      regions AS region_of_member ON(
        region_of_member.id = users.region_id
      )
WHERE
	users.region_id = '" . $input['region'] . "'
    and programs.region_id = '" . $input['region'] . "'
	and incentive_value is not null
	and incentive_date_sent BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "'
ORDER BY
	users.username"));

        } else if ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS) {
            $result = \DB::select(\DB::Raw("SELECT
    region_of_program.name AS programs_region,
    region_of_member.name AS members_region,
    programs.name,
    metric,
    users.medicaid_id,
    users.username,
    users.first_name,
    users.last_name,
    users.date_of_birth,
    users.sex,
    actual_visit_date,
    incentive_type,
    incentive_value,
    incentive_date_sent,
    gift_card_serial,
    gift_card_returned
FROM
    patient_program_visits ppv
    join users on users.id = ppv.patient_id
    JOIN programs on ppv.program_id = programs.id
    JOIN
      regions AS region_of_program ON(
        region_of_program.id = programs.region_id
      )
    JOIN
      regions AS region_of_member ON(
        region_of_member.id = users.region_id
      )
WHERE
    users.region_id = '" . $input['region'] . "'
    and programs.region_id = '" . $input['region'] . "'
    and incentive_value is not null
    and actual_visit_date BETWEEN '" . $date_ranges[0] . "' and '" . $date_ranges[1] . "'
ORDER BY
    users.username"));

        }


        return $result;

    }

    public function generate_cumulative_incentive_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));
        //$date_ranges[1] = date('Y-m-d', strtotime('+1 day', strtotime(trim($date_ranges[1]))));

        $cumulative_incentive_report_type = $input['cumulative_incentive_report_type'];
        $result = $this->cumulative_incentive_report_query($cumulative_incentive_report_type, $input, $date_ranges);

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $delimiter = ",";
        if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT)) {
            $report_type = '(Incentive Date)';
        } else {
            $report_type = '(DOS)';
        }
        if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS)) {
            //$filename = "$insurance_company->name/$region->name - Cumulative Incentive Report - " . \Helpers::format_date_DB($date_ranges[0]) . " - " . \Helpers::format_date_DB($date_ranges[1]) . ".csv";
            $filename = "STELLAR $region->abbreviation Cumulative Incentive Report $report_type  " . \Helpers::today_date_report_name() . ".csv";

        } else if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS)) {
            //$filename = "$insurance_company->name/$region->name - Gift Card Count Report - " . \Helpers::format_date_DB($date_ranges[0]) . " - " . \Helpers::format_date_DB($date_ranges[1]) . ".csv";
            $filename = "STELLAR $region->abbreviation Gift Card Count Report $report_type " . \Helpers::today_date_report_name() . ".csv";
        }

        $f = fopen('php://memory', 'w');

        $line = array("Member's region", "Program's region", 'Program', 'Metric', 'Medicaid ID', 'Patient ID',
            'First Name', 'Last Name', 'Date of birth', 'Sex', 'Actual Visit Date', 'Incentive Type', 'Incentive Amount',
            'Incentive Date', 'Incentive Code');

        if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS)) {
            array_push($line, "Cumulative YTD Incentives");
        } else if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS)) {
            array_push($line, "Incentive Returned");
        }

        fputcsv($f, $line, $delimiter);

        $total_dollar_value = 0;
        $total_gc = 0;
        $total_returned_gc = 0;

        foreach ($result as $item) {
            if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS)) {
                if ($item->gift_card_returned) {
                    $total_returned_gc++;
                } else {
                    $total_gc++;
                    $total_dollar_value += $item->incentive_value;
                }
            }


            $item->metric = \User::metric_toString($item->metric);
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->actual_visit_date = \Helpers::format_date_display($item->actual_visit_date);
            $item->incentive_value = "$" . $item->incentive_value;
            $item->incentive_date_sent = \Helpers::format_date_display($item->incentive_date_sent);
            if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS)) {
                $item->total_incentives = "$" . $item->total_incentives;
            }
            $item->gift_card_serial = ($item->gift_card_serial !== null) ? $item->gift_card_serial : 'Not Available';
            if ($item->gift_card_serial != 'Not Available') {
                $item->gift_card_serial = '="' . $item->gift_card_serial . '"';
            }

            $line = array("$item->programs_region", "$item->members_region", "$item->name", "$item->metric",
                "$item->medicaid_id", "$item->username", "$item->first_name", "$item->last_name", "$item->date_of_birth",
                "$item->sex", "$item->actual_visit_date", "$item->incentive_type", "$item->incentive_value",
                "$item->incentive_date_sent", "$item->gift_card_serial"
            );
            if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_CUMULATIVE_INCENTIVE_DOS)) {
                array_push($line, "$item->total_incentives");
            } else if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS)) {
                $item->gift_card_returned = $item->gift_card_returned ? "Yes" : "No";
                array_push($line, "$item->gift_card_returned");
            }

            fputcsv($f, $line, $delimiter);
        }

        if (($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT) OR ($cumulative_incentive_report_type == \Program::CUMULATIVE_INCENTIVE_REPORT_GIFT_CARD_COUNT_DOS)) {
            $line = array('', '', '', '', '', '', '', '', '', '', '', '');
            fputcsv($f, $line, $delimiter);
            fputcsv($f, $line, $delimiter);

            $line = array('', '', '', '', '', '', '', '', '', '', '', "Total Dollar Value:",
                number_format($total_dollar_value, 2, '.', ','), '');
            fputcsv($f, $line, $delimiter);
            $line = array('', '', '', '', '', '', '', '', '', '', '', "Total Gift Cards:", $total_gc, '');
            fputcsv($f, $line, $delimiter);
            $line = array('', '', '', '', '', '', '', '', '', '', '', "Total Returned GC:", $total_returned_gc, '');
            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();

        //*/
    }


    public function cribs_for_kids_report()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $regions = $insurance_companies_obj[0]->get_pregnancy_regions_as_key_value_array();

        $this->layout->content = View::make('admin/report/cribs_for_kids/cribs_for_kids_report')
            ->with('insurance_companies', $insurance_companies)
            ->with('regions', $regions)
            ->with('route', 'admin.reports.generate_cribs_for_kids_report')
            ->with('method', 'GET');
    }

    private function cribs_for_kids_report_query($input, $date_ranges)
    {
        return \DB::table('pregnancies')
            ->join('users', 'pregnancies.patient_id', '=', 'users.id')
            /*
            ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                $join->on('patient_program_visits.patient_id', '=', 'pregnancies.patient_id')
                    ->where('patient_program_visits.program_id', '=', $input["program"])
                    ->where('patient_program_visits.sign_up', '=', 1);
            })
            //*/
            ->leftjoin('patient_program_visits', 'patient_program_visits.id', '=', \DB::raw('(SELECT
                    patient_program_visits.id FROM patient_program_visits WHERE
                    patient_program_visits.program_instance_id = pregnancies.id
                    ORDER BY actual_visit_date DESC LIMIT 1)'))
            ->leftJoin('member_completed_required_visit_dates', 'pregnancies.member_completed_required_visit_dates', '=', 'member_completed_required_visit_dates.id')
            ->where('pregnancies.program_id', '=', $input["program"])
            ->where('eligible_for_gift_incentive', '=', '1')
            ->whereBetween(\DB::raw('CAST(eligible_date AS DATE)'), array($date_ranges[0], $date_ranges[1]))
            ->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                \DB::Raw(" DATE_SUB(due_date, INTERVAL 28 DAY) as week_36_date"),
                'date_added', 'actual_visit_date as sign_up_date',
                'due_date',
                'member_completed_required_visit_dates.title', 'cribs_quantity', 'eligible_date', 'eligibility_notes'
            )
            ->orderBy('users.username');
    }

    public function generate_cribs_for_kids_report()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);

        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;
        $result = $this->cribs_for_kids_report_query($input, $date_ranges);

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($result)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('date_of_birth', function ($model) {
                    return \Helpers::format_date_display($model->date_of_birth);
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone')
                ->addColumn('week_36_date', function ($model) {
                    return \Helpers::format_date_display($model->week_36_date);
                })
                ->addColumn('date_added', function ($model) {
                    return \Helpers::format_date_display($model->date_added);
                })
                ->addColumn('sign_up_date', function ($model) {
                    return \Helpers::format_date_display($model->sign_up_date);
                })
                ->addColumn('due_date', function ($model) {
                    return \Helpers::format_date_display($model->due_date);
                })
                ->showColumns('title', 'cribs_quantity')
                ->addColumn('eligible_date', function ($model) {
                    return \Helpers::format_date_display($model->eligible_date);
                })
                ->showColumns('eligibility_notes')
                ->searchColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name',
                    'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone',
                    'title', 'eligibility_notes'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();
        }


        $this->layout->content = View::make('admin/report/cribs_for_kids/show_cribs_for_kids_report')
            //->with('patients', $result)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('input', $input);
        //*/

    }

    public function generate_cribs_for_kids_report_csv()
    {
        $input = \Input::all();
        $date_ranges = explode(" to ", $input["date_range"]);
        if (count($date_ranges) < 2) {
            return 'No date range selected';
        }
        $date_ranges[0] = date('Y-m-d', strtotime(trim($date_ranges[0])));
        $date_ranges[1] = date('Y-m-d', strtotime(trim($date_ranges[1])));

        $insurance_company = \InsuranceCompany::find($input["insurance_company"]);
        $region = \Region::find($input["region"]);
        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;
        $result = $this->cribs_for_kids_report_query($input, $date_ranges)->get();

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation $program->abbreviation Cribs For Kids Report " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array("Insurance Company: $insurance_company->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '', '', '', '');
        fputcsv($f, $line, $delimiter);

        $line = array('Patient ID', 'Medicaid ID', 'First Name', 'Middle Name', 'Last Name', 'Date of birth',
            'Sex', 'Address1', 'Address2', 'City', 'State', 'Zip', 'County', 'Phone', 'TracPhone',
            'Week 36 Date', 'Opt In Date', 'Sign Up Date', 'Due Date', 'Gift', 'Quantity',
            'Eligibility Date', 'Eligibility Notes');

        fputcsv($f, $line, $delimiter);

        foreach ($result as $item) {
            $item->date_of_birth = \Helpers::format_date_display($item->date_of_birth);
            $item->week_36_date = \Helpers::format_date_display($item->week_36_date);
            $item->date_added = \Helpers::format_date_display($item->date_added);
            $item->sign_up_date = \Helpers::format_date_display($item->sign_up_date);
            $item->due_date = \Helpers::format_date_display($item->due_date);
            $item->eligible_date = \Helpers::format_date_display($item->eligible_date);

            $line = array("$item->username", "$item->medicaid_id", "$item->first_name", "$item->middle_initial",
                "$item->last_name", "$item->date_of_birth", "$item->sex", "$item->address1", "$item->address2",
                "$item->city", "$item->state", "$item->zip", "$item->county", "$item->phone1", "$item->trac_phone",
                "$item->week_36_date", "$item->date_added", "$item->sign_up_date", "$item->due_date",
                "$item->title", "$item->cribs_quantity", "$item->eligible_date", "$item->eligibility_notes",
            );

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

}
