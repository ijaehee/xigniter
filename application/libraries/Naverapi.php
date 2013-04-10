<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); 

class Naverapi {

    public function __construct(){ 
        $ci = &get_instance() ; 
        $ci->config->load('naver', FALSE, TRUE);
    }

    public function get_geocode($address){
        $geocode_list = $this->get_geocode_list($address) ; 

        if(count($geocode_list)){
            return $geocode_list[0] ; 
        }

        return null ; 
    }

    private function _get_data($url){
        $ch = curl_init() ; 
        curl_setopt($ch,CURLOPT_URL,$url) ; 
        curl_setopt($ch,CURLOPT_POST,0) ; 
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1) ; 
        $data = curl_exec($ch) ; 
        $xml = @simplexml_load_string($data) ; 
        $tmp = array() ; 
        if($xml->item){ 
            $tmp = $xml->item ; 
        }else if($xml->channel->item){ 
            $tmp = $xml->channel->item ; 

        }

        return $tmp ; 
    }

    public function get_blog_search($search_keyword,$page=1,$list_count=20){
        $ci = &get_instance() ; 
        $NAVER_API_KEY = $ci->config->item('naver_search_api_key') ; 

        $url = 'http://openapi.naver.com/search?key='.$NAVER_API_KEY.'&target=blog&display=20&query='.($search_keyword) ; 

        $list_count=20 ; 
        if($page){
            $url=$url.'&start='.(($page-1)*$list_count+1); 
        } 

        $result = $this->_get_data($url) ; 

        //$parsed_data = $this->xml_to_array($result) ; 

        $obj = array() ; 

        $obj['items'] = $result ; 

        return $obj ; 
    }

    public function get_image_search($search_keyword,$page=1,$list_count=20){
        $ci = &get_instance() ; 
        $NAVER_API_KEY = $ci->config->item('naver_search_api_key') ; 

        $url = 'http://openapi.naver.com/search?key='.$NAVER_API_KEY.'&target=image&display=100&query='.($search_keyword) ; 

        $list_count=20 ; 
        if($page){
            $url=$url.'&start='.(($page-1)*$list_count+1); 
        } 

        $result = $this->_get_data($url) ; 

        //$parsed_data = $this->xml_to_array($result) ; 

        $obj = array() ; 

        $obj['items'] = $result ; 

        return $obj ; 
    }

    public function get_geocode_list($address){ 
        $ci = &get_instance() ; 
        $NAVER_API_KEY = $ci->config->item('naver_map_api_key') ; 

        $search_keyword = str_replace(' ','%20',$address) ; 
        $url = 'http://openapi.map.naver.com/api/geocode.php?key='.$NAVER_API_KEY.'&encoding=utf-8&coord=latlng&query='.$search_keyword ; 

        $result = $this->_get_data($url) ;  
        return $this->parse_geocode($result) ; 
    } 

    public function xml_to_array($feed){
        return $feed ; 
    }

    public function parse_geocode($rss_data){
        foreach($rss_data as $item){ 
            $data = new stdClass ; 

            $data->lng = strval($item->point->x );
            $data->lat = strval($item->point->y );

            $arr[] = $data ; 
        }

        return $arr ;
    }

    public function xml_to_object($feed){

        $xml = @simplexml_load_string($feed) ; 

        $tmp = $xml->item ; 

        $arr = array() ; 

        foreach($tmp as $item){ 
            $data = new stdClass ; 

            $data->x = strval($item->point->x );
            $data->y = strval($item->point->y );

            $arr[] = $data ; 
        }

        return $arr ; 
    }
} 
