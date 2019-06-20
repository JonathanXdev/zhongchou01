$(function(){
    $('.category-order').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'norder',
           title: '输入排序值'
    });
    $('.category-name').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'name',
           title: '输入分类名称'
    });
	$('.category-content').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'content',
           title: '输入分类内容'
    });
	$('.category-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#category-sub').on('click',function(){
	   $.post(editurl, $("#category-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
});