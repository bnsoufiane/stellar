<?php

namespace Admin;

use View;

class RegionsController extends BaseController
{

    public function index()
    {
        $user = \User::find(\Sentry::getUser()->id);
        if ($user->isSysAdmin()) {
            $regions = \Region::all();
        } else {
            $insurance_company = $user->insurance_company()->first();

            $regions = $insurance_company->regions()->get();
        }

        $this->layout->content = View::make('admin/regions/index')
            ->with('regions', $regions);
    }

    public function create()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }
        //$all_programs = \Program::all();

        $this->layout->content = View::make('admin/regions/create')
            ->with('region', new \Region())
            ->with('insurance_companies', $insurance_companies)
            ->with('programs', array())
            //->with('all_programs', $all_programs)
            ->with('route', 'admin.regions.store');
    }

    public function store()
    {
        $input = \Input::all();
        $validator = \Region::validate($input);

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.create')
                ->withInput()
                ->withErrors($validator);
        } else {
            $input['no_limits'] = isset($input['no_limits']) ? 1 : 0;
            $input['annual_incentive_limit'] = !empty($input['annual_incentive_limit']) ? $input['annual_incentive_limit'] : 0;
            $region = \Region::create($input);
            $region->insurance_company_id = $input['insurance_company'];
            $region->insurance_company()->associate(\InsuranceCompany::find($input['insurance_company']));
            $region->save();

            return \Redirect::route('admin.regions.index')
                ->with('success', 'A region has been successfully created.');
        }
    }

    public function show()
    {
        return 'show';
    }

    public function edit($id)
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }
        $region = \Region::find($id);
        $insurance_company = $region->insurance_company()->first();
        $programs = $region->programs()->get();
        //$all_programs = \Program::all();

        $this->layout->content = View::make('admin/regions/edit')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('insurance_companies', $insurance_companies)
            ->with('programs', $programs)
            //->with('all_programs', $all_programs)
            ->with('route', array('admin.regions.update', $id))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        $validator = \Region::validate($input, $id);

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.edit', $id)
                ->withInput()
                ->withErrors($validator);
        } else {
            $region = \Region::find($id);
            $input['no_limits'] = isset($input['no_limits']) ? 1 : 0;
            $input['annual_incentive_limit'] = !empty($input['annual_incentive_limit']) ? $input['annual_incentive_limit'] : 0;
            $region->fill($input);
            $region->insurance_company()->associate(\InsuranceCompany::find($input['insurance_company']));
            $region->save();

            return \Redirect::route('admin.regions.index')
                ->with('success', 'A region has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $region = \Region::find($id);

        if (!$region) {
            \App::abort(404);
        }

        $programs_count = $region->programs()->count();

        if ($programs_count > 0) {
            return array('error' => 1);
        } else {
            $region->delete();
            return array('ok' => 1);
        }
    }

    public function upload()
    {
        if (!\Input::get('upload')) {
            return \Response::make('No file was provided.', 400);
        }

        $file = BaseController::handleUpload('upload');

        return array(
            'url' => '/uploads/' . $file['filename'],
            'filename' => $file['filename']
        );
    }

    public function create_patients($id)
    {
        $region = \Region::find($id);
        $insurance_company = $region->insurance_company()->first();
        $programs = $region->programs()->get();

        $this->layout->content = View::make('admin/regions/patients/create_patients')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('programs', array())
            ->with('all_programs', $programs)
            ->with('user', new \User())
            ->with('create_page', true)
            ->with('route', array('admin.regions.store_patients', $id));
    }

    public function store_patients($id)
    {
        $input = \Input::all();

        if (empty($input['password'])) {
            $input['password'] = \Hash::make('curotec');
        }

        if (\Input::get('programs_id') == null) {
            $program_ids = array();
        } else {
            $program_ids = \Input::get('programs_id');
        }

        $region = \Region::find($id);
        $isWC15_program_assigned = $this->isWC15_program_assigned($program_ids);

        $validator = \User::preValidate($input);

        if ($isWC15_program_assigned) {

            if (empty($input['mother_id'])) {
                $validator->getMessageBag()->add('mother_id', "The mother ID field is required.");
                return \Redirect::route('admin.regions.create_patients', $id)
                    ->withInput()
                    ->withErrors($validator);
            } else {
                $motherBelongsToPregnancyProgram = \User::belongsToPregnancyProgram($input['mother_id']);

                if (!$motherBelongsToPregnancyProgram) {
                    $validator->getMessageBag()
                        ->add('mother_id', "The mother ID you provided doesn't belong to the K2YC program.");

                    return \Redirect::route('admin.regions.create_patients', $id)
                        ->withInput()
                        ->withErrors($validator);
                } else {
                    $input['user_mother_id'] = $motherBelongsToPregnancyProgram;
                }
            }
        }

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.create_patients', $id)
                ->withInput()
                ->withErrors($validator);
        } else {

            if (!empty($input['date_of_birth'])) {
                $input['date_of_birth'] = date('Y-m-d', strtotime(str_replace('-', '/', $input['date_of_birth'])));
            } else {
                $input['date_of_birth'] = null;
            }

            try {
                $user = \User::create($input);

                $user->region()->associate($region);
                $user->insurance_company()->associate($region->insurance_company()->first());
                $user->addGroup(\Sentry::findGroupByName("Patient"));

                $user->programs()->sync(array_unique($program_ids));

                if (isset($input['user_mother_id'])) {
                    $user->mother_id = $input['user_mother_id'];
                }

                $user->save();
            } catch (\Exception $e) {

            }

        }

        return \Redirect::route('admin.regions.index')
            ->with('success', 'A patient has been successfully created.');
    }

    public function edit_patients($id)
    {
        $user = \User::find($id);

        if ($user->mother_id) {
            $user->mother_id = \User::findUsernameById($user->mother_id);
        }

        $region = $user->region()->first();
        $insurance_company = $region->insurance_company()->first();
        $programs = $region->programs()->get();

        $user->date_of_birth = date_format(date_create($user->date_of_birth), 'm/d/Y');
        $this->layout->content = View::make('admin/regions/patients/edit')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('user', $user)
            ->with('programs', $user->programs()->get())
            ->with('all_programs', $programs)
            ->with('route', array('admin.regions.update_patients', $id))
            ->with('method', 'post');
    }

    public function edit_patient_record($id)
    {
        $user = \User::find($id);
        $region = $user->region()->first();
        $insurance_company = $region->insurance_company()->first();
        $programs = $region->programs()->get();

        $user->date_of_birth = date_format(date_create($user->date_of_birth), 'm/d/Y');
        $this->layout = \View::make('admin.layouts.base_iframe');
        $this->layout->content = View::make('admin/regions/patients/edit_record')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('user', $user)
            ->with('programs', $user->programs()->get())
            ->with('all_programs', $programs)
            ->with('route', array('admin.regions.update_patients', $id))
            ->with('method', 'post');
    }

    public function update_patients($id)
    {
        $input = \Input::all();
        if (empty($input['password'])) {
            unset($input['password']);
        }

        if (\Input::get('programs_id') == null) {
            $program_ids = array();
        } else {
            $program_ids = \Input::get('programs_id');
        }

        $isWC15_program_assigned = $this->isWC15_program_assigned($program_ids);

        $validator = \User::preValidate($input, $id);

        if ($isWC15_program_assigned) {

            if (empty($input['mother_id'])) {
                $validator->getMessageBag()->add('mother_id', "The mother ID field is required.");
                return \Redirect::route('admin.regions.edit_patients', $id)
                    ->withInput()
                    ->withErrors($validator);
            } else {
                $motherBelongsToPregnancyProgram = \User::belongsToPregnancyProgram($input['mother_id']);

                if (!$motherBelongsToPregnancyProgram) {
                    $validator->getMessageBag()
                        ->add('mother_id', "The mother ID you provided doesn't belong to the K2YC program.");

                    return \Redirect::route('admin.regions.edit_patients', $id)
                        ->withInput()
                        ->withErrors($validator);
                } else {
                    $input['user_mother_id'] = $motherBelongsToPregnancyProgram;
                }
            }
        }

        if ($validator->fails()) {
            return \Redirect::route('admin.regions.edit_patients', $id)
                ->withInput()
                ->withErrors($validator);
        } else {

            $user = \User::find($id);
            $region = $user->region()->first();

            try {
                \DB::beginTransaction();

                // if the user does no longer belong to a program, we delete them from that program table
                $programs = $region->programs()->get();
                foreach ($programs as $program) {

                    if ($program->type == \Program::TYPE_PREGNANCY && !in_array($program->id, $program_ids)) {
                        \DB::table('pregnancies')->where('patient_id', '=', $user->id)->delete();

                    } else if ($program->type == \Program::TYPE_FIRST_TRIMESTER && !in_array($program->id, $program_ids)) {
                        \DB::table('first_trimesters')->where('patient_id', '=', $user->id)->delete();
                    }
                }

                $user->fill($input);

                $user->date_of_birth = date('Y-m-d', strtotime(str_replace('-', '/', $user->date_of_birth)));

                $user->programs()->sync(array_unique($program_ids));

                if (isset($input['user_mother_id'])) {
                    $user->mother_id = $input['user_mother_id'];
                }

                $user->save();

                \DB::commit();
            } catch (\Exception $e) {

            }

            return \Redirect::route('admin.regions.patients_roster', array($region->id))
                ->with('success', 'A patient has been successfully updated.');

        }
    }

    private function isWC15_program_assigned($program_ids)
    {
        $programs = \Program::find($program_ids);

        foreach ($programs as $program) {
            if ($program->type == \Program::TYPE_WC15_AHC) {
                return $program->id;
            }
        }

        return false;

    }


    public function import_patients($id)
    {
        $region = \Region::find($id);
        $insurance_company = $region->insurance_company()->first();
        $all_programs = $region->programs()->get();

        $this->layout = \View::make('admin.layouts.base_iframe');

        $this->layout->content = View::make('admin/regions/patients/import_patients')
            ->with('region', $region)
            ->with('insurance_company', $insurance_company)
            ->with('programs', array())
            ->with('all_programs', $all_programs)
            ->with('route', array('admin.regions.store_patients', $id));
    }

    public function store_imported_patients()
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

        $region = \Region::find(\Input::get('region'));
        $insurance_company = $region->insurance_company()->first();
        $insurance_company_id = $insurance_company->id;
        $region_id = $region->id;

        $programs = \Input::get('programs');

        $default_password = \Hash::make('curotec');//'curotec';

        \DB::beginTransaction();

        $now = \Carbon\Carbon::now()->toDateTimeString();
        $usersToSave = [];
        foreach ($data as $row) {
            //$patientInfo = explode(",", $row);
           $patientInfo = str_getcsv($row, ",", '"');

            if (count($patientInfo) == 16) {
                $username = $patientInfo[2];
                if (!empty($username)) {
                    $tab = array();
                    $tab['username'] = trim($patientInfo[0]);
                    $tab['medicaid_id'] = trim($patientInfo[1]);
                    $tab['password'] = $default_password;
                    $tab['first_name'] = trim($patientInfo[2]);
                    $tab['middle_initial'] = trim($patientInfo[3]);
                    $tab['last_name'] = trim($patientInfo[4]);
                    $tab['date_of_birth'] = date('Y-m-d', strtotime(trim($patientInfo[5])));
                    $tab['sex'] = trim($patientInfo[6]);
                    $tab['address1'] = trim($patientInfo[7]);
                    $tab['address2'] = trim($patientInfo[8]);
                    $tab['city'] = trim($patientInfo[9]);
                    $tab['state'] = trim($patientInfo[10]);
                    $tab['zip'] = trim($patientInfo[11]);
                    $tab['county'] = trim($patientInfo[12]);
                    $phone_formatted = trim($patientInfo[13]);
                    if (preg_match('/^(\d\d\d)(\d{3})(\d{0,4}).*/', $phone_formatted, $matches)) {
                        $phone_formatted = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
                    }
                    $tab['phone1'] = $phone_formatted;
                    $phone_formatted = trim($patientInfo[14]);
                    if (preg_match('/^(\d\d\d)(\d{3})(\d{0,4}).*/', $phone_formatted, $matches)) {
                        $phone_formatted = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
                    }
                    $tab['trac_phone'] = $phone_formatted;
                    $tab['email'] = trim($patientInfo[15]);
                    $tab['insurance_company_id'] = $insurance_company_id;
                    $tab['region_id'] = $region_id;
                    $tab['created_at'] = $now;
                    $tab['updated_at'] = $now;

                    $usersToSave[] = $tab;

                    try {
                        $user = \User::findByUsername($tab['username']);
                        if (!$user) {
                            $usr = \DB::table('users')->insertGetId($tab);

                            \DB::table('users_groups')->insert(
                                array('user_id' => $usr,
                                    'group_id' => 3)
                            );

                            foreach ($programs as $program_id) {

                                try {
                                    \DB::table('patient_program')->insert(
                                        array('patient_id' => $usr,
                                            'program_id' => $program_id,
                                            'send_reminder_flag' => 0,
                                            'patient_notes' => Null)
                                    );
                                } catch (\Exception $e) {

                                }
                            }

                        } else {
                            $usr = $user->id;
                            $user->programs()->sync(array_unique($programs), false);

                            $duplicatedPatients[] = $tab['last_name'] . ", " . $tab['first_name'];
                        }


                    } catch (\Exception $e) {
                        var_dump($e->getMessage());
                    }

                }
            }
        }

        \DB::commit();

        $message = '';
        if (isset($duplicatedPatients) && count($duplicatedPatients) > 0) {
            $message .= 'These patients are duplicated: <br/>';
            foreach ($duplicatedPatients as $item) {
                $message .= ($item . '<br/>');
            }
            $message .= '<br/>';
        }

        if (isset($duplicatedPatients)) {
            // Create a csv file based on $message Begin
            if (strpos(getcwd(), 'public') !== false) {
                $baselink = 'uploads/';
            } else {
                $baselink = 'public/uploads/';
            }
            $filename = $baselink . uniqid() . ".csv";

            $delimiter = ",";
            $f = fopen("$filename", 'w');

            $line = array("These patients are duplicated:");
            fputcsv($f, $line, $delimiter);

            foreach ($duplicatedPatients as $item) {
                fputcsv($f, array("$item"), $delimiter);
            }

            fclose($f);

            // Create a csv file based on $message End
            return \Response::make(array('ok' => $message, 'result_file' => str_replace("public/", "", $filename)), 201);
        } else {
            return \Response::make(array('ok' => true), 201);
        }
    }

    public function patients_roster($id)
    {
        $region = \Region::find($id);
        $insurance_company = $region->insurance_company()->first();
        $user = \User::find(\Sentry::getUser()->id);
        $isSysAdmin = $user->isSysAdmin();

        $query = \DB::table('users')
            ->join('users_groups', 'users_groups.user_id', '=', 'users.id')
            ->where('users_groups.group_id', 3)
            ->where('users.region_id', '=', $id);

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($query)
                ->showColumns('username', 'medicaid_id', 'first_name', 'middle_initial', 'last_name')
                ->addColumn('programs', function ($model) use (&$isSysAdmin) {
                    $patient = \User::where('id', $model->id)->first();
                    if ($isSysAdmin) {
                        return $patient->programs_toString_with_links();
                    } else {
                        return $patient->programs_toString();
                    }
                })
                ->addColumn('date_of_birth', function ($model) use (&$isSysAdmin) {
                    return date("m/d/Y", strtotime($model->date_of_birth));
                })
                ->showColumns('sex', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'email')
                ->showColumns('id')
                ->searchColumns('username', 'medicaid_id', 'first_name', 'last_name', 'middle_initial', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'email')
                ->orderColumns('username', 'medicaid_id', 'first_name', 'last_name', 'middle_initial', 'address1', 'address2', 'city', 'state', 'zip', 'county', 'phone1', 'trac_phone', 'email'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->make();

        }

        $this->layout->content = View::make('admin/regions/patients/patients_roster')
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('isSysAdmin', $isSysAdmin);
    }

    public function patients_roster_csv($id)
    {
        $region = \Region::find($id);

        $patients = \DB::table('patient_program')
            ->join('users', 'users.id', '=', 'patient_program.patient_id')
            ->join('programs', 'patient_program.program_id', '=', 'programs.id')
            ->select('programs.name as programs', 'users.id', 'username', 'first_name', 'last_name', 'date_of_birth')
            ->where('users.region_id', '=', $id)
            ->orderBy('users.id', 'asc')->get();

        $delimiter = ",";
        $filename = "STELLAR $region->abbreviation Member Roster " . \Helpers::today_date_report_name() . ".csv";

        $f = fopen('php://memory', 'w');

        $line = array('Patient ID', 'First Name', 'Last Name', 'Programs', 'Date of Birth');
        fputcsv($f, $line, $delimiter);

        $patients_list = [];
        $last_patient_id = '';
        foreach ($patients as $patient) {
            if ($last_patient_id !== $patient->id) {
                $patients_list[] = $patient;
            } else {
                end($patients_list)->programs .= ", " . $patient->programs;
            }

            $last_patient_id = $patient->id;
        }


        foreach ($patients_list as $patient) {
            $patient->date_of_birth = date_format(date_create($patient->date_of_birth), 'm/d/Y');

            $line = array("$patient->username", "$patient->first_name", "$patient->last_name", "$patient->programs", "$patient->date_of_birth");

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function all_doctors($id)
    {
        $region = \Region::find($id);
        $insurance_company = $region->insurance_company()->first();

        $user = \User::find(\Sentry::getUser()->id);
        $isSysAdmin = $user->isSysAdmin();

        $query = \DB::table('doctors')
            ->join('practice_groups', 'practice_groups.id', '=', 'doctors.practice_group_id')
            ->where('practice_groups.region_id', '=', $id)
            ->select('doctors.id', 'practice_group_id', 'region_id', 'pcp_id', 'first_name', 'last_name', 'practice_groups.name');

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($query)
                ->showColumns('pcp_id', 'first_name', 'last_name', 'name')
                ->showColumns('id', 'practice_group_id', 'region_id')
                ->searchColumns('pcp_id', 'first_name', 'last_name', 'name'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->orderColumns('pcp_id', 'first_name', 'last_name', 'name')
                ->make();
        }

        $this->layout->content = View::make('admin/regions/all_doctors/index')
            ->with('region', $region)
            ->with('insurance_company', $insurance_company)
            ->with('isSysAdmin', $isSysAdmin);
    }

    public function all_doctors_csv($id)
    {
        $region = \Region::find($id);

        $doctors = \DB::table('doctors')
            ->join('practice_groups', 'practice_groups.id', '=', 'doctors.practice_group_id')
            ->where('practice_groups.region_id', '=', $id)
            ->select('pcp_id', 'first_name', 'last_name', 'practice_groups.name')->get();

        $delimiter = ",";
        $filename = $region->name . " - Doctors Full List.csv";

        $f = fopen('php://memory', 'w');

        $line = array('Patient ID', 'First Name', 'Last Name', 'Programs', 'Date of Birth');
        fputcsv($f, $line, $delimiter);

        $patients_list = [];
        $last_patient_id = '';

        foreach ($doctors as $doctor) {
            $line = array("$doctor->pcp_id", "$doctor->first_name", "$doctor->last_name", "$doctor->name");

            fputcsv($f, $line, $delimiter);
        }

        fseek($f, 0);
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        fpassthru($f);

        die();
    }

    public function get_programs($id)
    {
        $region = \Region::find($id);

        return $region->get_programs_as_key_value_array();
    }

    public function get_programs_with_types($id)
    {
        $region = \Region::find($id);

        return $region->get_programs_with_types();
    }

}
