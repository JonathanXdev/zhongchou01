$(document).ready(function() {
    $('#sub-system-btn').on('click',function(){
        gocfg($("#system-set"));
	});
    $('.sub-face-img').on('click',function(){
        gocfg($("#system-wx-slide"));
    })
    $('#wxmsg-btn').on('click',function(){
        gocfg($("#system-wxmsg"));
    })
    $('#smsarr-btn').on('click',function(){
        gocfg($("#system-smsarr"));
    })
    $('#smscfg-btn').on('click',function(){
        gocfg($("#system-smscfg"));
    });
    $('#sub-pay-btn').on('click',function(){
        gocfg($("#pay-set"));
    });
    $('.sub-set-btn').on('click',function(){
        var form=$(this).data('f');
        gocfg($(form));
        return false;
    });
});
function gocfg(form){
    $.post(form.attr('action'), form.serialize(),function(data){
        if(data.id==1){
            toastr.success(data.msg)
        }else{
            toastr.info(data.msg)
        }
    }, "json");
}