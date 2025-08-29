<?php defined('BASEPATH') or exit('No direct script access allowed');

$from_date = $this->ci->input->post('date_f');
$to_date = $this->ci->input->post('date_t');
$staff = $this->ci->input->post('staff');
$location = $this->ci->input->post('location');
$status = $this->ci->input->post('status');
$aColumns = [
    'appointee',
    'company',
    'appointment_date',
    db_prefix() . 'appmgr_appointments.location as appointment_location',
    'status',
    'appointer'
];
$sIndexColumn = 'id';
$sTable = db_prefix() . 'appmgr_appointments';
$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'appmgr_appointments.appointer',
    'LEFT JOIN ' . db_prefix() . 'appmgr_appointies ON ' . db_prefix() . 'appmgr_appointies.id = ' . db_prefix() . 'appmgr_appointments.appointee',
    'LEFT JOIN ' . db_prefix() . 'appmgr_locations ON ' . db_prefix() . 'appmgr_locations.id = ' . db_prefix() . 'appmgr_appointments.location',
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'appmgr_appointments.client',
    'LEFT JOIN ' . db_prefix() . 'staff as practitioners ON practitioners.staffid = ' . db_prefix() . 'appmgr_appointies.staff',
    'LEFT JOIN ' . db_prefix() . 'appmgr_appointee_availibility ON  ' . db_prefix() . 'appmgr_appointee_availibility.appointee_id= ' . db_prefix() . 'appmgr_appointments.appointee',
];
$where = [];
if ($from_date && $to_date) {
    $from_date = date('Y-m-d 00:00:00', strtotime($from_date));
    $to_date = date('Y-m-d 23:59:59', strtotime($to_date));
    $where[] = "AND appointment_date BETWEEN '$from_date' AND '$to_date'";
}
if ($staff) {
    array_push($where, ' AND appointee =' . $staff);
}
if ($location) {

    array_push($where, ' AND ' . db_prefix() . 'appmgr_appointments.location = ' . $location);
}
if ($status) {

    array_push($where, ' AND ' . db_prefix() . 'appmgr_appointments.status = ' . $status);
}
if (!is_admin()) {
    if (staff_can('view_own', 'appointment_manager')) {
        array_push($where, ' AND ' . db_prefix() . 'appmgr_appointments.appointer=' . get_staff_user_id());
    }
}
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    db_prefix() . 'staff.firstname as assigned_firstname',
    db_prefix() . 'staff.lastname as assigned_lastname',
    'CONCAT(practitioners.firstname," ",practitioners.lastname) as practitioner',
    db_prefix() . 'appmgr_locations.name as location_name',
    'appointee',
    db_prefix() . 'appmgr_appointments.id',
    db_prefix() . 'appmgr_appointee_availibility.id as availid'
]);
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $nameRow = '<a href="' . admin_url('appointment_manager/appointee_availibility/' . $aRow['appointee'] . '/' . $aRow['availid']) . '">' . $aRow['practitioner'] . '</a>';
    $nameRow .= '<div class="row-options">';
    $nameRow .= '</div>';

    $row[] = $nameRow;
    $row[] = $aRow['company'];
    $row[] = _d($aRow['appointment_date']);
    $row[] = $aRow['location_name'];

    $status = get_appointment_status_by_id($aRow['status']);
    $row[] = '<span class="label appointment-status-' . $aRow['status'] . '" style="color:' . $status['color'] . ';border:1px solid ' . adjust_hex_brightness($status['color'], 0.4) . ';background: ' . adjust_hex_brightness($status['color'], 0.04) . ';">' . e($status['name']) . '</span>';

    if ($aRow['appointer']) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];
        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['appointer']) . '">' . staff_profile_image($aRow['appointer'], ['staff-profile-image-small']) . '</a>';
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
        $row[] = $assignedOutput;
    } else {
        $row[] = 'Public Form';
    }

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
