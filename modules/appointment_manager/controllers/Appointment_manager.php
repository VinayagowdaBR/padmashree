<?php
defined('BASEPATH') or exit('No direct script access allowed');
require_once FCPATH . 'modules/appointment_manager/vendor/autoload.php';

class Appointment_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('appointment_manager_model');
    }

    public function index()
    {
        $data = array();
        $data['holidays'] = $this->appointment_manager_model->get_holidays('main');
        $data['locations'] = $this->appointment_manager_model->get_locations(false);
        $data['appointies'] = $this->appointment_manager_model->get_appointies();
        $data['treatments'] = $this->appointment_manager_model->get_treatment();
        $data['categories'] = [];
        if (!class_exists('staff_model')) {
            $this->load->model('staff_model');
        }
        if (!class_exists('clients_model')) {
            $this->load->model('clients_model');
        }
        $data['staffs'] = $this->appointment_manager_model->get_practitioners();
        $data['clients'] = $this->clients_model->get();
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('appointment_manager', 'appointments_table'));
        }
        $data['statuses'] = $this->appointment_manager_model->get_appointment_statuses();
        $data['timeFormat'] = 12 == get_option('time_format') ? "H:i" : "H:i";
        $this->load->view('calendar', $data);
    }
    public function holidays()
    {
        $data = array();
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('appointment_manager', 'holidays_table'));
        }
        $this->load->view('manage_holidays', $data);
    }
    public function holiday($id = '')
    {
        $data = array();
        if (is_numeric($id)) {
            $data['holiday'] = $this->appointment_manager_model->get_holiday($id);
        }
        if ($this->input->post()) {
            $response = array('status' => 'danger', 'message' => 'Something went wrong!');
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d H:i:s');
            $post_data['added_by'] = get_staff_user_id();
            if (is_numeric($id)) {
                if ($this->appointment_manager_model->update_holiday($id, $post_data)) {
                    $response['status'] = 'success';
                    $response['message'] = 'Updated successfully!';
                }
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/holidays'));
            }
            if ($this->appointment_manager_model->add_holiday($post_data)) {
                $response['status'] = 'success';
                $response['message'] = 'Added successfully!';
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/holidays'));
            }
        }
        $this->load->view('holiday', $data);
    }
    public function locations()
    {
        $data = array();
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('appointment_manager', 'locations_table'));
        }
        $this->load->view('manage_locations', $data);
    }
    public function location($id = '')
    {
        $data = array();
        if (is_numeric($id)) {
            $data['location'] = $this->appointment_manager_model->get_location($id);
        }
        if ($this->input->post()) {
            $response = array('status' => 'danger', 'message' => 'Something went wrong!');
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d H:i:s');
            $post_data['operation_start_time'] = date('H:i:s', strtotime($post_data['operation_start_time']));
            $post_data['operation_end_time'] = date('H:i:s', strtotime($post_data['operation_end_time']));
            $post_data['added_by'] = get_staff_user_id();
            if (is_numeric($id)) {
                if ($this->appointment_manager_model->update_location($id, $post_data)) {
                    $response['status'] = 'success';
                    $response['message'] = _l('appmgr_update_loc_alert');
                }
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/locations'));
            }
            if ($this->appointment_manager_model->add_location($post_data)) {
                $response['status'] = 'success';
                $response['message'] = _l('appmgr_add_loc_alert');
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/locations'));
            }
        }
        $this->load->view('location', $data);
    }
    public function appointies()
    {
        $data = array();
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('appointment_manager', 'appointies_table'));
        }
        $this->load->view('manage_appointies', $data);
    }
    public function appointee($id = '')
    {
        if (is_numeric($id)) {
            $data['appointee'] = $this->appointment_manager_model->get_appointies($id);
        }
        if ($this->input->post()) {
            $response = array('status' => 'danger', 'message' => 'Something went wrong!');
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d H:i:s');
            $post_data['added_by'] = get_staff_user_id();
            if (is_numeric($id)) {
                if ($this->appointment_manager_model->update_appointee($id, $post_data)) {
                    $response['status'] = 'success';
                    $response['message'] = _l('appmgr_update_apppointee_alert');
                }
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/appointies'));
            }
            if ($this->appointment_manager_model->add_appointee($post_data)) {
                $response['status'] = 'success';
                $response['message'] = _l('appmgr_add_apppointee_alert');
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/appointies'));
            }
        }
        $data['staffs'] = $this->staff_model->get();
        $data['locations'] = $this->appointment_manager_model->get_locations(false);
        $this->load->view('appointee', $data);
    }
    
    public function appointee_view($id)
    {
        if (is_numeric($id)) {
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('appointment_manager', 'appointiee_unavailibility_table'), array('appointee_id' => $id));
            }
            $data['appointee'] = $this->appointment_manager_model->get_appointies($id);
            $this->load->view('appointee_view', $data);
        }
    }
    public function delete_appointee($id)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $this->db->delete(db_prefix() . 'appmgr_appointies');
            if ($this->db->affected_rows() > 0) {
                log_activity('Appointee Deleted [ID:' . $id . ']');
                set_alert('success', 'Appointee deleted');
                redirect(admin_url('appointment_manager/appointies'));
            }
        }
    }
    public function appointee_unavailibility($appointee_id, $unavailibility_id = '')
    {
        if ($this->input->post()) {
            $response = array('status' => 'danger', 'message' => 'Something went wrong!');
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d H:i:s');
            $post_data['added_by'] = get_staff_user_id();
            if (is_numeric($unavailibility_id)) {
                if ($this->appointment_manager_model->update_appointee_unavailibility($unavailibility_id, $post_data)) {
                    $response['status'] = 'success';
                    $response['message'] = _l('updated_successfully');
                }
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/appointee_view/' . $appointee_id));
            }
            if ($this->appointment_manager_model->add_appointee_unavailibility($post_data)) {
                $response['status'] = 'success';
                $response['message'] = _l('added_successfully');
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/appointee_view/' . $appointee_id));
            }
        }
        $data['appointee'] = $this->appointment_manager_model->get_appointies($appointee_id);
        if (is_numeric($unavailibility_id)) {
            $data['unavailibility'] = $this->appointment_manager_model->get_unavailibility($unavailibility_id);
        }
        $this->load->view('appointee_unavailibility', $data);
    }
    public function appointee_availibility($appointee_id, $availibility_id = '')
    {
        if ($this->input->post()) {
            $response = array('status' => 'danger', 'message' => 'Something went wrong!');
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d H:i:s');
            $post_data['added_by'] = get_staff_user_id();
            if (isset($post_data['DataTables_Table_0_length'])) {
                unset($post_data['DataTables_Table_0_length']);
            }
            if (isset($post_data['unavailable'])) {
                $post_data['appointee_id'] = $appointee_id;
                unset($post_data['unavailable']);
                if ($this->appointment_manager_model->add_appointee_unavailibility($post_data)) {
                    $response['status'] = 'success';
                    $response['message'] = _l('added_successfully');
                    set_alert($response['status'], $response['message']);
                    redirect(admin_url('appointment_manager/appointee_availibility/' . $appointee_id . '/' . $availibility_id));
                }
            } else {
                if (is_numeric($availibility_id)) {
                    if ($this->appointment_manager_model->update_appointee_availibility($availibility_id, $post_data)) {
                        $response['status'] = 'success';
                        $response['message'] = _l('updated_successfully');
                    } else {
                        if ($this->appointment_manager_model->add_appointee_availibility($post_data)) {
                            $response['status'] = 'success';
                            $response['message'] = _l('added_successfully');
                            set_alert($response['status'], $response['message']);
                            redirect(admin_url('appointment_manager/appointee_availibility/' . $appointee_id . '/' . $availibility_id));
                        }
                    }
                    set_alert($response['status'], $response['message']);
                    redirect(admin_url('appointment_manager/appointee_availibility/' . $appointee_id . '/' . $availibility_id));
                } else {
                    $availibility_id = $this->appointment_manager_model->add_appointee_availibility($post_data);
                    $response['status'] = 'success';
                    $response['message'] = _l('added_successfully');
                    set_alert($response['status'], $response['message']);
                    redirect(admin_url('appointment_manager/appointee_availibility/' . $appointee_id . '/' . $availibility_id));
                }
            }
        }
        $data['appointee'] = $this->appointment_manager_model->get_appointies($appointee_id);
        if (is_numeric($availibility_id)) {
            $data['availibility'] = $this->appointment_manager_model->get_availibility($availibility_id);
        }
        $data['unavailibilities'] = $this->appointment_manager_model->get_unavailibilities('', $appointee_id);
        $this->load->view('appointee_availibility', $data);
    }

    function appointments()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (isset($post_data['room'])) {
                $post_data['opted_rooms'] = implode(',', $post_data['room']);
                unset($post_data['room']);
            }
            $isEdit = $post_data['isEdit'];
            if ($isEdit) {
                if ($this->appointment_manager_model->update_appointment($post_data['isEdit'], $post_data)) {
                    set_alert('success', _l('added_successfully', _l('appmgr_appointment')));
                    redirect(admin_url('appointment_manager'));
                }
            }
            unset($post_data['isEdit']);
            if ($this->appointment_manager_model->add_appointment($post_data)) {
                set_alert('success', _l('added_successfully', _l('appmgr_appointment')));
                redirect(admin_url('appointment_manager'));
            }
        }
    }
    function ajax_search_practitioner()
    {
        if ($this->input->is_ajax_request()) {
            $q = $this->input->post('q');
            $this->db->select('CONCAT(' . db_prefix() . 'staff.firstname," ",' . db_prefix() . 'staff.lastname) as name,id');
            $this->db->from(db_prefix() . 'appmgr_appointies');
            $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'appmgr_appointies.staff', 'left');
            if ($this->input->post('location')) {
                $this->db->where('location', $this->input->post('location'));
            }
            if ($this->input->post('appointment_date')) {
                $this->db->where(db_prefix() . 'appmgr_appointies.id NOT IN (SELECT appointee_id FROM ' . db_prefix() . 'appmgr_appointee_unavailibility WHERE unavailable_date = "' . $this->input->post('appointment_date') . '")');
            }
            if (isset($q)) {
                $q = trim($q);
                $this->db->like('firstname', $q);
            }
            echo json_encode($this->db->get()->result_array());
        }
    }
    public function services()
    {
        $data = array();
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('appointment_manager', 'treatments_table'));
        }
        $this->load->view('manage_treatments', $data);
    }

    public function service($id = '')
    {
        $data = [];
        if (is_numeric($id)) {
            $data['treatment'] = $this->appointment_manager_model->get_treatment($id);
        }
        if ($this->input->post()) {
            $response = array('status' => 'danger', 'message' => 'Something went wrong!');
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d H:i:s');
            $post_data['added_by'] = get_staff_user_id();
            if (is_numeric($id)) {
                if ($this->appointment_manager_model->update_treatment($id, $post_data)) {
                    $response['status'] = 'success';
                    $response['message'] = 'Updated successfully!';
                }
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/services'));
            }
            if ($this->appointment_manager_model->add_treatment($post_data)) {
                $response['status'] = 'success';
                $response['message'] = 'Added successfully!';
                set_alert($response['status'], $response['message']);
                redirect(admin_url('appointment_manager/services'));
            }
        }
        $this->load->view('treatment', $data);
    }
    function appointments_calendar()
    {
        $get_data = $this->input->get();
        $result = $this->appointment_manager_model->get_calendar_appointments($get_data);
        echo json_encode($result);
        die();
    }
    public function view_appointment($id)
    {
        $data['event'] = $this->appointment_manager_model->get_appointment($id);
        echo json_encode($data);
    }

    public function get_appointies_json($id)
    {
        $options = $this->appointment_manager_model->get_appointies($id);
        echo json_encode($options);
    }


    public function all_appointments($id = '')
    {
        if (staff_can('view_own', 'appointment_manager') || staff_can('view', 'appointment_manager')) {
            $data = array();
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('appointment_manager', 'appointments_table'));
            }
            $data['statuses'] = $this->appointment_manager_model->get_appointment_statuses();
            $data['staffs'] = $this->staff_model->get('', ['active' => 1]);
            $data['clients'] = $this->clients_model->get();
            $data['locations'] = $this->appointment_manager_model->get_locations(false);
            $data['treatments'] = $this->appointment_manager_model->get_treatment();
            $data['statuses'] = $this->appointment_manager_model->get_appointment_statuses();
            $data['appointment'] = $this->appointment_manager_model->get_appointment($id);
            $data['timeFormat'] = 12 == get_option('time_format') ? "H:i" : "H:i";
            $data['categories'] = [];
            $this->load->view('manage_appointments', $data);
        } else {
            access_denied();
        }
    }
    public function del_appointment($id)
    {
        if (is_numeric($id)) {
            $table = db_prefix() . 'appmgr_appointments';
            $this->appointment_manager_model->delete_row($id, $table);
            set_alert('success', _l('deleted', _l('appmgr_appointment')));
            redirect(admin_url('appointment_manager/all_appointments'));
        }
    }
    public function del_holiday($id)
    {
        if (is_numeric($id)) {
            $table = db_prefix() . 'appmgr_holidays';
            $this->appointment_manager_model->delete_row($id, $table);
            set_alert('success', _l('deleted', _l('appmgr_holiday_heading')));
            redirect(admin_url('appointment_manager/holidays'));
        }
    }
    public function del_treatment($id)
    {
        if (is_numeric($id)) {
            $table = db_prefix() . 'appmgr_treatments';
            $this->appointment_manager_model->delete_row($id, $table);
            set_alert('success', _l('deleted', _l('appmgr_treatment_label')));
            redirect(admin_url('appointment_manager/services'));
        }
    }
    public function statuses()
    {
        if (!is_admin()) {
            access_denied('Appointment Statuses');
        }
        $data['statuses'] = $this->appointment_manager_model->get_appointment_statuses();
        $data['title'] = 'Appointment statuses';
        $this->load->view('manage_appointment_statuses', $data);
    }

    public function appointment_status()
    {
        if (!is_admin()) {
            access_denied('Appointment Statuses');
        }
        if ($this->input->post()) {
            $data = $this->input->post();
            $isdefault = isset($data['isdefault']) ? 1 : 0;
            if ($isdefault == 1) {

                $this->db->update(db_prefix() . 'appmgr_appointment_status', ['isdefault' => 0]);
            }
            if (!$this->input->post('id')) {
                $inline = isset($data['inline']);
                if (isset($data['inline'])) {
                    unset($data['inline']);
                }
                $data['isdefault'] = $isdefault;
                $id = $this->appointment_manager_model->add_appointment_status($data);
                if (!$inline) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('appmgr_appointment_status')));
                    }
                } else {
                    echo json_encode(['success' => $id ? true : false, 'id' => $id]);
                }
            } else {
                $id = $data['id'];
                unset($data['id']);
                $data['isdefault'] = $isdefault;
                $success = $this->appointment_manager_model->update_appointment_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('appmgr_appointment_status')));
                }
            }
        }
    }

    public function delete_status($id)
    {
        if (!is_admin()) {
            access_denied('Appointment Statuses');
        }
        if (!$id) {
            redirect(admin_url('appointment_manager/statuses'));
        }
        $response = $this->appointment_manager_model->delete_status($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('lead_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('lead_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('lead_status_lowercase')));
        }
        redirect(admin_url('appointment_manager/statuses'));
    }

    public function getrooms($locid, $appid = '')
    {
        $html = '';
        $response = array('success' => false);
        $rooms = explode(',', prep_tags_input(get_tags_in($locid, 'appmgr_location')));
        if (isset($rooms) && !empty($rooms) && !empty($rooms[0])) {
            $optRooms = '';
            if (!empty($appid)) {
                $optRooms = $this->appointment_manager_model->getOptedRooms($appid);
            }
            $i = 1;
            foreach ($rooms as $room) {
                $isDisableOrCheck = '';
                if ($optRooms) {
                    $optRoomsArr = explode(",", $optRooms);
                    if (in_array($room, $optRoomsArr)) {
                        $isDisableOrCheck = 'checked';
                    }
                    if (isRoomDisable($appid, $room)) {
                        $isDisableOrCheck = 'disabled';
                    }
                    $html .= '<div class="form-check ck-wrap"><input class="form-check-input" name="room[]" type="checkbox" value="' . $room . '" id="check' . $i . '" ' . $isDisableOrCheck . '><label class="form-check-label" for="check' . $i . '">' . $room . '</label></div>';
                } else {
                    $html .= '<div class="form-check ck-wrap"><input class="form-check-input" name="room[]" type="checkbox" value="' . $room . '" id="check' . $i . '"><label class="form-check-label" for="check' . $i . '">' . $room . '</label></div>';
                }
                $response['success'] = true;
                $response['html'] = $html;
                $i++;
            }
        }
        echo json_encode($response);
        die;
    }
    function get_practitioner_busy_times()
    {
        if ($this->input->is_ajax_request()) {
            echo $this->appointment_manager_model->getPractitionerBusyTimes($this->input->post());
        }
    }

    public function appmgr_reports()
    {
        if (staff_can('view_own', 'appointment_manager') || staff_can('view', 'appointment_manager')) {
            $data = array();
            if ($this->input->is_ajax_request()) {
                $this->app->get_table_data(module_views_path('appointment_manager', 'reports_table'), $this->input->post());
            }
            $data['staffs'] = $this->staff_model->get('', ['active' => 1]);
            $data['locations'] = $this->appointment_manager_model->get_locations(false);
            $data['status'] = $this->appointment_manager_model->get_appointment_statuses();
            $this->load->view('manage_reports', $data);
        } else {
            access_denied();
        }
    }
    public function mark_as($status, $id)
    {
        $message = '';
        if (staff_can('approve', 'appointment_manager')) {
            $success = $this->appointment_manager_model->mark_as($status, $id);
            if ($success) {
                $message = _l('appointment_marked_as_success', format_task_status($status, true, true));
            }
            echo json_encode([
                'success' => $success,
                'message' => $message
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        }
    }
    function appointment($id)
    {
        if (is_numeric($id)) {
            $appointment = $this->appointment_manager_model->get_appointment($id);
            if (isset($appointment) && !empty($appointment)) {
                $data['appointment'] = $appointment;
                $data['locations'] = $this->appointment_manager_model->get_locations(false);
                $data['treatments'] = $this->appointment_manager_model->get_treatment();
                $data['statuses'] = $this->appointment_manager_model->get_appointment_statuses();
                if (!class_exists('clients_model')) {
                    $this->load->model('clients_model');
                }
                $data['staffs'] = $this->staff_model->get('', ['active' => 1]);
                $data['clients'] = $this->clients_model->get();
                $appmgr_custom_fields = false;
                if (total_rows(db_prefix() . 'customfields', ['fieldto' => 'appmgr', 'active' => 1]) > 0) {
                    $appmgr_custom_fields = true;
                }
                if ($appmgr_custom_fields) {
                    $rel_id = $id;
                    $data['c_fileds'] = render_custom_fields('appmgr', $rel_id);
                }
                echo json_encode($data);
                return;
            }
        }
        echo json_encode(null);
    }

    public function getTaCapabilty()
    {
        $response = ['create' => false, 'edit' => false];
        if (staff_can('create', 'appointment_manager')) {
            $response['create'] = true;
        }
        if (staff_can('edit', 'appointment_manager')) {
            $response['edit'] = true;
        }
        echo json_encode($response);
        die;
    }

    public function import()
    {
        $dbFields = $this->db->list_fields(db_prefix() . 'appmgr_appointments');
        array_push($dbFields, 'name');
        array_push($dbFields, 'email');
        array_push($dbFields, 'phonenumber');
        $this->load->library('import/import_appointments', [], 'import');
        $this->import->setDatabaseFields($dbFields)
            ->setCustomFields(get_custom_fields('appmgr_appointments'));
        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if (
            $this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != ''
        ) {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['statuses'] = $this->appointment_manager_model->get_appointment_statuses();
        $data['staffs'] = $this->staff_model->get('', ['active' => 1]);

        $data['title'] = _l('import');
        $this->load->view('appointment_manager/import', $data);
    }

    function update_appointments()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (isset($post_data['room'])) {
                $post_data['opted_rooms'] = implode(',', $post_data['room']);
                unset($post_data['room']);
            }
            $isEdit = $post_data['isEdit'];
            if ($isEdit) {
                if ($this->appointment_manager_model->update_appointment($post_data['isEdit'], $post_data)) {
                    set_alert('success', _l('updated_successfully', _l('appmgr_appointment')));
                    redirect(admin_url('appointment_manager/all_appointments'));
                }
            }
        }
    }

    function add_update_clients_appointments()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (isset($post_data['room'])) {
                $post_data['opted_rooms'] = implode(',', $post_data['room']);
                unset($post_data['room']);
            }
            $isEdit = $post_data['isEdit'];
            $client = $post_data['client'];
            if ($isEdit) {
                if ($this->appointment_manager_model->update_appointment($post_data['isEdit'], $post_data)) {
                    set_alert('success', _l('updated_successfully', _l('appmgr_appointment')));
                    redirect(admin_url('clients/client/' . $client . '?group=appointment_manager'));
                }
            } else {
                if (isset($post_data['isEdit'])) {
                    unset($post_data['isEdit']);
                }
                $this->appointment_manager_model->add_appointment($post_data);
                set_alert('success', _l('added_successfully', _l('appmgr_appointment')));
                redirect(admin_url('clients/client/' . $client . '?group=appointment_manager'));
            }
        }
    }
    function project($appointment_id)
    {
        if ($this->input->is_ajax_request()) {

            $post_data = $this->input->post();
            $title                            = _l('add_new', _l('project_lowercase'));
            $data['auto_select_billing_type'] = $this->projects_model->get_most_used_billing_type();
            $data['last_project_settings'] = $this->projects_model->get_last_project_settings();
            $data['customer_id'] = $this->input->get('customer_id');
            if (count($data['last_project_settings'])) {
                $key                                          = array_search('available_features', array_column($data['last_project_settings'], 'name'));
                $data['last_project_settings'][$key]['value'] = unserialize($data['last_project_settings'][$key]['value']);
            }
            $data['settings'] = $this->projects_model->get_settings();
            $data['statuses'] = $this->projects_model->get_project_statuses();
            $data['staff']    = $this->staff_model->get('', ['active' => 1]);
            $data['title'] = $title;
            $data['id'] = $appointment_id;
            $data['appointment'] = $this->appointment_manager_model->get_appointment($appointment_id);
            $this->load->view('create_opportunity', $data);
        }
    }
    function get_practitioner_by_location($id)
    {
        if ($this->input->is_ajax_request()) {
            $result = $this->appointment_manager_model->get_practitioners('', ['location' => $id]);
            if (isset($result) && !empty($result)) {
                echo render_select('staff', $result, ['staffid', 'name'], '', '', ['data-none-selected-text' => _l('appmgr_appointee_label')]);
            }
        }
    }
    public function del_unavailibility($id)
    {
        if (is_numeric($id)) {
            $unavailibility = $this->appointment_manager_model->get_unavailibilities($id);
            $availibility = $this->appointment_manager_model->get_availibility('', ['appointee_id' => $unavailibility->appointee_id]);
            if (isset($availibility) && !empty($availibility)) {
                $availibility = $availibility[0];
            }
            $table = db_prefix() . 'appmgr_appointee_unavailibility';
            $this->appointment_manager_model->delete_row($id, $table);
            set_alert('success', _l('deleted'));
            redirect(admin_url('appointment_manager/appointee_availibility/' . $unavailibility->appointee_id . '/' . $availibility['id']));
        }
    }

    function practionars_availibility()
    {
        if ($this->input->is_ajax_request()) {
            $post_data = $this->input->post();
            $result = $this->appointment_manager_model->get_availibility_by_appointee_id($post_data['appointee']);
            echo json_encode($result);
        }
    }
    function get_holidays($calendar = 'main')
    {
        if ($this->input->is_ajax_request()) {
            $holidays = $this->appointment_manager_model->get_holidays($calendar);
            echo json_encode($holidays);
        }
    }
    public function google_auth()
    {
        if (!get_option('am_g_auth_json')) {
            set_alert('danger', 'Crdentials json file not found!');
            redirect(admin_url('appointment_manager'));
        }
        $client = new Google_Client();
        $client->setAuthConfig('modules/appointment_manager/config/client_secret_oauth.json');
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $auth_url = $client->createAuthUrl();
        redirect($auth_url);
    }
    function googleSync()
    {
        if (!get_option('am_g_auth_json')) {
            set_alert('danger', 'Crdentials json file not found!');
            redirect(admin_url('appointment_manager'));
        }
        $access_token = $this->appointment_manager_model->check_google_token();
        if ($access_token) {
            set_alert('success', _l('Google calendar mapped successfully'));
            redirect(admin_url('appointment_manager'));
        } else {
            redirect(admin_url('appointment_manager/google_auth'));
        }
    }
    public function delete_credential()
    {
        $path = module_dir_path('appointment_manager', 'config/client_secret_oauth.json');
        if (file_exists($path)) {
            unlink($path);
            delete_option('am_g_auth_json');
            delete_option('am_g_accesstoken');
            delete_option('am_g_refreshtoken');
            delete_option('am_g_tokenexpirein');
            delete_option('am_g_auth_json_');
            set_alert('success', _l('deleted', 'Credentials'));
            redirect(admin_url('settings?group=appmgr_google'));
        }
        redirect(admin_url('settings?group=appmgr_google'));
    }
    public function del_location($id)
    {
        if (is_numeric($id)) {
            $table = db_prefix() . 'appmgr_locations';
            $this->appointment_manager_model->delete_row($id, $table);
            set_alert('success', _l('deleted', _l('appmgr_location')));
            redirect(admin_url('appointment_manager/locations'));
        }
    }
    public function service_categories()
    {
        $data = array();
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('appointment_manager', 'service_categories_table'));
        }
        $this->load->view('manage_service_categories', $data);
    }
    public function service_category($id = '')
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post('modal')) {
                $data['services'] = $this->appointment_manager_model->get_treatment();
                $data['form_action'] = admin_url('appointment_manager/service_category');
                if (is_numeric($this->input->post('edit'))) {
                    $data['service_category'] = $this->appointment_manager_model->get_service_categories($this->input->post('edit'));
                    $data['id'] = $this->input->post('edit');
                    $data['form_action'] = admin_url('appointment_manager/service_category/'.$data['id']);
                }
                $this->load->view('service_cat_modal', $data);
            }
        }elseif ($this->input->post()) {
            $post_data = $this->input->post();
            $post_data['added_at'] = date('Y-m-d');
            if (is_numeric($id)) {
                if ($this->appointment_manager_model->update_service_category($id, $post_data)) {
                    set_alert('success', _l('updated_successfully', _l('appmgr_ser_cat')));
                    redirect(admin_url('appointment_manager/service_categories'));
                }
            }
            if ($this->appointment_manager_model->add_service_category($post_data)) {
                set_alert('success', _l('added_successfully', _l('appmgr_ser_cat')));
                redirect(admin_url('appointment_manager/service_categories'));
            }
            set_alert('success', _l('added_successfully', _l('appmgr_ser_cat')));
            redirect(admin_url('appointment_manager/service_categories'));
        }
    }
    public function del_service_cat($id)
    {
        if (is_numeric($id)) {
            $table = db_prefix() . 'appmgr_service_cats';
            $this->appointment_manager_model->delete_row($id, $table);
            set_alert('success', _l('deleted', _l('appmgr_ser_cat')));
            redirect(admin_url('appointment_manager/service_categories'));
        }
    }
    function get_service_categories_ajax($service_id){
        if (is_numeric($service_id)) {
            echo json_encode($this->appointment_manager_model->get_service_categories('', ['service_id' => $service_id]));
        }
    }
}
