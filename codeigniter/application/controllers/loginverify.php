<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LoginVerify extends CI_Controller {
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	function __construct() {
		parent::__construct();
	}

	function index() {
		// this method will have the credentials validation
		$this->load->library('form_validation');

		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|callback_check_login');

		if($this->form_validation->run() == false) {
			$this->load->view('login'); // user redirected to login page
		} else {
			redirect('admin', 'refresh'); // go to private area
		}
	}
	
	function check_login($password) {
		$username = $this->input->post('username');

		$result = false;
		$this->config->load('easyfile');
		if($username == $this->config->item('easyfile_username') && $password == $this->config->item('easyfile_password')) {
			$result = true;
		}

		if($result) {
			$session_array = array(
				'username' => $username
			);
			$this->session->set_userdata('logged_in', $session_array);
			
			return true;
		} else {
			$this->form_validation->set_message('check_login', 'Invalid username or password');
			return false;
		}
	}
}
?>