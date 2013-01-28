<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class RSSLib {

    var $feed_uri ; 
    var $data ; 
    var $channel_data ;
    var $feed_unavailable ; 
    var $cache_file ; 
    var $write_cache_flag = false ; 
    var $cache_dir ; 

    function __construct(){ 

    } 

    function init($params){
        $this->CI = &get_instance() ; 
        $this->cache_dir = ($this->CI->config->item('cache_path')=='')? BASEPATH.'cache/' : $this->CI->config->item('cache_path') ;
        $this->cache_life = $params['life'] ; 
        $this->feed_uri = $params['feed_uri'] ; 
        $this->current_feed['title'] = '' ; 
        $this->current_feed['description'] = '' ; 
        $this->current_feed['link'] = '' ; 
        $this->data = array() ; 
        $this->channel_data = array() ; 
    }

    function clear($feed_url=''){ 
        $filename = $this->cache_dir.'rss_parse_'.md5($this->feed_uri) ; 
        unlink($filename) ; 
        
    }

    function parse($feed_url=''){
        if($feed_url != ''){
            $this->feed_uri = $feed_url ; 
        }

        if($this->cache_life != 0 ){
            $filename = $this->cache_dir.'rss_parse_'.md5($this->feed_uri) ; 

            if(file_exists($filename)){
                $timediff = (time() - filemtime($filename));
                if($timediff <  ($this->cache_life * 60)){ 
                    $this->xml_to_object(implode('',file($filename))); 
                    return true ; 
                }

                $this->write_cache_flag = true ; 
            }else{ 
                $this->write_cache_flag = true ; 
            } 
        } 
         
        $rawFeed = file_get_contents($this->feed_uri) ; 

      
        if($this->xml_to_object($rawFeed) == false){
            return false ; 
        }

        if($this->write_cache_flag){
            if(! $fp = @fopen($filename,'wb')){
                echo "ERROR" ;
                return ; 
            }
            flock($fp,LOCK_EX) ;
            fwrite($fp,$rawFeed) ; 
            flock($fp,LOCK_UN); 
            fclose($fp) ; 
        }

        return true ; 

    }//end of function parse() 

    function getAllItems(){ 
        return $this->data ;
    }

    function getItems($num){
        $c = 0 ; 
        $return = array() ; 

        foreach($this->data as $item){
            $return[] = $item ; 
            $c++ ; 
            if($c == $num) break; 
        }

        return $return ; 
    }

    function &getChannelData(){
        $flag = false ; 
        if(!empty($this->channel_data)){ 
            return $this->channel_data ; 
        }else{
            return flag ; 
        }
    }

    function errorInResponse(){
        return $this->feed_unavailable; 
    }

    function xml_to_object($feed){
        $xml = @simplexml_load_string($feed) ; 

        if((is_object($xml==false)|| (sizeof($xml)) <= 0 )){
            return false ; 
        } 

        $this->channel_data['title'] = strval($xml->channel->title) ; 
        $this->channel_data['description'] = strval($xml->channel->description) ; 

        $tmp = $xml->item ; 

        if((is_object($tmp) == false) || (sizeof($tmp)) <= 0){
            $xml = $xml->channel ; 
        }

        if((is_object($xml==false)|| (sizeof($xml)) <= 0 )){
            return false ; 
        }

        foreach($xml->item as $item){ 
            $data = new stdClass ; 

            $data->title = strval($item->title) ; 
            $data->description = strval($item->description) ; 
            $data->pubDate = strval($item->pubDate) ; 
            $data->link =strval($item->link) ; 

            $this->data[] = $data ; 
        }

        return $this->data ; 
    }
} 

/* End of file RSSParser.php */
/* Location: ./application/libraries */
