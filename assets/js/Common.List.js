Common.ListPanel = function(config){
    var url = config.url, 
        pagination = config.pagination||{list_count:20,page_count:10,endless:true} , 
        cpage = 1 , 
        panel_body = config.panel_body ,
        target_id = config.target_id , 
        item_config = config.item_config , 
        target_selector = null ,
        items = [] , 
        $items_area = null ,
        $body = null , 
        req = null ,
        tmpl = config.tmpl ; 

    req = { 
        xhrCall:function(param){
            
            $.getJSON(url,param ,function(data){
                that.setItems(data.items) ; 

                var items = that.getItems() ; 
                var item = null ; 
                var field_name  ;  

                for(var i = 0 ; i < items.length ; i++){ 
                    item = items[i] ; 
                    var fields =  item_config.display_fields ;
                    var obj = {} ; 

                    for(var j = 0 ; j <fields.length ;j++){
                        field_name =fields[j].name ; 
                        obj[field_name] = item[field_name] ; 
                        if(fields[j].data_format){
                            obj[field_name] =fields[j].data_format(item[field_name]) ;  
                        }
                    }

                    var html = Common.Util.printf(item_config.tmpl,obj); 

                    //html = '<li style="'+item_config.item_wrapper_css+'" >'+html+'</li>' ; 
                    
                    $(html).appendTo($body) ; 

                    //$(html).appendTo($body.find('ul')).bind('click',function(){ 
                        //that.selectItem(items[m]) ; 
                    //}); 
                    
                } 
            }); 
        },
        next : function(){
            cpage = cpage+1 ; 

            var data = { 
                //search_keyword : search_keyword ,
                page : cpage 
            }; 

            req.xhrCall( data ) ;
            return false ; 
        },
        prev : function(){ 
            if(cpage <=1){
                cpage = 1 ;  
            }else{
                cpage = cpage-1 ; 
            }

            var data = { 
                search_keyword : search_keyword ,
                page : cpage 
            };

            req.xhrCall( data ) ; 

            return false ; 
        },
        search : function(){ 
            var data = { 
                search_keyword : search_keyword
            };


            req.xhrCall(data) ; 
            return false ; 
        }
    }


    var that = {} ; 

    that.render = function(){
        var $el = $(tmpl) ; 
        $el.appendTo($('#'+target_id)) ; 
        $body = $el.find('#_items_area') ; 


        $('<div><a class="btn btn-block css3pie"> 더보기</a> </div>').appendTo($('#'+target_id)).click(function(){
            req.next( ) ;   
        }); 


        req.xhrCall( ) ;
    }; 

    that.call  = function(){

    }; 

    that.setItems = function(_items){
        items = _items ; 
    };

    that.getItems = function(){
        return items ; 
    } 

    return that ; 
}; 
