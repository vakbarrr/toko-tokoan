<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Produk extends CI_Controller {

	// Load model
	public function __construct()
	{
		parent::__construct();
		$this->load->model('Modelproduk');
		$this->load->model('Modelkategori');
		$this->load->model('Modelkonfigurasi');

	}

	// Listing data produk
	public function index()
	{
		$site 				= $this->Modelkonfigurasi->listing();
		$listing_kategori 	= $this->Modelproduk->listing_kategori();
		// Ambil data total
		$total 				= $this->Modelproduk->total_produk();
		// Pagination Start
		$this->load->library('pagination');
		
		$config['base_url'] 		= base_url().'produk/index/';
		$config['total_rows'] 		= $total->total;
		$config['use_page_numbers']	= TRUE;
		$config['per_page'] 		= 4;
		$config['uri_segment'] 		= 3;
		$config['num_links'] 		= 3;
		$config['full_tag_open'] 	= '<ul class="pagination">';
		$config['full_tag_close'] 	= '</ul>';
		$config['first_link'] 		= 'First';
		$config['first_tag_open'] 	= '<li class="disabled"><li class="active"><a href="#">';
		$config['first_tag_close'] 	= '<span class="sr-only"></a></li></li>';
		$config['last_link'] 		= 'Last';
		$config['last_tag_open'] 	= '<div>';
		$config['last_tag_close'] 	= '</div>';
		$config['next_link'] 		= '&gt;';
		$config['next_tag_open'] 	= '<div>';
		$config['next_tag_close'] 	= '</div>';
		$config['prev_link'] 		= '&lt;';
		$config['prev_tag_open'] 	= '<div>';
		$config['prev_tag_close'] 	= '</div>';
		$config['cur_tag_open'] 	= '<b>';
		$config['cur_tag_close'] 	= '</b>';
		$config['first_url']		= base_url().'/produk/';		
		$this->pagination->initialize($config);
		// Ambil data produk
		$page 	= ($this->uri->segment(3)) ? ($this->uri->segment(3)-1) * $config['per_page']:0;
		$produk = $this->Modelproduk->produk($config['per_page'],$page);
		// Paginasi end

		$data = array(	'title' 			=> 'Produk '.$site->namaweb,
						'site'				=> $site,
						'listing_kategori'	=> $listing_kategori,
						'produk'			=> $produk,
						'pagin'				=> $this->pagination->create_links(),
		 				'page'				=> 'user/produk/list',
		 			);
		$this->load->view('user/layout/wrapper', $data, FALSE);
	}

	// Listing data kategori produk
	public function kategori($slug_kategori)
	{
		// kategori detail
		$kategori 			= $this->Modelkategori->read($slug_kategori);
		$id_kategori		= $kategori->id_kategori;
		$site 				= $this->Modelkonfigurasi->listing();
		$listing_kategori 	= $this->Modelproduk->listing_kategori();
		// Ambil data total
		$total 				= $this->Modelproduk->total_kategori($id_kategori);
		// Pagination Start
		$this->load->library('pagination');
		
		$config['base_url'] 		= base_url().'produk/kategori/'.$slug_kategori.'/index/';
		$config['total_rows'] 		= $total->total;
		$config['use_page_numbers']	= TRUE;
		$config['per_page'] 		= 12;
		$config['uri_segment'] 		= 5;
		$config['num_links'] 		= 3;
		$config['full_tag_open'] 	= '<ul class="pagination">';
		$config['full_tag_close'] 	= '</ul>';
		$config['first_link'] 		= 'First';
		$config['first_tag_open'] 	= '<li class="disabled"><li class="active"><a href="#">';
		$config['first_tag_close'] 	= '<span class="sr-only"></a></li></li>';
		$config['last_link'] 		= 'Last';
		$config['last_tag_open'] 	= '<div>';
		$config['last_tag_close'] 	= '</div>';
		$config['next_link'] 		= '&gt;';
		$config['next_tag_open'] 	= '<div>';
		$config['next_tag_close'] 	= '</div>';
		$config['prev_link'] 		= '&lt;';
		$config['prev_tag_open'] 	= '<div>';
		$config['prev_tag_close'] 	= '</div>';
		$config['cur_tag_open'] 	= '<b>';
		$config['cur_tag_close'] 	= '</b>';
		$config['first_url']		= base_url().'/produk/kategori/'.$slug_kategori;		
		$this->pagination->initialize($config);
		// Ambil data produk
		$page 	= ($this->uri->segment(5)) ? ($this->uri->segment(5)-1) * $config['per_page']:0;
		$produk = $this->Modelproduk->kategori($id_kategori, $config['per_page'], $page);
		// Paginasi end

		$data = array(	'title' 			=> $kategori->nama_kategori,
						'site'				=> $site,
						'listing_kategori'	=> $listing_kategori,
						'produk'			=> $produk,
						'pagin'				=> $this->pagination->create_links(),
		 				'page'				=> 'user/produk/list',
		 			);
		$this->load->view('user/layout/wrapper', $data, FALSE);
	}

	public function detail($slug_produk)
	{
		$site 				= $this->Modelkonfigurasi->listing();
		$produk 			= $this->Modelproduk->read($slug_produk);
		$id_produk			= $produk->id_produk;
		$gambar				= $this->Modelproduk->gambar($id_produk);
		$produk_related		= $this->Modelproduk->home();
		$keranjang			= $this->cart->contents();
		$isBelumAda			= true;

		$data = array(	'title' 			=> $produk->nama_produk,
						'site'				=> $site,
						'produk'			=> $produk,
						'produk_related'	=> $produk_related,
						'gambar'			=> $gambar,
						'keranjang'			=> $keranjang,
						'isBelumAda'		=> $isBelumAda,
		 				'page'				=> 'user/produk/detail',

		 			);
		$this->load->view('user/layout/wrapper', $data, FALSE);
	}

}

/* End of file Produk.php */
/* Location: ./application/controllers/Produk.php */