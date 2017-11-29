<ul>
    <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.index') === 0) {{'section_active'}} @endif">
        <a href="{{ URL::route('index') }}">
            <span class="icon_house_alt first_level_icon"></span>
            <span class="menu-title">Dashboard</span>
        </a>
    </li>
    @if ($user->isSysAdmin())
        <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.users') === 0) {{'section_active'}} @endif">
            <a href="{{ URL::route('admin.users.index') }}">
                <span class="el-icon-adult first_level_icon"></span>
                <span class="menu-title">Admins</span>
            </a>
        </li>
    @endif
    <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.patients') === 0) {{'section_active'}} @endif">
        <a href="{{ URL::route('admin.patients.index') }}">
            <span class="social_myspace first_level_icon"></span>
            <span class="menu-title">Patients</span>
        </a>
    </li>
    @if ($user->isSysAdmin())
        <li class="first_level @if (strpos(Route::currentRouteName(), 'admin.insurance_companies') === 0) {{'section_active'}} @endif">
            <a href="{{ URL::route('admin.insurance_companies.index') }}">
                <span class="icon_document_alt first_level_icon"></span>
                <span class="menu-title">Insurance Companies</span>
            </a>
        </li>
    @endif
    <li class="first_level @if ((strpos(Route::currentRouteName(), 'admin.regions') === 0) || (strpos(Route::currentRouteName(), 'admin.programs') === 0)) {{'section_active'}} @endif">
        <a href="{{ URL::route('admin.regions.index') }}">
            <span class="icon_document_alt first_level_icon"></span>
            <span class="menu-title">Regions</span>
        </a>
    </li>
    @if ($user->isSysAdmin())
        <li class="first_level">
            <a href="javascript:void(0)">
                <span class="el-icon-wrench first_level_icon"></span>
                <span class="menu-title">General Settings</span>
            </a>
            <ul>
                <li><a href="{{ URL::route('admin.phones.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.phones') === 0) {{'act_nav'}} @endif">
                        Phone Pool</a>
                </li>
                <li><a href="{{ URL::route('admin.discontinue_tracking_reasons.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.discontinue_tracking_reasons') === 0) {{'act_nav'}} @endif">
                        Discontinue Tracking Reasons</a>
                </li>
                <li><a href="{{ URL::route('admin.discontinue_tracking_wc15_reasons.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.discontinue_tracking_wc15_reasons') === 0) {{'act_nav'}} @endif">
                        Discontinue Tracking WC15 Reasons</a>
                </li>
                <li><a href="{{ URL::route('admin.outreach_codes.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.outreach_codes') === 0) {{'act_nav'}} @endif">
                        Outreach Codes</a>
                </li>
                <li><a href="{{ URL::route('admin.visit_date_verified.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.visit_date_verified') === 0) {{'act_nav'}} @endif">
                        Visit Date Verified By</a>
                </li>
                <li><a href="{{ URL::route('admin.member_completed_required_visit_dates.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.member_completed_required_visit_dates') === 0) {{'act_nav'}} @endif">
                        Member Completed Required Visit Dates</a>
                </li>
                <li><a href="{{ URL::route('admin.how_did_you_hear.index') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.how_did_you_hear') === 0) {{'act_nav'}} @endif">How
                        Did You Hear</a>
                </li>

            </ul>
        </li>
        <li class="first_level">
            <a href="javascript:void(0)">
                <span class="icon_document_alt first_level_icon"></span>
                <span class="menu-title">Reports</span>
            </a>
            <ul>
                <li>
                    <a href="{{ URL::route('admin.reports.member_roster_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.member_roster_report') === 0) {{'act_nav'}} @endif">
                        Member Roster Report
                    </a>
                </li>
                <li><a href="{{ URL::route('admin.programs.report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.programs.report') === 0) {{'act_nav'}} @endif">Program
                        Reports</a></li>
                <li><a href="{{ URL::route('admin.reports.scheduled_visit_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.scheduled_visit_report') === 0) {{'act_nav'}} @endif">Scheduled
                        Visit Report</a></li>
                <li><a href="{{ URL::route('admin.reports.incentive_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.incentive_report') === 0) {{'act_nav'}} @endif">Incentive
                        Report</a></li>
                <li><a href="{{ URL::route('admin.reports.no_incentives_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.no_incentives_report') === 0) {{'act_nav'}} @endif">No
                        Incentives Report</a></li>
                <li><a href="{{ URL::route('admin.reports.pregnancy_report') }}"
                       class="@if (Route::currentRouteName() == 'admin.reports.pregnancy_report')  {{'act_nav'}} @endif">Pregnancy
                        Reports</a></li>
                <li><a href="{{ URL::route('admin.reports.pregnancy_report_38_weeks') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.pregnancy_report_38_weeks') === 0) {{'act_nav'}} @endif">Pregnancy
                        Report - 36, 38 Weeks</a></li>
                <li><a href="{{ URL::route('admin.reports.first_trimester_report') }}"
                       class="@if (Route::currentRouteName() == 'admin.reports.first_trimester_report')  {{'act_nav'}} @endif">First
                        Trimester Reports</a></li>
                <li><a href="{{ URL::route('admin.reports.wc15_report') }}"
                       class="@if (Route::currentRouteName() == 'admin.reports.wc15_report')  {{'act_nav'}} @endif">WC15
                        Reports</a></li>
                <li><a href="{{ URL::route('admin.reports.returned_gift_card_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.returned_gift_card_report') === 0) {{'act_nav'}} @endif">Returned
                        Gift Card Report</a></li>
                <li><a href="{{ URL::route('admin.reports.outreach_codes_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.outreach_codes_report') === 0) {{'act_nav'}} @endif">Outreach
                        Code Report</a></li>
                <li><a href="{{ URL::route('admin.reports.quarterly_incentive_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.quarterly_incentive_report') === 0) {{'act_nav'}} @endif">Quarterly
                        Incentive Report</a></li>
                <li><a href="{{ URL::route('admin.reports.user_activity_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.user_activity_report') === 0) {{'act_nav'}} @endif">User
                        Activity Report</a></li>
                <li><a href="{{ URL::route('admin.reports.patient_outreach_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.patient_outreach_report') === 0) {{'act_nav'}} @endif">Patient
                        Outreach Report</a></li>
                <li><a href="{{ URL::route('admin.reports.cumulative_incentive_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.cumulative_incentive_report') === 0) {{'act_nav'}} @endif">Cumulative
                        Incentive report</a></li>
                <li><a href="{{ URL::route('admin.reports.cribs_for_kids_report') }}"
                       class="@if (strpos(Route::currentRouteName(), 'admin.reports.cribs_for_kids_report') === 0) {{'act_nav'}} @endif">Cribs
                        For Kids report</a></li>
            </ul>
        </li>
    @endif
</ul>