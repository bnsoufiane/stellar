<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('as' => 'index', 'uses' => 'MainController@index'));
Route::get('/send-sms', array('as' => 'send-sms', 'uses' => 'MainController@send_sms'));
Route::get('/cron-job', array('as' => 'cron-job', 'uses' => 'MainController@cron_job'));
Route::get('/outreach', array('as' => 'outreach', 'uses' => 'MainController@outreach'));

Route::get('sign-in', array('as' => 'sign-in', 'uses' => 'AuthController@showAuthForm'));
Route::post('sign-in', array('uses' => 'AuthController@postAuthForm'));
Route::get('sign-out', array('as' => 'sign-out', 'uses' => 'AuthController@signOut'));

Route::get('404', array('as' => 'errors.404', function () {
    return View::make('errors.404');
}));

Route::get('500', array('as' => 'errors.500', function () {
    return View::make('errors.500');
}));



Route::group(array('prefix' => 'admin', 'namespace' => 'Admin', 'before' => 'auth.admin'), function () {
    Route::get('', array('as' => 'admin.index', 'uses' => 'DashboardController@showIndex'));
    Route::post('parse_file', array('uses' => 'BaseController@parse_file', 'as' => 'admin.parse_file'));
    Route::resource('insurance_companies', 'InsuranceCompaniesController');
    Route::resource('regions', 'RegionsController');
    Route::resource('programs', 'ProgramsController');

    /* Region Programs Begin */
    Route::get('regions/{regionId}/create_program', array('uses' => 'ProgramsController@create', 'as' => 'admin.regions.create_program'));
    Route::get('regions/{regionId}/programs_roster', array('uses' => 'ProgramsController@index', 'as' => 'admin.regions.programs_roster'));
    Route::get('regions/{regionId}/programs/{programId}/edit', array('uses' => 'ProgramsController@edit', 'as' => 'admin.programs.edit'));

    Route::get('regions/{regionId}/programs/{programId}/import_visit_dates', array('uses' => 'ProgramsController@import_visit_dates', 'as' => 'admin.programs.import_visit_dates'));
    Route::post('programs/store_imported_visit_dates', array('uses' => 'ProgramsController@store_imported_visit_dates', 'as' => 'admin.programs.store_imported_visit_dates'));
    Route::post('programs/store_imported_visit_dates_post_partum', array('uses' => 'PostpartumimportController@store_imported_visit_dates_post_partum', 'as' => 'admin.programs.store_imported_visit_dates_post_partum'));

     Route::get('post_partum_import/resume_paused_records/{region_id}', array('uses' => 'PostpartumimportController@resume_import', 'as' => 'admin.programs.resume_import'));

    Route::get('post_partum_import/process_imported_dates_post_partum/{region_id}', array('uses' => 'PostpartumimportController@process_imported_dates_post_partum', 'as' => 'admin.programs.process_imported_dates_post_partum'));
    // route for ajax call while post partum import
     Route::get('post_partum_import/ajax/{region_id}', array('uses' => 'PostpartumimportController@store_imported_visit_dates_post_partum_ajax', 'as' => 'admin.programs.store_imported_visit_dates_post_partum_ajax'));

    /* Region Programs End */

    /* Patient Programs Relations Begin */
    Route::get('patients/{patientId}/programs/{programId}', array('uses' => 'ProgramsController@patient_visits', 'as' => 'admin.programs.patient_visits'));
    Route::get('patients/{patientId}/programs/{programId}/instance/{programInstanceId}', array('uses' => 'ProgramsController@patient_visits', 'as' => 'admin.programs.patient_visits_program_instance'));
    Route::put('regions/{regionId}/patients/{patientId}/programs/{programId}/add_patient_actual_visit', array('uses' => 'ProgramsController@add_patient_actual_visit', 'as' => 'admin.programs.add_patient_actual_visit'));

    Route::get('patients/{patientId}/programs/{programId}/add_new_pregnancy', array('uses' => 'ProgramsController@add_new_pregnancy', 'as' => 'admin.programs.add_new_pregnancy'));

    Route::get('patients/{patientId}/programs/{programId}/add_new_post_partum_instance', array('uses' => 'ProgramsController@add_new_post_partum_instance', 'as' => 'admin.programs.add_new_post_partum_instance'));

    Route::get('patients/{patientId}/programs/{programId}/add_new_first_trimester', array('uses' => 'ProgramsController@add_new_first_trimester', 'as' => 'admin.programs.add_new_first_trimester'));


    Route::get('programs/{programId}/patients_list_csv', array('uses' => 'ProgramsController@patients_list_csv', 'as' => 'admin.programs.patients_list_csv'));
    Route::get('programs/{programId}/patients_list', array('uses' => 'ProgramsController@patients_list', 'as' => 'admin.programs.patients_list'));

    /* Patient Programs Relations End */

    /* Region patients Begin */
    Route::get('regions/{regionId}/import_patients', array('uses' => 'RegionsController@import_patients', 'as' => 'admin.regions.import_patients'));
    Route::post('regions/upload', array('uses' => 'RegionsController@upload', 'as' => 'admin.regions.upload'));
    Route::post('regions/store_imported_patients', array('uses' => 'RegionsController@store_imported_patients', 'as' => 'admin.regions.store_imported_patients'));
    Route::get('regions/{regionId}/create_patients', array('uses' => 'RegionsController@create_patients', 'as' => 'admin.regions.create_patients'));
    Route::post('regions/{regionId}/store_patients', array('uses' => 'RegionsController@store_patients', 'as' => 'admin.regions.store_patients'));
    Route::get('patients/{patientId}/edit_patients', array('uses' => 'RegionsController@edit_patients', 'as' => 'admin.regions.edit_patients'));
    Route::get('patients/{patientId}/edit_patient_record', array('uses' => 'RegionsController@edit_patient_record', 'as' => 'admin.regions.edit_patient_record'));

    Route::post('patients/{patientId}/update_patients', array('uses' => 'RegionsController@update_patients', 'as' => 'admin.regions.update_patients'));
    Route::get('regions/{regionId}/patients_roster', array('uses' => 'RegionsController@patients_roster', 'as' => 'admin.regions.patients_roster'));
    Route::get('regions/{regionId}/patients_roster_csv', array('uses' => 'RegionsController@patients_roster_csv', 'as' => 'admin.regions.patients_roster_csv'));
    Route::get('regions/{regionId}/all_doctors', array('uses' => 'RegionsController@all_doctors', 'as' => 'admin.regions.all_doctors'));
    Route::get('regions/{regionId}/all_doctors_csv', array('uses' => 'RegionsController@all_doctors_csv', 'as' => 'admin.regions.all_doctors_csv'));
    /* Region patients End */

    /* Patients report Begin */
    Route::get('patients/{patientId}/report', array('uses' => 'PatientsController@report', 'as' => 'admin.patients.report'));
    /* Patients report End */
    Route::get('patients/full_list_cvs', array('uses' => 'UsersController@full_list_cvs', 'as' => 'admin.users.full_list_cvs'));

    /* Program reports Begin */
    Route::get('program_reports', array('uses' => 'ProgramsController@program_reports', 'as' => 'admin.programs.report'));
    Route::get('program_reports_result', array('uses' => 'ProgramsController@generate_report', 'as' => 'admin.programs.generate_report'));
    Route::get('program_reports_result_csv', array('uses' => 'ProgramsController@generate_report_csv', 'as' => 'admin.programs.generate_report_csv'));

    Route::get('scheduled_visit_report', array('uses' => 'PatientProgramVisitController@scheduled_visit_report', 'as' => 'admin.reports.scheduled_visit_report'));
    Route::get('scheduled_visit_report_result', array('uses' => 'PatientProgramVisitController@generate_scheduled_visit_report', 'as' => 'admin.reports.generate_scheduled_visit_report'));
    Route::get('scheduled_visit_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_scheduled_visit_report_csv', 'as' => 'admin.reports.generate_scheduled_visit_report_csv'));

    Route::get('incentive_report', array('uses' => 'PatientProgramVisitController@incentive_report', 'as' => 'admin.reports.incentive_report'));
    Route::get('incentive_report_result', array('uses' => 'PatientProgramVisitController@generate_incentive_report', 'as' => 'admin.reports.generate_incentive_report'));
    Route::get('incentive_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_incentive_report_csv', 'as' => 'admin.reports.generate_incentive_report_csv'));

    Route::get('member_roster_report', array('uses' => 'PatientProgramVisitController@member_roster_report', 'as' => 'admin.reports.member_roster_report'));
    Route::get('member_roster_report_result', array('uses' => 'PatientProgramVisitController@generate_member_roster_report', 'as' => 'admin.reports.generate_member_roster_report'));
    Route::get('member_roster_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_member_roster_report_csv', 'as' => 'admin.reports.generate_member_roster_report_csv'));

    Route::get('no_incentives_report', array('uses' => 'PatientProgramVisitController@no_incentives_report', 'as' => 'admin.reports.no_incentives_report'));
    Route::get('no_incentives_report_result', array('uses' => 'PatientProgramVisitController@generate_no_incentives_report', 'as' => 'admin.reports.generate_no_incentives_report'));
    Route::get('no_incentives_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_no_incentives_report_csv', 'as' => 'admin.reports.generate_no_incentives_report_csv'));

    Route::get('patient_outreach_report', array('uses' => 'PatientProgramVisitController@patient_outreach_report', 'as' => 'admin.reports.patient_outreach_report'));
    Route::get('patient_outreach_report_result', array('uses' => 'PatientProgramVisitController@generate_patient_outreach_report', 'as' => 'admin.reports.generate_patient_outreach_report'));
    Route::get('patient_outreach_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_patient_outreach_report_csv', 'as' => 'admin.reports.generate_patient_outreach_report_csv'));

    Route::get('quarterly_incentive_report', array('uses' => 'PatientProgramVisitController@quarterly_incentive_report', 'as' => 'admin.reports.quarterly_incentive_report'));
    Route::get('quarterly_incentive_report_result', array('uses' => 'PatientProgramVisitController@generate_quarterly_incentive_report', 'as' => 'admin.reports.generate_quarterly_incentive_report'));
    Route::get('quarterly_incentive_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_quarterly_incentive_report_csv', 'as' => 'admin.reports.generate_quarterly_incentive_report_csv'));

    Route::get('pregnancy_report', array('uses' => 'PatientProgramVisitController@pregnancy_report', 'as' => 'admin.reports.pregnancy_report'));
    Route::get('pregnancy_report_result', array('uses' => 'PatientProgramVisitController@generate_pregnancy_report', 'as' => 'admin.reports.generate_pregnancy_report'));
    Route::get('pregnancy_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_pregnancy_report_csv', 'as' => 'admin.reports.generate_pregnancy_report_csv'));

    Route::get('first_trimester_report', array('uses' => 'PatientProgramVisitController@first_trimester_report', 'as' => 'admin.reports.first_trimester_report'));
    Route::get('first_trimester_report_result', array('uses' => 'PatientProgramVisitController@generate_first_trimester_report', 'as' => 'admin.reports.generate_first_trimester_report'));
    Route::get('first_trimester_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_first_trimester_report_csv', 'as' => 'admin.reports.generate_first_trimester_report_csv'));

    Route::get('wc15_report', array('uses' => 'PatientProgramVisitController@wc15_report', 'as' => 'admin.reports.wc15_report'));
    Route::get('wc15_report_result', array('uses' => 'PatientProgramVisitController@generate_wc15_report', 'as' => 'admin.reports.generate_wc15_report'));
    Route::get('wc15_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_wc15_report_csv', 'as' => 'admin.reports.generate_wc15_report_csv'));

    Route::get('pregnancy_report_38_weeks', array('uses' => 'PatientProgramVisitController@pregnancy_report_38_weeks', 'as' => 'admin.reports.pregnancy_report_38_weeks'));
    Route::get('pregnancy_report_38_weeks_result_csv', array('uses' => 'PatientProgramVisitController@generate_pregnancy_report_38_weeks_csv', 'as' => 'admin.reports.generate_pregnancy_report_38_weeks_csv'));

    Route::get('returned_gift_card_report', array('uses' => 'PatientProgramVisitController@returned_gift_card_report', 'as' => 'admin.reports.returned_gift_card_report'));
    Route::get('returned_gift_card_report_result', array('uses' => 'PatientProgramVisitController@generate_returned_gift_card_report', 'as' => 'admin.reports.generate_returned_gift_card_report'));
    Route::get('returned_gift_card_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_returned_gift_card_report_csv', 'as' => 'admin.reports.generate_returned_gift_card_report_csv'));

    Route::get('outreach_codes_report', array('uses' => 'PatientProgramVisitController@outreach_codes_report', 'as' => 'admin.reports.outreach_codes_report'));
    Route::get('outreach_codes_report_result', array('uses' => 'PatientProgramVisitController@generate_outreach_codes_report', 'as' => 'admin.reports.generate_outreach_codes_report'));
    Route::get('outreach_codes_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_outreach_codes_report_csv', 'as' => 'admin.reports.generate_outreach_codes_report_csv'));

    Route::get('user_activity_report', array('uses' => 'PatientProgramVisitController@user_activity_report', 'as' => 'admin.reports.user_activity_report'));
    Route::get('user_activity_report_result', array('uses' => 'PatientProgramVisitController@generate_user_activity_report', 'as' => 'admin.reports.generate_user_activity_report'));
    Route::get('user_activity_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_user_activity_report_csv', 'as' => 'admin.reports.generate_user_activity_report_csv'));

    Route::get('cumulative_incentive_report', array('uses' => 'PatientProgramVisitController@cumulative_incentive_report', 'as' => 'admin.reports.cumulative_incentive_report'));
    Route::get('cumulative_incentive_report_result', array('uses' => 'PatientProgramVisitController@generate_cumulative_incentive_report', 'as' => 'admin.reports.generate_cumulative_incentive_report'));

    Route::get('cribs_for_kids_report', array('uses' => 'PatientProgramVisitController@cribs_for_kids_report', 'as' => 'admin.reports.cribs_for_kids_report'));
    Route::get('cribs_for_kids_report_result', array('uses' => 'PatientProgramVisitController@generate_cribs_for_kids_report', 'as' => 'admin.reports.generate_cribs_for_kids_report'));
    Route::get('cribs_for_kids_report_result_csv', array('uses' => 'PatientProgramVisitController@generate_cribs_for_kids_report_csv', 'as' => 'admin.reports.generate_cribs_for_kids_report_csv'));

    //get regions of an insurance company
    Route::get('insurance_company/{insurance_company_id}/regions', array('uses' => 'InsuranceCompaniesController@get_regions', 'as' => 'admin.insurance_company.regions'));
    Route::get('insurance_company/{insurance_company_id}/first_trimester_regions', array('uses' => 'InsuranceCompaniesController@get_first_trimester_regions', 'as' => 'admin.insurance_company.first_trimester_regions'));
    Route::get('insurance_company/{insurance_company_id}/pregnancy_regions', array('uses' => 'InsuranceCompaniesController@get_pregnancy_regions', 'as' => 'admin.insurance_company.pregnancy_regions'));
    Route::get('insurance_company/{insurance_company_id}/wc15_regions', array('uses' => 'InsuranceCompaniesController@get_wc15_regions', 'as' => 'admin.insurance_company.wc15_regions'));
    Route::get('regions/{region_id}/programs', array('uses' => 'RegionsController@get_programs', 'as' => 'admin.region.programs'));
    Route::get('regions/{region_id}/programs_with_types', array('uses' => 'RegionsController@get_programs_with_types', 'as' => 'admin.region.programs_with_types'));
    Route::get('program/{program_id}/patients', array('uses' => 'ProgramsController@get_patients', 'as' => 'admin.program.patients'));

    /**
     * Routes for to be automated reports, currently not cron till reviewed by stellar
     */
    
     Route::get('automated_reports', array('uses' => 'AutomatedReportsController@index', 'as' => 'admin.auto.repots'));
    
    Route::get('automated_roster_reports', array('uses' => 'AutomatedReportsController@generate_member_roster_report_csv', 'as' => 'admin.auto.report.roster'));

    Route::get('automated_pregnancy_reports', array('uses' => 'AutomatedReportsController@generate_pregnancy_report_csv', 'as' => 'admin.auto.report.roster'));
    
    /**
     * Routes for automated report ends
     */



    /* Program reports End */

    Route::resource('users', 'UsersController');
    Route::get('patients', array('as' => 'admin.patients.index', 'uses' => 'UsersController@patients'));
    Route::get('phones', array('as' => 'admin.phones.index', 'uses' => 'PhonesController@index'));
    Route::post('phones/update', array('as' => 'admin.phones.update', 'uses' => 'PhonesController@update'));
    Route::get('discontinue_tracking_reasons', array('as' => 'admin.discontinue_tracking_reasons.index', 'uses' => 'DiscontinueTrackingReasonsController@index'));
    Route::post('discontinue_tracking_reasons/update', array('as' => 'admin.discontinue_tracking_reasons.update', 'uses' => 'DiscontinueTrackingReasonsController@update'));
    Route::get('discontinue_tracking_wc15_reasons', array('as' => 'admin.discontinue_tracking_wc15_reasons.index', 'uses' => 'DiscontinueTrackingWC15ReasonsController@index'));
    Route::post('discontinue_tracking_wc15_reasons/update', array('as' => 'admin.discontinue_tracking_wc15_reasons.update', 'uses' => 'DiscontinueTrackingWC15ReasonsController@update'));
    Route::get('outreach_codes', array('as' => 'admin.outreach_codes.index', 'uses' => 'OutreachCodesController@index'));
    Route::post('outreach_codes/update', array('as' => 'admin.outreach_codes.update', 'uses' => 'OutreachCodesController@update'));
    Route::get('how_did_you_hear', array('as' => 'admin.how_did_you_hear.index', 'uses' => 'HowDidYouHearController@index'));
    Route::post('how_did_you_hear/update', array('as' => 'admin.how_did_you_hear.update', 'uses' => 'HowDidYouHearController@update'));

    Route::get('visit_date_verified', array('as' => 'admin.visit_date_verified.index', 'uses' => 'VisitDateVerifiedController@index'));
    Route::post('visit_date_verified/update', array('as' => 'admin.visit_date_verified.update', 'uses' => 'VisitDateVerifiedController@update'));

    Route::get('member_completed_required_visit_dates', array('as' => 'admin.member_completed_required_visit_dates.index', 'uses' => 'MemberCompletedRequiredVisitDatesController@index'));
    Route::post('member_completed_required_visit_dates/update', array('as' => 'admin.member_completed_required_visit_dates.update', 'uses' => 'MemberCompletedRequiredVisitDatesController@update'));


    Route::resource('practice_groups', 'PracticeGroupsController');
    Route::get('regions/{regionId}/create_practice_group', array('uses' => 'PracticeGroupsController@create', 'as' => 'admin.regions.create_practice_group'));
    Route::get('regions/{regionId}/practice_groups_roster', array('uses' => 'PracticeGroupsController@index', 'as' => 'admin.regions.practice_groups_roster'));
    Route::get('regions/{regionId}/practice_groups/{practiceGroupsId}/edit', array('uses' => 'PracticeGroupsController@edit', 'as' => 'admin.practice_groups.edit'));
    Route::get('regions/{regionId}/import_practice_groups', array('uses' => 'PracticeGroupsController@import_practice_groups', 'as' => 'admin.regions.import_practice_groups'));
    Route::post('regions/store_imported_practice_groups', array('uses' => 'PracticeGroupsController@store_imported_practice_groups', 'as' => 'admin.regions.store_imported_practice_groups'));
    Route::get('regions/{regionId}/practice_groups_full_list_cvs', array('uses' => 'PracticeGroupsController@full_list_cvs', 'as' => 'admin.regions.practice_groups_full_list_cvs'));

    Route::resource('doctors', 'DoctorsController');
    Route::get('regions/{regionId}/practice_groups/{practiceGroupID}/create_doctor', array('uses' => 'DoctorsController@create', 'as' => 'admin.practice_groups.create_doctor'));
    Route::get('regions/{regionId}/practice_groups/{practiceGroupID}/doctors_roster', array('uses' => 'DoctorsController@index', 'as' => 'admin.practice_groups.doctors_roster'));
    Route::get('regions/{regionId}/practice_groups/{practiceGroupID}/doctors/{doctorsId}/edit', array('uses' => 'DoctorsController@edit', 'as' => 'admin.doctors.edit'));
    Route::get('regions/{regionId}/import_doctors', array('uses' => 'DoctorsController@import_doctors', 'as' => 'admin.regions.import_doctors'));
    Route::post('regions/store_imported_doctors', array('uses' => 'DoctorsController@store_imported_doctors', 'as' => 'admin.regions.store_imported_doctors'));

    Route::resource('manual_outreaches', 'ManualOutreachesController');
    Route::get('patients/{patientId}/programs/{programId}/manual_outreaches/{manualOutreachID}/edit', array('uses' => 'ManualOutreachesController@edit', 'as' => 'admin.manual_outreaches.edit'));

    Route::resource('patient_program_visits', 'PatientProgramVisitController');
    Route::get('patients/{patientId}/programs/{programId}/patient_program_visits/{patientProgramVisit_ID}/edit', array('uses' => 'PatientProgramVisitController@edit', 'as' => 'admin.patient_program_visits.edit'));

});