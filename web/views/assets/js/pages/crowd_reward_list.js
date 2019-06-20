var page=1;
var limit=50;
var reward_status='all';
var reward_id=0;
var key='';
var lock=false;
$( document ).ready(function() {
	loadorder();
	$('.reward_order').on('click',function(){
		$('.orderbox-nav li').removeClass('active');
		$(this).parent('li').addClass('active');
		lock=false;
		page=1;
		key='';
		reward_status=$(this).data('rst');
		reward_id=$(this).data('aid');
		loadorder();
	})
	$('.search_order').on('click',function(){
		lock=false;
		page=1;
		reward_status='all';
		reward_id=0;
		key=$('.search_order_input').val();
		loadorder();
	})
	$('.reward_sub').on('click',function(){
		$.post($('#order-reward-id').attr('action'),$('#order-reward-id').serialize(),function(res){
			toastr.success(res.msg);
			$('.crowd_order_reward_md').modal('hide');
		},'json')
	})
    $('#download-btn').on('click',function(){
        var fst=$('input[name=fst]').val();
        var fed=$('input[name=fed]').val();
        var dpage=$('select[name=dpage]').val();
        window.open(downurl+'&crowd_id='+crowd_id+'&reward_id='+reward_id+'&reward_status='+reward_status+(fst!=''?'&fst='+fst:'')+(fed!=''?'&fed='+fed:'')+(dpage!=''?'&dpage='+dpage:''));
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
		data:{reward_status:reward_status,key:key,page:page,limit:limit,crowd_id:crowd_id,reward_id:reward_id},
		success:function(data){
			if(data.id==1){
				$('.report_box').html(reporthtml(data.data));
				$('.pageinfo').html('第'+page+'/'+data.page[0]+'页 共'+data.page[1]+'条');
				var dpagehtml='<option disabled>选择包</option>';
				var climit=100;
				for(var di=0;di*climit<data.page[1];di++){
                    var sl=(di)*climit;
                    var ttnum=di+1;
                    var etnum=sl+climit>data.page[1]?data.page[1]:sl+climit;
                    dpagehtml+='<option value="'+ttnum+'">第'+ttnum+'到'+etnum+'条</option>';
				}
				$('select[name=dpage]').html(dpagehtml);
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
		html+='<button class="btn btn-default select_order_reward" data-toggle="tooltip" data-placement="top" title="选中处理回报" data-original-title="选中处理回报"><i class="fa fa-gift"></i></button>';
        html+='</div>';
        html+='<div class="btn-group">';
        html+='<a class="btn btn-default" onclick="gopage(\'pre\')"><i class="fa fa-angle-left"></i></a>';
        html+='<a class="btn btn-default" onclick="gopage(\'next\')"><i class="fa fa-angle-right"></i></a>';
        html+='</div>';
        html+='</th>';
        html+='</tr>';
        html+='<tr>';
        html+='<th></th>';
        html+='<th>用户ID</th>';
        html+='<th>昵称</th>';
    	html+='<th>数量</th>';
        html+='<th>用户地址</th>';
        html+='<th>额外信息</th>';
		html+='<th>回报类型</th>';
        html+='<th>处理时间</th>';
        html+='<th>操作</th>';
        html+='</tr>';
        html+='</thead>';
        html+='<tbody>';
                    
	for(o in d){
		html+='<tr class="report_li" data-aid="'+d[o].reward_id+'" data-st="'+d[o].status+'" data-rst="'+d[o].reward_status+'">'
        html+='<td class="hidden-xs"><span><div class="checker"><span><input type="checkbox" class="checkbox-order" value="'+d[o].id+'"></span></div></span></td>';
        html+='<td class="hidden-xs">#'+d[o].id+'</td>';
        html+='<td class="hidden-xs">'+d[o].nickname+'</td>';
        html+='<td class="hidden-xs">'+d[o].reward_num+'</td>';
        if(d[o].reward_type=='0'){
		html+='<td class="hidden-xs"><address><strong>'+d[o].uaprovince+','+d[o].uacity+'</strong><br>'+d[o].uaother+'<br>'+d[o].uaname+',邮编 '+d[o].uazipcode+'<br><abbr title="电话">P:</abbr>'+d[o].uaphone+'</address></td>';
        }else{
            html+='<td class="hidden-xs">无</td>';
		}
		var zdname=d[o].reward_fd;
		var zdvalue=d[o].reward_other;
		var zdgrptxt='';
		if(zdname!=''){
			for(var oo in zdname){
            	zdgrptxt+=zdname[oo]+':'+(!zdvalue[oo]?'空':zdvalue[oo])+'<br/>'
			}
        }
		html+='<td>'+(zdgrptxt==''?'无':zdgrptxt)+'</td>';
		html+='<td>'+(d[o].reward_type==1?'虚拟':'实物')+'</td>';
        html+='<td>'+(d[o].reward_time==0?'无':d[o].ureward_time)+'</td>';
		if(d[o].reward_id>0 && d[o].status=='1'){
			if(d[o].reward_status==0){
				rewardtit="处理回报";
				rewardcls="btn-success order_reward"
			}else{
				rewardtit="已回报";
				rewardcls="btn-default disabled"
			}
		}else{
			rewardtit="无需回报";
			rewardcls="btn-default disabled"
		}
        html+='<td><button class="btn '+rewardcls+'" data-oid="'+d[o].id+'" data-aid="'+d[o].reward_id+'">'+rewardtit+'</button></td>';
		html+='</tr>';
	}
	html+='</tbody>';
    html+='</table>';
	return html;
}