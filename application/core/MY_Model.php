<?php 
    class MY_Model extends CI_Model { 
        protected $_table  ; 
        protected $id_field  ; 

        public function __construct(){
            parent::__construct() ; 
            $this->load->helper('inflector') ; 
            $this->load->helper('favoritefn') ; 
            $this->load->database() ; 

            if(!$this->_table){
                $this->_table = strtolower(plural(str_replace('_model','',get_class($this)))) ; 
            }

            if(!$this->id_field){ 
                $this->id_field = strtolower(str_replace('_model','_id',get_class($this))) ; 
            }
        }

        public function get($where){ 
            return $this->db->where($where)
                            ->get($this->_table) 
                            ->row() ; 
        }

        public function getItemById($hotel_id){ 
            return $this->db->where($this->id_field,$hotel_id)->get($this->_table)->row() ; 
        }

        public function getItem(){
            return $this->db->get($this->_table) 
                            ->row() ; 
        }

        public function getItems(){
            $args = func_get_args() ; 
            
            if($args!=null && (count($args) > 1 || is_array($args[0]))){
                $this->db->where($args) ; 
            }else if(count($args) == 1 ){
                $this->db->where($this->id_field,$args[0]) ; 
            }

            return $this->db->get($this->_table) 
                            ->result() ;
        }

        public function insert($data){ 
            $data[$this->id_field] = unique_id() ; 
            $data['created_at'] = $data['updated_at'] = date("Y-m-d H:i:s") ; 

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

            return ($success) ? $args[1] : FALSE ; 
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

        public function getPagination($cur_page=1,$list_count=20){ 
            $total_count = $this->db->count_all_results($this->_table) ; 

            $pagination = array() ; 
            $pagination['total_count'] = $total_count ; 
            $pagination['list_count'] = $list_count ; 
            $pagination['page'] = $cur_page ; 
            $pagination['page_count'] = ceil($total_count/$list_count) ; 

            return $pagination ; 
        }

        public function sorted($key=null,$order='desc'){ 
            $idx = $key ? $key : $this->id_field ; 
            $this->db->order_by($idx,$order) ; 
            return $this ; 
        }

        public function paging($page=1, $list_count=20){
            $this->db->limit($list_count,($page-1)*$list_count) ;  
            return $this ; 
        }

        public function validate($data){
            if(!empty($this->validate)){
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

