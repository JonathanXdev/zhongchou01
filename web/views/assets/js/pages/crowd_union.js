$(document).ready(function() {
  $('.union-change-md').on('click',function(){
	  $('input[name=union_id]').val($(this).data('id'));
	  $('select[name=status]').val($(this).data('st'));
	  $('.bs-bmsh-modal-sm').modal();
	  $('.union-form-sub').on('click',function(){
		$.post($("#union-form").attr('action'), $("#union-form").serialize(),function(data){
			 if(data.id==1){
				location.reload();
			 }else{
				toastr.warning(data.msg)
			 }
		   }, "json");
	  })
  })
  $('.bmsh-dismiss').on('click',function(){
	  $('input[name=union_id]').val('');
	  $('select[name=status]').val('');
	$('.bs-bmsh-modal-sm').modal('hide');
  })
});