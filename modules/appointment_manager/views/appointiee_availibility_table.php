<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'repeat',
    'from_time',
    'to_time',
    'added_by as unavl_added_by',
    'added_at as unavl_added_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'appmgr_appointee_availibility';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'appmgr_appointee_availibility.added_by'
];

$where = [' AND '.db_prefix() . 'appmgr_appointee_availibility.appointee_id = '.$appointee_id];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [db_prefix() . 'staff.firstname as assigned_firstname', db_prefix() . 'staff.lastname as assigned_lastname', db_prefix() . 'appmgr_appointee_availibility.appointee_id', 'available_date_from','available_date_to']);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $hrefAttr = 'href="' . admin_url('appointment_manager/appointee_view/' . $aRow['appointee_id']) . '"';
    $hrefAttrEdit = 'href="' . admin_url('appointment_manager/appointee/' . $aRow['appointee_id']) . '"';
    $hrefAttrDelete = 'href="' . admin_url('appointment_manager/delete_appointee/' . $aRow['appointee_id']) . '"';
    $nameRow = '<a ' . $hrefAttr . '>' . _d($aRow['repeat']) . '</a>';
    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttrDelete . ' class="text-danger _delete">' . _l('delete') . '</a>';
    $nameRow .= '</div>';

    $row[] = $nameRow;
    $row[] = date('g:i A', strtotime($aRow['from_time']));
    $row[] = date('g:i A', strtotime($aRow['to_time']));
    if ($aRow['avl_added_by']) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];
        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['avl_added_by']) . '">' . staff_profile_image($aRow['avl_added_by'], [
            'staff-profile-image-small',
        ]) . '</a>';
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
        $row[] = $assignedOutput;
    }
    $row[] = _d($aRow['avl_added_at']);
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
