<?php
defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'name',
    'tittle',
    db_prefix() .'appmgr_service_cats.added_at as addedat'
];
$sIndexColumn = 'id';
$sTable       = db_prefix() . 'appmgr_service_cats';
$join = ['LEFT JOIN ' . db_prefix() . 'appmgr_treatments ON ' . db_prefix() . 'appmgr_treatments.id = ' . db_prefix() . 'appmgr_service_cats.service_id'];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [],[db_prefix() .'appmgr_service_cats.id as catid']);
$output  = $result['output'];
$rResult = $result['rResult'];
foreach ($rResult as $aRow) {
    $row = [];
    $nameRow = $aRow['name'];
    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a href="#" onclick="editServiceCat('.$aRow['catid'].')">' . _l('appmgr_edit') . '</a> | <a class="_delete text-danger" title="'._l('appmgr_del_title').'" href="' . admin_url('appointment_manager/del_service_cat/' . $aRow['catid']) . '"><i class="fa-solid fa-trash"></i></a>';
    $nameRow .= '</div>';
    $row[] = $nameRow;
    $row[] = $aRow['tittle'];
    $row[] = $aRow['addedat'];
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
