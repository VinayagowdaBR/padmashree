<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Download extends App_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('download');
    }
    public function credential($path='')
    {
        if(!$path){
            $path = module_dir_path('appointment_manager','config/client_secret_oauth.json');
        }
        force_download($path, null);
    }
}