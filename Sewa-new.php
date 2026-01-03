<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sewa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		if (empty($this->session->userdata("idadmin"))) {
			$this->session->set_flashdata('pesan_gagal', 'You Must Login');
			redirect('login','refresh');
		}
		$this->load->library('form_validation');
		$this->load->model("Msewa");
		$this->load->model("Mcabang");
		$this->load->model("Mkamar");
		$this->load->model("Mkota");
		$this->load->model("Mpelanggan");
		$this->load->model("Mpenjaga");
		$this->load->model("Mdetailsewa");
		$this->load->model("Mkelompokkamar");
		$this->load->model("Msewarencana");
		$this->load->model("Msewadetail");
		$this->load->model("Mbiayalain");
		$this->load->model("Mrekening");
	}
	public function index()
	{
		$data['sewa'] = $this->Msewa->tampil_sewa();
		$this->load->view("header");
		$this->load->view("sewa",$data);
		$this->load->view("footer");
	}
	
	function tambah()
	{
		$data['cabang'] = $this->Mcabang->tampil_cabang();
		$data['pelanggan'] = $this->Mpelanggan->tampil_pelanggan();
		$data['kota'] = $this->Mkota->tampil_kota();
		
		$input = $this->input->post();

		$mulai = date("Y-m-d");
		$selesai = date("Y-m-d", strtotime("+1 day"));
		$random = $this->Mkota->tampil_kota_random();
		$idkota = $random["idkota"];
		$jenis = "H";
		if ($this->input->post()) {
			
			$mulai = $this->input->post("mulai");
			$selesai = $this->input->post("selesai");
			$idkota = $this->input->post("idkota");
			$jenis = $this->input->post("jenis");
		}

		$data["kamar"] = $this->Mkamar->kamar_tersedia($idkota,$mulai,$selesai,$jenis);

		$data['idkota'] = $idkota;
		$data['jenis'] = $jenis;
		$data['mulai'] = $mulai;
		$data['selesai'] = $selesai;
		$this->load->view("header");
		$this->load->view("sewa_tambah",$data);
		$this->load->view("footer");

	}
	function edit($idsewa)
	{
		$data['cabang'] = $this->Mcabang->tampil_cabang();
		$data['pelanggan'] = $this->Mpelanggan->tampil_pelanggan();
		$data['sewa'] = $this->Msewa->detail_sewa($idsewa);
		$input = $this->input->post();
		$data["biayalain"] = $this->Msewa->tampil_biayalain($kodesewa);
		$this->form_validation->set_rules('idcabang', 'idcabang', 'required');
		$this->form_validation->set_rules('idpelanggan', 'idpelanggan', 'required');
		$this->form_validation->set_rules('tanggal', 'tanggal', 'required');
		if ($this->form_validation->run() == TRUE) {
			$this->load->model('Msewa');
			$this->Msewa->edit_sewa($input,$idsewa);
			$this->session->set_flashdata('pesan_sukses', 'Berhasil Terubah');
			redirect('sewa','refresh');
		}
		$this->load->view("header");
		$this->load->view("sewa_edit",$data);
		$this->load->view("footer");
	}
	function hapus($kode_sewa)
	{
		$this->Msewa->hapus_sewa($kode_sewa);
		$this->session->set_flashdata('pesan_sukses', 'Berhasil Terhapus');
		redirect('sewa','refresh');
	}
	function hapusrencana($id_rencana)
	{
		$this->Msewarencana->hapus_rencana($id_rencana);
		$this->session->set_flashdata('pesan_sukses', 'Berhasil Terhapus');
		redirect('sewa','refresh');
	}
	/// DETAILSEWA ///
	function detail($id_rencana)
	{
		$data["sewa_rencana"] = $this->Msewa->detail_rencana($id_rencana);
		$data["kelompokkamar"] = $this->Mkelompokkamar->tampil_kelompokkamar($data['sewa_rencana']['id_cabang']);
		$data["kelompokkamars"] = $this->Mkelompokkamar->tampil_kelompokkamar();
		$data["biayalain"] = $this->Mbiayalain->tampil_biayalain();
		$data["rekening"] = $this->Mrekening->tampil_rekening();
		$data["penjaga"] = $this->Mpenjaga->tampil_penjaga();
		$data['kamars'] = $this->Mkamar->tampil_kamar();

		if ($this->input->post()) {
			$this->Msewarencana->update($this->input->post(), $id_rencana);

			$input["status"] = $this->input->post("status");
			$this->Msewa->edit_sewa($input, $id_rencana);
			$this->session->set_flashdata('pesan_sukses', 'Rencana sewa telah diupdate');
			redirect('sewa/detail/'.$id_rencana,'refresh');
		}

		$this->load->view("header");
		$this->load->view("sewa_detail",$data);
		$this->load->view("footer");
	}
	function detailhapus($idsewa,$iddetailsewa)
	{
		$this->Mdetailsewa->hapus_detailsewa($iddetailsewa);
		$this->session->set_flashdata('pesan_sukses', 'Berhasil Terhapus');
		redirect('sewa/detail/'.$idsewa,'refresh');
	}
	function cekkamar($idkelompokkamar, $tglm, $tgls) {
		$kamar = $this->Mkamar->cekkamar_admin($idkelompokkamar, $tglm, $tgls);
		echo "<option value=''>Pilih</option>";
		foreach ($kamar as $key => $value) {
			echo "<option value='".$value["idkamar"]."'>";
			echo $value['nomorkamar'];
			echo "</option>";
		}
	}
	function cekkamars($idkelompokkamar, $tglm, $tgls) {
		$kamar = $this->Mkamar->cekkamar_admins($idkelompokkamar, $tglm, $tgls);
		echo "<option value=''>Pilih</option>";
		foreach ($kamar as $key => $value) {
			echo "<option value='".$value["idkamar"]."'>";
			echo $value['nomorkamar'];
			echo "</option>";
		}
	}
	function hapusbiaya($id_sewa_biayalain,$id_rencana){

		$this->db->where('id_sewa_biayalain',$id_sewa_biayalain);
		$this->db->delete('sewa_biayalain');

		$this->session->set_flashdata('pesan_sukses', 'Biaya lain berhasil dihapus');
		redirect('sewa/detail/'.$id_rencana,'refresh');
	}
	function pindahkamar() {

		$this->db->where('id_rencana', $this->input->post("id_rencana"));
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();
		$input = $this->input->post();
		if (!empty($sewa_rencana)) {
			$this->db->where('id_rencana', $input["id_rencana"]);
			$this->db->update('sewa_rencana', ['id_kamar' => $input["idkamar"]]);

			$this->Msewarencana->pindahkamar($input);
			$this->session->set_flashdata('pesan_sukses', 'Kamar berhasil dipindah');
			redirect('sewa/detail/'.$sewa_rencana['id_rencana'],'refresh');
		}
	}
	function preview($idsewa){

		$data['sewa'] = $this->Msewa->detail_sewa($idsewa);
		$this->load->view('header',$data);
		$this->load->view('sewa_preview',$data);
		$this->load->view('footer',$data);
	}
	function simpanbiaya(){
		$this->form_validation->set_rules('id_biayalain', 'Biaya Lain', 'required');
		$this->form_validation->set_rules('tarif', 'Tarif', 'required');
		$this->form_validation->set_rules('jumlah', 'Jumlah', 'required');
		$input = $this->input->post();

		$this->db->where('id_rencana', $this->input->post("id_rencana"));
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();

		if ($this->form_validation->run() == TRUE) {
			if (!empty($sewa_rencana)) {
				$input['tanggal_pesan'] = date("Y-m-d H:i:s");
				$this->db->insert('sewa_biayalain', $input);
				$this->session->set_flashdata('pesan_sukses', 'Biaya tambahan Tersimpan');
				redirect('sewa/detail/'.$sewa_rencana["id_rencana"],'refresh');
			}
		}
	}
	function updaterencana() {
		$this->db->where('id_rencana', $this->input->post("id_rencana"));
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();

		if (!empty($sewa_rencana)) {

			$this->db->where('id_rencana', $this->input->post("id_rencana"));
			$this->db->update('sewa_rencana', $this->input->post());

			if (!empty($this->input->post("realisasi_checkout"))) {
				//kosongkan tanggal pakai setelah tanggal realisasi checkout 
				$this->db->where('id_rencana', $this->input->post("id_rencana"));
				$this->db->where('pakai >', $this->input->post("realisasi_checkout"));
				$this->db->delete('sewa_detail');
			}


			$this->session->set_flashdata('pesan_sukses', 'Rencana sewa Tersimpan');
			redirect('sewa/detail/'.$sewa_rencana["id_rencana"],'refresh');
		}
	}
	function simpanbayar() {
		$this->db->where('id_rencana', $this->input->post("id_rencana"));
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();

		if (!empty($sewa_rencana)) {
			$input = $this->input->post();
			$config['upload_path'] = $this->config->item("bayar_url");
			$config['allowed_types'] = 'gif|jpg|png|jpeg|pdf';
			$config['encrypt_name'] = true;
			$config['detect_mime'] = true;
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('bukti_bayar')) {
				$input['bukti_bayar'] = $this->upload->data('file_name');
			}

			$this->db->insert("sewa_bayar", $input);

			$this->session->set_flashdata('pesan_sukses', 'Pembayaran sewa Tersimpan');
			redirect('sewa/detail/'.$sewa_rencana["id_rencana"],'refresh');
		}
	}
	function editbayar($id_bayar){
		$this->db->where('id_bayar', $id_bayar);
		$data['sewa_bayar'] = $this->db->get('sewa_bayar')->row_array();
		$data["rekening"] = $this->Mrekening->tampil_rekening();

		if ($this->input->post()) {
			$input = $this->input->post();
			$config['upload_path'] = $this->config->item("bayar_url");
			$config['allowed_types'] = 'gif|jpg|png|jpeg';
			$config['encrypt_name'] = true;
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if ($this->upload->do_upload('bukti_bayar')) {
				$input['bukti_bayar'] = $this->upload->data('file_name');
			}
			$this->db->where('id_bayar', $id_bayar);
			$this->db->update("sewa_bayar", $input);

			$this->session->set_flashdata('pesan_sukses', 'Pembayaran sewa Tersimpan');
			redirect('sewa/detail/'.$data['sewa_bayar']["id_rencana"],'refresh');
		}


		$this->load->view('header');
		$this->load->view('sewa_bayaredit', $data);
		$this->load->view('footer');
	}
	function hapusbayar($id_bayar) {
		$this->db->where('id_bayar', $id_bayar);
		$this->db->join('sewa_rencana', 'sewa_rencana.id_rencana = sewa_bayar.id_rencana', 'left');
		$sewa_bayar = $this->db->get('sewa_bayar')->row_array();

		if (!empty($sewa_bayar)) {
			$this->db->where('id_bayar', $id_bayar);
			$this->db->delete('sewa_bayar');

			$this->session->set_flashdata('pesan_sukses', 'Pembayaran terhapus');
			redirect('sewa/detail/'.$sewa_bayar["id_rencana"],'refresh');
		}
	}
	function simpanpotongan() {
		$this->db->where('id_rencana', $this->input->post("id_rencana"));
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();

		if (!empty($sewa_rencana)) {
			$input = $this->input->post();
			$this->db->insert("sewa_potongan", $input);

			$this->session->set_flashdata('pesan_sukses', 'Pembayaran sewa Tersimpan');
			redirect('sewa/detail/'.$sewa_rencana["id_rencana"],'refresh');
		}
	}
	function hapuspotongan($id_potongan) {
		$this->db->where('id_potongan', $id_potongan);
		$this->db->join('sewa_rencana', 'sewa_rencana.id_rencana = sewa_potongan.id_rencana', 'left');
		$sewa_potongan = $this->db->get('sewa_potongan')->row_array();

		if (!empty($sewa_potongan)) {
			$this->db->where('id_potongan', $id_potongan);
			$this->db->delete('sewa_potongan');

			$this->session->set_flashdata('pesan_sukses', 'Potongan terhapus');
			redirect('sewa/detail/'.$sewa_potongan["id_rencana"],'refresh');
		}
	}
	function editpotongan($id_potongan) {
		$this->db->where('id_potongan', $id_potongan);
		$data['potongan'] = $this->db->get('sewa_potongan')->row_array();

		if ($this->input->post()) {
			$input = $this->input->post();
			if ($data['potongan']['jumlah_potongan']!==$this->input->post("jumlah_potongan") ) {
				$input['validasi_potongan'] = "terkirim";
			}
			$this->db->where('id_potongan', $id_potongan);
			$this->db->update('sewa_potongan', $input);
			$this->session->set_flashdata('pesan_sukses', 'potongan telah terubah');
			redirect('sewa/detail/'.$data["potongan"]["id_rencana"],'refresh');
		}

		$this->load->view('header');
		$this->load->view('sewa_potonganedit', $data);
		$this->load->view('footer');
	}
	function tambahhari() {

		$this->db->where('id_rencana', $this->input->post("id_rencana"));
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();

		if (empty($sewa_rencana)) return;

		$startDate = new DateTime($this->input->post("mulai"));
		$endDate   = new DateTime($this->input->post("hingga"));
		$interval  = new DateInterval('P1D');
		$dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

		$bentrok = [];
		$insert  = 0;

		foreach ($dateRange as $date) {

			$pakai = $date->format('Y-m-d');

			$cek = $this->Msewarencana->cek_kamar_dipakai(
				$this->input->post("id_kamar"),
				$pakai
			);

			if ($cek) {
				$bentrok[] = $pakai.' ('.$cek['kode_sewa'].')';
				continue;
			}

			$this->db->insert('sewa_detail', [
				'pakai'      => $pakai,
				'id_rencana' => $this->input->post("id_rencana"),
				'id_kamar'   => $this->input->post("id_kamar")
			]);

			$insert++;
		}

		$pesan = $insert.' tanggal berhasil ditambahkan.';
		if (!empty($bentrok)) {
			$pesan .= ' Tanggal '.implode(', ', $bentrok).' tidak dapat ditambahkan karena sudah dipesan.';
		}

		$this->session->set_flashdata('pesan_sukses', $pesan);
		redirect('sewa/detail/'.$this->input->post("id_rencana"),'refresh');
	}


	function tambahrencana() {

		$this->db->where('id_sewa', $this->input->post("id_sewa"));
		$sewa = $this->db->get('sewa')->row_array();
		if (empty($sewa)) return;

		$kmr = $this->Mkamar->detail_kamar($this->input->post('id_kamar'));

		$this->db->insert('sewa_rencana', [
			'kode_sewa'          => $sewa['kode_sewa'],
			'id_kamar'           => $this->input->post("id_kamar"),
			'biaya_rencana'      => $kmr['tarifbulanan'],
			'perpanjang_rencana' => '-',
			'jenis_rencana'      => 'B',
			'rencana_checkin'    => $this->input->post("mulai"),
			'rencana_checkout'   => $this->input->post("hingga"),
			'realisasi_checkin'  => $this->input->post("mulai"),
			'realisasi_checkout' => $this->input->post("hingga")
		]);

		$id_rencana = $this->db->insert_id();

		$startDate = new DateTime($this->input->post("mulai"));
		$endDate   = new DateTime($this->input->post("hingga"));
		$interval  = new DateInterval('P1D');
		$dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

		$bentrok = [];
		$insert  = 0;

		foreach ($dateRange as $date) {

			$pakai = $date->format('Y-m-d');

			$cek = $this->Msewarencana->cek_kamar_dipakai(
				$this->input->post("id_kamar"),
				$pakai
			);

			if ($cek) {
				$bentrok[] = $pakai.' ('.$cek['kode_sewa'].')';
				continue;
			}

			$this->db->insert('sewa_detail', [
				'pakai'      => $pakai,
				'id_rencana' => $id_rencana,
				'id_kamar'   => $this->input->post("id_kamar")
			]);

			$insert++;
		}

		$pesan = 'Rencana dibuat. '.$insert.' tanggal berhasil ditambahkan.';
		if (!empty($bentrok)) {
			$pesan .= ' Tanggal '.implode(', ', $bentrok).' tidak dapat ditambahkan karena sudah dipesan.';
		}

		$this->session->set_flashdata('pesan_sukses', $pesan);
		redirect('sewa/detail/'.$id_rencana,'refresh');
	}



	function hapusdetail($id_detail, $id_rencana) {
		$this->db->where('id_detail', $id_detail);
		$this->db->where('id_rencana', $id_rencana);
		$this->db->delete('sewa_detail');

		$this->session->set_flashdata('pesan_sukses', 'tanggal pakai terhapus');
		redirect('sewa/detail/'.$id_rencana,'refresh');
	}
	function editbiaya($id_sewa_biayalain, $id_rencana) {
		$this->db->where('id_sewa_biayalain', $id_sewa_biayalain);
		$this->db->where('id_rencana', $id_rencana);
		$data['sewa_biayalain'] = $this->db->get('sewa_biayalain')->row_array();

		$data["biayalain"] = $this->Mbiayalain->tampil_biayalain();
		$data["rekening"] = $this->Mrekening->tampil_rekening();
		$data["penjaga"] = $this->Mpenjaga->tampil_penjaga();
		$data["rekening"] = $this->Mrekening->tampil_rekening();

		$this->form_validation->set_rules('id_biayalain', 'Biaya Lain', 'required');
		$this->form_validation->set_rules('tarif', 'Tarif', 'required');
		$this->form_validation->set_rules('jumlah', 'Jumlah', 'required');
		$input = $this->input->post();

		$this->db->where('id_rencana', $id_rencana);
		$sewa_rencana = $this->db->get('sewa_rencana')->row_array();

		if ($this->form_validation->run() == TRUE) {
			if (!empty($sewa_rencana)) {
				$this->db->where('id_sewa_biayalain', $id_sewa_biayalain);
				$this->db->update('sewa_biayalain', $input);
				$this->session->set_flashdata('pesan_sukses', 'Biaya tambahan Terubah');
				redirect('sewa/detail/'.$id_rencana,'refresh');
			}
		}

		$this->load->view('header');
		$this->load->view('sewa_editbiayalain', $data);
		$this->load->view('footer');
	}
	function editdetail($id_detail,$id_rencana){
		$this->db->where('id_rencana', $id_rencana);
		$data['sewa_rencana'] = $this->db->get('sewa_rencana')->row_array();
		

		$data['sewa_detail'] = $this->Msewadetail->detail($id_detail,$id_rencana);
		$data['kelompokkamar'] =  $this->db->get('kelompokkamar')->result_array();
		$inputan = $this->input->post();
		if ($inputan) {
			$inputan['id_kamar'] = $inputan['idkamar'];
			unset($inputan['tglm']); 
			unset($inputan['tgls']) ;
			unset($inputan['idkelompokkamar']);
			unset($inputan['idkamar']);
			$this->Msewadetail->edit($id_detail,$inputan);
			$this->session->set_flashdata('pesan_sukses', 'Berhasil Terubah');
			redirect('sewa/detail/'.$id_rencana,'refresh');
		}
		$this->load->view('header');
		$this->load->view('sewa_detail_edit', $data);
		$this->load->view('footer');
	}

}
/* End of file Sewa.php */
/* Location: ./application/controllers/Sewa.php */
