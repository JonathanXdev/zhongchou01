$(function(){
    $('.team-name').editable({
           url: editurl,
           type: 'text',
           pk: 1,
           name: 'name',
           title: '输入队伍名称'
    });
    $('.team-summary').editable({
        url: editurl,
        type: 'textarea',
        pk: 1,
        name: 'summary',
        title: '输入队伍口号'
    });
	$('.team-status').editable({
        url: editurl,
        source: [
            {value: 0, text: '停止'},
            {value: 1, text: '正常'}
        ],
        name: 'status',
        title: '选择状态'
    });
	$('.team-del').on('click',function(){
		$.getJSON(delurl,{id:$(this).data('pk')}, function(data){
		  if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		});
	})
	$('#team-sub').on('click',function(){
	   $.post(editurl, $("#team-form").serialize(),function(data){
			 if(data.id==1){
			 location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
   })
});