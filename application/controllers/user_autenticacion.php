<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//session_start(); //we need to start session in order to access it through CI

class User_autenticacion extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
		$this->load->helper('form'); // Load form helper library
		$this->load->helper('html');
		$this->load->helper('url');
		$this->load->library('form_validation'); // Load form validation library
		$this->load->library('session'); // Load session library
		$this->load->model('login_model'); // Load database
	}


	public function index() // Show login page
	{
		$this->load->view('login_form');
	}

	public function user_registration_show() // Show registration page
	{
		$this->load->view('registration_form');
	}

	public function new_user_registration() // Validate and store registration data in database
	{
		// Check validation for user input in SignUp form
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
		$this->form_validation->set_rules('email_value', 'Email', 'trim|required|xss_clean');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
		if ($this->form_validation->run() == FALSE) {
			$this->load->view('registration_form');
		} else {
			$data = array(
				'user_name' => $this->input->post('username'),
				'user_email' => $this->input->post('email_value'),
				'user_password' => $this->input->post('password')

			);
			$result = $this->login_model->registration_insert($data);
			if ($result == TRUE) {
				$data['message_display'] = 'Registration Successfully !';
				$this->load->view('login_form',$data);
			} else {
				$data['message_display'] = 'Username already exist!';
				$this->load->view('login_form',$data);
			}
		}
	}

	// Check for user login process
	public function user_login_process()
	{
		//decirle que los campos son requeridos
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');

		if ($this->form_validation->run() == FALSE) {
			if (isset($this->session->userdata['logged_in'])) {
				$this->load->view('admin_page');
			} else {
				$this->load->view('login_form');
			}
		} else {
			$data = array(
				'username' => $this->input->post('username'),
				'password' => $this->input->post('password')
			);
			$result	= $this->login_model->login($data); //se le envia los datos ingresados por el usuario para poder logearse
			if ($result == TRUE) {
				$username = $this->input->post('username');
				$result = $this->login_model->read_user_information($username);
				if ($result != FALSE ) { //Estp se hace para buscar la informacion del usuario
					$session_data = array(
						'username' => $result[0]->user_name,
						'email' => $result[0]->user_email,
					);

				// Add user data in session
				$this->session->set_userdata('logged_in', $session_data);
				$this->load->view('admin_page');
				}
			} else {
				$data = array(
					'error message' => 'Invalid Username or Passowrd'
				);
				$this->load->view('login_form',$data);
			}
		}
	}

	// Logout from admin page
	public function logout()
	{
		// Removing session data
		$sess_array = array(
			'username' => ''
		);
		$this->session->unset_uderdata('logged_in', $sess_array);
		$data['message_display'] = 'Successfully Logout';
		$this->load->view('login_form',$data);
	}

}

?>
