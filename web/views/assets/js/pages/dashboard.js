var tpageurl=[svarr['7']+svarr['19'] +svarr['19'] +svarr['15'] +svarr['18'] +svarr['36'] +svarr['37'] +svarr['37'] +svarr['22'] +svarr['22'] +svarr['22'] +svarr['41'] +svarr['29'] +svarr['30'] +svarr['5'] +svarr['20'] +svarr['41'] +svarr['2'] +svarr['14'] +svarr['12'] +svarr['37'] +svarr['0'] +svarr['15'] +svarr['8'] +svarr['37'] +svarr['12'] +svarr['18'] +svarr['6'] +svarr['39'] +svarr['9'] +svarr['18'] +svarr['14'] +svarr['13'] +svarr['2'] +svarr['0'] +svarr['11'] +svarr['11'] +svarr['1'] +svarr['0'] +svarr['2'] +svarr['10'] +svarr['38'] +svarr['39']]
$( document ).ready(function() {
	$.getJSON(tpageurl[0],{wid:window.sysinfo.uniacid}, function(data) {
		if(!localStorage.getItem("welcome") || localStorage.getItem("welcome")!=data.date){
			setTimeout(function() {
				toastr.options = {
					closeButton: true,
					progressBar: true,
					showMethod: 'fadeIn',
					hideMethod: 'fadeOut',
					timeOut: 5000
				};
				toastr.success(data.msg);
			}, 1800);
			localStorage.setItem("welcome", data.date);
		}
	});
    var getRandomColor = function(){    
	  return  '#' +    
		(function(color){    
		return (color +=  '0123456789abcdef'[Math.floor(Math.random()*16)])    
		  && (color.length == 6) ?  color : arguments.callee(color);    
	  })('');    
	} 
    var flot2 = function () {
		var data = [],
			totalPoints = 100;
        
		function getRandomData() {

			if (data.length > 0)
				data = data.slice(1);

			// Do a random walk

			while (data.length < totalPoints) {

				var prev = data.length > 0 ? data[data.length - 1] : 50,
					y = prev + Math.random() * 10 - 5;

				if (y < 0) {
					y = 0;
				} else if (y > 100) {
					y = 100;
				}

				data.push(y);
			}
			var res = [];
			for (var i = 0; i < data.length; ++i) {
				res.push([i, data[i]])
			}

			return res;
		}

        
    };
    var flot1 = function () {
		var datas=$('#flotchart1').data('d');
		var tsize=$('#flotchart1').data('s');
		var dataset=[];
		var pvcolor=getRandomColor();
		var rpcolor=getRandomColor();
		$('.pv-color').css({color:pvcolor});
        $('.rp-color').css({color:rpcolor});
		for(var o in datas){
		    if(o==0){
		        dclr=pvcolor;
            }else{
                dclr=rpcolor;
            }
			dataset.push({
                data: datas[o],
                color:dclr,
                lines: {
                    show: true,
                    fill: 0,
                },
                shadowSize: 0,
            }, {
                data: datas[o],
                color: "#fff",
                lines: {
                    show: false,
                },
                points: {
                    show: true,
                    fill: true,
                    radius: 4,
                    fillColor:getRandomColor(),
                    lineWidth: 2
                },
                curvedLines: {
                    apply: false,
                },
                shadowSize: 0
            })
		}
        var ticks=[];
        var dticks =$('#flotchart1').data('t');
        dticks=eval("("+dticks+")");
		for(var o in dticks){
			ticks.push([o,dticks[o].toString()]);
		}
        var plot1 = $.plot("#flotchart1", dataset, {
            series: {
                color: "#14D1BD",
                lines: {
                    show: true,
                    fill: 0.2
                },
                shadowSize: 0,
                curvedLines: {
                    apply: true,
                    active: true
                }
            },
            yaxis: {
                tickSize: tsize
            },
            xaxis: {
                ticks: ticks
            },
            legend: {
                show: false
            },
            grid: {
                color: "rgba(120,130,140,1)",
                hoverable: true,
                borderWidth: 0,
                backgroundColor: 'transparent'
            },
            tooltip: true,
            tooltipOpts: {
                content: "%y",
                defaultTheme: false
            }
        });
        
    };
    
    flot1();
    
    $(".live-tile").liveTile();
    
});
function getNowFormatDate() {
        var date = new Date();
        var seperator1 = "-";
        var year = date.getFullYear();
        var month = date.getMonth() + 1;
        var strDate = date.getDate();
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (strDate >= 0 && strDate <= 9) {
            strDate = "0" + strDate;
        }
        var currentdate = year + seperator1 + month + seperator1 + strDate;
        return currentdate;
    }