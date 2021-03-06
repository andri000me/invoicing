<?php 
        
defined('BASEPATH') OR exit('No direct script access allowed');
        
class Produk extends CI_Controller {
  public function __construct()
	{

		parent::__construct();

		$this->load->model('ProdukModel');
		$this->load->model('AdminModel');
		$this->load->model('HistoriModel');
	}
	public function index()
	{
		$this->cekLogin();
		$id_admin = $this->session->userdata('id_admin');
		// var_dump($id_admin);die;
		$r = $this->AdminModel->getRole($id_admin, 'produk_r')->r;
		$c = $this->AdminModel->getRole($id_admin, 'produk_c')->r;
		$u = $this->AdminModel->getRole($id_admin, 'produk_u')->r;
		$d = $this->AdminModel->getRole($id_admin, 'produk_d')->r;


		if ($r == '1' || $c == '1' || $u == '1' || $d == '1') {
			$data['content'] = 'master_produk';

			$this->load->view('templates/index', $data);
		} else {
			redirect('Landing');
			// die;
		}

		// $this->load->view('templates/header');     
	}
	public function getAllProduk()
	{
		$id_admin = $this->session->userdata('id_admin');

		$pr = $this->AdminModel->getRole($id_admin, 'produk_r')->r;
		$pc = $this->AdminModel->getRole($id_admin, 'produk_c')->r;
		$pu = $this->AdminModel->getRole($id_admin, 'produk_u')->r;
		$pd = $this->AdminModel->getRole($id_admin, 'produk_d')->r;


		$dt = $this->ProdukModel->data_AllProduk($_POST);
		$bu = base_url();
		$datatable['draw']      = isset($_POST['draw']) ? $_POST['draw'] : 1;
		$datatable['recordsTotal']    = $dt['totalData'];
		$datatable['recordsFiltered'] = $dt['totalData'];
		$datatable['data']            = array();
		$start  = isset($_POST['start']) ? $_POST['start'] : 0;
		// var_dump($dt['data']->result());die();
		$no = $start + 1;
		$status = "";
		foreach ($dt['data']->result() as $row) {
			if ($row->status == 1) {
				$status = "<span class='btn btn-rounded btn-outline-success px-3 btn-sm'>Aktif</span>";
			} else {
				$status = "<span class='btn btn-rounded btn-outline-warning px-3 btn-sm'>Non Aktiv</span>";
			}


			$fields = array($no++);
			$fields[] = $row->nama_produk . '<br>';
			$fields[] = $status . '<br>';
			if ($pu == 1 and $pd == 1){

				$fields[] = '
				<button class="btn btn-round btn-info btn_edit"  data-toggle="modal" data-target="#modal" 
				data-id_produk="' . $row->id_produk . '" 
				data-nama_produk="' . $row->nama_produk . '" 
				data-status="' . $row->status . '"  		
				></i> Ubah</button>
	
				<button class="btn btn-round btn-danger hapus" data-id_produk="' . $row->id_produk . '" data-nama_produk="' . $row->nama_produk . '" >Hapus</button>    ';
			} else if ($pu == 0 and $pd == 1) {
				$fields[] = '	
				<button class="btn btn-round btn-danger hapus" data-id_produk="' . $row->id_produk . '" data-nama_produk="' . $row->nama_produk . '" >Hapus</button>    ';
			} else if ($pu == 1 and $pd == 0) {
				$fields[] = '	
				<button class="btn btn-round btn-info btn_edit"  data-toggle="modal" data-target="#modal" 
				data-id_produk="' . $row->id_produk . '" 
				data-nama_produk="' . $row->nama_produk . '" 
				data-status="' . $row->status . '"  		
				></i> Ubah</button>';
			} else {
				$fields[] = '
				<button class="btn btn-round btn-danger" 
				>Tidak Punya Akses!</button>              

				';
			}
		

			$datatable['data'][] = $fields;
		}



		echo json_encode($datatable);

		exit();
	}
	public function tambah()
	{
		$username = $this->input->post('nama', TRUE);
		$stat = $this->input->post('status', TRUE);

		$message = 'Gagal menambahkan Produk Baru!<br>Silahkan lengkapi data yang diperlukan.';
		$errorInputs = array();
		$status = true;


		$in = array(
			'nama_produk' => $username,
			'status' => $stat,
		);

		if (empty($username)) {
			$status = false;
			$errorInputs[] = array('#username', 'Silahkan pilih username');
		}
		if ($status) {
			$this->ProdukModel->tambah_admin($in);
			$status = true;
			$message = 'Berhasil Menambahkan Produk.';

			$id_userReal = $_SESSION['id_admin'];
			$created = date('Y-m-d H:i:s');
			$desk = 'Tambah  Produk  : ' . $username;
			$namaLog = 'Tambah  Produk';
			$this->HistoriModel->log($id_userReal, $namaLog, $desk, $created);
		} else {
			$message = 'Gagal menambahkan Produk Baru!<br>Silahkan lengkapi data yang diperlukan.';

			$message = 'Gagal ';
			$status = false;
		}



		echo json_encode(array(
			'status' => $status,
			'message' => $message,
			'errorInputs' => $errorInputs
		));
	}
	public function edit()
	{
		// var_dump($_POST);die;
		$id_admin = $this->input->post('id_admin', TRUE);
		$nama = $this->input->post('nama', TRUE);
		$stat = $this->input->post('status', TRUE);

		$message = 'Gagal Update!<br>Silahkan lengkapi data yang diperlukan.';
		$errorInputs = array();
		$status = true;
		// var_dump($transaksi_ldu_lihat);die();
		$in = array(
			'nama_produk' => $nama,
			'status' => $stat,
		);

		if (empty($nama)) {
			$status = false;
			$errorInputs[] = array('#username', 'Silahkan pilih username');
		}
		// var_dump($in);die();

		if ($status) {
			$this->ProdukModel->editDariTable('produk', $in, $id_admin,'id_produk');
			$message = 'Berhasil Update Produk ';

			$id_userReal = $_SESSION['id_admin'];
			$created = date('Y-m-d H:i:s');
			$desk = 'Edit  Produk  : ' . $nama;
			$namaLog = 'Edit  Produk';
			$this->HistoriModel->log($id_userReal, $namaLog, $desk, $created);

		} else {
			$message = 'Gagal Meng-Update Produk! ';
		}
		echo json_encode(array(
			'status' => $status,
			'message' => $message,
			'errorInputs' => $errorInputs
		));
	}
	public function hapus()
	{

		$id_user = $this->input->post('id_user', true);
		$data = $this->ProdukModel->getById($id_user);
		// var_dump($data);die();
		$status = false;
		$message = 'Gagal menghapus Produk!';
		if (count($data) == 0) {
			$message .= '<br>Tidak terdapat Produk yang dimaksud.';
		} else {
			$this->ProdukModel->hapusDariTable('produk', $id_user,'id_produk');
			$status = true;
			$message = 'Berhasil menghapus Produk: <b>' . $data[0]->nama_produk . '</b>';


			$id_userReal = $_SESSION['id_admin'];
			$created = date('Y-m-d H:i:s');
			$desk = 'Hapus  Produk  : ' . $data[0]->nama_produk;
			$namaLog = 'Hapus  Produk';
			$this->HistoriModel->log($id_userReal, $namaLog, $desk, $created);

		}

		echo json_encode(array(
			'status' => $status,
			'message' => $message,
		));
	}
	function cekLogin()
	{
		if (!$this->isLoggedInAdmin()) {
			$this->session->set_flashdata(
				'notifikasi',
				array(
					'alert' => 'alert-danger',
					'message' => 'Silahkan Login terlebih dahulu.',

				)
			);
			redirect('login');
		}
	}
	public function isLoggedInAdmin()
	{
		// Cek apakah terdapat session "admin_session"

		if ($this->session->userdata('admin_session'))
		return true; // sudah login
		else
			return false; // belum login
	}

        
}
        
    /* End of file  produk.php */
        
                            