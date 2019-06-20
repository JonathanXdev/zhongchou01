$(document).ready(function() {
	$('#sub-help').on('click',function(){
		$.post($('#sub-help-form').attr('action'),$('#sub-help-form').serialize(),function(res){
			if(res.id==1){
				toastr.success(res.msg);
				location.href=jumpurl;
			}else{
				toastr.error(res.msg);
			}
		},'json')
	})
	$('.del_help').on('click',function(){
		var delid=$(this).data('id');
		$.getJSON(delurl,{id:delid},function(d){
			if(d.id==1){
				location.reload();
			}
		})
	})
});