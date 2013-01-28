<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Control_panel extends CI_Controller {
    function __construct(){ 
        parent::__construct() ; 
        $this->load->library('aglayout') ; 
    } 

    public function index(){
        $this->schemaList() ; 
    }

    public function refreshTable(){
        $table_name = $this->input->post('table_name') ;
        $table_path = $this->input->post('table_path') ;

        $this->load->database(); 
        $this->load->dbforge() ; 

        if($this->db->table_exists($table_name)){ 
            $this->dbforge->drop_table($table_name) ; 
        } 

        $this->load->helper('file') ; 
        $this->load->library('ag_dbutil') ; 

        $f = read_file($table_path) ; 
        $result = $this->ag_dbutil->schema_parse($f) ; 
        $this->ag_dbutil->create_table($table_name,$result['columns']) ; 
    }

    public function deleteTable(){
        $table_name = $this->input->post('table_name') ;

        $this->load->database(); 
        $this->load->dbforge() ; 

        if($this->db->table_exists($table_name)){ 
            $this->dbforge->drop_table($table_name) ; 
        }
    }

    public function schemaList(){

        $this->load->helper('directory') ; 
        $this->load->helper('file') ; 
        $map = directory_map(APPPATH.'schemas/',3); 
 
        $schema_list = array() ; 
        $file_list = array() ; 

        foreach($map as $key => $row){ //modules list 
           $schema_list_cnt = count($row) ;  
           $path = APPPATH.'schemas/'.$key ; 

           for($i=0 ; $i < $schema_list_cnt ;$i++){ 
               if(strpos($row[$i],'xml')){
                   $result = explode('.',$row[$i]); 
                   $file_list[$result[0]] = array('path' => $path.'/'.$row[$i] , 
                           'table' => $result[0], 
                           'is_exists' => false 
                           ); 
               }

           } 
        }

        $this->load->library('ag_dbutil') ; 

        $data = array() ; 

        foreach($file_list as $key => $row){ 
            $file_list[$key]['is_exists'] =  $this->ag_dbutil->is_exists($row['table']) ;
        } 

        $data['schema_list']= $file_list ;

        $this->aglayout->layout('superadmin/layout'); 
        $this->aglayout->moduleViewPath('superadmin/control_panel/') ; 
        print_r(Aglayout::$header_data) ;
        $this->aglayout->add('header') ; 
        $this->aglayout->add('sidebar') ; 
        $this->aglayout->add('schemaList') ; 
        print_r(Aglayout::$header_data) ;
        $this->aglayout->add('footer') ; 
        $this->aglayout->show($data) ; 
    } 
} 
/* End of control_panel.php */
/* Location: ./application/controller/admin/control_panel.php */
