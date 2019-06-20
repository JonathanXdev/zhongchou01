$(function(){
    $('.rule-name').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'name',
           title: '输入入口名称'
    });
    $('.rule-key').editable({
        url: editurl,
        type: 'text',
        pk: 1,
        name: 'key',
        title: '输入入口关键词'
    });
    $('.rule-content').editable({
        url: editurl,
        type: 'textarea',
        pk: 1,
        name: 'content',
        title: '输入入口描述'
    });
    $('.rule-link').editable({
        url: editurl,
        type: 'text',
        name: 'link',
        title: '输入入口链接',
        success:function(d){
        	location.reload()
		}
    });
    $('.rule-status').editable({
        url: editurl,
        source: [
            {value: 1, text: '开'},
            {value: 0, text: '关'}
        ],
        type: 'select',
        pk: 1,
        name: 'status',
        title: '开关控制'
    });
	$('.rule-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#rule-sub').on('click',function(){
	   $.post(editurl, $("#rule-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
	$('#sls-link').on('change',function(){
		if($(this).val()==0){
            $('input[name=link]').val('');
			$('input[name=link]').attr('type','text');
		}else{
            $('input[name=link]').attr('type','hidden');
            $('input[name=link]').val($(this).val());
		}
	})
});