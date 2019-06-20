$.fn.editable.defaults.mode = 'inline' ;
$(function(){
    $('.role_name').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'role_name',
           title: '部门名称'
    });
    $('.role_info').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'role_info',
           title: '部门信息'
    });
	$('.role_sls').editable({
	   url: editurl,
	   type: 'checklist',
       pk: 1,
	   name: 'role',
       source:rolemenu
    }); 
	$('.role-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#role-sub').on('click',function(){
	   $.post(editurl, $("#role-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
});