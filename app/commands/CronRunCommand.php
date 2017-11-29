<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
class CronRunCommand extends Command {
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'cron:run';
  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Run the scheduler';

  protected $timestamp;

  protected $runAt = '21:30';
  
  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
    $this->timestamp = time();
   
  }
  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function fire()
  {

    $this->wednesdays(function()
    {
      $server_url = 'http://stellar.curotecserver.com/uploads'; // to be changed when server URL changes 
      // $server_url = 'http://stellar.local/uploads'; // to be changed when server URL changes 
      $filesArr = []; //to be used to save generated files
      $reportArr = []; //to be used to save diffrerent report query param
      $reportArr[0]['region'] = 2;
      $reportArr[0]['program'] = 33;
      $reportArr[0]['pregnancy_report_type'] = 0;
      $reportArr[0]['insurance_company'] = 1;
      $reportArr[1]['region'] = 3;
      $reportArr[1]['program'] = 33;
      $reportArr[1]['pregnancy_report_type'] = 0;
      $reportArr[1]['insurance_company'] = 1;
      $reportArr[2]['region'] = 8;
      $reportArr[2]['program'] = 33;
      $reportArr[2]['pregnancy_report_type'] = 0;
      $reportArr[2]['insurance_company'] = 1;
    

    foreach ($reportArr as $input) {    
       
        $all_dates_flag = false;      
        $curr_day = \Carbon::now();
        $curr_month = $curr_day->month;
        $curr_year = $curr_day->year;
        $first_day_curr_month = new \Carbon('first day of this month');
        $date_ranges[0] = $first_day_curr_month;
        $date_ranges[1] = $curr_day;
        $first_day_curr_month = $first_day_curr_month->toDateString();
        $curr_day = $curr_day->toDateString(); 

        $program = \DB::table('programs')
            ->where('region_id', '=', $input["region"])
            ->where('type', '=', \Program::TYPE_PREGNANCY)
            ->first();
        $input["program"] = $program->id;

        
       

        $pregnancy_report_type = $input['pregnancy_report_type'];
        //query starts
        
        //setting up date range that could be used for trailing summary raws in report
        
        $dt = \Carbon::now();
        $this_year  = $dt->year;        
        $this_month = $dt->month;

        
        $start_date_month_previous_this_year = new \Carbon('first day of '.date('F', mktime(0, 0, 0, $this_month - 1, 10)).' '.$this_year);
        $end_date_month_previous_this_year  = new \Carbon('last day of '.date('F', mktime(0, 0, 0, $this_month - 1, 10)).' '.$this_year);

         $start_date_two_months_previous_last_year = new \Carbon('first day of '.date('F', mktime(0, 0, 0, $this_month - 2, 10)).' '.$this_year - 1);
        $end_date_two_months_previous_last_year  = new \Carbon('last day of '.date('F', mktime(0, 0, 0, $this_month - 2, 10)).' '.$this_year - 1);

         $start_date_three_months_previous_last_year = new \Carbon('first day of '.date('F', mktime(0, 0, 0, $this_month - 3, 10)).' '.$this_year - 1);
        $end_date_three_months_previous_last_year  = new \Carbon('last day of '.date('F', mktime(0, 0, 0, $this_month - 3, 10)).' '.$this_year - 1);

         $start_date_four_months_previous_last_year = new \Carbon('first day of '.date('F', mktime(0, 0, 0, $this_month - 4, 10)).' '.$this_year - 1);
        $end_date_four_months_previous_last_year  = new \Carbon('last day of '.date('F', mktime(0, 0, 0, $this_month - 4, 10)).' '.$this_year - 1);

        //setting up date range ends
        
        if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OPT_IN) {

            $this->last_month_this_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })         
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_month_previous_this_year, $end_date_month_previous_this_year))
                ->count();

             $this->two_month_previous_last_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })          
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_two_months_previous_last_year, $end_date_two_months_previous_last_year))
                ->count();

             $this->three_month_previous_last_year_count =  \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })        
               ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_three_months_previous_last_year, $end_date_three_months_previous_last_year))
                ->count();

             $this->four_month_previous_last_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                })         
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_four_months_previous_last_year, $end_date_four_months_previous_last_year))
                ->count();

            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->whereRaw("( delivery_date = ? OR delivery_date is null )", array('0000-00-00 00:00:00'))
                ->where(function ($query) {
                    $query->whereNull('discontinue')
                        ->orWhere('discontinue', '=', 0);
                });

            if (!$all_dates_flag) {
                $result->whereBetween(\DB::raw('CAST(date_added AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                'date_added', 'enrolled_by', 'primary_insurance')
                ->orderBy('date_added','desc');

        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_ACTIVE_PATIENT_OUTREACH) {
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->leftJoin('patient_program_visits', function ($join) use (&$input) {
                    $join->on('patient_program_visits.patient_id', '=', 'pregnancies.patient_id')
                        ->where('patient_program_visits.program_id', '=', $input["program"]);
                })
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

             $this->last_month_this_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5')          
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_month_previous_this_year, $end_date_month_previous_this_year))
                ->count();

             $this->two_month_previous_last_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5')        
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_two_months_previous_last_year, $end_date_two_months_previous_last_year))
                ->count();

             $this->three_month_previous_last_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5')        
               ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_three_months_previous_last_year, $end_date_three_months_previous_last_year))
                ->count();

             $this->four_month_previous_last_year_count = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5')       
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_four_months_previous_last_year, $end_date_four_months_previous_last_year))
                ->count();


            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->join('discontinue_tracking_reasons', 'pregnancies.discontinue_reason_id', '=', 'discontinue_tracking_reasons.id')
                ->where('pregnancies.program_id', '=', $input["program"])
                ->where('discontinue', '=', '1')
                ->where('discontinue_reason_id', '<>', '5');

            if (!$all_dates_flag) {
                $result->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                'reason', 'discontinue_date', 'date_added', 'enrolled_by', 'primary_insurance')
                ->orderBy('discontinue_date','desc');

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
                $result->whereBetween(\DB::raw('CAST(discontinue_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
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
                ->where('pregnancies.program_id', '=', $input["program"]);
            if (!$all_dates_flag) {
                $result->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex', 'address1', 'address2',
                'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date', 'delivery_date'
                , 'scheduled_visit_date', 'scheduled_visit_date_notes', 'actual_visit_date', 'doctor_id',
                'visit_notes', 'incentive_type', 'incentive_value', 'incentive_date_sent', 'gift_card_serial', 'manually_added', 'date_added', 'enrolled_by', 'primary_insurance');


        } else if ($pregnancy_report_type == \Program::PREGNANCY_REPORT_DELIVERY) {
            $this->last_month_this_year_count =  \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])           
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_month_previous_this_year, $end_date_month_previous_this_year))
                ->count();

             $this->two_month_previous_last_year_count =  \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])           
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_two_months_previous_last_year, $end_date_two_months_previous_last_year))
                ->count();

             $this->three_month_previous_last_year_count =  \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])           
               ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_three_months_previous_last_year, $end_date_three_months_previous_last_year))
                ->count();

             $this->four_month_previous_last_year_count =  \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"])           
                ->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($start_date_four_months_previous_last_year, $end_date_four_months_previous_last_year))
                ->count();
               
            
            
            $result = \DB::table('pregnancies')
                ->join('users', 'pregnancies.patient_id', '=', 'users.id')
                ->where('pregnancies.program_id', '=', $input["program"]);
            if (!$all_dates_flag) {
                $result->whereBetween(\DB::raw('CAST(delivery_date AS DATE)'), array($date_ranges[0], $date_ranges[1]));
            }
            $result->select('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name', 'date_of_birth', 'sex',
                'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'due_date',
                'delivery_date', 'date_added', 'enrolled_by', 'primary_insurance');

            $result->orderBy('delivery_date','desc');

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

        

        //query ends
        $result = $result->get();

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

        $file_name = "STELLAR $region->abbreviation $program->abbreviation $report_name " . time() . ".csv";

       

        // if (strpos(getcwd(), 'public') !== false) {
        //         $baselink = 'uploads/';
        //     } else {
        //         $baselink = 'public/uploads/';

        //     }

        // $filename = $baselink . $file_name;


      $base_path = base_path();
      $baselink = $base_path.'/public/uploads/';
      $filename = $baselink . $file_name;
      
     
       $f = fopen("$filename", 'w'); 

        $line = array("Insurance Company: $insurance_company->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Region: $region->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Program: $program->name", '', '', '');
        fputcsv($f, $line, $delimiter);
        $line = array("Date Range: $curr_day to $first_day_curr_month", '', '', '');
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
        $line = array("Previous months summary", '', '', '');
        fputcsv($f, $line, $delimiter);
       
        $line = array(date('F', mktime(0, 0, 0, $curr_month - 1, 10)).' '.($curr_year).' count: '.$this->last_month_this_year_count, '', '', '');
        fputcsv($f, $line, $delimiter);

        $line = array(date('F', mktime(0, 0, 0, $curr_month - 2, 10)).' '.($curr_year -1).' count: '.$this->two_month_previous_last_year_count, '', '', '');
        fputcsv($f, $line, $delimiter);
         $line = array(date('F', mktime(0, 0, 0, $curr_month - 3, 10)).' '.($curr_year -1).' count: '.$this->three_month_previous_last_year_count, '', '', '');
        fputcsv($f, $line, $delimiter);
         $line = array(date('F', mktime(0, 0, 0, $curr_month - 4, 10)).' '.($curr_year -1).' count: '.$this->four_month_previous_last_year_count, '', '', '');
        fputcsv($f, $line, $delimiter);
     


        // fseek($f, 0);
        // header('Content-Type: application/csv');
        // header('Content-Disposition: attachment; filename="' . $filename . '";');
        // fpassthru($f);
    
        fclose($f);

       

        array_push($filesArr,$file_name);
        



       

        //commented mail feature - to be tested and operated after reports tested/approved 

        // \Mail::send('emails.report', array('file_url' => $file), function($message)
        // {
        //     $message->to('harshad@curotec.com', 'Report Desk')->subject('Automated Stellarrx report!');
        // });

       

       }

     

         \Mail::send('emails.report', array('files' => $filesArr, 'server_url'=>$server_url), function($message)
        {
        $message->sender('stellar.app9@gmail.com');
        $message->to('harshad@curotec.com', 'Report Desk')->subject('Automated Stellarrx report!');
        });
    
   
    });

  }

  protected function everyFiveMinutes(callable $callback)
  {
    if((int) date('i', $this->timestamp) % 5 === 0) call_user_func($callback);
  }

  
  protected function tuesdays(callable $callback)
  {
    if(date('D', $this->timestamp) === 'Tue' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
  }

  protected function wednesdays(callable $callback)
  {
    if(date('D', $this->timestamp) === 'Wed' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
  }
 

}