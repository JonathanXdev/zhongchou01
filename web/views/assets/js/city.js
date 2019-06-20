function slscity(dom){
    var a1=$('.a1-sls').data('val');
    var a2=$('.a2-sls').data('val');
    var a3=$('.a3-sls').data('val');
    cityarr(a1,a2,a3);
    $('.a1-sls').on('change',function(){
        var a1=$(this).val();
        cityarr(a1);
        dom.val(a1);
    })
    $('.a2-sls').on('change',function(){
        var a1=$('.a1-sls').val();
        var a2=$(this).val();
        cityarr(a1,a2);
        dom.val(a2);
    })
    $('.a3-sls').on('change',function(){
        var a3=$(this).val();
        dom.val(a3);
    })
}
function cityarr(a1,a2,a3){
    $('.a1-sls').html('');
    var $a1arr= getcity();
    $('.a1-sls').html('');
    for(var o in $a1arr){
        var a1sls='';
        if(a1!='' && a1==$a1arr[o].value){
            a1sls='selected';
        }else if(o==0){
            a1sls='selected';
        }
        $('.a1-sls').append('<option '+a1sls+' value="'+$a1arr[o].value+'">'+$a1arr[o].text+'</option>');
    }
    var $a2arr= getcity(!a1?$a1arr[0].value:a1);
    $('.a2-sls').html('');
    for(var o1 in $a2arr){
        var a2sls='';
        if(a2!='' && a2==$a2arr[o1].value){
            a2sls='selected';
        }else if(o1==0){
            a2sls='selected';
        }
        $('.a2-sls').append('<option '+a2sls+' value="'+$a2arr[o1].value+'">'+$a2arr[o1].text+'</option>');
    }
    var $a3arr= getcity(!a1?$a1arr[0].value:a1,!a2?$a2arr[0].value:a2);
    $('.a3-sls').html('');
    for(var o2 in $a3arr){
        var a3sls='';
        if(a3!='' && a3==$a3arr[o2].value){
            a3sls='selected';
        }else if(o2==0){
            a3sls='selected';
        }
        $('.a3-sls').append('<option '+a3sls+' value="'+$a3arr[o2].value+'">'+$a3arr[o2].text+'</option>');
    }

}
function getcity(a1,a2,a3){
    var arr=[];
    if(!a1 && !a2 && !a3){
        for(var o1 in cityData3){
            arr[o1]={text:cityData3[o1].text,value:cityData3[o1].value}
        }
    }else if(a1 && !a2 && !a3){
        for(var o1 in cityData3){
            if(cityData3[o1].value==a1){
                var arr2=cityData3[o1].children;
                for(var o2 in arr2){
                    arr[o2]={text:arr2[o2].text,value:arr2[o2].value}
                }
            }
        }
    }else if(a1 && a2 && !a3){
        for(var o1 in cityData3){
            if(cityData3[o1].value==a1){
                var arr2=cityData3[o1].children;
                for(var o2 in arr2){
                    if(arr2[o2].value==a2){
                        var arr3=arr2[o2].children;
                        for(o3 in arr3){
                            arr[o3]={text:arr3[o3].text,value:arr3[o3].value}
                        }
                    }

                }
            }
        }
    }
    return arr;
}