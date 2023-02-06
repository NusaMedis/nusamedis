/*----------------------------------------------------------------------------\
  |                                  Chart 1.0                                  |
  |-----------------------------------------------------------------------------|
  |                          Created by Emil A Eklund                           |
  |                        (http://eae.net/contact/emil)                        |
  |-----------------------------------------------------------------------------|
  | Client side chart painter, supports line, area and bar charts and stacking, |
  | uses Canvas (mozilla,  safari,  opera) or SVG (mozilla, opera) for drawing. |
  | Can be used with IECanvas to allow the canvas painter to be used in IE.     |
  |-----------------------------------------------------------------------------|
  |                      Copyright (c) 2006 Emil A Eklund                       |
  |- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -|
  | This program is  free software;  you can redistribute  it and/or  modify it |
  | under the terms of the MIT License.                                         |
  |- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -|
  | Permission  is hereby granted,  free of charge, to  any person  obtaining a |
  | copy of this software and associated documentation files (the "Software"),  |
  | to deal in the  Software without restriction,  including without limitation |
  | the  rights to use, copy, modify,  merge, publish, distribute,  sublicense, |
  | and/or  sell copies  of the  Software, and to  permit persons to  whom  the |
  | Software is  furnished  to do  so, subject  to  the  following  conditions: |
  | The above copyright notice and this  permission notice shall be included in |
  | all copies or substantial portions of the Software.                         |
  |- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -|
  | THE SOFTWARE IS PROVIDED "AS IS",  WITHOUT WARRANTY OF ANY KIND, EXPRESS OR |
  | IMPLIED,  INCLUDING BUT NOT LIMITED TO  THE WARRANTIES  OF MERCHANTABILITY, |
  | FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE |
  | AUTHORS OR  COPYRIGHT  HOLDERS BE  LIABLE FOR  ANY CLAIM,  DAMAGES OR OTHER |
  | LIABILITY, WHETHER  IN AN  ACTION OF CONTRACT, TORT OR  OTHERWISE,  ARISING |
  | FROM,  OUT OF OR  IN  CONNECTION  WITH  THE  SOFTWARE OR THE  USE OR  OTHER |
  | DEALINGS IN THE SOFTWARE.                                                   |
  |- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -|
  |                         http://eae.net/license/mit                          |
  |-----------------------------------------------------------------------------|
  | Dependencies: canvaschartpainter.js  - Canvas chart painter implementation. |
  |               canvaschart.css        - Canvas chart painter styles.         |
  |           or: svgchartpainter.js     - SVG chart painter implementation.    |
  |-----------------------------------------------------------------------------|
  | 2006-01-03 | Work started.                                                  |
  | 2006-01-05 | Added legend and axis labels. Changed the painter api slightly |
  |            | to allow two-stage initialization (required for ie/canvas) and |
  |            | added legend/axis related methods. Also updated bar chart type |
  |            | and added a few options, mostly related to bar charts.         |
  | 2006-01-07 | Updated chart size calculations to take legend and axis labels |
  |            | into consideration.  Split painter implementations to separate |
  |            | files.                                                         |
  | 2006-01-10 | Fixed bug in automatic range calculation.  Also added explicit |
  |            | cast to float for stacked series.                              |
  | 2006-04-16 | Updated constructor to set painter factory  based on available |
  |            | and supported implementations.                                 |
  | 2007-02-01 | Brought chart related methods of PainterFactory classes into   |
  |            | the Chart class, and reduced PainterFactory to simpler drawing |
  |            | primitives only. (by Ashutosh Bijoor  -bijoor@gmail.com)       |
  |-----------------------------------------------------------------------------|
  | Created 2006-01-03 | All changes are in the log above. | Updated 2007-02-01 |
  \----------------------------------------------------------------------------*/

/*----------------------------------------------------------------------------\
  |                                    Chart                                    |
  |- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -|
  | Chart class, control class that's used to represent a chart. Uses a painter |
  | class for the actual drawing.  This is the only  class that should be  used |
  | directly, the other ones are internal.                                      |
  \----------------------------------------------------------------------------*/

