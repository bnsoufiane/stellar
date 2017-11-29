<?php

namespace Admin;

use View;

class PatientsController extends UsersController
{

    public function report($id)
    {
        $year = \Input::get('year');
        $year = !empty($year) ? $year : date("Y");

        $patient = \User::find($id);

        if (!$patient) {
            \App::abort(404);
        }

        $insurance_company = $patient->insurance_company()->first();
        $region = $patient->region()->first();
        $programs = $patient->patient_programs();

        $this->layout->content = View::make('admin/users/patient_report/index')
            ->with('patient', $patient)
            ->with('insurance_company', $insurance_company)
            ->with('region', $region)
            ->with('programs', $programs)
            ->with('year', $year);
    }
}
