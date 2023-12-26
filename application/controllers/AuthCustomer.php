<?php

defined('BASEPATH') or exit('No direct script access allowed');

class AuthCustomer extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('Customer_model');
        $this->load->library('session');
        $this->load->library('form_validation');
    }

    public function login()
    {
        if (($this->session->userdata('customer_id')) == null) {
            $data = [
                'header' => 'V_partials/loginRegister/header',
                'content' => 'V_partials/loginRegister/login2',
                'js' => 'V_partials/loginRegister/js'

            ];
            $this->load->view('customer/loginView', $data);
        } else {
            redirect('home');
        }
    }

    public function process_login()
    {

        $email_or_username = $this->input->post('email_or_username');
        $password = $this->input->post('password_customer');

        if (filter_var($email_or_username, FILTER_VALIDATE_EMAIL)) {
            $customer = $this->Customer_model->get_email($email_or_username, $password);
        } else {
            $customer = $this->Customer_model->get_username($email_or_username, $password);
        }

        if ($customer) {
            $customer_data = array(
                'customer_id' => $customer->id_customer,
                'email' => $customer->email,
                'username' => $customer->username,
                'logged_in' => true
            );
            $this->session->set_userdata($customer_data);
            redirect('home');
        } else {
            // Tampilkan pesan error jika login gagal
            $this->session->set_flashdata('error_message', '<div class="alert alert-danger" role="alert">Login Gagal</div>');
            redirect('authCustomer/login');
        }
    }

    public function register()
    {
        $this->form_validation->set_rules('username', 'Username', 'required|trim|min_lenght[8]|is_unique[customer.username]|alpha_numeric', array(
            'required' => 'username cannot be empty!',
            'min_lenght' => 'username must be at least 8 characters!',
            'is_unique' => 'username already taken!',
            'alpha_numeric' => 'test'
        ));
        $this->form_validation->set_rules('password_customer', 'Password Customer', 'required|trim|min_lenght[8]|regex_match[/[0-9]/]', array(
            'required' => 'password cannot be empty!',
            'min_lenght' => 'password must be at least 8 characters!',
            'regex_match' => 'password must contain at least 1 number'
        ));
        $this->form_validation->set_rules('nama_customer', 'Nama Customer', 'required|trim', array(
            'required' => 'name cannot be empty!'
        ));

        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[customer.email]', array(
            'required' => 'email cannot be empty!',
            'is_unique' => 'email already taken!'
        ));
        $this->form_validation->set_rules('telepon', 'Telepon', 'required|trim|numeric', array(
            'required' => 'phone number cannot be empty!'
        ));

        if ($this->form_validation->run() == FALSE) {

            $data = [
                'header' => 'V_partials/loginRegister/header',
                'content' => 'V_partials/loginRegister/register',
                'js' => 'V_partials/loginRegister/js'
            ];
            $this->load->view('customer/registerCustomer', $data);
        } else {

            $username = $this->input->post('username');
            $password_customer = $this->input->post('password_customer');
            $nama_customer = $this->input->post('nama_customer');
            $email = $this->input->post('email');
            $telepon = $this->input->post('telepon');

            $data = array(
                'username' => $username,
                'password_customer' => $password_customer,
                'nama_customer' => $nama_customer,
                'email' => $email,
                'telepon' => $telepon
            );

            $this->db->insert('customer', $data);
            redirect('customer/login');
        }
    }

    public function forgotPassword()
    {
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

        if ($this->form_validation->run() == FALSE) {
            $data = [
                'header' => 'V_partials/loginRegister/header',
                'content' => 'V_partials/loginRegister/forgotPassword',
                'js' => 'V_partials/loginRegister/js'
            ];
        } else {
            $email = $this->input->post('email');
            $user = $this->db->get_where('customer', ['email' => $email])->row_array();

            $link = base_url('AuthCustomer/editPass');
            $subject = 'lupa Password';
            $message =
                "<html>
                    <p>silahkan mengklik link di bawah ini untuk mengganti password anda </p>
                    <a href='$link'>ganti password</a>
                <html>";
            if ($user) {
                $this->session->set_userdata('reset', $email);
                $this->send_email($email, $subject, $message);
                // $this->edit;
                if (isset($email)) {
                    $this->session->set_flashdata('pesan', '<div class="alert alert-success" role="alert">
                        pliss check your email </div>');
                    redirect('AuthCustomer/forgotPassword');
                } else {
                    redirect('AuthCustomer/login');
                }
            } else {
                $this->session->set_flashdata('pesan', '<div class="alert alert-danger" role="alert">
                your email was not found </div>');
                redirect('AuthCustomer/forgotPassword');
            }
        }
        $this->load->view('customer/forgotPass', $data);
    }

    public function send_email($to, $subject, $message)
    {
        $this->email->set_newline("\r\n");
        $this->email->from('balinirvanakomputer@gmail.com', 'Bali Nirvana Computer');
        $this->email->to($to);
        $this->email->subject($subject);
        $this->email->message($message);

        if ($this->email->send()) {
            return true;
        } else {
            echo $this->email->print_debugger();
            die;
            // return show_error($this->email->print_debugger());
        }
    }

    public function logout()
    {
        $this->session->unset_userdata('customer_id');
        $this->session->unset_userdata('email');
        $this->session->unset_userdata('logged_in');
        redirect('AuthCustomer/login');
    }
}

/* End of file AuthCustomer.php */
