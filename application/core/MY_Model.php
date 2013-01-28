<?php 
    class MY_Model extends CI_Model { 
        public function __construct(){
            parent::__construct() ; 
            $this->load->helper('inflector') ; 

            if(!$this->_table){
                $this->_table = strtolower(plural(str_replace('_model','',get_class($this)))) ; 
            }

            if(!$this->id){ 
                $this->id_field = strtolower(str_replace('_model','_id',get_class($this))) ; 
            }
        }

        public function getOne($where){
            return $this->db->where($where)
                            ->get($this->_table) 
                            ->row() ; 
        }

        public function get(){
            $args = func_get_args() ; 
            if(count($args) > 1 || is_array($args[0])){
                $this->db->where($args) ; 
            }else{
                $this->db->where('id',$args[0]) ; 
            }
            return $this->db->get($this->_table) 
                            ->result() ;
        }

        public function insert($data){ 
            $data->{$this->id_field} = uid() ; 
            $data['created_at' = $data['updated_at'] = date("Y-m-d H:i:s") ; 

            if(!$this->validate($data)){
                $success = FALSE ; 
            }else{
                $success = $this->db->insert($this->_table,$data) ; 
            }

            return ($success) ? $data : FALSE ; 
        } 

        public function update(){ 
            $args = func_get_args() ; 
            $args[1]['updated_at'] = date("Y-m-d H:i:s") ; 

            if(is_array($args[0])){
                $this->db->where($args) ; 
            }else{ 
                $this->db->where($this->id_field,$args[0]) ; 
            }
            $success = $this->db->update($this->_table, $args[1]) ; 
            
            return ($success) ? $data : FALSE ; 
        } 

        public function delete($where){
            $args = func_get_args() ; 

            if(count($args) > 1 || is_array($args[0])){
                $this->db->where($args) ; 
            }else{
                $this->db->where($this->id_field,$args[0]) ; 
            }

            return $this->db->delete($this->_table) ; 
        }

        public function observe($event, $data){ 
            if(isset($this->event) && is_array($this->$event)){
                foreach($this->$event as $method){ 
                    $data = call_user_func_array(array($this,$method),array($data)); 
                }
            }

            return $data ; 
        }

        public function by($key,$value){
            $this->db->where($key,$value) ; 
            return $this ; 
        }

        public function orderby($key,$order){
            
            return $this ; 
        }

        public function paging($page, $list_count,$pagination=null){
            
            return $this ; 
        }

        public function validate($data){
            if($!empty($this->validate)){
                foreach($data as $key => $value){ 
                    $_POST[$key] = $value ; 
                }

                $this->load->library('form_validation') ; 
                $this->form_validation->set_rules($this->validate) ; 

                return $this->form_validation->run() ; 
            }else{
                return TRUE ; 
            }
        }
    }