function PieChart(el,config) {
	this._cont             = el;
	this._series           = new Array();
	this._painter          = null;
	var defaultConfig = {
		yMin:0,
		yMax:0,
		xGrid:0,
		yGrid:10,
		labelPrecision:0,
		showLegend:true,
		xLabels:new Array(),
		painterType:'canvas',
		legendWidth:150,
		backgroundColor:'white',
		gridColor:'silver',
		axesColor:'black',
		textColor:'black'
	};
	for (var p in config) {if (config[p] !='') defaultConfig[p]=config[p];}
	this.config=defaultConfig;

	/*
	 * Determine painter implementation to use based on what's available and
	 * supported. CanvasChartPainter is the prefered one, JsGraphicsChartPainter
	 * the fallback one as it works in pretty much any browser. The
	 * SVGChartPainter implementation one will only be used if set explicitly as
	 * it's not up to pair with the other ones.
	 */
	if (this.config.painterType == 'canvas') {
		try {
			this.setPainterFactory(CanvasChartPainterFactory);
		} catch(e) {
			alert("Canvas painter not loaded");
		}
	} else if (this.config.painterType == 'jsgraphics') {
		try {
		this.setPainterFactory(JsGraphicsChartPainterFactory);
		} catch(e) {
			alert("JSGraphics painter not loaded");
		}
	} else {
		try {
			this.setPainterFactory(CanvasChartPainterFactory);
		} catch(e) {
			try {
				this.setPainterFactory(JsGraphicsChartPainterFactory);
			} catch(e1) {
				alert("No supported painter factory found");
			}
		}
	}

	if (!this._painter) { return; }

	/* Initialize chart range */
	this.xlen = this.config.xLabels.length; /* number of x ticks */
	this.ymin = this.config.yMin; /* min y value */
	this.ymax = this.config.yMax; /* max y value */

	/* Initialize painter object */
    this.w=this._painter.getWidth();
    this.h=this._painter.getHeight();
    this.chartx = 0;
    this.charty = 0;
    this.chartw	= this.w;
    this.charth	= this.h;
	
	/* Initialize bar offset to 0 */
	this.offset=0;

}

/*
 * Function for ChartSeries objects to retrieve painter from Chart 
 */
PieChart.prototype.getPainter = function() {
	return this._painter;
};


/*
 * Function to add a ChartSeries object to the Chart 
 */
PieChart.prototype.add = function(series) {
	try {
		// is it a valid ChartSeries object?
		series.getLabel();
	} catch(e) {
		// no... has a type been defined?
		try {
			series=new PieChartSeries(series,this._cont);
		}catch(e1) {
			alert(e);
		}
	}
	this._series.push(series);
	/* Adjust the Chart range in case the series has values outside the chart */
	var range=series.getRange(this);
	/* Calculate y range and xstep in case the range changed*/
    this.adjustRange(range);
	/* Do we need to increment the offset? required for bar charts */
	if (series.toOffset()) {
		this.offset++;
	}
};


/*
 * Function to draw one or all Chart Series. 
 */
PieChart.prototype.draw = function(seriesLabel) {

	if (!this._painter) { return; }

	if (typeof seriesLabel != 'undefined') {
		var series=this.find(seriesLabel);
		if (series) {
			series.draw(this);
			if (this.config.showLegend) { this.drawLegend(series); }
		}
	} else {
		/* Draw all series */
		for (var i = 0; i < this._series.length; i++) {
			this._series[i].draw(this);
			if (this.config.showLegend) {this.drawLegend(this._series[i])};
		}
	}

	/*
	 * Draw axes (after the series since the anti aliasing of the lines may
	 * otherwise be drawn on top of the axis)
	 */
	this.drawAxes();

};


/*
 * Function to find a ChartSeries from the Chart by specifying the series label
 */
PieChart.prototype.find = function(label) {
    for (var i = 0; i < this._series.length; i++) {
		if (this._series[i].getLabel()==label) {
			return this._series[i];
		}
	}
	return null;
};

/*
 * function to clear chart background and draw grid, legend
 * Draws the chart grid and labels
 */
