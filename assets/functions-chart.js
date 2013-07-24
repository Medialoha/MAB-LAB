/**
 * Copyright (c) 2013 EIRL DEVAUX J. - Medialoha.
 * All rights reserved. This program and the accompanying materials
 * are made available under the terms of the GNU Public License v3.0
 * which accompanies this distribution, and is available at
 * http://www.gnu.org/licenses/gpl.html
 *
 * Contributors:
 *     EIRL DEVAUX J. - Medialoha - initial API and implementation
 */

var PIE_CHART_TYPE_ID = 0;
var BAR_CHART_TYPE_ID = 1;
var LINE_CHART_TYPE_ID = 2;


function loadChart(containerId, chartId, type) {
	$.ajax({ url:"?a=getchartdata", type:"get", data: { chartId:chartId } })
	.done(function(data) {
		try {
			var result = data.split('|'); 			
			if (!(result.length==2) || result[0]!='OK') {
				onError(containerId, result[0]);
				return false;
			}
			
			chartData = jQuery.parseJSON(result[1]);
			console.log(chartData);
			
		} catch(err) { onError(err); return false; }
		
		switch (type) {
			case PIE_CHART_TYPE_ID : drawPieChart(containerId, chartData);
				break;
			case BAR_CHART_TYPE_ID : drawBarChart(containerId, chartData);
				break;
			case LINE_CHART_TYPE_ID : drawLineChart(containerId, chartData);
				break;	
		}
	});
}

function drawPieChart(containerId, data) {	
	$.plot(containerId, data, {
		    series: { pie:{ show: true, 
		    				label: { show:true }, 
		    				combine: { color:'#ccc', threshold:0.01 }
		    			  }},
		    legend: { show:false }
		});	
}

function drawBarChart(containerId, data) {
	$.plot(containerId,	[{ label: "",
	                       data: data.data,
	                       color: "#33b5e5",
	                       bars: { show:true, barWidth:0.7, align:"center" }

	                     }],
	                    { xaxis: { ticks:data.ticks },
        				  grid: { show:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} }    	
	                    });	
}

function drawLineChart(containerId, data) {
	$.plot(containerId,	[{ label: "",
	                       data: data.data,
	                       color: "#33b5e5",
	                       lines: { show:true, barWidth:0.7, align:"center" }

	                     }],
	                    { xaxis: { ticks:data.ticks },
        				  grid: { show:true, color:"#666666", backgroundColor:"#ffffff", borderColor:"#666666", borderWidth:{top:0, right:0, bottom:1, left:1} }    	
	                    });	
}

function onError(containerId, message) {
	$(containerId).html(message);
}