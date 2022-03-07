<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Video extends CI_Controller {

	public function index() {
        $data['test'] = "test";
        //echo "<pre>"; print_r($data); echo "</pre>"; die;
        $this->load->view('video', $data);
	}
}
?>