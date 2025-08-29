<?php defined('BASEPATH') or exit('No direct script access allowed');
function get_appointment_status_by_id($id)
{
    $CI = &get_instance();
    if (!class_exists('appointment_manager_model')) {
        $CI->load->model('appointment_manager_model');
    }

    $statuses = $CI->appointment_manager_model->get_appointment_statuses();

    $status = [
        'id'    => 0,
        'color' => '#333',
        'name'  => '[Status Not Found]',
        'order' => 1,
    ];

    foreach ($statuses as $s) {
        if ($s['id'] == $id) {
            $status = $s;

            break;
        }
    }

    return $status;
}
function core_calandar_handle($data, $d_arr)
{
    $CI = &get_instance();
    if (!class_exists('appointment_manager_model')) {
        $CI->load->model('appointment_manager/appointment_manager_model');
    }
    $appointment_data = $CI->appointment_manager_model->get_other_calendar_appointments($data, $d_arr);
    return $appointment_data;
}
function send_appmgr_appointments_reminder()
{
    $CI = &get_instance();
    $CI->db->where('isstartnotified', 0);
    $events = $CI->db->get(db_prefix() . 'appmgr_appointments')->result_array();
    $notified_users            = [];
    $notificationNotifiedUsers = [];
    $all_notified_events       = [];
    foreach ($events as $event) {
        $date_compare = date('Y-m-d H:i:s', strtotime('+' . $event['reminder_before'] . ' ' . strtoupper($event['reminder_before_type'])));

        if ($event['appointment_date'] <= $date_compare) {
            array_push($all_notified_events, $event['id']);
            array_push($notified_users, $event['appointee']);

            $eventNotifications = hooks()->apply_filters('appmgr_event_notifications', true);

            if ($eventNotifications) {
                $notified = add_notification([
                    'description'     => 'not_event',
                    'touserid'        => $event['appointee'],
                    'fromcompany'     => true,
                    'link'            => 'appointment_manager',
                    'additional_data' => serialize([
                        $event['client'],
                    ]),
                ]);

                // $staff = $CI->staff_model->get($event['appointee'])->row();
                $client_p_contact = $CI->db->get_where(db_prefix() . 'contacts', ['is_primary ' => 1, 'userid' => $event['client']])->row();
                if (isset($client_p_contact->email) && !empty($client_p_contact->email)) {
                    send_mail_template('appointment_manager_reminder_email_send_to_client','appointment_manager', array_to_object($event), $client_p_contact->email);
                }
                array_push($notificationNotifiedUsers, $event['appointee']);
            }
            $CI->db->where('id', $event['id']);
            $events = $CI->db->update(db_prefix() . 'appmgr_appointments', ['isstartnotified' => 1]);
        }
    }

    pusher_trigger_notification($notificationNotifiedUsers);
}
function render_appointment_manager_templates()
{
    $CI = &get_instance();
    if (!staff_can('email_templates', '', 'view')) {
        access_denied('email_templates');
    }
    $langCheckings = get_option('email_templates_language_checks');
    if ($langCheckings == '') {
        $langCheckings = [];
    } else {
        $langCheckings = unserialize($langCheckings);
    }
    update_option('email_templates_language_checks', serialize($langCheckings));
    $data['templates'] = $CI->emails_model->get("`language`='english' AND `type` = 'appointment_manager'");
    $data['title'] = _l('email_templates');
    $data['hasPermissionEdit'] = staff_can('email_templates', '', 'edit');
    echo $CI->load->view('appointment_manager/email_templates', $data);
}
function appointment_manager_register_other_merge_fields($for)
{
    $for[] = 'appointment_manager';

    return $for;
}
function appointment_manager_allow_staff_merge_fields_for_appointment_manager_reminder_templates($fields)
{

    $tacalendarStaffFields = ['{staff_firstname}', '{staff_lastname}'];

    foreach ($fields as $index => $group) {
        foreach ($group as $key => $groupFields) {
            if ($key == 'staff') {
                foreach ($groupFields as $groupIndex => $groupField) {
                    if (in_array($groupField['key'], $tacalendarStaffFields)) {
                        $fields[$index][$key][$groupIndex]['available'] = array_merge($fields[$index][$key][$groupIndex]['available'], ['appointment_manager']);
                    }
                }
                break;
            }
        }
    }

    return $fields;
}

