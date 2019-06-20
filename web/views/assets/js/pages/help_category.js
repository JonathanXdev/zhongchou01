$(function(){
    $('.category_name').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'name',
           title: '分类名称'
    });
    $('.category_sort').editable({
        url: editurl,
        type: 'text',
        pk: 1,
        name: 'sort',
        title: '分类名称'
    });
    $('.category_ndesc').editable({
           url: editurl,
           type: 'textarea',
           pk: 1,
           name: 'ndesc',
           title: '分类描述'
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