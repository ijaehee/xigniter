<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH.'libraries/phpass-0.1/PasswordHash.php') ; 
require_once( APPPATH."libraries/facebook.php");

class Xauth {
    public $ci = null ;  
    public $user_model = null ; 

    function __construct(){ 
        $this->ci = &get_instance() ; 
        $this->ci->load->library('session');
        $this->ci->load->model('member/user_model','user') ; 
        $this->user_model = $this->ci->user ; 
    } 

    public function validate($user_data){
        return $this->user_model->validate($user_data) ; 
    }

    public function is_existed($user_data){ 
        $email = $user_data['email']; 
        $result = $this->user_model->by('email',$email)->get_item() ; 

        if($result){ 
            return TRUE ; 
        }

        return FALSE ; 
    }

    public function generate_password($password){
        $hasher = new PasswordHash(
                    $this->ci->config->item('phpass_hash_strength','xauth') ,
                    $this->ci->config->item('phpass_hash_portable','xauth')
                    ); 

        return $hasher->HashPassword($password) ;
    }

    public function check_password($input_password,$db_password){
        $hasher = new PasswordHash(
            $this->ci->config->item('phpass_hash_strength','xauth') ,
            $this->ci->config->item('phpass_hash_portable','xauth')
        ); 

        if($hasher->CheckPassword($input_password,$db_password)){ 
            return TRUE ;  
        }

        return FALSE; 
    }

    public function is_required_activation(){ 
        $this->ci->config->load('xauth');
        $is_required_activation = $this->ci->config->item('is_required_activation') ; 

        return $is_required_activation ; 
    }
    
    public function create_user($user_data){ 
        $user_data['password'] = $this->generate_password(trim($user_data['password'])) ; 
        $is_required_activation = $this->is_required_activation() ; 
        $user_data['is_activated'] = $is_required_activation ? 'N' : 'Y' ;
        unset($user_data['passconf']); 
        $user_data['email_key'] = $is_required_activation ? $this->generate_email_key() : null ; 
        $user_data['email_key_expiry_date'] = $is_required_activation ? date("Y-m-d H:i:s",strtotime("+1 day")) : null ; 
        $ret = $this->user_model->insert($user_data) ; 
        $is_required_activation ? $this->request_email_activation($ret['email_key']) : null ; 
        $this->set_logged_user($ret) ; 
        
        return $ret ; 
    }

    public function request_email_activation($email_key){
        $user_info = $this->user_model->by('email_key',$email_key)->get_item() ; 

        if($user_info){
            $this->ci->config->load('email');
            $mail_data = array() ; 
            $mail_data['from_address'] = $this->ci->config->item('sender');
            $mail_data['to_address'] = $user_info->email;
            $mail_data['subject'] = $this->ci->config->item('invite_subject');
            $mail_data['message'] = $this->ci->config->item('invite_message').base_url()."member/member/email_activate/".$user_info->email_key;

            $this->send_email($mail_data);
        }
    }

    public function email_activate($email_key){
        $item = $this->user_model->by('email_key',$email_key)->by('email_key_expiry_date >',date('Y-m-d H:i:s'))->get_item() ; 
        $ret = null ;

        if($item){ 
            $where = array("email_key" => $email_key);
            $data = array("is_activated" => "Y");
            $ret = $this->user_model->update($where,$data); 
        }

        return $ret ; 
    }

    public function generate_email_key(){ 
        $this->ci->load->helper('security') ; 

        return do_hash(rand().microtime(),'md5');
    }

    public function delete_user(){

    }

    public function modify_user(){

    } 

    public function is_login(){ 
        $is_success = $this->get_logged_user()!=null ? TRUE : FALSE  ; 

        return $is_success ; 
    }

    public function get_logged_user(){
        return $this->ci->session->userdata('logged_user') ; 
    }

    public function set_logged_user($user_info){
        $this->ci->session->set_userdata('logged_user',$user_info) ; 
    }

    public function login($user_data){
        $msg = array() ; 

        $user_info = $this->user_model->by('email',$user_data['email'])->get_item() ; 

        if(!$user_info ||
            !$this->check_password($user_data['password'],$user_info->password)
        ){ 
            $msg['code'] = 'error' ; 
            $msg['comment'] = '사용자 정보가 없거나 비밀번호가 일치하지 않습니다.'; 
            $this->ci->session->set_flashdata('msg',$msg) ; 
            return FALSE; 
        } 

        $this->last_login($user_info); 
        $this->set_logged_user($user_info) ; 
        return $user_info ; 
    }

    public function send_email($mail_data){
        $this->ci->load->library('email',$this->ci->config->item('config'));

        $this->ci->email->from($mail_data['from_address']);
        $this->ci->email->to($mail_data['to_address']);
        $this->ci->email->subject($mail_data['subject']);
        $this->ci->email->message($mail_data['message']);

        $this->ci->email->send();
    }

    public function is_available_email_key($email_key){
        $item = $this->user_model->by('email_key',$email_key)->get_item() ; 
        if(!$item){
            return FALSE;
        }

        return TRUE;
    }

    public function is_expired_email_key($email_key){
        $item = $this->user_model->by('email_key',$email_key)->by('email_key_expiry_date >',date('Y-m-d H:i:s'))->get_item() ; 

        if($item){
            return FALSE;
        }

        return TRUE;
    }

    public function change_email_key($email_key){
        $new_email_key = $this->generate_email_key();
        $where = array("email_key" => $email_key);
        $data = array("email_key" => $new_email_key,
            "email_key_expiry_date" => date("Y-m-d H:i:s",strtotime("+1 day")));
        $ret = $this->user_model->update($where,$data);

        return $ret;
    }

    public function last_login($user_data){
        $where = array("email" => $user_data->email);
        $data = array("last_ip" => $this->ci->input->ip_address(),
                      "last_login" => date("Y-m-d H:i:s"));
        $ret = $this->user_model->update($where,$data);

        return $ret;
    }

    ///////// facebook oauth ////////////
    
    public function facebook_login(){
        $this->ci->config->load('facebook');

        $base_url = $this->ci->config->item('base_url');

        $facebook = new FaceBook(array(
            'appId' => $this->ci->config->item('appID'),
            'secret' => $this->ci->config->item('appSecret'),
        ));

        $user = $facebook->getUser();
        if ($user) {
            try {
                $user_profile = $facebook->api('/me');
            } catch (FacebookApiException $e) {
                error_log($e);
                $user = null;
            }
        }     

        if ($user) {
            $logoutUrl = $facebook->getLogoutUrl();
        } else {
            $loginUrl = $facebook->getLoginUrl();
        }

        $naitik = $facebook->api('/naitik');
    }
}