function appointment_manager_allow_client_merge_fields_for_appointment_manager_reminder_templates($fields)
{
    $tacalendarStaffFields = ['{client_company}', '{client_phonenumber}', '{client_email}'];
    foreach ($fields as $index => $group) {
        foreach ($group as $key => $groupFields) {
            if ($key == 'client') {
                foreach ($groupFields as $groupIndex => $groupField) {
                    if (in_array($groupField['key'], $tacalendarStaffFields)) {
                        $fields[$index][$key][$groupIndex]['available'] = array_merge($fields[$index][$key][$groupIndex]['available'], ['appointment_manager']);
                    }
                }
                break;
            }
        }
    }

    return $fields;
}

function render_appointment_manager_dashboard_widget($widgets)
{
    $widgets[] = [
        'container' => 'left-8',
        'path'      => 'appointment_manager/widget_dashboard_core',
    ];
    return $widgets;
}

function timeDurationDiff($time1, $time2)
{
    if ($time1 && $time2) {
        $difference = round(abs(strtotime($time2) - strtotime($time1)) / 3600, 2);
        return $difference;
    }
    return 0;
}
function get_appmgr_email_template_by_status($status)
{
    $CI = &get_instance();
    if (!class_exists('appointment_manager_model')) {
        $CI->load->model('appointment_manager_model');
    }
    $status = $CI->appointment_manager_model->get_appointment_statuses($status);
    return 'appointment_manager_' . appmgr_slugify($status->name) . '_email_send_to_client';
}
function render_appointments_status_select($statuses, $selected = '', $lang_key = '', $name = 'status', $select_attrs = [], $exclude_default = false)
{
    foreach ($statuses as $key => $status) {
        if ($status['isdefault'] == 1) {
            if ($exclude_default == false) {
                $statuses[$key]['option_attributes'] = ['data-subtext' => _l('appointments_converted_to_client')];
            } else {
                unset($statuses[$key]);
            }

            break;
        }
    }
    return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
}

function render_practitoner_select($staffs, $selected = '', $lang_key = '', $name = 'staff', $select_attrs = [], $exclude_default = false)
{
    foreach ($staffs as $key => $staff) {
        if ($staff['active'] == 1) {
            if ($exclude_default == false) {
                $staffs[$key]['option_attributes'] = ['data-subtext' => _l('appointments_converted_to_client')];
            } else {
                unset($staffs[$key]);
            }

            break;
        }
    }
    return render_select($name, $staffs, ['id', 'firstname'], $lang_key, $selected, $select_attrs);
}


function get_location_id($location = '')
{
    $CI = &get_instance();
    if (!class_exists('appointment_manager_model')) {
        $CI->load->model('appointment_manager_model');
    }
    $location_id = $CI->appointment_manager_model->get_locations_id($location);
    return $location_id;
}
function get_treatment_id($name = '')
{
    $CI = &get_instance();
    if (!class_exists('appointment_manager_model')) {
        $CI->load->model('appointment_manager_model');
    }
    $treatment_id = $CI->appointment_manager_model->get_treatment_id($name);
    return $treatment_id;
}
function isRoomDisable($apt_id, $room)
{
    if (is_numeric($apt_id)) {
        $CI = &get_instance();
        $total = 0;
        if (!class_exists('appointment_manager_model')) {
            $CI->load->model('appointment_manager_model');
        }
        $appointment = $CI->appointment_manager_model->get_appointment($apt_id);
        if (isset($appointment) && !empty($appointment)) {
            $aptDate = $appointment->appointment_date;
            $result = $CI->db->query("SELECT count(*) as total FROM `" . db_prefix() . "appmgr_appointments` WHERE appointment_date = '" . to_sql_date($aptDate) . "' AND FIND_IN_SET('" . $room . "', opted_rooms) AND id != " . $apt_id . ";")->row();
            if (isset($result)) {
                return $result->total;
            }
            return $total;
        }
    }
}

