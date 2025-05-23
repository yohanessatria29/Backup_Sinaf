<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{

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
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	function __construct()
	{
		parent::__construct();
		$this->load->model('Mlogin', 'Mlogin');
		// Load the database
		$this->load->database();
	}

	function index()
	{
		if ($this->session->userdata('login') != TRUE) {
			$this->load->view('auth-login');
		} else {
			$url = base_url('/pengajuan');
			redirect($url);
		};
	}

	function autentikasi()
	{
		$email = str_replace("'", "", htmlspecialchars($this->input->post('email', TRUE), ENT_QUOTES));
		$password = str_replace("'", "", htmlspecialchars($this->input->post('password', TRUE), ENT_QUOTES));
		$password1 = str_replace("'", "", htmlspecialchars($this->input->post('password', TRUE), ENT_QUOTES));

		$salt      = '1m_@_SaLT_f0R_4kreD!t4$i';
		$hashed    = hash('sha256', $password1 . $salt);

		$where = array(
			'email' => $email,
			'password_enkripsi' => $hashed,
		);

		$cek = $this->Mlogin->cek_login("users", $where)->num_rows();
		$show_user = $this->Mlogin->cek_login("users", $where)->result_array();


		$where2 = array(
			'email' => $email
		);
		$show_user2 = $this->Mlogin->cek_login("users", $where2)->result_array();

		if ($cek > 0) {
			$this->db->where('email', $email);

			if ($show_user[0]['validate'] == 2) {
				$data_session = array(
					'email' => $email,
					'status' => "login",
					'user_id' => $show_user[0]['id'],
					'nama_lengkap' => $show_user[0]['nama'],
					'kriteria_id' => $show_user[0]['kriteria_id']
				);

				$this->session->set_userdata($data_session);
			}
		}

		$validasi_email = $this->Mlogin->query_validasi_email($email);
		if ($validasi_email->num_rows() > 0) {
			$validate_ps = $this->Mlogin->query_validasi_password($email, $hashed);
			if ($validate_ps->num_rows() > 0) {
				$x = $validate_ps->row_array();
				if ($x['user_status'] == '1') {
					$log_id = $this->Mlogin->insert_log($x['id'], 'SUCCESS');

					if (!$log_id) {
						log_message('error', 'Gagal mencatat log login untuk user ID: ' . $x['id']);
					}

					$this->session->set_userdata('login_log_id', $log_id);
					$this->session->set_userdata('logged', TRUE);
					$this->session->set_userdata('user', $email);
					$this->session->set_userdata('kriteria_id', $x['kriteria_id']);
					$this->session->set_userdata('id', $x['id']);
					$this->session->set_userdata('name', $x['nama']);
					$this->session->set_userdata('lpa_id', $x['lpa_id']);

					if ($x['kriteria_id'] == '1') { //Admin Lembaga
						$this->session->set_userdata('access', 'Admin Lembaga');
						$this->session->set_userdata('kriteria', 'Admin Lembaga');
						$this->session->set_userdata('lpa_id', $x['lpa_id']);
						$this->session->set_userdata('kriteria_id', $x['kriteria_id']);

						if ($x['lpa_id'] == '15' || $x['lpa_id'] == '16' || $x['lpa_id'] == '17' || $x['lpa_id'] == '18' || $x['lpa_id'] == '19' || $x['lpa_id'] == '20') {
							redirect('pengajuan/surveior');
						} else {
							redirect('pengajuan');
						}
					} else if ($x['kriteria_id'] == '2') { //Admin Kemenkes
						$this->session->set_userdata('access', 'Kemenkes');
						$this->session->set_userdata('kriteria', 'Admin Kemenkes');
						redirect('kemenkes');
					} else if ($x['kriteria_id'] == '3') { //Surveior
						$this->session->set_userdata('access', 'Surveior');
						$this->session->set_userdata('kriteria', 'Surveior');
						redirect('surveior');
					} else if ($x['kriteria_id'] == '4') { //Verifikator
						$this->session->set_userdata('access', 'Verifikator');
						$this->session->set_userdata('kriteria', 'Verifikator');
						redirect('verifikator');
					} else if ($x['kriteria_id'] == '5') { // Ketua LPA
						$this->session->set_userdata('access', 'Ketua LPA');
						$this->session->set_userdata('kriteria', 'Ketua Lembaga');
						redirect('admin');
					} else if ($x['kriteria_id'] == '6') { // Dinkes Provinsi
						$this->session->set_userdata('access', 'Dinkes Provinsi');
						$this->session->set_userdata('kriteria', 'Dinkes Provinsi');
						redirect('admin');
					} else if ($x['kriteria_id'] == '7') { // Dinkes Kab Kota
						$this->session->set_userdata('access', 'Dinkes Kab Kota');
						$this->session->set_userdata('kriteria', 'Dinkes Kab Kota');
						redirect('admin');
					} else if ($x['kriteria_id'] == '8') { // Dinkes Kab Kota
						$this->session->set_userdata('access', 'Ketua Tim');
						$this->session->set_userdata('kriteria', 'Ketua Tim');
						redirect('ketua');
						// Nasrul
					} else if ($x['kriteria_id'] == '9') { // Direktur
						$this->session->set_userdata('access', 'Direktur');
						$this->session->set_userdata('kriteria', 'Direktur');
						redirect('direktur');
					} else if ($x['kriteria_id'] == '10') { // Binwas
						$this->session->set_userdata('access', 'Binwas');
						$this->session->set_userdata('kriteria', 'Binwas');
						redirect('binwas');
					} else if ($x['kriteria_id'] == '11') { // Kesmas
						$this->session->set_userdata('access', 'Kesmas');
						$this->session->set_userdata('kriteria', 'Kesmas');
						redirect('kesmas');
					} else if ($x['kriteria_id'] == '13') { // Primer
						$this->session->set_userdata('access', 'Mutu Primer');
						$this->session->set_userdata('kriteria', 'Mutu Primer');
						redirect('primer/proses');
					} else if ($x['kriteria_id'] == '14') { // Primer
						$this->session->set_userdata('access', 'Mutu Primer');
						$this->session->set_userdata('kriteria', 'Mutu Primer');
						redirect('primer/proses');
					} else if ($x['kriteria_id'] == '15') { // Primer
						$this->session->set_userdata('access', 'Mutu Primer');
						$this->session->set_userdata('kriteria', 'Mutu Primer');
						redirect('primer/proses');
					}
				} else {
					$log_id = $this->Mlogin->insert_log($x['id'], 'FAILURE_INACTIVE');

					if (!$log_id) {
						// Misalnya, tampilkan flashdata atau kirim notifikasi
						log_message('error', 'Gagal mencatat log login untuk user ID: ' . $x['id']);
					}
					$url = base_url('login');
					$message = "Akun anda Belum. Silahkan hubungi Admin Kemenenterian Kesehatan";
					echo "<script type='text/javascript'>alert('$message');</script>";
					die(redirect($url, 'refresh'));
				}
			} else {
				$data = $validasi_email->result_array();
				$user_id = $data[0]['id'];
				$log_id = $this->Mlogin->insert_log($user_id, 'FAILURE_PASSWORD');

				if (!$log_id) {
					// Misalnya, tampilkan flashdata atau kirim notifikasi
					log_message('error', 'Gagal mencatat log login untuk user ID: ' . $user_id);
				}
				$url = base_url('login');
				$message = "Password Salah";
				echo "<script type='text/javascript'>alert('$message');</script>";
				die(redirect($url, 'refresh'));
			}
		} else {
			$url = base_url('login');
			$message = "Email yg anda masukan salah";
			echo "<script type='text/javascript'>alert('$message');</script>";
			die(redirect($url, 'refresh'));
		}
	}


	public function get_location_by_ip($ip)
	{
		$url = "http://ip-api.com/json/{$ip}?fields=country,regionName,city,status";

		$response = @file_get_contents($url);
		if ($response !== FALSE) {
			$data = json_decode($response, true);
			if (isset($data['status']) && $data['status'] === 'success') {
				return $data['city'] . ', ' . $data['regionName'] . ', ' . $data['country'];
			}
		}

		return null; // fallback jika gagal
	}


	function logout()
	{
		$log_id = $this->session->userdata('login_log_id');

		if ($log_id) {
			$this->Mlogin->update_logout_time($log_id);
			$this->session->unset_userdata('login_log_id');
		}
		$this->session->sess_destroy();
		$url = base_url('login');
		redirect($url);
	}
}
