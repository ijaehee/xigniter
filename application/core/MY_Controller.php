<?php 
class MY_Controller extends CI_Controller {
    
    public $models = array() ; 
    protected $data = array() ; 

    public function __construct(){
        parent::__construct() ; 
        //$this->load->helper('inflector') ; 
        //$model = strtolower(singular(get_class($this))); 

        foreach($this->models as $model){ 
            if(file_exists(APPPATH.'models/'.$model.'_model.php')){ 
                $arr = explode('/',$model) ; 
                $this->load->model($model.'_model',$arr[count($arr)-1]) ; 
            }
        }
    }

    public function _remap($method, $parameters){
        if(method_exists($this, $method)){
            $this->setData('action',$method) ;
            call_user_func_array(array($this,$method), $parameters) ; 
        }else{ 
            show_404() ; 
        }
    } 

    public function getData($key=null){
        if($key){ 
            return isset($this->data[$key]) ? $this->data[$key] : null ; 
        }else{ 
            return $this->data ; 
        }
    }

    public function setData($key,$value){ 
        $this->data[$key] = $value ; 
    }
}