function update_dates_for_new_month()
{
    $CI = &get_instance();
    if (date('j') == 1) {
        $availibilities = $CI->db->get(db_prefix() . 'appmgr_appointee_availibility')->result_array();
        if (isset($availibilities) && !empty($availibilities)) {
            foreach ($availibilities as $availibility) {
                $available_date_from = new DateTime($availibility['available_date_from']);
                $available_date_from = $available_date_from->modify('+1 month');
                $available_date_from = $available_date_from->format('Y-m-d');
                $available_date_to = new DateTime($availibility['available_date_to']);
                $available_date_to = $available_date_to->modify('+1 month');
                $available_date_to = $available_date_to->format('Y-m-d');
                $CI->db->where('id', $availibility['id']);
                $CI->db->update(db_prefix() . 'appmgr_appointee_availibility', ['available_date_from' => $available_date_from, 'available_date_to' => $available_date_to]);
            }
        }
    }
}
function validateDate($date, $format = 'Y-m-d')
{
    $d = DateTime::createFromFormat($format, $date);
    return $d && strtolower($d->format($format)) === strtolower($date);
}
function appmgr_slugify($string)
{
    // Convert to lowercase
    $slug = strtolower($string);

    // Replace spaces and non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9]+/', '_', $slug);

    // Trim any leading or trailing hyphens
    $slug = trim($slug, '_');

    return $slug;
}
function am_custom_fileds_select($fields)
{
    $selected = '';
    if (isset($fields) && $fields->fieldto == "appmgr") {
        $selected = 'selected';
    }
    echo '<option value="appmgr" ' . $selected . '>' . _l('appointment_manager') . '</option>';
}
function handle_post_settings_appmgr($data)
{
    if (isset($_FILES['am_g_auth_json'])) {
        handle_gauth_json_upload();
    }
}
function handle_gauth_json_upload()
{
    $success   = false;
    $index = 'am_g_auth_json';
    if (isset($_FILES[$index]) && !empty($_FILES[$index]['name']) && _perfex_upload_error($_FILES[$index]['error'])) {
        set_alert('warning', _perfex_upload_error($_FILES[$index]['error']));
        return false;
    }
    if (isset($_FILES[$index]['name']) && $_FILES[$index]['name'] != '') {
        $path = module_dir_path(APPOINTMENT_MANAGER_MODULE_NAME, 'config/');
        $tmpFilePath = $_FILES[$index]['tmp_name'];
        if (!empty($tmpFilePath) && $tmpFilePath != '') {
            $extension          = strtolower(pathinfo($_FILES[$index]['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['json'];
            if (!in_array($extension, $allowed_extensions)) {
                set_alert('warning', 'File extension not allowed.');
                return;
            }
            // Setup our new file path
            $filename    = 'client_secret_oauth.' . $extension;
            $newFilePath = $path . $filename;
            _maybe_create_upload_path($path);
            if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                update_option($index, 1);
                $success = true;
            }
        }
    } else {
        $success = false;
    }
    return $success;
}
function convertTo24Hour($time)
{
    // Check if the time contains AM/PM (12-hour format)
    if (preg_match('/^(\d{1,2}):(\d{2})\s?(AM|PM)$/i', $time, $matches)) {
        $hour = (int)$matches[1];
        $minute = $matches[2];
        $period = strtoupper($matches[3]); // AM or PM

        // Convert to 24-hour format
        if ($period === 'AM' && $hour == 12) {
            $hour = 0; // Midnight case (12:00 AM should be 00:00)
        } elseif ($period === 'PM' && $hour != 12) {
            $hour += 12; // Convert PM hours (e.g., 2 PM to 14)
        }

        // Return in 24-hour format, without AM/PM
        return sprintf('%02d:%02d', $hour, $minute);
    }

    // If it's already in 24-hour format (e.g., 14:30), just return it as is
    return $time;
}
