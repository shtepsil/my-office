$(function(){
	
    $('[name=beget]').on('click',function(){
        
        var $this = $(this),
            res = $('.res'),
			wrap = $('.btns'),
            load = wrap.find('img.loading'),
			name = $this.attr('name'),
            Data = {};
        
		res.html('');
		
		Data['name'] = name;
		
        cl(Data);
//        return;
        
        $.ajax({
            url: 'ajax/beget',
            type: 'post',
            dataType: 'json',
            cache: 'false',
            data: Data,
            beforeSend: function(){ load.fadeIn(100); }
        }).done(function(data){
//            res.html('Done<br>' + JSON.stringify(data));
            res.html('Done<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
        }).fail(function(data){
//            res.html('Fail<br>' + JSON.stringify(data));
            res.html('Fail<br><pre>' + prettyPrintJson.toHtml(data) + '</pre>');
        }).always(function(){
            load.fadeOut(100);
        });
        
    });
    
});


