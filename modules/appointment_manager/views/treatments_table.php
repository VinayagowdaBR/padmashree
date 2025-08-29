<?php
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'tittle',
    'description',
    'added_by',
    'added_at'
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'appmgr_treatments';
$join = ['LEFT JOIN ' . db_prefix() . 'staff ON ' . db_prefix() . 'staff.staffid = ' . db_prefix() . 'appmgr_treatments.added_by'];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], [db_prefix() . 'staff.firstname as assigned_firstname', db_prefix() . 'staff.firstname as assigned_firstname', db_prefix() . 'staff.lastname as assigned_lastname', db_prefix() . 'appmgr_treatments.id']);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $nameRow = $aRow['tittle'];
    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a href="' . admin_url('appointment_manager/service/'. $aRow['id']) . '">' . _l('appmgr_edit') . '</a> | <a class="_delete text-danger" title="'._l('appmgr_del_title').'" href="' . admin_url('appointment_manager/del_treatment/' . $aRow['id']) . '"><i class="fa-solid fa-trash"></i></a>';
    $nameRow .= '</div>';
    $row[] = $nameRow;
    $row[] = $aRow['description'];
    if ($aRow['added_by']) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];
        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['added_by']) . '">' . staff_profile_image($aRow['added_by'], [
            'staff-profile-image-small',
        ]) . '</a>';
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
        $row[] = $assignedOutput;
    }
    $row[] = $aRow['added_at'];
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
