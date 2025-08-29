<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'modules/appointment_manager/vendor/autoload.php';
class Appointment_manager_client extends ClientsController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('appointment_manager_model');
        $this->load->model('staff_model');
        $this->load->model('clients_model');
    }

    public function public_form()
    {
        $data['title'] = 'Public Form';
        $data['clients'] = get_client();
        $data['staffs'] = $this->staff_model->get('', ['active' => 1]);
        $data['locations'] = $this->appointment_manager_model->get_locations(false);
        $data['appointies'] = $this->appointment_manager_model->get_appointies();
        $data['treatments'] = $this->appointment_manager_model->get_treatment();
        $data['status_id'] = $this->appointment_manager_model->get_status_id_by_default();
        $data['site_url'] = base_url('');
        $data['alert']      = isset($_GET['alert']) ?  $_GET['alert'] : false;
        $data['timeZone'] = get_option('default_timezone');
        $data['timeFormat'] = get_option('time_format');
        $navigationDisabled = hooks()->apply_filters('disable_navigation_on_public_ticket_view', true);
        if ($navigationDisabled) {
            $this->disableNavigation();
        }
        $this->disableFooter();
        $this->data($data);
        $this->view('public_form');
        $this->layout(true);
    }

    public function add_appointments()
    {
        $post_data = $this->input->post();
        $alertIframe = $post_data['iframe'];
        $client_data = [
            'company' => $post_data['company'],
            // 'phonenumber' => $post_data['phonenumber']
        ];
        $client = $this->clients_model->get('', [db_prefix() . 'clients.phonenumber' => $post_data['phonenumber']]);
        $rooms = NULL;

        if (isset($data['mobile'])) {
        $data['phone_number'] = $data['mobile'];
        unset($data['mobile']);
    }
        if (isset($post_data['room']) && !empty($post_data['room'])) {
            $rooms = implode(', ', $post_data['room']);


        }
        $appointment_data = [
            'appointment_date' => $post_data['appointment_date'],
            'appointment_start_time' => $post_data['appointment_start_time'],
            'appointment_end_time' => $post_data['appointment_end_time'],
            'description' => $post_data['description'],
            'appointee' => $post_data['appointee'],
            'treatment' => $post_data['treatment'],
            'location' => $post_data['location'],
            'opted_rooms' => $rooms,
              'mobile' => $post_data['phonenumber'], // ✅ Save phone number as mobile
                'affiliate_code' => $post_data['affiliate_code'], // ✅ Save affiliate code
        ];
        if (isset($post_data['service_cat'])) {
            $appointment_data['service_cat'] = $post_data['service_cat'];
        }
        $client_id = '';
        if (count($client) > 0) {
            $client_id = $client[0]['userid'];
        }
        if (!is_numeric($client_id)) {
            $client_id = $this->clients_model->add($client_data);
        }
        if ($client_id) {
            $this->clients_model->add_contact(array('phonenumber' => $post_data['phonenumber'], 'is_primary' => 1, 'firstname' =>  $post_data['company'], 'lastname' => $post_data['company'], 'email' => $post_data['email']), $client_id);
            $appointment_data['client'] = $client_id;
            $appointment_manager_status = $this->appointment_manager_model->get_appointment_manager_status_by_name('Waiting For Approval');
            if ($appointment_manager_status && $appointment_manager_status->isdefault == 1) {
                $appointment_data['status'] = $appointment_manager_status->id;
            } else {
                $data['isdefault'] = '1';
                if ($appointment_manager_status) {
                    $this->appointment_manager_model->update_appointment_status($data, $appointment_manager_status->id);
                    $appointment_data['status'] = $appointment_manager_status->id;
                } else {
                    $data['name'] = 'Waiting For Approval';
                    $data['statusorder'] = 1;
                    $data['color'] = '#e2ade5';
                    $appointment_data['status'] = $this->appointment_manager_model->add_appointment_status($data);
                }
            }
            if (isset($post_data['custom_fields'])) {
                $custom_fields = $post_data['custom_fields'];
                $appointment_data['custom_fields'] = $custom_fields;
                unset($post_data['custom_fields']);
            }
            $success = $this->appointment_manager_model->add_appointment($appointment_data, true);
            if ($success) {
                set_alert('success', _l('appmgr_thanks_appointment_created'));
                if ($alertIframe) {
                    redirect(site_url('appointment_manager/appointment_manager_client/public_form?alert=success'));
                } else {
                    redirect(site_url('appointment_manager/appointment_manager_client/public_form'));
                }
            } else {
                set_alert('danger', _l('Failed to create appointment'));
                if ($alertIframe) {
                    redirect(site_url('appointment_manager/appointment_manager_client/public_form?alert=danger'));
                } else {
                    redirect(site_url('appointment_manager/appointment_manager_client/public_form'));
                }
            }
        } else {
            set_alert('danger', _l('Failed to add client'));
            redirect(site_url('appointment_manager/appointment_manager_client/public_form'));
        }
    }
    function get_practitioner_busy_times()
    {
        if ($this->input->is_ajax_request()) {
            echo $this->appointment_manager_model->getPractitionerBusyTimes($this->input->get());
        }
    }

    function ajax_search_practitioner()
    {
        if ($this->input->is_ajax_request()) {
            $this->db->select('CONCAT(' . db_prefix() . 'staff.firstname," ",' . db_prefix() . 'staff.lastname) as name,id');
            $this->db->from(db_prefix() . 'appmgr_appointies');
            $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'appmgr_appointies.staff', 'left');
            if ($this->input->post('location_id')) {
                $this->db->where(db_prefix() . 'appmgr_appointies.location', $this->input->post('location_id'));
            }
            echo json_encode($this->db->get()->result_array());
        }
    }
    public function getrooms($locid = '', $appid = '')
    {
        $html = '';
        $response = array('success' => false);
        $rooms = explode(',', prep_tags_input(get_tags_in($locid, 'appmgr_location')));

        if (isset($rooms) && !empty($rooms) && !empty($rooms[0])) {
            $optRooms = $this->appointment_manager_model->getcOptedRooms($locid, $this->input->get('app_date'), $appid);

            $i = 1;
            foreach ($rooms as $room) {
                if ($optRooms) {
                    $optRoomsArr = explode(",", $optRooms);
                    if (in_array($room, $optRoomsArr)) {
                        $html .= '<div class="form-check ck-wrap"><input class="form-check-input" name="room[]" type="checkbox" value="' . $room . '" id="check' . $i . '" disabled><label class="form-check-label" for="check' . $i . '">' . $room . '</label></div>';
                    } else {
                        $html .= '<div class="form-check ck-wrap"><input class="form-check-input" name="room[]" type="checkbox" value="' . $room . '" id="check' . $i . '"><label class="form-check-label" for="check' . $i . '">' . $room . '</label></div>';
                    }
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
    function practionars_availibility()
    {
        if ($this->input->is_ajax_request()) {
            $post_data = $this->input->post();
            $result = $this->appointment_manager_model->get_availibility_by_appointee_id($post_data['appointee']);
            echo json_encode($result);
        }
    }
    public function google_callback()
    {
        $client = new Google_Client();
        $client->setAuthConfig('modules/appointment_manager/config/client_secret_oauth.json');
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if ($token) {
            update_option('am_g_accesstoken',json_encode($token));
            update_option('am_g_refreshtoken',$token['refresh_token']);
            update_option('am_g_tokenexpirein',$token['expires_in']);
        }
        redirect(admin_url('appointment_manager'));
    }
    function get_service_categories_ajax($service_id){
        if (is_numeric($service_id)) {
            echo json_encode($this->appointment_manager_model->get_service_categories('', ['service_id' => $service_id]));
        }
    }
}
