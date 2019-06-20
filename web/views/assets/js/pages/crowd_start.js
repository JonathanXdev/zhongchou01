;(function($){
    addfd();
	$.fn.datetimepicker.dates['zh-CN'] = {
			days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
			daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
			daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
			months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
			monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
			today: "今天",
			suffix: [],
			meridiem: ["上午", "下午"]
	};
}(jQuery));
$(document).ready(function() {
    $('.date-picker').datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        language: 'zh-CN'
    });
    $('.rewardbox .panel-remove').on('click',function(){
    	$(this).parents('.rewardbox').remove();
	})
    $('#sub-crowd-all').on('click',function(e){
		var allarr=[];
		var ntype=$(this).data('ntype');
        var d={ntype:ntype};
		var founder= $('#subcrowd-founder').serializeArray();
		$.each(founder, function () {
			if (d[this.name] !== undefined) {
				if (!d[this.name].push) {
					d[this.name] = [d[this.name]];
				}
				d[this.name].push(this.value || '');
			} else {
				d[this.name] = this.value || '';
			}
		});
		var baseinfo= $('#subcrowd-baseinfo').serializeArray();
		$.each(baseinfo, function () {
			if (d[this.name] !== undefined) {
				if (!d[this.name].push) {
					d[this.name] = [d[this.name]];
				}
				d[this.name].push(this.value || '');
			} else {
				d[this.name] = this.value || '';
			}
		});
		var content= $('#subcrowd-content').serializeArray();
		$.each(content, function () {
			if (d[this.name] !== undefined) {
				if (!d[this.name].push) {
					d[this.name] = [d[this.name]];
				}
				d[this.name].push(this.value || '');
			} else {
				d[this.name] = this.value || '';
			}
		});
		if(ntype=='0'){
			var rewards= $('#subcrowd-rewards').serializeArray();
			$.each(rewards, function () {
				if (d[this.name] !== undefined) {
					if (!d[this.name].push) {
						d[this.name] = [d[this.name]];
					}
					d[this.name].push(this.value || '');
				} else {
					d[this.name] = this.value || '';
				}
			});
        }
		//console.log(d);
		//return false;
        $.ajax({
            url:subcrowdurl,
            data:d,
            type:'POST',
            dataType:'json',
            success:function(d){
                console.log(d);
                if(d.id==1){
                    toastr.success(d.msg);
                    if(d.link){
                        setTimeout(function(){
                            location.href=d.link;
                        },2000)
                    }
                }else{
                    toastr.info(d.msg)
                }
                return false;
            }
        })
        return false;
    })
    $('.addrewardbox').on('click',function(){
        $('#subcrowd-rewards').append(rewardhtml(rewardnum));
        addfd();
        $('#reward_'+rewardnum+' .panel-remove').on('click',function(){
            $(this).parents(".rewardbox").remove()
        })
        rewardnum++;
    })
		var authnum=1;
	$('.addauthbtn').on('click',function(){
		$('input[name=authnum]').val('0');
		$('input[name=authname]').val('');
		$('input[name=authimage]').val('');
		$('select[name=authstatus]').val('');
		$('.authmodal').modal();
	})
	$('#add-auth').on('click',function(){
		var rauthnum=$('input[name=authnum]').val();
		var authname=$('input[name=authname]').val();
		var authimg=$('input[name=authimage]').val();
		var authstatus=$('select[name=authstatus]').val();
		if(authname=='' || authimg=='' || authstatus==''){
			toastr.info('请确认全部项目都已填写或设置完成')
			return false;
		}
		if(authstatus==0){
			statustitle='未核验';
		}else if(authstatus==1){
			statustitle='已核验';
		}
		if(rauthnum>0){
			var ahtml='<td>'+authname+'<input type="hidden" name="authname[]" class="authname" value="'+authname+'"></td>'+
                   '<td><img src="'+window.sysinfo.attachurl+authimg+'"  width="80"><input type="hidden" name="authimg[]" class="authimg" value="'+authimg+'"></td>'+
                   '<td>'+statustitle+'<input type="hidden" name="authstatus[]" value="'+authstatus+'" class="authstatus"></td>'+
                   '<td>'+
                   '<div class="btn-group" role="group" aria-label="Justified button group">'+
                   '<a href="#" class="btn btn-info edit_auth" role="button" data-id="'+rauthnum+'" onclick="showauthmodal('+rauthnum+')">编辑</a>'+
                   '<a href="#" class="btn btn-primary del_auth" role="button" data-id="'+rauthnum+'" onclick="delauth('+rauthnum+')">移除</a>'+
                   '</div>'+
                   '</td>';
			$('#auth-'+rauthnum).html(ahtml);
		}else{
		var ahtml='<tr id="auth-'+authnum+'">'+
                   '<td>'+authname+'<input type="hidden" name="authname[]" class="authname" value="'+authname+'"></td>'+
                   '<td><img src="'+window.sysinfo.attachurl+authimg+'" width="80"><input type="hidden" name="authimg[]" class="authimg" value="'+authimg+'"></td>'+
                   '<td>'+statustitle+'<input type="hidden" name="authstatus[]" value="'+authstatus+'" class="authstatus"></td>'+
                   '<td>'+
                   '<div class="btn-group" role="group" aria-label="Justified button group">'+
                   '<a href="#" class="btn btn-info edit_auth" role="button" data-id="'+authnum+'" onclick="showauthmodal('+authnum+')">编辑</a>'+
                   '<a href="#" class="btn btn-primary del_auth" role="button" data-id="'+authnum+'" onclick="delauth('+authnum+')">移除</a>'+
                   '</div>'+
                   '</td>'+
                   '</tr>';
		$('#auth-box tbody').append(ahtml);
		authnum++;
		}
		$('.authmodal').modal('hide');
		$('input[name=authnum]').val('0');
		$('input[name=authname]').val('');
		$('input[name=authimage]').val('');
		$('input[name=authstatus]').val('');
	});

});
	function showauthmodal(num){
		$('.authmodal').modal();
		var tdom=$('#auth-'+num);
		$('input[name=authnum]').val(num);
		$('input[name=authname]').val(tdom.find('.authname').val());
		$('input[name=authimage]').val(tdom.find('.authimg').val());
		$('select[name=authstatus]').val(tdom.find('.authstatus').val());
	}
	function delauth(num){
		var tdom=$('#auth-'+num);
		tdom.remove();
	}
	function addfd(){
        $('.add_fd').unbind("click");
		$('.add_fd').on('click',function(){
			rwdid=$(this).data('id');
			var fdhtml='<div class="fd_b_'+fdnum+' row">'+
			'<div class="col-sm-6"><input type="text" class="form-control col-sm-6" name="reward_fd['+rwdid+'][]" value="" placeholder="字段名称，如“手机号码”"></div>'+
            '<div class="col-sm-3"><button type="button" class="btn btn-danger btn-addon m-b-sm remove_fd" data-id="'+fdnum+'" data-rid="'+rwdid+'"><i class="fa fa-remove"></i> 移除</button></div>'+
            '</div>';
			$('#reward_'+rwdid).find('.fd_box').append(fdhtml);
            fdnum++;
            removefd();
		})
	}
	function removefd(){
        $('.remove_fd').on('click',function(){
            var fdid=$(this).data('id');
            var rid=$(this).data('rid');
            $('#reward_'+rid).find('.fd_b_'+fdid).remove();
        })
	}