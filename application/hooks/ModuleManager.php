<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ModuleManager { 

    public function install(){ 
        require_once(BASEPATH.'helpers/file_helper.php') ; 
        require_once(BASEPATH.'helpers/directory_helper.php') ; 

        $modules = directory_map(APPPATH.'modules/') ; 

        foreach($modules as $module_name => $module){
            $this->_install($module_name) ; 
        }
    } 

    private function _install($module){
        if(is_dir(APPPATH.'modules/'.$module)){
            $this->clean_module($module) ; 

            $elements = array('models','views','controllers','assets','schemas') ; 
            foreach($elements as $key => $element){ 
                $this->install_element($module,$element) ; 
            }
        }
    } 

    private function clean_module($module){ 

    }

    private function make_node($src_dir,$dest_dir,$pos,$file){ 
        if(!is_int($pos)){ 
            $src_dir = $src_dir.$pos.'/';
            $dest_dir = $dest_dir.$pos.'/'; 
            !is_dir($dest_dir) ? mkdir($dest_dir,0777) : null ; 
        }

        if(is_string($file)){ 
            copy($src_dir.$file,$dest_dir.$file) ; 
        }else if(is_array($file)){ 
            foreach($file as $key => $item){
                $this->make_node($src_dir,$dest_dir,$key,$item) ; 
            }// end foreach ; 
        }
    }

    private function install_element($module , $what){ 
        if($what == 'assets'){ 
            $src_dir = APPPATH.'modules/'.$module.'/assets/' ; 
            $dest_dir = 'assets/modules/'.$module.'/'; 
            !is_dir($dest_dir) ? mkdir($dest_dir,0777) : null ; 
        }else{
            $src_dir = APPPATH.'modules/'.$module.'/'.$what.'/' ; 
            $dest_dir = APPPATH.$what.'/'.$module.'/'; 
            !is_dir($dest_dir) ? mkdir($dest_dir,0777) : null ; 
        } 
            
        $files = directory_map($src_dir) ; 
        $this->make_node($src_dir,$dest_dir,0,$files) ; 
    }

}
