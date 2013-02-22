<?php 
function bs_form_input($arr){
    $name = $arr['name'] ; 

    if(isset($arr['required']) &&  $arr['required'] ==TRUE){
        $badge = '<span class="badge badge-danger">필수</span>' ; 
    }else{ 
        $badge = '' ;
    }

    $placeholder = isset($arr['placeholder']) ? $arr['placeholder']: ''; 
    $cls = isset($arr['class']) ? $arr['class'] : '' ; 
    $filter = isset($arr['filter']) ? $arr['filter'] : ''; 
    $value = isset($arr['value']) ? $arr['value'] : ''; 
    $readonly = isset($arr['readonly']) ? "readonly=\"readonly\"" : "" ;  
    $input_tag = sprintf('<input type="text" name="%s" placeholder="%s" class="%s" filter="%s" value="%s" %s />',$name, $placeholder, $cls , $filter, $value, $readonly); 

    $ret = '<div class="control-group">'.
        '<label class="control-label">'.$badge.$arr['label_name'].'</label>'.
        '<div class="controls">'.
        $input_tag.
        '</div>'.
        '</div>'; 

    return $ret ; 

}

function bs_form_select($arr,$options){
    $input_tag = sprintf('<select type="text" name="%s" placeholder="%s" class="%s" filter="%s" >',$arr['name'], $arr['placeholder'], $arr['class'] , $arr['filter']); 

    foreach($options as $key => $option){
        $input_tag = $input_tag.'<option value="'.$option['value'].'">'.$option['text'].'</option>' ; 
    }

    $input_tag = $input_tag.'</select>' ; 

    $ret = '<div class="control-group">'.
        '<label class="control-label">'.$arr['label_name'].'</label>'.
        '<div class="controls">'.
        $input_tag.
        '</div>'.
        '</div>'; 

    return $ret ; 

}

function bs_pagination($first_page=1,$last_page=1,$cur_page=1,$base_link='#',$search_keyword=null){
    
}

?>
