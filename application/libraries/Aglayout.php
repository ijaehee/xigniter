<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Aglayout{
    public $layout ;
    public $moduleViewPath ; 
    public static $header_data ;
    static $footer_data ; 
    public $body = array() ; 

    function __construct() { 
		$this->ci =& get_instance();
    }

    public function layout($layout){ 
        $this->layout ='./../layouts/'.$layout ; 
    }

    public function moduleViewPath($path){
        $this->moduleViewPath = $path ; 

        return $this->moduleViewPath ; 
    } 

    public function add($view_file){ 
        $this->body[] = $view_file ; 

        return $this->body ; 
    } 
    

    public function addMetaData($where,$data){
        self::$header_data = '<div class="hero-unit">hello</div>' ; 
    }

    public function compile($data){ 
        if(!empty(self::$header_data)){
            $data['_header_data'] = self::$header_data  ; 
        }

        if(!empty(self::$footer_data)){
            $data['_footer_data'] = self::$footer_data ; 
        }


        $body_html = '' ; 

        foreach($this->body as $key => $row){
            $body_html .= $this->ci->load->view($this->moduleViewPath.$row,$data,TRUE) ;
        }
        
        $data['_contents'] = $body_html ; 

        return $this->ci->load->view($this->layout,$data,TRUE) ; 

    }

    public function show($data=null) { 
        $str = $this->compile($data)  ; 
        echo @$this->ci->load->view($this->layout,$data,TRUE); 
    }
} 

/* End of file Aglayout.php */
/* Location: ./application/libraries/Aglayout.php */
