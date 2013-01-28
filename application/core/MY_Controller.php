<?php 
class MY_Controller extends CI_Controller {
    
    public $models = array() ; 
    public $model = '' ; 

    public function __construct(){
        parent::__construct() ; 
        $this->load->helper('inflector') ; 
        $model = strtolower(singular(get_class($this))); 
    }

    public function _remap($method, $parameters){
        if(method_exists($this, $method)){
            call_user_func_array(array($this,$method), $parameters) ; 
        }

        show_404() ; 
    } 
}
