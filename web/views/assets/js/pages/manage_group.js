$(function(){
    $('.group_name').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'group_name',
           title: '部门名称'
    });
    $('.group_info').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'group_info',
           title: '部门信息'
    });
	$('.group-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#group-sub').on('click',function(){
	   $.post(editurl, $("#group-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
});