<?php

namespace Admin;

use View;

class UsersController extends BaseController
{

    public function index()
    {
        $admins_group = \Sentry::findGroupByName('System Administrator');
        $clients_group = \Sentry::findGroupByName('Client');
        $users = \User::ofGroups(array($admins_group, $clients_group))->get();

        $this->layout->content = View::make('admin/users/index')
            ->with('users', $users);
    }

    public function patients()
    {
        $user = \User::find(\Sentry::getUser()->id);
        $isSysAdmin = $user->isSysAdmin();
        if ($isSysAdmin) {
            $query = \DB::table('users')
                ->join('users_groups', 'users_groups.user_id', '=', 'users.id')
                ->where('users_groups.group_id', 3);
        } else {
            $insurance_company = $user->insurance_company()->first();

            $query = \DB::table('users')
                ->join('users_groups', 'users_groups.user_id', '=', 'users.id')
                ->where('users_groups.group_id', 3)
                ->where('users.insurance_company_id', '=', $insurance_company->id);
        }

        if (\Datatable::shouldHandle()) {
            return \Datatable::query($query)
                ->showColumns('username', 'first_name', 'last_name')
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
                ->showColumns('id')
                ->searchColumns('username', 'first_name', 'last_name'
                    , \DB::Raw("CONCAT(`first_name`, ' ', `last_name`)"), \DB::Raw("CONCAT(`last_name`, ' ', `first_name`)"))
                ->orderColumns('username', 'first_name', 'last_name', 'date_of_birth')
                ->make();
        }

        $this->layout->content = View::make('admin/users/patients')
            ->with('isSysAdmin', $isSysAdmin);
    }

    public function full_list_cvs()
    {
        $patients = \DB::table('patient_program')
            ->join('users', 'users.id', '=', 'patient_program.patient_id')
            ->join('programs', 'patient_program.program_id', '=', 'programs.id')
            ->select('programs.name as programs', 'users.id', 'username', 'first_name', 'last_name', 'date_of_birth')
            ->orderBy('users.id', 'asc')->get();

        $delimiter = ",";
        $filename = "Patients Full List.csv";

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

    public function create()
    {
        $insurance_companies_obj = \InsuranceCompany::all();
        $insurance_companies = [];
        $insurance_companies[null] = '- Select an Insurance Company -';
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $groups_obj = \Sentry::findAllGroups();
        $groups = [];
        foreach ($groups_obj as $group) {

            if ($group->name !== 'Patient') {
                $groups[$group->id] = $group->name;
            }
        }

        $this->layout->content = View::make('admin/users/create')
            ->with('user', new \User())
            ->with('groups', $groups)
            ->with('insurance_companies', $insurance_companies)
            ->with('route', 'admin.users.store');
    }

    public function store()
    {
        $input = \Input::all();

        $validator = \User::preValidate($input);

        if ($input['group'] == 2 && $input['insurance_company'] == '') {
            $validator->getMessageBag()->add('insurance_company', 'You should select an Insurance Company.');
            return \Redirect::back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            return \Redirect::route('admin.users.create')
                ->withInput()
                ->withErrors($validator);
        } else {
            $user = \User::create($input);
            $user->addGroup(\Sentry::findGroupById($input['group']));
            if ($input['insurance_company']) {
                $user->insurance_company()->associate(\InsuranceCompany::find($input['insurance_company']));
            } else {
                $user->insurance_company_id = null;
            }
            $user->save();

            return \Redirect::route('admin.users.index')
                ->with('success', 'A user has been successfully created.');
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
        $insurance_companies[null] = '- Select an Insurance Company -';
        foreach ($insurance_companies_obj as $insurance_company) {
            $insurance_companies[$insurance_company->id] = $insurance_company->name;
        }

        $groups_obj = \Sentry::findAllGroups();
        $groups = [];
        foreach ($groups_obj as $group) {

            if ($group->name !== 'Patient') {
                $groups[$group->id] = $group->name;
            }
        }

        $user = \User::find($id);
        $this->layout->content = View::make('admin/users/edit')
            ->with('user', $user)
            ->with('groups', $groups)
            ->with('insurance_companies', $insurance_companies)
            ->with('route', array('admin.users.update', $id))
            ->with('method', 'PUT');
    }

    public function update($id)
    {
        $input = \Input::all();
        if (empty($input['password'])) {
            unset($input['password']);
        }
        $validator = \User::preValidate($input, $id);

        if (isset($input['group']) && $input['group'] == 2 && $input['insurance_company'] == '') {
            $validator->getMessageBag()->add('insurance_company', 'You should select an Insurance Company.');
            return \Redirect::back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            return \Redirect::route('admin.users.edit', $id)
                ->withInput()
                ->withErrors($validator);
        } else {
            $user = \User::find($id);
            $user->fill($input);

            if (isset($input['insurance_company'])) {
                if ($input['insurance_company']) {
                    $user->insurance_company()->associate(\InsuranceCompany::find($input['insurance_company']));
                } else {
                    $user->insurance_company_id = null;
                }
            }

            $user->save();
            if (isset($input['group'])) {
                $groups_obj = \Sentry::findAllGroups();
                foreach ($groups_obj as $group) {
                    $user->removeGroup($group);
                }
                $user->addGroup(\Sentry::findGroupById($input['group']));
            }

            return \Redirect::route('admin.users.index')
                ->with('success', 'A user has been successfully updated.');
        }
    }

    public function destroy($id)
    {
        $user = \Sentry::findUserById($id);

        if (!$user) {
            \App::abort(404);
        }
        $user->delete();

        return array('ok' => 1);
    }

}
