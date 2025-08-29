<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'CONCAT(practitionar.firstname," ",practitionar.lastname) as appointee_name',
    'location',
    db_prefix() . 'appmgr_appointies.description as appointee_desc',
    db_prefix() . 'appmgr_appointies.added_by as appointee_added_by',
    db_prefix() . 'appmgr_appointies.added_at as appointee_added_at'
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'appmgr_appointies';

$join = [
    'LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'appmgr_appointies.added_by',
    'LEFT JOIN ' . db_prefix() . 'appmgr_locations ON ' . db_prefix() . 'appmgr_locations.id = ' . db_prefix() . 'appmgr_appointies.location',
    'LEFT JOIN ' . db_prefix() . 'staff as practitionar ON practitionar.staffid = ' . db_prefix() . 'appmgr_appointies.staff',
    'LEFT JOIN ' . db_prefix() . 'appmgr_appointee_availibility ON  '.db_prefix().'appmgr_appointee_availibility.appointee_id= ' . db_prefix() . 'appmgr_appointies.id',
];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [db_prefix() . 'staff.firstname as assigned_firstname', db_prefix() . 'staff.lastname as assigned_lastname', db_prefix() . 'appmgr_locations.name as location', db_prefix() . 'appmgr_appointies.id',db_prefix().'appmgr_appointee_availibility.id as availid']);

$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $href =  admin_url('appointment_manager/appointee_availibility/' . $aRow['id']);
    if (isset($aRow['availid']) && !empty($aRow['availid'])) {
        $href =  admin_url('appointment_manager/appointee_availibility/' . $aRow['id'] . '/' . $aRow['availid']);
    }
    $hrefAttrEdit = 'href="' . admin_url('appointment_manager/appointee/' . $aRow['id']) . '"';
    $hrefAttrDelete = 'href="' . admin_url('appointment_manager/delete_appointee/' . $aRow['id']) . '"';
    $nameRow = '<a href="' . $href . '">' . e($aRow['appointee_name']) . '</a>';
    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttrEdit . '>' . _l('edit') . '</a> | <a ' . $hrefAttrDelete . ' class="text-danger _delete">' . _l('delete') . '</a>';
    $nameRow .= '</div>';

    $row[] = $nameRow;
    $row[] = $aRow['location'];
    $row[] = $aRow['appointee_desc'];
    if ($aRow['appointee_added_by']) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];
        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['appointee_added_by']) . '">' . staff_profile_image($aRow['appointee_added_by'], [
            'staff-profile-image-small',
        ]) . '</a>';
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
        $row[] = $assignedOutput;
    }
    $row[] = $aRow['appointee_added_at'];
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
