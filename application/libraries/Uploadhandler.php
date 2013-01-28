<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Uploadhandler {
    protected $options;
    var $config = array() ; 

    public function __construct(){ 
    }

    public function index(){

    }

    public function uploadToExhb(){
        $ci = &get_instance() ; 

        $ci->load->library('upload') ; 
        if(!$ci->upload->do_upload()){
            return null ; 
        }

        $data = $ci->upload->data(); 

        $today = date("Ymd") ; 

        $save_dir = $data['is_image'] == 1 ? './files/filebox/img/'.$today : './files/filebox/binary/'.$today ;

        !is_dir($save_dir) ? mkdir($save_dir,0777) : null ; 

        $dest = $save_dir.'/'.$data['file_name'] ; 
        rename($data['full_path'], $dest ) ;

        $data['full_path'] = $dest  ;
        $data['file_path'] = $save_dir ; 

        return $data ;
    } 

    public function upload($upload_field = 'userfile' ){
        $ci = &get_instance() ; 

        $ci->load->library('upload') ; 
        if(!$ci->upload->do_upload($upload_field)){
            return null ; 
        }

        $data = $ci->upload->data(); 

        $today = date("Ymd") ; 

        $save_dir = $data['is_image'] == 1 ? './files/filebox/img/'.$today : './files/filebox/binary/'.$today ;

        !is_dir($save_dir) ? mkdir($save_dir,0777) : null ; 

        $dest = $save_dir.'/'.$data['file_name'] ; 
        rename($data['full_path'], $dest ) ;

        $data['full_path'] = $dest  ;
        $data['file_path'] = $save_dir ; 
        $ret = $this->insertToDB($data) ; 

        return $ret ;
    } 

    public function get_extension($filename)
	{
		$x = explode('.', $filename);
		return '.'.end($x);
	}

    public function set_filename($path, $filename,$file_ext) {
	    mt_srand();
		$saved_filename = md5(uniqid(mt_rand())).$file_ext;

		if ( ! file_exists($path.$saved_filename)) {
			return $saved_filename;
		}

		$filename = str_replace($file_ext, '', $saved_filename );

		$new_filename = '';
		for ($i = 1; $i < 100; $i++) {
			if ( ! file_exists($path.$filename.$i.$file_ext)) {
				$new_filename = $filename.$i.$file_ext;
				break;
			}
		}

		if ($new_filename == '') {
			$this->set_error('upload_bad_filename');
			return FALSE;
		} else {
			return $new_filename;
		}
	}

    public function set_image_properties($fullpath = '') { 
		if (function_exists('getimagesize')) {
			if (FALSE !== ($D = @getimagesize($fullpath))) {
				$types = array(1 => 'gif', 2 => 'jpeg', 3 => 'png');

				$obj->image_width		= $D['0'];
				$obj->image_height		= $D['1'];
				$obj->image_type		= ( ! isset($types[$D['2']])) ? 'unknown' : $types[$D['2']];
				$obj->image_size_str	= $D['3'];  // string containing height and width

                return $obj ; 
			}
		}
	}

    protected function _filter_mime_type($mime_type){
        $png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array($mime_type, $png_mimes)) {
			return 'image/png';
		}

		if (in_array($mime_type, $jpeg_mimes)) {
			return 'image/jpeg';
		} 

        return $mime_type ; 
    }

    public function is_image($file_type) { 
		$png_mimes  = array('image/x-png');
		$jpeg_mimes = array('image/jpg', 'image/jpe', 'image/jpeg', 'image/pjpeg');

		if (in_array($file_type, $png_mimes)) {
			$file_type = 'image/png';
		}

		if (in_array($file_type, $jpeg_mimes)) {
			$file_type = 'image/jpeg';
		}

		$img_mimes = array(
							'image/gif',
							'image/jpeg',
							'image/png',
						);

		return (in_array($file_type, $img_mimes, TRUE)) ? TRUE : FALSE;
	}

    public function createUploadPath($is_image = 0){
        $today = date("Ymd") ; 
        $save_dir = $is_image == 1 ? './files/filebox/img/'.$today : './files/filebox/binary/'.$today ; 
        !is_dir($save_dir) ? mkdir($save_dir,0777) : null ;

        return $save_dir ; 
    } 

    public function uploadFileFromServer($file_full_path) { 
        $ci = &get_instance() ; 
        $ci->load->model('filebox/filebox_model','filebox'); 

        $original_file_name  = basename($file_full_path) ; 
        $file_ext = $this->get_extension($original_file_name) ; 

        $file_type = $this->_file_mime_type($file_full_path) ; 
        $file_type = $this->_filter_mime_type($file_type) ; 
        $is_image = $this->is_image($file_type) ;

        $args = new stdClass ; 

        if($is_image){
            $obj = $this->set_image_properties($file_full_path) ;

            $args->image_type = $obj->image_type ; 
            $args->image_height = $obj->image_height ; 
            $args->image_width = $obj->image_width ; 
        }

        $save_dir = $this->createUploadPath($is_image) ; 
        $encrypted_file_name  = $this->set_filename($save_dir,$file_full_path,$file_ext) ; 

        $dest = $save_dir.'/'.$encrypted_file_name; 
        rename($file_full_path, $dest ) ; 

        $args->file_type = $file_type ; 
        $args->encrypted_file_name = $encrypted_file_name ;
        $args->original_file_name = $original_file_name ;
        $args->file_ext = $file_ext ; 
        $args->file_size_kb = round(filesize($dest)/1024);
        $args->is_image = $is_image ;
        
        $args->full_path = $dest ;
        $args->file_path = $save_dir ; 
        $args->ip_address = '' ; 

        $ret_data = $ci->filebox->insert($args) ;
        return $ret_data ; 
    }

    public function insertToDB($upload_data){
        $ci = &get_instance() ; 
        $ci->load->model('filebox/filebox_model','filebox'); 
        
        $args->file_type = $upload_data['file_type'] ; 
        $args->encrypted_file_name = $upload_data['file_name'] ; 
        $args->original_file_name = $upload_data['orig_name'] ; 
        $args->file_ext = $upload_data['file_ext'] ; 
        $args->file_size_kb = $upload_data['file_size'] ; 
        //$args->tags = $upload_data['tags'] ; 
        $args->is_image = $upload_data['is_image'] ;
        $args->image_type = $upload_data['image_type'] ; 
        $args->image_height = $upload_data['image_height'];
        $args->image_width = $upload_data['image_width']; 
        $args->full_path = $upload_data['full_path']; 
        $args->file_path = $upload_data['file_path']; 
        $args->ip_address = '' ; 

        $ret_data = $ci->filebox->insert($args) ;

        return $ret_data ;
    } 

    protected function _file_mime_type($full_path) {
		if ( (float) substr(phpversion(), 0, 3) >= 5.3 && function_exists('finfo_file') ) {
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if ($finfo !== FALSE) {
				$file_type = $finfo->file($full_path); 
				if (strlen($file_type) > 1)
				{
					//$this->file_type = $file_type;
					return $file_type;
				}
			}
		}

		if (function_exists('mime_content_type'))
		{
			//$this->file_type = @mime_content_type($file['tmp_name']);
			return @mime_content_type($file['tmp_name']) ;
		} 
		
		if (DIRECTORY_SEPARATOR !== '\\' && function_exists('exec'))
		{
			$output = array();
			@exec('file --brief --mime-type ' . escapeshellarg($file['tmp_path']), $output, $return_code);
			if ($return_code === 0 && strlen($output[0]) > 0) // A return status code != 0 would mean failed execution
			{
				//$this->file_type = 
				return rtrim($output[0]);
			}
		}

		//$this->file_type = $file['type'];
	}
}