PieChart.prototype.clear = function() {


	/*
	 * Create legend div
	 */
	if (this.config.showLegend) {
		this.createLegend();
	}

	/* clear background with white */
    this._painter.fillRect(this.config.backgroundColor,0, 0, this.w, this.h);

	/* Set xGrid to xlen in case it is not specified in config */
	if (this.config.xGrid<=this.xlen-1) {
		this.config.xGrid=this.xlen-1;
	}
	this.adjustRange();
	/*
	 *  draw grid
	 */
    if (this.xGridDensity) {
		for (var i = 0; i < this.config.xGrid; i++) {
			var x=1+this.chartx+(i*this.xGridDensity);
			//this._painter.line(this.config.gridColor,1,x+15, this.charty, x+15, this.charty + this.charth);
		}
		//this._painter.line(this.config.gridColor,1,this.chartx+this.chartw, this.charty, this.chartx+this.chartw, this.charty + this.charth);
    }
    if (this.yGridDensity) {
		for (var i = 0; i < this.config.yGrid; i++) {
			var y=this.charty+this.charth - (i*this.yGridDensity)-1;
			//this._painter.line(this.config.gridColor,1,this.chartx + 1,y, this.chartx + this.chartw + 1,y);
		}
		//this._painter.line(this.config.gridColor,1,this.chartx+1, this.charty, this.chartx+this.chartw, this.charty);
    }
	this.adjustRange();
	/* draw axes */
	//this.drawAxes();

};


/*
 * Internal function for setting the painter factory
 */
PieChart.prototype.setPainterFactory = function(f) {
	this._painterFactory = f;
	/* Create painter object */
	this._painter = this._painterFactory();
	this._painter.create(this._cont);
    this._painter.fillRect(this.config.backgroundColor,0, 0, 1, 1);
};

/*
 * Internal function to calculate chart range
 */
PieChart.prototype.adjustRange = function(range) {
	if (typeof range != 'undefined') {
		if (range.xlen > this.xlen) { this.xlen = range.xlen; }
		if (range.ymin < this.ymin) {this.ymin=range.ymin;}
		if (range.ymax > this.ymax) {this.ymax=range.ymax;}
	}
    this.range = this.ymax - this.ymin;
    this.xstep = this.chartw / (this.xlen - 1);
	/*
	 * Determine whatever or not to show the legend and axis labels
	 * Requires density and labels to be set.
	 */
	this.xGridDensity=0;
	this.yGridDensity=0;
	if (this.config.xGrid>0) {
		this.xGridDensity=Math.round((this.chartw-1)/this.config.xGrid);
	}
	if (this.config.yGrid>0) {
		this.yGridDensity=Math.round((this.charth-1)/this.config.yGrid);
	}
	this.showLabels = (this.xGridDensity) && (this.yGridDensity);

};

/*
 * Internal function to draw chart axes
 */
PieChart.prototype.drawAxes = function() {
    var x1 = this.chartx;
    var x2 = this.chartx + this.chartw + 1;
    var y1 = this.charty;
    var y2 = this.charty + this.charth - 1;
    this._painter.line(this.config.axesColor,1,x1, y1, x1, y2);
    this._painter.line(this.config.axesColor,1,x1, y2, x2, y2);
    this._painter.line(this.config.axesColor,1,x2, y1, x2, y2);
    this._painter.line(this.config.axesColor,1,x1, y1, x2, y1);
};

/*
 * Internal function to create the chart legend div
 */
PieChart.prototype.createLegend = function() {
	var series=this._series;
    this.legend = document.createElement('div');
    this.legend.style.position = 'absolute';
    this.legendList = document.createElement('ul');
	this.legendList.style.listStyle='square';
	this.legend.style.backgroundColor=this.config.backgroundColor;
    this.legend.style.width = this.config.legendWidth+'px';
    this.legend.style.right = '0px';
	this.legend.style.border='1px solid '+this.config.textColor;
	this.legend.style.borderColor=this.config.textColor;
    this.legend.style.top  = this.charty + (this.charth / 2) - (this.legend.offsetHeight / 2) + 'px';
    this.legend.appendChild(this.legendList);
    this._cont.appendChild(this.legend);
    /* Recalculate chart width and position based on labels and legend */
    this.chartw	= this.w - (this.config.legendWidth + 5);
    this.adjustRange();
};

/*
 * Internal function to draw the legend for a series
 */
PieChart.prototype.drawLegend = function(series) {
	if (typeof series == 'undefined') {
		for(var i=0;i<this._series.length;i++) {
			this.drawLegend(this._series[i]);
		}
		return;
	}
	
	color = series.getColor();
	label = series.getXLabel();
	//alert(label);
	for(i=0;i<color.length;i++){
		this.legendList.innerHTML+='<li style="color:'+color[i]+'"><span style="color:'+this.config.textColor+'">'+label[i]+'</span>';
	}
	/*******
	item = document.createElement('li');
	item.style.color = series.getColor();
	label = document.createElement('span');
	label.appendChild(document.createTextNode(series.getLabel()));
	label.style.color = 'black';
	item.appendChild(label);
	this.legendList.appendChild(item);
	********/
};

