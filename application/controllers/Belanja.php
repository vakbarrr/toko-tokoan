<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Belanja extends CI_Controller {

	// Load Model
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Modelproduk');
		$this->load->model('Modelkategori');
		$this->load->model('Modelkonfigurasi');
		$this->load->model('Modelpelanggan');
		$this->load->model('Modeldetailtransaksi');
		$this->load->model('Modeltransaksi');
		$this->load->model('Modelrekening');
		// load helper random string
		$this->load->helper('string');
	}

	// Halaman belanja
	public function index()
	{
		$keranjang	= $this->cart->contents();

		$data		= array('title' => 'Keranjang Belanja',
							'keranjang'	=> $keranjang,
							'page'		=> 'user/belanja/list' 
						);
		$this->load->view('user/layout/wrapper', $data, FALSE);
	}

	// Sukses belanja
	public function sukses()
	{
		$konfigurasi = $this->Modelkonfigurasi->listing();

		// Rekening
		$data_rekening_pembayaran = $this->Modelrekening->listing();

		$data		= array('title' => 'Pemesanan Berhasil.',
							'konfigurasi' => $konfigurasi,
							'data_rekening_pembayaran' => $data_rekening_pembayaran,
							'page'	=> 'user/belanja/sukses' 
						);
		$this->load->view('user/layout/wrapper', $data, FALSE);
	}

	public function checkout()
	{
		// Cek pelanggan sudah login apa belum
		// Jika belum maka registrasi
		// sekaligus login. mengecek dengan session email

		// kondisi sudah login
		if ($this->session->userdata('email')) {
			$email 			= $this->session->userdata('email');
			$pelanggan 		= $this->Modelpelanggan->sudah_login($email);
			
			$keranjang 	= $this->cart->contents();

			// Validasi input
			$valid = $this->form_validation;
			
			$valid->set_rules('nama_pelanggan','Nama lengkap','required',
				array(	'required'		=> '%s harus diisi'));

			$valid->set_rules('telepon','Nomor telepon','required',
				array(	'required'		=> '%s harus diisi'));

			$valid->set_rules('alamat','Alamat','required',
				array(	'required'		=> '%s harus diisi'));

			$valid->set_rules('email','Email','required|valid_email',
				array(	'required'		=> '%s harus diisi',
						'valid_email'	=> '%s tidak valid',
						));

			if ($valid->run()===FALSE) {
			// End validasi

			$data		= array('title' 	=> 'Checkout',
								'keranjang'	=> $keranjang,
								'pelanggan'	=> $pelanggan,
								'page'		=> 'user/belanja/checkout' 
							);
			$this->load->view('user/layout/wrapper', $data, FALSE);
			// Masuk database
			}else{
				$i = $this->input;
				$ongkir = $this->input->post('ongkir');
				if ($ongkir = '1') {
					$totalongkir = 6000;
				}else{
					$totalongkir = 4000;
				}
				$data = array(	'id_pelanggan'		=> $pelanggan->id_pelanggan,
								'nama_pelanggan'	=> $i->post('nama_pelanggan'),
								'email'				=> $i->post('email'),
								'telepon'			=> $i->post('telepon'),
								'alamat'			=> $i->post('alamat'),
								'kode_transaksi'	=> $i->post('kode_transaksi'),
								'tanggal_transaksi'	=> $i->post('tanggal_transaksi'),
								'jumlah_transaksi'	=> $i->post('jumlah_transaksi'),
								'status_bayar'		=> 'Belum',
								'tanggal_post'		=> date('Y-m-d H:i:s'),
								'ongkir' => $totalongkir
							);
				// proses masuk ke header transaksi
				$this->Modeldetailtransaksi->tambah($data);

				// proses masuk ke tabel transaksi
				foreach ($keranjang as $keranjang) {
					$sub_total =  $keranjang['price'] * $keranjang['qty'] ;

					$data = array(	'id_pelanggan' 		=> $pelanggan->id_pelanggan,
					 				'kode_transaksi'	=> $i->post('kode_transaksi'),
					 				'id_produk'			=> $keranjang['id'],
					 				'harga'				=> $keranjang['price'],
					 				'jumlah'			=> $keranjang['qty'],
					 				'total_harga'		=> $sub_total,
					 				'tanggal_transaksi'	=> $i->post('tanggal_transaksi'),
									 
					 			);
					$this->Modeltransaksi->tambah($data);
				}
				// End proses masuk ke tabel transaksi

				// Setelah masuk ke tabel transaksi, maka keranjang dikosongkan lagi
				$this->cart->destroy();
				// End pengosongan keranjang

				$this->session->set_flashdata('sukses','Checkout berhasil');
				redirect(base_url('belanja/sukses'),'refresh');
			}
			// End masuk database

		}else {
			// kalau belum maka harus registrasi
			$this->session->set_flashdata('sukses', 'Silahkan login atau registrasi terlebih dahulu');
			redirect(base_url('registrasi'),'refresh');
		}
	}

	// Tambahkan ke keranjang
	public function add()
	{
		// Ambil data dari form
		$id 			= $this->input->post('id');
		$qty 			= $this->input->post('qty');
		$price 			= $this->input->post('price');
		$name 			= $this->input->post('name');
		$redirect_page 	= $this->input->post('redirect_page');

		// Memasukkan ke keranjang belanja
		$data = array(	'id'      => $id,
        				'qty'     => $qty,
        				'price'   => $price,
        				'name'    => $name
    	);
    	$this->cart->insert($data);
    	redirect($redirect_page,'refresh');
	}
	

	// Update cart
	public function update_cart($rowid)
	{
		// Jika ada rowid
		if ($rowid) {
			$data = array(	'rowid' => $rowid,
			 				'qty' 	=> $this->input->post('qty')
			 			);
			$this->cart->update($data);
			$this->session->set_flashdata('sukses', 'Data Keranjang Telah Diupdate');
			redirect(base_url('belanja'),'refresh');
		}else {
			// Jika tidak ada rowid
			redirect(base_url('belanja'),'refresh');
		}
	}

	public function hapus($rowid)
	{
		if ($rowid) {
			// Hapus per item keranjang
			$this->cart->remove($rowid);
			$this->session->set_flashdata('sukses', 'Data telah dihapus');
			redirect(base_url('belanja'),'refresh');
		}
	}
	

}

/* End of file Belanja.php */
/* Location: ./application/controllers/Belanja.php */