$(function(){
    $('.notice-title').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'title',
           title: '输入公告标题'
    });
	$('.notice-content').editable({
           url: editurl,
           type: 'textarea',
           pk: 1,
           name: 'content',
           title: '输入公告内容'
    });
	$('.notice-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#notice-sub').on('click',function(){
	   $.post(editurl, $("#notice-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
});