/*
 * Internal function to draw vertical labels and ticks
 */
PieChart.prototype.drawVerticalLabels = function() {
    var axis, item, step, y, ty, n, yoffset, value, multiplier, w, items, pos;
	var ygd, precision;
	ygd=this.config.yGrid;
	if (ygd<=0) return;
	precision=this.config.labelPrecision;
    /* Calculate step size and rounding precision */
    multiplier = Math.pow(10, precision);
    step       = this.range / this.config.yGrid;

    /* Create container */
	//axis=jQuery(this._cont).append('<div style="position:absolute;left:0;top:0;text-align:right"></div>').get(0);
    axis = document.createElement('div');
    axis.style.position = 'absolute';
    axis.style.left  = '0px';
    axis.style.top   = '0px';
    axis.style.textAlign = 'right';
    this._cont.appendChild(axis);
	
    /* Draw labels and points */
    w = 0;
    items = new Array();
    for (i=0;i<=this.config.yGrid;i++) {
		value = parseInt((this.ymin+(i*step)) * multiplier) / multiplier;
		//item=jQuery(axis).append('<span>'+value+'</span>').get(0);
		item = document.createElement('span');
		item.appendChild(document.createTextNode(value));
		axis.appendChild(item);
		items.push(item);
		if (item.offsetWidth > w) { w = item.offsetWidth; }
    }
	
    /* Draw last label and point (lower left corner of chart) */
    item = document.createElement('span');
    item.appendChild(document.createTextNode(this.ymin));
    axis.appendChild(item);
    items.push(item);
    if (item.offsetWidth > w) { w = item.offsetWidth; }
	
    /* Set width of container to width of widest label */
    axis.style.width = w + 'px';
	
    /* Recalculate chart width and position based on labels and legend */
    this.chartx = w + 5;
    this.charty = item.offsetHeight / 2;
    this.charth = this.h - ((item.offsetHeight * 1.5) + 5);
    this.chartw	= this.w - (((this.legend)?this.legend.offsetWidth:0) + w + 10);
    this.adjustRange();
	
    /* Position labels on the axis */
    for (i = 0; i < items.length; i++) {
		y=this.charty+this.charth-(i*this.yGridDensity);
		ty=this.charth-(i*this.yGridDensity);
		item = items[i];
		this._painter.fillRect(this.config.textColor,this.chartx - 5, y, 5, 1);
		item.style.position = 'absolute';
		item.style.right = '0px';
		item.style.top   = ty + 'px';
		item.style.color=this.config.textColor;
    }	
};


/*
 * Internal function to draw horixontal labels and ticks
 */
PieChart.prototype.drawHorizontalLabels = function() {
	var axis, item, step, x, tx, lebar;
	var xlen, labels, xgd, precision;
	var items = new Array();

	labels=this.config.xLabels;

	/* Create container */
	axis = document.createElement('div');
	axis.style.position = 'absolute';
	axis.style.left   = '0px';
	axis.style.top    = (this.charty + this.charth + 5) + 'px';
	axis.style.width  = this.w + 'px';
	this._cont.appendChild(axis);
	
	lebar = 0;
	for (i = 0; i < this._series.length; i++) {
		if(this._series[i].config.type=="Bar") lebar += this._series[i].config.width;
	}
	
	/* Draw labels and points */
	x = this.chartx;
	for (i = 0; i < this.xlen-1; i++) {
		item = document.createElement('span');
		if (labels[i]) {
			item.appendChild(document.createTextNode(labels[i]));
		}
		axis.appendChild(item);
          incr = (lebar/2);
		tx = x - (item.offsetWidth/2) + incr;
		item.style.position = 'absolute';
		item.style.left = tx + 'px';
		item.style.top  = '0px';
		item.style.color=this.config.textColor;
		this._painter.fillRect("000000",x+incr, this.charty + this.charth, 2,2);
		x += this.xstep;
	}	
};


AbstractChartSeries.prototype.getXLabel = function() {
	return this.config.xLabels;
};

  /*----------------------------------------------------------------------------\
  |                              PieChartSeries                                 |
  |- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -|
  | Bar Chart Series                                                            |
  \----------------------------------------------------------------------------*/

