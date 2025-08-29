<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_appointments extends App_import
{
    private $uniqueValidationFields = [];

    protected $notImportableFields = [];

    protected $requiredFields = [];

    protected $extraImportableFields = ['name', 'email', 'phonenumber'];

    protected $sources;

    protected $statuses;

    protected $locations;

    protected $treatments;

    protected $clients;
    protected $contacts;

    protected $staffs;

    public function __construct()
    {
        $this->notImportableFields = hooks()->apply_filters('not_importable_ta_appointments_fields', ['id', 'status', 'opted_rooms', 'reminder_before', 'reminder_before_type', 'isstartnotified', 'added_by', 'added_at', 'appointment_start_time', 'appointment_end_time', 'client']);
        parent::__construct();
        $this->statuses = $this->ci->db->get('appmgr_appointment_status')->result_array();
        $this->locations = $this->ci->db->get('appmgr_locations')->result_array();
        $this->treatments = $this->ci->db->get('appmgr_treatments')->result_array();
        $this->clients = $this->ci->db->get('clients')->result_array();
        $this->contacts = $this->ci->db->where(['is_primary' => 1])->get(db_prefix() . 'contacts')->result_array();
        $this->staffs = $this->ci->staff_model->get();
    }

    public function perform()
    {
        $this->initialize();
        $databaseFields = $this->getImportableDatabaseFields();
        $totalDatabaseFields = count($databaseFields);
        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            //print_r($row); die;
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);
                // if ($databaseFields[$i] == 'name' && empty($row[$i])) {
                //     $row[$i] = '/';
                // }
                // elseif ($databaseFields[$i] == 'client') {
                //     $row[$i] = $this->clientValue($row[$i]);
                // }
                if ($databaseFields[$i] == 'email') {
                    if (empty($row[$i])) {
                        continue;
                    } else {
                        $userId = $this->clientValueByContactEmail($row[$i]);
                        if (empty($userId)) {
                            $row[$i] = $this->crateClientWithPrimaryContact($row, $databaseFields);
                        } else {
                            $row[$i] = $userId;
                        }
                    }
                } elseif ($databaseFields[$i] == 'appointee') {
                    $row[$i] = $this->staffValue($row[$i]);
                } elseif ($databaseFields[$i] == 'appointer') {
                    $row[$i] = $this->staffValue($row[$i]);
                } elseif ($databaseFields[$i] == 'location') {
                    $row[$i] = $this->locationValue($row[$i]);
                } elseif ($databaseFields[$i] == 'treatment') {
                    $row[$i] = $this->treatmentValue($row[$i]);
                } elseif ($databaseFields[$i] == 'appointment_date') {
                    $row[$i] = to_sql_date($row[$i], true);
                }
                $insert[$databaseFields[$i]] = $row[$i];
            }
            $insert = $this->trimInsertValues($insert);
            if (count($insert) > 0) {
                if ($this->isDuplicateLead($insert)) {
                    continue;
                }

                $this->incrementImported();

                $id = null;

                if (!$this->isSimulation()) {
                    if (!isset($insert['appointee'])) {
                        $insert['appointee'] = get_staff_user_id();
                    }
                    if (!isset($insert['appointer'])) {
                        $insert['appointer'] = get_staff_user_id();
                    }
                    $insert['client'] = $insert['email'];

                    if (!isset($insert['location'])) {
                        $insert['location'] = get_location_id();
                    }
                    if (!isset($insert['treatment'])) {
                        $insert['treatment'] = get_treatment_id();
                    }
                    if (isset($insert['phonenumber'])) {
                        unset($insert['phonenumber']);
                    }
                    if (isset($insert['name'])) {
                        unset($insert['name']);
                    }
                    if (isset($insert['email'])) {
                        unset($insert['email']);
                    }
                    $this->ci->db->insert('appmgr_appointments', $insert);
                    $id = $this->ci->db->insert_id();
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
                }

                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'appointment');
            }

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    protected function findtreatment($value)
    {
        $treatment_array = NULL;
        foreach ($this->treatments as $treatment) {
            if (strtolower($treatment['tittle']) == strtolower($value) || $treatment['id'] == $value) {
                $treatment_array =  $treatment;
            }
        }
        if (empty($treatment_array)) {
            $treatment_array =  $this->createTreatment($value);
        }
        return $treatment_array;
    }
    protected function findclient($value)
    {
        foreach ($this->clients as $client) {
            if (strtolower($client['company']) == strtolower($value) || $client['userid'] == $value) {
                return $client;
            }
        }
    }
    protected function findclientbyprimarycontactemail($value)
    {
        foreach ($this->contacts as $contact) {
            if (strtolower($contact['email']) == strtolower(trim($value)) || $contact['userid'] == $value) {
                return $contact;
            }
        }
    }
    protected function findstaff($value)
    {
        if (!empty($value)) {
            foreach ($this->staffs as $staff) {
                if (strtolower($staff['full_name']) == strtolower($value) || $staff['staffid'] == $value) {
                    return $staff;
                }
            }
        }
    }
    protected function findlocation($value)
    {
        if (!empty($value)) {
            foreach ($this->locations as $location) {
                if (strtolower($location['name']) == strtolower($value) || $location['id'] == $value) {
                    return $location;
                }
            }
        }
    }



    protected function clientValue($value)
    {
        return $this->findclient($value)['id'] ?? $this->ci->input->post('client');
    }
    protected function clientValueByContactEmail($value)
    {
        $client = $this->findclientbyprimarycontactemail($value);
        if (isset($client) && !empty($client)) {
            return $client['userid'];
        } else {
            return NULL;
        }
    }
    protected function addedByValue($value)
    {
        return $this->findstaff($value)['staffid'] ?? $this->ci->input->post('appointee');
    }
    protected function locationValue($value)
    {
        return $this->findlocation($value)['id'] ?? $this->ci->input->post('location');
    }
    protected function staffValue($value)
    {
        return $this->findstaff($value)['staffid'] ?? $this->ci->input->post('appointer');
    }
    protected function treatmentValue($value)
    {
        return $this->findtreatment($value)['id'] ?? $this->ci->input->post('treatment');
    }

    protected function tags_formatSampleData()
    {
        return 'tag1,tag2';
    }

    public function formatFieldNameForHeading($field)
    {
        if (strtolower($field) == 'title') {
            return 'Position';
        }

        return parent::formatFieldNameForHeading($field);
    }

    protected function email_formatSampleData()
    {
        return uniqid() . '@example.com';
    }

    protected function failureRedirectURL()
    {
        return admin_url('ta_calendar/import');
    }

    private function isDuplicateLead($data)
    {
        foreach ($this->uniqueValidationFields as $field) {
            if ((isset($data[$field]) && $data[$field] != '') && total_rows('appmgr_apointments', [$field => $data[$field]]) > 0) {
                return true;
            }
        }

        return false;
    }

    private function formatValuesForSimulation($values)
    {
        foreach ($values as $column => $val) {
            if ($column == 'country' && !empty($val) && is_numeric($val)) {
                $country = $this->getCountry(null, $val);
                if ($country) {
                    $values[$column] = $country->short_name;
                }
            } elseif ($column == 'email') {
                $returnClient = $this->clientValueByContactEmail($val);
                if (empty($returnClient)) {
                    $values[$column] = 'N/A';
                } else {
                    $contact = $this->ci->clients_model->get_contact(get_primary_contact_user_id($returnClient));
                    $values[$column] = $contact->email;
                }
            } elseif ($column == 'appointee') {
                $values[$column] = $this->findstaff($val)['full_name'] ?? 'N/A';
            } elseif ($column == 'appointer') {
                $values[$column] = $this->findstaff($val)['full_name'] ?? 'N/A';
            } elseif ($column == 'treatment') {
                $values[$column] = $this->findtreatment($val)['tittle'] ?? 'N/A';
            } elseif ($column == 'location') {
                $values[$column] = $this->findlocation($val)['name'] ?? 'N/A';
            } elseif ($column == 'added_by') {
                $values[$column] = $this->findstaff($val)['full_name'] ?? 'N/A';
            }
        }

        return $values;
    }

    private function getCountry($search = null, $id = null)
    {
        if ($search) {
            $this->ci->db->where('iso2', $search)
                ->or_where('short_name', $search)
                ->or_where('long_name', $search);
        } else {
            $this->ci->db->where('country_id', $id);
        }

        return $this->ci->db->get('countries')->row();
    }

    private function countryValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $country = $this->getCountry($value);
                $value = $country ? $country->country_id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }
    private function crateClientWithPrimaryContact($row, $databaseFields)
    {
        if (!class_exists('clients_model')) {
            $this->ci->load->model('clients_model');
        }
        $formattedRow = array();
        for ($i = 0; $i < count($databaseFields); $i++) {
            $formattedRow[$databaseFields[$i]] = $row[$i];
        }
        $client_data = array('company ' => $formattedRow['name'], 'phonenumber' => $formattedRow['phonenumber'], 'contact_phonenumber' => $formattedRow['phonenumber'], 'email' => $formattedRow['email'], 'is_primary' => 1, 'password' => $formattedRow['phonenumber']);
        return $this->ci->clients_model->add($client_data, true);
    }
    private function createTreatment($value)
    {
        $this->ci->db->insert(db_prefix() . 'appmgr_treatments', array('tittle' => $value, 'added_at' => date('Y-m-d H:i:s'), 'added_by' => get_staff_user_id()));
        $insert_id = $this->ci->db->insert_id();
        if ($insert_id) {
            $treatment = $this->ci->db->where('id', $insert_id)->get('appmgr_treatments')->row();
            return (array) $treatment;
        }
    }
}
