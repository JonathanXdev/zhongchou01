$(function(){
    $('.march-title').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'title',
           title: '输入进展标题'
    });
    $('.march-nfrom').editable({
        url: editurl,
        type: 'text',
        pk: 1,
        name: 'nfrom',
        title: '输入进展来源'
    });
	$('.march-content').editable({
           url: editurl,
           type: 'textarea',
           pk: 1,
           name: 'content',
           title: '输入进展内容'
    });
	$('.march-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#march-sub').on('click',function(){
	   $.post(editurl, $("#march-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
});