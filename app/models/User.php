<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends \Cartalyst\Sentry\Users\Eloquent\User implements UserInterface, RemindableInterface
{

    use UserTrait, RemindableTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = array('password', 'remember_token');

    protected $fillable = array('username', 'medicaid_id', 'first_name', 'last_name', 'middle_initial', 'password', 'date_of_birth', 'sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'email');

    protected static $rules = array(
        'username' => 'required|min:5|max:255|unique:users,username',
        'password' => 'min:5|max:255',
        'first_name' => 'required|min:2|max:255',
        'last_name' => 'required|min:2|max:255',
        'medicaid_id' => 'max:255',
        'middle_initial' => 'max:255',
    );

    public function getFullNameAttribute()
    {
        return $this->first_name . "  " . $this->last_name;
    }

    public static function preValidate($input, $id = null)
    {
        $rules = self::$rules;

        if ($id !== null) {
            $rules['username'] .= ',' . $id;
        } else {
            $rules['password'] .= '|required';
        }

        return Validator::make($input, $rules);
    }

    public function programs()
    {
        return $this->belongsToMany('Program', 'patient_program', 'patient_id', 'program_id');
    }

    public function previous_contacts($program_id)
    {
        return \DB::table('patient_contacted')->where('patient_id', '=', $this->id)->where('program_id', '=', $program_id)
            ->orderBy('updated_at', 'desc')->get();
    }

    public function patient_programs()
    {
        return \DB::table('patient_program')
            ->join('programs', 'patient_program.program_id', '=', 'programs.id')
            ->where('patient_id', '=', $this->id)
            ->get();
    }

    public function actual_visits($program_id, $pregnancy_id = false)
    {
        if ($pregnancy_id != false) {
            return \DB::table('patient_program_visits')->where('patient_id', '=', $this->id)->where('program_id', '=', $program_id)->where('program_instance_id', '=', $pregnancy_id)
                ->orderBy('sign_up', 'desc')
                ->orderBy('estimated_date', 'asc')
                ->orderBy('scheduled_visit_date', 'asc')->get();

        } else {
            return \DB::table('patient_program_visits')->where('patient_id', '=', $this->id)->where('program_id', '=', $program_id)
                ->orderBy('sign_up', 'desc')
                ->orderBy('estimated_date', 'asc')
                ->orderBy('scheduled_visit_date', 'asc')->get();
        }
    }

    public function manual_outreaches($program_id, $pregnancy_id = false)
    {
        if ($pregnancy_id != false) {
            return \DB::table('manual_outreaches')
                ->join('outreach_codes', 'manual_outreaches.outreach_code', '=', 'outreach_codes.id')
                ->leftjoin('users', 'manual_outreaches.created_by', '=', 'users.id')
                ->where('patient_id', '=', $this->id)
                ->where('program_id', '=', $program_id)
                ->where('program_instance_id', '=', $pregnancy_id)
                ->orderBy('manual_outreaches.created_at', 'desc')
                ->select('manual_outreaches.id', 'outreach_date', 'code_name', 'outreach_notes', DB::raw('CONCAT(first_name, " ", last_name) AS created_by'))
                ->get();
        } else {
            return \DB::table('manual_outreaches')
                ->join('outreach_codes', 'manual_outreaches.outreach_code', '=', 'outreach_codes.id')
                ->leftjoin('users', 'manual_outreaches.created_by', '=', 'users.id')
                ->where('patient_id', '=', $this->id)->where('program_id', '=', $program_id)
                ->orderBy('manual_outreaches.created_at', 'desc')
                ->select('manual_outreaches.id', 'outreach_date', 'code_name', 'outreach_notes', DB::raw('CONCAT(first_name, " ", last_name) AS created_by'))
                ->get();
        }
    }

    public function actual_visit_for_current_year($program_id)
    {
        $last_actual_visit = \DB::table('patient_program_visits')->where('patient_id', '=', $this->id)->where('program_id', '=', $program_id)
            ->orderBy('actual_visit_date', 'desc')->first();

        if ($last_actual_visit) {
            if (date("Y") == date('Y', strtotime($last_actual_visit->actual_visit_date))) {
                return date('Y-m-d', strtotime($last_actual_visit->actual_visit_date));
            }
        }

        return 'Not Available';
    }


    public function patient_program_visits_for_selected_year($program_id, $year)
    {
        $first_date_of_year = date('Y-01-01 00:00:00', strtotime($year . '-01-01 00:00:00'));
        $last_date_of_year = date('Y-12-31 23:59:59', strtotime($year . '-12-31 23:59:59'));

        $actual_visits = \DB::table('patient_program_visits')
            ->leftjoin('doctors', 'patient_program_visits.doctor_id', '=', 'doctors.id')
            ->leftjoin('practice_groups', 'doctors.practice_group_id', '=', 'practice_groups.id')
            ->where('patient_id', '=', $this->id)->where('program_id', '=', $program_id)
            ->whereBetween('actual_visit_date', array($first_date_of_year, $last_date_of_year))
            ->orderBy('program_id')
            ->orderBy('metric', 'asc')
            ->orderBy('actual_visit_date', 'desc')
            ->get();

        foreach ($actual_visits as $actual_visit) {
            $actual_visit->metric = $this->metric_toString($actual_visit->metric);

            if ($year == date('Y', strtotime($actual_visit->actual_visit_date))) {
                $actual_visit->actual_visit_date = \Helpers::format_date_display($actual_visit->actual_visit_date);
                $actual_visit->scheduled_visit_date = \Helpers::format_date_display($actual_visit->scheduled_visit_date);
            }
        }

        return $actual_visits;
    }

    public static function metric_toString($metric)
    {
        switch ($metric) {
            case \Program::METRIC_URINE:
                return "Urine";
            case \Program::METRIC_BLOOD:
                return "Blood";
            case \Program::METRIC_EYE:
                return "Eye";
            case \Program::METRIC_BLOOD_AND_URINE:
                return "Blood & Urine";
            case \Program::METRIC_BLOOD_URINE_EYE:
                return "Blood, Urine, Eye";
            case \Program::METRIC_URINE_EYE:
                return "Urine & Eye";
            case \Program::METRIC_BLOOD_EYE:
                return "Blood & Eye";
        }

        //return "Not Available";
        return "";

    }

    public function insurance_company()
    {
        return $this->belongsTo('InsuranceCompany');
    }

    public function region()
    {
        return $this->belongsTo('Region');
    }

    public static function findByUsername($username)
    {
        return User::where('username', $username)->first();
    }

    public static function findUsernameById($id)
    {
        try {
            return User::where('id', $id)->first()->username;
        } catch (\Exception $e) {
            return 'Not Available';
        }
    }

    public static function findTempChildPatient($mother, $pregnancy_id)
    {
        return User::where('mother_id', $mother->id)
            ->where('pregnancy_id', '=', $pregnancy_id)
            //->where('username', 'LIKE', '%TEMP%')
            ->first();
    }

    public static function belongsToPregnancyProgram($username)
    {
        $user = \User::findByUsername($username);
        if ($user) {
            $programs = $user->programs()->get();

            foreach ($programs as $program) {
                if ($program->type == \Program::TYPE_PREGNANCY) {
                    return $user->id;
                }
            }
        }

        return false;
    }

    public function scopeOfGroup($query, $group)
    {
        return $query->whereHas('groups', function ($q) use ($group) {
            $q->where('id', '=', $group->id);
        });
    }

    public function scopeOfGroups($query, $groups)
    {
        $groups_ids = [];
        foreach ($groups as $group) {
            $groups_ids[] = $group->id;
        }

        return $query->whereHas('groups', function ($q) use ($groups_ids) {
            $q->whereIn('id', $groups_ids);
        });
    }

    public function isClient()
    {
        $clientGroup = \Sentry::findGroupByName('Client');

        return $this->inGroup($clientGroup);
    }

    public function isSysAdmin()
    {
        $sysAdminGroup = \Sentry::findGroupByName('System Administrator');

        return $this->inGroup($sysAdminGroup);
    }

    public function role()
    {
        if ($this->isClient())
            return 'Client';

        if ($this->isSysAdmin())
            return 'Admin';
    }

    public static function getAllAdminsAndClients()
    {
        $admins_group = Sentry::findGroupByName('System Administrator');
        $clients_group = Sentry::findGroupByName('Client');

        return \User::ofGroups(array($admins_group, $clients_group))
            ->orderBy('first_name')->get()->lists('full_name', 'id');
    }

    public static function getAllPatients()
    {
        $patients_group = Sentry::findGroupByName('Patient');

        return \User::ofGroup($patients_group)->get();
    }

    public function programs_toString()
    {
        $programs = $this->programs()->get();
        $programs_str = '';
        foreach ($programs as $program) {
            if ($programs_str !== '') {
                $programs_str .= ', ';
            }
            $programs_str .= $program->name;
        }

        return $programs_str;
    }

    public function programs_toString_with_links()
    {
        $programs = $this->programs()->get();
        $programs_str = '';
        foreach ($programs as $program) {
            if ($programs_str !== '') {
                $programs_str .= ', ';
            }
            $programs_str .= '<a href="' . URL::route('admin.programs.patient_visits', array($this->id, $program->id)) . '">' . $program->name . '</a>';
        }

        return $programs_str;
    }

    public function contactNow($program)
    {
        $actual_visits = $this->actual_visits($program->id);

        if (count($actual_visits) == 0) {
            return $this->contactAfterCheckingPreviousContacts($program);
        } else {
            $futureVisitDate = strtotime('2970-01-01 00:00:00');
            switch ($program->visit_requirement_period) {
                case \Program::PER_WEEK:
                    $futureVisitDate = strtotime('+1 week', strtotime($actual_visits[0]->actual_visit_date));
                    break;
                case \Program::PER_MONTH:
                    $futureVisitDate = strtotime('+1 month', strtotime($actual_visits[0]->actual_visit_date));
                    break;
                case \Program::PER_YEAR:
                    $futureVisitDate = strtotime('+1 year', strtotime($actual_visits[0]->actual_visit_date));
                    break;
            }

            switch ($program->contact_frequency_period) {
                case \Program::PER_WEEK:
                    $futureVisitDate = strtotime('-3 week', $futureVisitDate);
                    break;
                case \Program::PER_MONTH:
                    $futureVisitDate = strtotime('-3 month', $futureVisitDate);
                    break;
                case \Program::PER_YEAR:
                    $futureVisitDate = strtotime('-3 year', $futureVisitDate);
                    break;
            }

            if (strtotime('now') >= $futureVisitDate) {
                return $this->contactAfterCheckingPreviousContacts($program);
            }
        }

        return false;
    }

    public function contactAfterCheckingPreviousContacts($program)
    {
        $previous_contacts = $this->previous_contacts($program->id);

        if (count($previous_contacts) == 0) {
            return true;
        } else {
            $futureContactDate = strtotime('2970-01-01 00:00:00');
            switch ($program->contact_frequency_period) {
                case \Program::PER_WEEK:
                    $futureVisitDate = strtotime('+1 week', strtotime($previous_contacts[0]->updated_at));
                    break;
                case \Program::PER_MONTH:
                    $futureVisitDate = strtotime('+1 month', strtotime($previous_contacts[0]->updated_at));
                    break;
                case \Program::PER_YEAR:
                    $futureVisitDate = strtotime('+1 year', strtotime($previous_contacts[0]->updated_at));
                    break;
            }

            if (strtotime('now') >= $futureVisitDate) {
                return true;
            }
        }

        return false;
    }
}