function PieChartSeries(config,cont) {
	// config hash contains keys
	var defaultConfig = {
		label:"BarChart",// label - name of series
		color:[],    // color - HTML color for series
		values:[],       // values - array of values
		radius:200,        // width - Sets with of bars for bar charts
		left:100,        // width - Sets with of bars for bar charts
		top:100,        // width - Sets with of bars for bar charts
		xLabels:[],
		type:"pie"     
	};
	for (var p in config) {defaultConfig[p]=config[p];}
	this.config=defaultConfig;
	this.offset=0;
	this._cont = cont;
}

PieChartSeries.prototype=new AbstractChartSeries;

PieChartSeries.prototype._getRange=AbstractChartSeries.prototype.getRange;
PieChartSeries.prototype.getRange = function(chart) {
	var range=this._getRange(chart);
	range.xlen++;
	if (chart.offset && (!this.config.stackedOn || this.config.stackedOn=='')) {
		this.offset = this.config.distance + chart.offset * (this.config.width + this.config.distance);
	} else {
		if (this.config.stackedOn) {
			var stackedOn=chart.find(this.config.stackedOn);
			if (stackedOn) {
				this.offset=stackedOn.offset;
			}
		}
	}
	return range;
};

PieChartSeries.prototype.toOffset = function() {
	return (!this.config.stackedOn || this.config.stackedOn=='')?1:0;
};

PieChartSeries.prototype.draw = function(chart) {
	// draws a bar chart
    var i, len, x, y, barHt, yBottom, n, yoffset,painter,values, item;
	painter=chart.getPainter();
	values=this.getStackedValues(chart);



    len = values.length;
    if (len > chart.xlen) { len = chart.xlen; }
    if (len) {
          startAngle = 0;
          total = 0;
		for (i = 0; i < len; i++) total += eval(this.config.values[i]);
          
          painter.fillArc("#5e5b5b",this.config.left,this.config.top,this.config.radius,startAngle,360);
          painter.fillArc("#FFFFFF",this.config.left+2,this.config.top+2,this.config.radius-4,startAngle,360);

		tengahX = this.config.left+(this.config.radius/2);
		tengahY = this.config.top+(this.config.radius/2);

		for (i = 0; i < len; i++) {
			
               persen = Math.round((eval(this.config.values[i])/total)*100)/100;
               
			if(i==len-1) endAngle = 360;
			else endAngle = startAngle + parseInt(persen*360);


               painter.fillArc(this.config.color[i],this.config.left+5,this.config.top+5,this.config.radius-10,startAngle,endAngle);

/*			jari2 = (this.config.radius/2);
			
			sudutTengah = parseInt(((endAngle-startAngle) / 2) + startAngle);
			sudutTengah = sudutTengah*2*3.14/360;
			
			tambahX = jari2 * Math.cos(sudutTengah);
			tambahY = jari2 * Math.sin(sudutTengah);
			
			if(tambahX<0) tambahX = tambahX - 30;
			else tambahX = tambahX + 5;

			if(tambahY>0) tambahY = tambahY + 20;
			else tambahY = tambahY;


			kiri = tengahX + tambahX;
			atas = tengahY - tambahY;

			label = persen*100 + '%';


			// ---- create persen label ----
			item = document.createElement('span');
			item.appendChild(document.createTextNode(label));
	
			item.style.position = 'absolute';
			item.style.left = kiri + 'px';
			item.style.top   = atas + 'px';
			item.style.color="#000000";
			item.style.backgroundColor = "#fffdc9";
			item.style.borderColor= "#5e5b5b";
			item.style.borderStyle= "solid";
			item.style.borderWidth= "1px";

			this._cont.appendChild(item);
			//items.push(item);
			//if (item.offsetWidth > w) { w = item.offsetWidth; }
 */
               startAngle = endAngle;

		}
    }
    
/*
          painter.fillArc("#5e5b5b",100,100,200,0,360);
          painter.fillArc("#FFFFFF",105,105,190,0,360);

          painter.fillArc("#FF0000",107,107,172,0,90);
          painter.fillArc("#FFFF00",107,107,172,90,180);
          painter.fillArc("#0000FF",107,107,172,180,270);
          painter.fillArc("#00FF00",107,107,172,270,360);
          
*/
};

