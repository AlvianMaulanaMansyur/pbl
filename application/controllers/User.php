<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('M_produk');
        $this->load->model('M_cart');
        $this->load->model('M_personalInfo');
        $this->load->model('M_pesanan');
        $this->load->model('Customer_model');
        $this->load->library('form_validation');

        if (empty($this->session->userdata('customer_id'))) {
            if ($this->input->is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['redirect' => base_url('AuthCustomer/login')]);
                exit();
            } else {
                redirect('AuthCustomer/login');
            }
        }
    }

    public function userProfile()
    {
        // Memuat model
        $this->load->model('Customer_model');

        $id_customer = $this->session->userdata('customer_id');
        $customer_data = $this->Customer_model->get_customer_data($id_customer);
        // var_dump($customer_data);die;
        $data = [
            'title' => 'Profile Pengguna',
            'content' => 'customer/info',
            'customer_data' => $customer_data,
        ];
        // Menampilkan data
        $this->load->view('template', $data);
    }

    public function editProfile($id_customer)
    {
        // Ambil data dari formulir
        $nama_customer = $this->input->post('nama_customer');
        $telepon = $this->input->post('telepon');

        // var_dump($nama_customer);die;

        $this->Customer_model->editProfile($id_customer, $nama_customer, $telepon);

        redirect('profil'); // Sesuaikan dengan nama controller dan method yang sesuai
    }

    public function insertCart($id_produk)
    {
        $result = $this->M_cart->insertCart($id_produk);
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error_message' => 'Gagal menambahkan produk ke keranjang. Jumlah produk di keranjang melebih stok']);
        }
    }

    public function getCart()
    {
        $id = $this->session->userdata('customer_id');
        $cart['cart'] = $this->M_cart->getCart($id);
        if (!empty($cart['cart'])) {
            $data = array(
                'content' => 'V_user/cart',
                'title' => 'Cart',
                'cart' => $cart['cart'],
            );
        } else {
            $data = array(
                'content' => 'V_user/cart_kosong',
                'title' => 'Cart Kosong',
            );
        }
        $this->load->view('template', $data);
    }

    public function updateIsCheck($id_cart)
    {
        $cart = $this->M_cart->getCartByID($id_cart);

        if (isset($cart[0]) && $cart[0]['deleted'] == 0) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $is_checked = $this->input->post('is_check') ? 1 : 0;
                $isUpdateSuccessful = $this->M_cart->updateIsCheck($id_cart, $is_checked);

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => $isUpdateSuccessful]));
            } else {
                show_404();
            }
        } else {
            show_404();
        }
    }


    public function updateTotalCheckedPrice()
    {
        $id_customer = $this->session->userdata('customer_id');
        $total_checked_price = $this->M_cart->calculateTotalCheckedPrice($id_customer);

        header('Content-Type: application/json');
        echo json_encode(['total_checked_price' => $total_checked_price]);
    }

    public function updateQuantity($id_cart)
    {
        $newQuantity = intval($this->input->post('newQuantity'));
        $cart = $this->M_cart->getCartByID($id_cart);

        if (!empty($cart)) {
            if ($cart[0]['deleted'] == 0) {
                $qty_produk = $cart[0]['qty_produk'];
                $result = $this->M_produk->getDetailProdukByID($cart[0]['id_produk']);
                $stok_produk = $result['stok_produk'];

                if (is_numeric($newQuantity) && $newQuantity > 0 && $newQuantity <= $stok_produk) {
                    $qty_produk = $newQuantity;
                } else if ($newQuantity > $stok_produk) {
                    $qty_produk = $stok_produk;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Qty tidak valid']);
                    return;
                }

                $this->M_cart->updateQuantity($id_cart, $qty_produk);

                $updatedCart = $this->M_cart->getCartByID($id_cart);

                header('Content-Type: application/json');
                echo json_encode($updatedCart[0]);
            } else {
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cart not found']);
        }
    }

    public function deleteCart($id_cart)
    {
        $this->M_cart->deleteProdukHasCart($id_cart);
        $this->M_cart->deleteCartItem($id_cart);
        redirect('user/getCart');
    }

    public function personalInfo()
    {
        $personal_info = $this->M_personalInfo->getPersonalInfo();

        $data = [
            'content' => 'V_user/personalInfo',
            'title' => 'Checkout',
            'personal' => $personal_info,
        ];

        $this->load->view('template', $data);
    }

    public function uploadPersonalInfo()
    {
        $this->M_personalInfo->insertPersonalInfo();
        redirect('user/checkout');
    }

    public function checkout()
    {
        $id = $this->session->userdata('customer_id');
        $checkout = $this->M_cart->getCheckout($id);
        $kota = $this->M_personalInfo->kotaKec();
        $total_harga = $this->M_cart->calculateTotalCheckedPrice($id);
        $personal_info = $this->M_personalInfo->getPersonalInfoByIdCustomer($id);

        if (!empty($checkout)) {
            foreach ($checkout as $item) {
                $stockAvailable = $item['stok_produk'];

                if ($item['qty_produk'] > $stockAvailable) {
                    $this->session->set_flashdata('error', 'Jumlah produk dalam keranjang melebihi stok yang tersedia.');
                    redirect('user/getcart');
                }
            }

            $customer_id = $this->session->userdata('customer_id');
            $this->form_validation->set_rules('alamat', 'Alamat', 'required');
            $this->form_validation->set_rules('kota', 'Kabupaten', 'required');
            $this->form_validation->set_rules('kecamatan', 'Kecamatan', 'required');
            $this->form_validation->set_rules('kodepos', 'Kodepos', 'required|numeric|exact_length[5]');

            if ($this->form_validation->run() == FALSE) {

                $data = [
                    'content' => 'V_user/checkout1',
                    'title' => 'Checkout',
                    'cart' => $checkout,
                    'kota' => $kota,
                    'total_harga' => $total_harga,
                ];
                $this->load->view('template', $data);
            } else {
                $data_info = array(
                    'alamat' => $this->input->post('alamat'),
                    'kodepos' => $this->input->post('kodepos'),
                    'detail_alamat' => $this->input->post('detail_alamat'),
                    'id_customer' => $customer_id,
                    'id_kecamatan' => $this->input->post('kecamatan'),
                );
                $id_pesanan = $this->M_pesanan->createOrder($data_info);
                $response = array('id_pesanan' => $id_pesanan);
                echo json_encode($response);
                // redirect('prosespesanan/' . $id_pesanan);
            }
        } else {
            redirect('cart');
        }
    }

    public function setOrderIdToSession($id_pesanan)
    {
        $this->session->set_userdata('order_id', $id_pesanan);
        redirect('pesanan');
    }

    public function hlmOrder()
    {
        $id_pesanan = $this->session->userdata('order_id');
        $customer_id_login = $this->session->userdata('customer_id');

        $order = $this->M_pesanan->getOrder($id_pesanan);
        $total_harga = $this->M_pesanan->getTotalPriceOrder($id_pesanan);

        if (!empty($order) && $order[0]['id_customer'] == $customer_id_login) {
            $data = [
                'title' => 'Pesanan',
                'content' => 'V_user/pesanan',
                'order' => $order,
                'total_harga' => $total_harga,
            ];

            $this->load->view('template', $data);
        } else {
            redirect('error_page');
        }
    }

    public function Orders()
    {
        $id = $this->session->userdata('customer_id');
        $order = $this->M_pesanan->getAllOrder($id);

        $data = [
            'title' => 'Daftar Pesanan',
            'content' => 'V_user/daftar_pesanan',
            'orders' => $order,
        ];

        $this->load->view('template', $data);
    }

    public function Info()
    {
        $this->load->view('customer/info');
    }
}

/* End of file User.php */
