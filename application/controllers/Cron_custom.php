<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Cron_custom extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Clients_model');
    }

    public function reset_daily_option()
    {
        // Update the option with ID 36 to value '1'
        $updated = $this->Clients_model->update_option_value(36, '1');

        if ($updated) {
            log_message('error', 'Cron Custom: Daily option reset successfully (id=36).');
            echo "Success: Option 36 reset to 1.";
        } else {
            log_message('error', 'Cron Custom: Failed to reset daily option (id=36).');
            echo "Error: Failed to reset option.";
        }
    }
}
