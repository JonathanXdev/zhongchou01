var page=1;
var limit=20;
var status='all';
var key='';
var lock=false;
$( document ).ready(function() {
	loadorder();
	$('.filter_order').on('click',function(){
		$('.orderbox-nav li').removeClass('active');
		$(this).addClass('active');
		lock=false;
		page=1;
		key='';
		status=$(this).data('st');
		loadorder();
	})
	$('.search_order').on('click',function(){
		lock=false;
		page=1;
		status='all';
		key=$('.search_order_input').val();
		loadorder();
	})
	$('.reply_sub').on('click',function(){
	    $('.do-loading').show();
		$.post($('#order-reply-id').attr('action'),$('#order-reply-id').serialize(),function(res){
			if(res.id==1){
                toastr.success(res.msg);
                lock=false;
                loadorder();
			}else{
                toastr.error(res.msg);
			}
            $('.do-loading').hide();
			$('.crowd_order_reply_md').modal('hide');
		},'json')
	})
});
function gopage(f){
	if(f=='pre'){
		if(page>1){
			page--;
			lock=false;
			loadorder()
		}
	}else{
		page++;
		loadorder()
	}
}
function loadorder(){
	if(lock===true){
		toastr.info('没有更多啦');
		return false;
	}
	$('.report_box').html('');
	$.ajax({
		url:loadurl,
		type:'get',
		dataType:'json',
		data:{status:status,key:key,page:page,limit:limit,crowd_id:crowd_id,union_id:union_id,team_id:team_id},
		success:function(data){
			if(data.id==1){
				$('.report_box').html(reporthtml(data.data));
				$('.pageinfo').html('第'+page+'/'+data.page[0]+'页 共'+data.page[1]+'条');
			var hiddenOrderOptions = function() {
				if($('.checkbox-order:checked').length) {
					$('.order-hidden-options').css('display', 'inline-block');
				} else {
					$('.order-hidden-options').css('display', 'none');
				};
			};
			hiddenOrderOptions();
			$('.check-order-all').click(function () {
				$('.checkbox-order').click();
			});
			
			$('.checkbox-order').each(function() {
				$(this).click(function() {
					if($(this).closest('tr').hasClass("checked")){
						$(this).closest('tr').removeClass('checked');
						hiddenOrderOptions();
					} else {
						$(this).closest('tr').addClass('checked');
						hiddenOrderOptions();
					}
				});
			});
			$('.select_order_reply').on('click',function(){
				var changeid='';
				var num=0
				$('.checkbox-order:checked').each(function(){
					if($(this).parents('.report_li').data('st')=='1'){
						if(num==0){
							changeid+=$(this).val();
						}else{
							changeid+='|'+$(this).val();
						}
						num++;
					}
				})
				$('input[name=order_id]').val(changeid);
				$('.crowd_order_reply_md').modal();
			});
			$('.download_order').on('click',function(){
                var changeid='';
                var num=0
                $('.checkbox-order:checked').each(function(){
                    if($(this).parents('.report_li').data('st')=='1'){
                        if(num==0){
                            changeid+=$(this).val();
                        }else{
                            changeid+='|'+$(this).val();
                        }
                        num++;
                    }
                });
                openpost(downloadurl,{changeid:changeid,tp:0});
            })
			$('.select_order_reward').on('click',function(){
				var changeid='';
				var num=0
				$('.checkbox-order:checked').each(function(){
					if($(this).parents('.report_li').data('aid')>0 && $(this).parents('.report_li').data('st')=='1' && $(this).parents('.report_li').data('rst')=='0'){
						if(num==0){
							changeid+=$(this).val();
						}else{
							changeid+='|'+$(this).val();
						}
						num++;
					}
				})
				$('input[name=order_id]').val(changeid);
				$('.crowd_order_reward_md').modal();
			})
			$('.order_reply').on('click',function(){
				$('input[name=order_id]').val($(this).data('oid'));
				$('.crowd_order_reply_md').modal();
			})
			$('.order_reward').on('click',function(){
				$('input[name=order_id]').val($(this).data('oid'));
				$('.crowd_order_reward_md').modal();
			})
			$('.order-md-dismiss').on('click',function(){
				$('input[name=order_id]').val('');
			})
			$('.refresh_order').on('click',function(){
				lock=false;
				loadorder();
			})
				if(page>=data.page[0]){
				lock=true;
				}
			}else{
				lock=true;
				toastr.info('没有更多啦');
				return false;
			}
		}
	})
}
function reporthtml(d){
	var html='';
		html+='<table class="table">';
        html+='<thead>';
        html+='<tr>';
        html+='<th colspan="1" class="hidden-xs">';
        html+='<span><div class="checker"><span><input type="checkbox" class="check-order-all"></span></div></span>';
        html+='</th>';
        html+='<th class="text-right" colspan="8">';
        html+='<span class="text-muted m-r-sm pageinfo"></span>';
        html+='<button class="btn btn-default m-r-sm refresh_order" data-toggle="tooltip" data-placement="top" title="" data-original-title="刷新"><i class="fa fa-refresh"></i></button>';
        html+='<div class="btn-group m-r-sm order-hidden-options" style="display: none;">';
        html+='<button class="btn btn-default select_order_reply" data-toggle="tooltip" data-placement="top" title="选中退款" data-original-title="选中退款"><i class="fa fa-reply"></i></button>';
        html+='</div>';
        html+='<div class="btn-group">';
        html+='<a class="btn btn-default download_order" data-toggle="tooltip" data-placement="top" title="下载全部订单" data-original-title="下载全部订单"><i class="fa fa-cloud-download"></i></a>';
        html+='</div>';
        html+='<div class="btn-group">';
        html+='<a class="btn btn-default" onclick="gopage(\'pre\')"><i class="fa fa-angle-left"></i></a>';
        html+='<a class="btn btn-default" onclick="gopage(\'next\')"><i class="fa fa-angle-right"></i></a>';
        html+='</div>';
        html+='</th>';
        html+='</tr>';
        html+='<tr>';
        html+='<th></th>';
        html+='<th>ID</th>';
		html+='<th>用户ID</th>';
        html+='<th>昵称</th>';
        html+='<th>支付</th>';
        html+='<th>微信订单号</th>';
        html+='<th>支持金额</th>';
        html+='<th>支持时间</th>';
        html+='<th>支付时间</th>';
        html+='<th>操作</th>';
        html+='</tr>';
        html+='</thead>';
        html+='<tbody>';
                    
	for(o in d){
		html+='<tr class="report_li" data-aid="'+d[o].reward_id+'" data-st="'+d[o].status+'" data-rst="'+d[o].reward_status+'">'
        html+='<td class="hidden-xs"><span><div class="checker"><span><input type="checkbox" class="checkbox-order" value="'+d[o].id+'"></span></div></span></td>';
        html+='<td class="hidden-xs">#'+d[o].id+'</td>';
        html+='<td class="hidden-xs">#'+d[o].user_id+'</td>';
        html+='<td class="hidden-xs">'+d[o].nickname+'</td>';
		var statstr='';
		var replytit="退款";
		var replycls="btn-default disabled"
		if(d[o].status==0){
			statstr='<i class="fa fa-star icon-state-warning"></i> 未支付';
		}else if(d[o].status==1){
			replytit="退款";
			replycls="btn-default order_reply"
			statstr='<i class="fa fa-star icon-state-success"></i> 已支付';
		}else if(d[o].status==-1){
			statstr='<i class="fa fa-star"></i> 已取消';
		}else if(d[o].status==-2){
			statstr='<i class="fa fa-star icon-state-info"></i> 已退款';
		}
		html+='<td class="hidden-xs">'+statstr+'</td>';
        html+='<td>'+d[o].wx_orderid+'</td>';
        html+='<td>￥'+d[o].money+'</td>';
        html+='<td>'+d[o].uaddtime+'</td>';
        html+='<td>'+d[o].upaytime+'</td>';
        html+='<td><button class="btn '+replycls+'" data-oid="'+d[o].id+'">'+replytit+'</button></td>';
		html+='</tr>';
	}
	html+='</tbody>';
    html+='</table>';
	return html;
}