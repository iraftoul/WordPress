if (typeof W3Ex === "undefined") {
    var W3Ex = {};
}
W3Ex.vtfModule = (function ($) {
	var _dataItems = [];
	var _selstyle = "";
	var _selstyledlg = "";
	var _arrColors = [];
	var _arrSpecial = [];
	var _arrMerge = [];
	var _cellsize = 0;
	var _selected;
	var _thetable = "#w3exvtf-thetable";
	var _htable;
	var _arrPalette = [
		["rgb(0, 0, 0)", "rgb(67, 67, 67)", "rgb(102, 102, 102)", 
		"rgb(204, 204, 204)", "rgb(217, 217, 217)",  "rgb(255, 255, 255)"],
		["rgb(152, 0, 0)", "rgb(255, 0, 0)", "rgb(255, 153, 0)", "rgb(255, 255, 0)", "rgb(0, 255, 0)",
		"rgb(0, 255, 255)", "rgb(74, 134, 232)", "rgb(0, 0, 255)", "rgb(153, 0, 255)", "rgb(255, 0, 255)"],
		["rgb(230, 184, 175)", "rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)",
		"rgb(208, 224, 227)", "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)",
		"rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)",
		"rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(159, 197, 232)", "rgb(180, 167, 214)", "rgb(213, 166, 189)",
		"rgb(204, 65, 37)", "rgb(224, 102, 102)", "rgb(246, 178, 107)", "rgb(255, 217, 102)", "rgb(147, 196, 125)",
		"rgb(118, 165, 175)", "rgb(109, 158, 235)", "rgb(111, 168, 220)", "rgb(142, 124, 195)", "rgb(194, 123, 160)",
		"rgb(166, 28, 0)", "rgb(204, 0, 0)", "rgb(230, 145, 56)", "rgb(241, 194, 50)", "rgb(106, 168, 79)",
		"rgb(69, 129, 142)", "rgb(60, 120, 216)", "rgb(61, 133, 198)", "rgb(103, 78, 167)", "rgb(166, 77, 121)",
		"rgb(91, 15, 0)", "rgb(102, 0, 0)", "rgb(120, 63, 4)", "rgb(127, 96, 0)", "rgb(39, 78, 19)",
		"rgb(12, 52, 61)", "rgb(28, 69, 135)", "rgb(7, 55, 99)", "rgb(32, 18, 77)", "rgb(76, 17, 48)"]
    ];
	var _F = {"BOLD":1,"ITALIC":2,"UNDERLINE":4,"STRIKE":8};
	var _but = {"remcolor":"#w3exvtf-removecolor","bold":"#w3exvtf-makebold","italic":"#w3exvtf-makeitalic",
	"underline":"#w3exvtf-makeunder","strike":"#w3exvtf-makestrike","italic":"#w3exvtf-makeitalic","left":"#w3exvtf-left","right":"#w3exvtf-right",
	"up":"#w3exvtf-up","down":"#w3exvtf-down","remcolor":"#w3exvtf-removecolor"};
	
	function HandleObject(source)
	{
		var dest;
		if(source === undefined) return dest;
	 	var newobj = {};
		for(var propd in source)
		{
			newobj[propd] = source[propd];
		}
		dest = newobj;
		return dest;
	}
	function HandleMove(direction)
	{
		 var selArr = _htable.getSelected();
		 if(typeof selArr === "undefined") return;
		 if(selArr.length !== 4) return; //some bug encountered
		 var startx,starty,endx,endy,tmp,startyval,startxval,endxval,endyval;				
		 startx = selArr[0]; //start row
		 starty = selArr[1]; //start col
		 endx   = selArr[2]; //end row
		 endy   = selArr[3]; //end col
		 var colcount = _htable.countCols();
		 var rowcount = _htable.countRows();
		  //check for reverse selection
		 if(startx > endx)
		 {
		 	tmp = endx;
			endx = startx;
			startx = tmp;
		 }
		 if(starty > endy){
		 	tmp = endy;
			endy = starty;
			starty = tmp;
		 }
		 startyval = starty;
		 startxval = startx;
		 endxval   = endx;
		 endyval   = endy;
		 var selecttext = $('#w3exvtf-text').find(":selected").val();
		 var ifmove = true;
		 var iftext = true;
		 var ifformat = true;
		 if($('#w3exvtf-move').find(":selected").val() !== "move")
		 	ifmove = false;
		 if(selecttext === "text")
		 	ifformat = false;
		 if(selecttext === "format")
		 	iftext = false;
		 if(direction === "up" || direction ==="down")
		 {
		 	var rowsource = [];
			var rowmove = [];
			var indexx = startx - 1;
			if(direction === "down") 
				indexx = startx + 1;
			if(iftext)
			{
				if(_dataItems[indexx] !== undefined)
				{//get data from upper row
					var row = _dataItems[indexx];
					while(starty <= endy)
					{
						rowsource[starty] = row[starty];
						starty++;
					}
					starty = startyval;
				}
				
				if(_dataItems[startx] !== undefined)
				{//get data from upper row
					var row = _dataItems[startx];
					while(starty <= endy)
					{
						rowmove[starty] = row[starty];
						if(ifmove)
							row[starty] = rowsource[starty];
						starty++;
					}
					starty = startyval;
				}else{
					if(rowsource.length != 0 && ifmove)
					_dataItems[startx] = rowsource;
				}
				if(_dataItems[indexx] !== undefined)
				{
					var row = _dataItems[indexx];
					while(starty <= endy)
					{
						row[starty] = rowmove[starty];
						starty++;
					}
					starty = startyval;
				}else{
					if(rowmove.length != 0)
					_dataItems[indexx] = rowmove;
				}
				//move special
				var rowsource = [];
				var rowmove = [];
				//////////
				{
					if(_arrSpecial[indexx] !== undefined)
					{//get data from upper row
						var row = _arrSpecial[indexx];
						while(starty <= endy)
						{
							rowsource[starty] = row[starty];
							starty++;
						}
						starty = startyval;
					}
					if(_arrSpecial[startx] !== undefined)
					{//get data from sel row
						var row = _arrSpecial[startx];
						while(starty <= endy)
						{
							rowmove[starty] = HandleObject(row[starty]);
							if(ifmove)//row[starty] = rowsource[starty];//copy
								row[starty] = HandleObject(rowsource[starty]);
							starty++;
						}
						starty = startyval;
					}else{
						if(rowsource.length != 0 && ifmove)
						_arrSpecial[startx] = rowsource;
					}
					if(_arrSpecial[indexx] !== undefined)//upperrow
					{
						var row = _arrSpecial[indexx];
						while(starty <= endy)
						{
							row[starty] = rowmove[starty];
							starty++;
						}
						starty = startyval;
					
					}else{
						if(rowmove.length != 0)
						_arrSpecial[indexx] = rowmove;
					}
				}
			}
			//move format
			var rowsource = [];
			var rowmove = [];
			if(ifformat)
			{
				if(_arrColors[indexx] != undefined)
				{//get data from upper row
					var row = _arrColors[indexx];
					while(starty <= endy)
					{
						rowsource[starty] = row[starty];
						starty++;
					}
					starty = startyval;
				}
				if(_arrColors[startx] !== undefined)
				{//get data from upper row
					var row = _arrColors[startx];
					while(starty <= endy)
					{
						rowmove[starty] = HandleObject(row[starty]);
						if(ifmove)
							row[starty] = HandleObject(rowsource[starty]);
						starty++;
					}
					starty = startyval;
				}else{
					if(rowsource.length != 0 && ifmove)
					_arrColors[startx] = rowsource;
				}
				if(_arrColors[indexx] !== undefined)
				{
					var row = _arrColors[indexx];
					while(starty <= endy)
					{
						row[starty] = rowmove[starty];
						starty++;
					}
					starty = startyval;
				}else{
					if(rowmove.length != 0)
					_arrColors[indexx] = rowmove;
				}
			}
			if(direction == "down") 
			{
				startx++;
				endx++;
			}else
			{
				startx--;
				endx--;
			}
		 }else if(direction == "left" || direction == "right")
		 {
		 	var rowsource = [];
			var rowmove = [];
			var indexy = starty - 1;
			if(direction == "right")
			   indexy = starty + 1;
			if(iftext)
			{
				while(startx <= endx)
				{
					if(_dataItems[startx] !== undefined)
					{
						var row = _dataItems[startx];
						rowsource[startx] = row[indexy];
					}
					startx++;
				}
				startx = startxval;
				while(startx <= endx)
				{
					if(_dataItems[startx] !== undefined)
					{
						var row = _dataItems[startx];
						rowmove[startx] = row[starty];
						if(ifmove)
							row[starty] = rowsource[startx];
					}
					startx++;
				}
				startx = startxval;
				while(startx <= endx)
				{
					if(_dataItems[startx] !== undefined)
					{
						var row = _dataItems[startx];
						row[indexy] = rowmove[startx];
					}
					startx++;
				}
				startx = startxval;
				//move special
				var rowsource = [];
				var rowmove = [];
				/////////////////////////
				{
					while(startx <= endx)
					{
						if(_arrSpecial[startx] !== undefined)
						{
							var row = _arrSpecial[startx];
							rowsource[startx] = row[indexy];
						}
						startx++;
					}
					startx = startxval;
					while(startx <= endx)
					{
						if(_arrSpecial[startx] !== undefined)
						{
							var row = _arrSpecial[startx];
							rowmove[startx] = HandleObject(row[starty]);
							if(ifmove)
								row[starty] = HandleObject(rowsource[startx]);
						}
						startx++;
					}
					startx = startxval;
					while(startx <= endx)
					{
						if(_arrSpecial[startx] !== undefined)
						{
							var row = _arrSpecial[startx];
							row[indexy] = rowmove[startx];
						}
						startx++;
					}
					startx = startxval;
				}
			}
			//format
			var rowsource = [];
			var rowmove = [];
			if(ifformat)
			{
				while(startx <= endx)
				{
					if(_arrColors[startx] !== undefined)
					{
						var row = _arrColors[startx];
						rowsource[startx] = row[indexy];
					}
					startx++;
				}
				startx = startxval;
				while(startx <= endx)
				{
					if(_arrColors[startx] !== undefined)
					{
						var row = _arrColors[startx];
						rowmove[startx] = HandleObject(row[starty]);
						if(ifmove)
							row[starty] = HandleObject(rowsource[startx]);
					}
					startx++;
				}
				startx = startxval;
				while(startx <= endx)
				{
					if(_arrColors[startx] !== undefined)
					{
						var row = _arrColors[startx];
						row[indexy] = rowmove[startx];
					}
					startx++;
				}
				startx = startxval;
			}
			if(direction == "right")
			{
				starty++;
				endy++;
			}else
			{
				starty--;
				endy--;
			}
		 }
		 _htable.selectCell(startx,starty,endx,endy);
		 RefreshTable();
	}
	
	function ImportFormat(row,col,format)
	{
		if(_arrColors[row] !== undefined)					
		{
			var rowArr = _arrColors[row];
		 	if(rowArr[col] !== undefined)
			{
				var colorItem = rowArr[col];
				colorItem.fflags = format;
			}else
			{//add cell to array
				var colorItemNew = {};
				colorItemNew.fflags = format;
				rowArr[col] = colorItemNew;
			}
		}else
		{//add row to array
			var rowArr = [];
		 	var colorItemNew = {};
			colorItemNew.fflags = format;
			rowArr[col] = colorItemNew;
			_arrColors[row] = rowArr;
	    }	
	}
	function GenerateFromShortcode()
	{
		var textshortcode = $('#w3exvtf-importdata').val();
		if(isBlank(textshortcode)) return;
		var cols = FindString(textshortcode,"cols=\"","\"");
		_arrColors.length = 0;
		if(cols != "")
		{//get rows
			var arrRows = FindString(cols,"{","}",true);
			if(arrRows !== undefined)
			{
				for(var i=0;i<arrRows.length;i++)
				{
					if(arrRows[i] !== undefined)
					{
						var Row = arrRows[i];
						var arrCols = Row.split(";");
						var datarow = [];
			  			_arrColors[i] = datarow;
						for(var j=0; j < arrCols.length ; j++)
						{
							if(arrCols[j] != undefined && arrCols[j] != "")
							{
								var colitem = arrCols[j];
								if(colitem.indexOf(":") !== -1)
								{
									var cells = colitem.substring(0,colitem.indexOf(":"));
									var cellcolor = colitem.substring(colitem.indexOf(":") + 1,colitem.length);
									cellcolor = "#" + cellcolor;
									if(cells.indexOf("-") !== -1)
									{//multiple
										var start = cells.substring(0,cells.indexOf("-"));
										var end   = cells.substring(cells.indexOf("-") +1 , cells.length);
										start = parseInt(start,10);
										end = parseInt(end,10);
										if(start < end && (start >=0 && end <=1000 ))
										{
											while(start <= end)
											{
												var colorItem = {};
												colorItem.color = cellcolor;
												datarow[start] = colorItem;
												start++;
											}	
										}
									}else
									{//single
										var singlecol = parseInt(cells,10);
										var colorItem = {};
										colorItem.color = cellcolor;
										datarow[singlecol] = colorItem;
									}
								}
							}
						}
					}
				}	
			}
		}
		
			var text =  FindString(textshortcode,"]\n","[/");
			var split = text.split(';nn;\n');
			var lines = [];
			for (var i = 0; i < split.length; i++)
				if (split[i]) lines.push(split[i]);
		    if(lines.length != 0)
			{
				var oldrows = _htable.countRows() ;
				var oldcols = _htable.countCols();
			    var biggestval =0;
				for (var i = 0; i < lines.length; i++)
				{
					var row = lines[i];
					var datarow = [];
				 	_dataItems[i] = datarow;
					var rowitems = [];
					rowitems = row.split(";;;");
					var isspecial = false;
					for(var j=0; j < rowitems.length ; j++)
					{
						var cellitem = rowitems[j];
						if(cellitem.indexOf('{f',0) !== -1 && cellitem.indexOf('}',0) <= 6)
						{//special cell format
							var FormatItem = FindString(cellitem,"{f","}");
							if(FormatItem !== "")
							{
								var format = parseInt(FormatItem, 16);
								ImportFormat(i,j,format);
								cellitem = cellitem.slice(cellitem.indexOf('}',0) + 1,cellitem.length);
							}
						}
						if(cellitem.indexOf("{;n}") != 0)//preserve new line
			 				cellitem = cellitem.replace(/{;n}/g,"\n");
						datarow[j] = cellitem;
					}
					if(j > biggestval)
					{
						biggestval = j;	
					}
				}
				if(biggestval !== 0)
				{
					if(_dataItems.length > i)
					{
						_dataItems.splice(i,_dataItems.length);
					}
					
					RefreshTable();
					 var Cols = _htable.countCols();
					 while(Cols < biggestval)
				  	 {
					 	  _htable.alter('insert_col',null);
						  Cols++;
					 }
					 if(oldcols > Cols)
					    UpdateColumns(Cols,oldcols - Cols);
					 Cols = _htable.countRows();
					 if(oldrows > Cols)
					    UpdateRows(Cols,oldrows - Cols);
				}
			}
			RefreshTable();
	}
	function GenerateShortcode()
	{
		var text = ""
		var textcols = "";
		var textfontcols = "";
		var textst = "";
		//gen inner parameters
		for(var i=0 ; i < _arrColors.length ; i++)
		{
			if(_arrColors[i] == undefined) continue;
			var arrRow = _arrColors[i];
			var colorbefore = null;
			var colorbindex = -1;
		    var textcolstemp = textcols;
			textcols = "";
			for(var j = 0; j < arrRow.length ; j++)
			{
				if(arrRow[j] == undefined)
				{
					if(colorbindex != -1 && (colorbefore !== null && colorbefore !== undefined))
					{
						if((j - colorbindex) > 1)
						{//store as multivalue
							textcols+= colorbindex.toString() + "-" + (j-1).toString() + ":" + colorbefore + ";";
						}else
						{//single
							textcols+= (j-1).toString() + ":" + colorbefore + ";";
						}	
					}
					colorbefore = null;
					colorbindex = -1;
					 continue;
				}
				var colorItem = arrRow[j];
				if(colorItem.color != undefined)
				{
					if(colorItem.color != colorbefore)
					{
						if(colorbindex != -1 && (colorbefore !== null && colorbefore !== undefined))
						{
							if((j - colorbindex) > 1)
							{//store as multivalue
								textcols+= colorbindex.toString() + "-" + (j-1).toString() + ":" + colorbefore + ";";
							}else
							{//single
								textcols+= colorbindex.toString() + ":" + colorbefore + ";";
							}	
						}
						if(j == (arrRow.length - 1))
						{//last item, store current
							textcols+= j.toString() + ":" + colorItem.color + ";";
						}	
						colorbefore = colorItem.color;
						colorbindex = j;
					}else
					{//same color
						if(j == (arrRow.length - 1))
						{//last item
							if(colorbindex != -1 && (colorbefore !== null && colorbefore !== undefined))
							{
								if((j - colorbindex) >= 1)
								{//store as multivalue
									textcols+= colorbindex.toString() + "-" + (j).toString() + ":" + colorbefore + ";";
								}else
								{//single
									textcols+= (j).toString() + ":" + colorbefore + ";";
								}	
							}
						}
					}
					
				}else
				{
					if(colorbindex != -1 && (colorbefore !== null && colorbefore !== undefined))
					{
						if((j - colorbindex) > 1)
						{//store as multivalue
							textcols+= colorbindex.toString() + "-" + (j-1).toString() + ":" + colorbefore + ";";
						}else
						{//single
							textcols+= (j-1).toString() + ":" + colorbefore + ";";
						}	
					}
					colorbefore = null;
					colorbindex = j;
				}
			}
			if(textcols != "")
			{//add row
				textcols = textcolstemp + "{" + i + "}" + textcols + "{/}";
			}else
			{
				textcols = textcolstemp;
			}
		}
		textcols = textcols.replace(/#/g,"");
		if(textcols !== "")
		{
			text = "cols=\"" + 	textcols + "\"";
		}	
		if(textst !== "")
		{
			text+= " st=\"" + textst + "\"";
		}
		text = "<p>[vtftable " + text + "]<br/>\n";
		var items = "";
		for(var iRow = 0;iRow < _dataItems.length;iRow++ )
		{
			var hasinner = false;
			var arrRow = _dataItems[iRow];
			var arrRowFormat = _arrColors[iRow];
			for(var iCol = 0;iCol < arrRow.length;iCol++)
			{
				var curritem = " ";
				var format = "";
				if(arrRowFormat !== undefined)
				{
					var col = arrRowFormat[iCol];
					if(col !== undefined)
					{
						if((col.fflags !== undefined) && (col.fflags !== 0))
						{
							format = "{f" + col.fflags.toString(16) + "}";
						}
					}
				}
				if(arrRow[iCol] !== null)
				{
					curritem = arrRow[iCol];
				}
				if(curritem.indexOf("\n") != 0)//preserve new line
			 		curritem = curritem.replace(/\n/g,"{;n}");
				curritem = format + curritem;
				if(iCol == (arrRow.length -1))
					items+=  curritem;
				else
					items+= curritem + ";;;";
			}
			items+= ";nn;<br/>\n";
		}
		text+= items + "[/vtftable]</p>";
		return text;
	}
	 //change cell color on change picker, direct call from picker
	function GetAllArrs(arrcurrent)
	{
		var allarrs = [];
		var tmp;
		//check for reverse selection
		if(arrcurrent.length !== 4) return;
 		if(arrcurrent[0] > arrcurrent[2])
		{
			tmp = arrcurrent[2];
			arrcurrent[2] = arrcurrent[0];
			arrcurrent[0] = tmp;
		}
		if(arrcurrent[1] > arrcurrent[3]){
			tmp = arrcurrent[3];
			arrcurrent[3] = arrcurrent[1];
			arrcurrent[1] = tmp;
		}
		allarrs.push(arrcurrent);
		return allarrs;
	}

	
	function ChangeColor(color,parrSel,coloristring,pdelete) 
	{
		parrSel = typeof parrSel !== 'undefined' ? parrSel : _htable.getSelected();
		coloristring = typeof coloristring !== 'undefined' ? coloristring : false;
		pdelete = typeof pdelete !== 'undefined' ? pdelete : false;
        var colorhex;
		if(coloristring)
			colorhex = color;
		else
			colorhex = color.toHexString(); 
		if(typeof parrSel === "undefined") return;
		var ArrsSel = [];
		if(!pdelete)
			ArrsSel = GetAllArrs(parrSel);
		else
			ArrsSel.push(parrSel);
	    for(var j=0; j < ArrsSel.length; j++)
		{
			var currArr = ArrsSel[j];
			var startx,starty,endx,endy,startyval;		
			startx = currArr[0];
			starty = currArr[1];
			endx   = currArr[2];
			endy   = currArr[3];
			startyval = starty;
			while(startx <= endx)
			{
				//find if row exists
				if(_arrColors[startx] !== undefined)					
				{
				 	 var rowArr = _arrColors[startx];
					 starty = startyval;					
				     while(starty <= endy)
				     {
					 	 if(rowArr[starty] !== undefined)
						 {
						 	 var colorItem = rowArr[starty];
							 if(pdelete)
							 {
							 	if(colorItem.color !== undefined)
									delete colorItem.color;
							 }else
						     	colorItem.color = colorhex;								
						 }else
						 {//add cell to array
						 	if(!pdelete)
							{
								var colorItemNew = {};
								colorItemNew.color = colorhex;
								rowArr[starty] = colorItemNew;
							}
						 }
					     starty++;
					 }
				 }else
				 {//add row to array
				    if(!pdelete)
					{
						 var rowArr = [];
						 starty = startyval;					
					     while(starty <= endy)
					     {
						 	var colorItemNew = {};
							colorItemNew.color = colorhex;
							rowArr[starty] = colorItemNew;
						    starty++;
						 }
						 _arrColors[startx] = rowArr;
					}
				    
			     }	
			     startx++;
			 }//go down a row
		 }
		 RefreshTable();
    }
	function RefreshTable()
	{
		_htable.setDataAtCell (0,0,_htable.getDataAtCell(0,0)); 
		UpdateButtonStatus();
	}
	function UpdateButtonStatus()
	{
		 var selArr = _htable.getSelected();
		 if(typeof selArr !== "undefined")
		 {
			 if(selArr.length != 4) return; //some bug encountered
			 var startx,starty,endx,endy,tmp,startyval,startxval;				
			 startx = selArr[0]; //start row
			 starty = selArr[1]; //start col
			 endx   = selArr[2]; //end row
			 endy   = selArr[3]; //end col
			 var colcount = _htable.countCols();
			 var rowcount = _htable.countRows();
			  //check for reverse selection
			 if(startx > endx)
			 {
			 	tmp = endx;
				endx = startx;
				startx = tmp;
			 }
			 if(starty > endy){
			 	tmp = endy;
				endy = starty;
				starty = tmp;
			 }
			 startyval = starty;
			 startxval = startx;
			 var formatflag = 0;
			 var addedformatflag = 0;
			 var hascolor = false;
			 while(startx <= endx)
			 {
				 starty = startyval;
				 if(_arrColors[startx] !== undefined)	//has row				
				 {
				 	 var rowArr = _arrColors[startx];
					 starty = startyval;					
				     while(starty <= endy)
				     {
					 	 if(rowArr[starty] !== undefined) //has cell
						 {
						 	 var colorItem = rowArr[starty];
							 if(colorItem.color !== undefined)
						     {
							 	hascolor = true;
							 }
							 if(colorItem.fontColor !== undefined)
						     {
							 	hascolor = true;
							 }
							 if(colorItem.fflags !== undefined)
						     {
								for(var theflag in _F)
								{
							       if(!(colorItem.fflags & _F[theflag]))
								   {
							 		   formatflag &= ~_F[theflag];
									   addedformatflag |= _F[theflag];
								   }
								   else
							   	   {
								   	 	if(!(addedformatflag & _F[theflag]))
										{
											addedformatflag |= _F[theflag];
											formatflag |= _F[theflag];
										}
								   }
								}
							 }else
							 {
							 	 formatflag = 0;
								 addedformatflag = 1023;
							 }
							
						 }else
						 {
						 	formatflag = 0;
							addedformatflag = 1023;
						 }
					     starty++;
					 }
				 }else
				 {
				 	 formatflag = 0;
					 addedformatflag = 1023;
				 }
			     startx++;
			 }//go down a row
			 	if((formatflag & _F.BOLD) != $(_but.bold).prop('checked'))
				{
					$(_but.bold).prop('checked',(formatflag & _F.BOLD));
					$(_but.bold).button('refresh');
				}
				if((formatflag & _F.UNDERLINE) != $(_but.underline).prop('checked'))
				{
					$(_but.underline).prop('checked',(formatflag & _F.UNDERLINE));
					$(_but.underline).button('refresh');
				}
				if((formatflag & _F.STRIKE) != $(_but.strike).prop('checked'))
				{
					$(_but.strike).prop('checked',(formatflag & _F.STRIKE));
					$(_but.strike).button('refresh');
				}
				if((formatflag & _F.ITALIC) != $(_but.italic).prop('checked'))
				{
					$(_but.italic).prop('checked',(formatflag & _F.ITALIC));
					$(_but.italic).button('refresh');
				}
				
			 if(hascolor){
			 	$(_but.remcolor).button('enable');
			 }else
			 {
			 	$(_but.remcolor).button('disable');
			 }
			 if((startxval - endx === 0)&&(startyval - endy === 0)){
			 	//single cell
				$(_but.left).button('enable');
				$(_but.right).button('enable');
				$(_but.up).button('enable');
				$(_but.down).button('enable');
				if(startxval === 0)
				{
					$(_but.up).button('disable');
				}
				if((endx+1) === rowcount)
				{
					$(_but.down).button('disable');
				}
				if(startyval === 0)
				{
					$(_but.left).button('disable');
				}
				if((endy+1) === colcount)
				{
					$(_but.right).button('disable');
				}
			 }else
			 {
				if(startyval - endy == 0)
				{//single column, enable left/right
					if(startyval === 0)
					{
						$(_but.left).button('disable');
					}else
						$(_but.left).button('enable');
					if((endy+1) === colcount)
					{
						$(_but.right).button('disable');
					}else
						$(_but.right).button('enable');
					
					$(_but.up).button('disable');
					$(_but.down).button('disable');
					
				}else if(startxval - endx === 0)
				{//single row, enable up/down
					if(startxval === 0)
					{
						$(_but.up).button('disable');
					}else
						$(_but.up).button('enable');
					if((endx+1) === rowcount)
					{
						$(_but.down).button('disable');
					}else
						$(_but.down).button('enable');
					$(_but.left).button('disable');
					$(_but.right).button('disable');
				}else
				{//too much to move
					$(_but.left).button('disable');
					$(_but.right).button('disable');
					$(_but.up).button('disable');
					$(_but.down).button('disable');
				}
			 }
		 }
	}
	 
	function cellRenderer(instance, td, row, col, prop, value, cellProperties) {
		Handsontable.TextCell.renderer.apply(this, arguments);

		if(_cellsize == 1)
		{
			td.className = td.className + " medium";
		}else if(_cellsize == 2)
		{
			td.className = td.className + " big";
		}else if(_cellsize == 3)
		{
			td.className = td.className + " biggest";
		}
		
		if(_arrColors[row] !== undefined)
		{
			if(_arrColors[row][col] !== undefined)
		   	{
		  		var colorItem = _arrColors[row][col];
				if(colorItem.color !== undefined)
				{
					td.style.backgroundColor = colorItem.color;
				}
				if(colorItem.fflags !== undefined)
				{
					if(colorItem.fflags & _F.BOLD)
					   td.style.fontWeight = "bold";
					else
					   td.style.fontWeight = "normal";
					if(colorItem.fflags & _F.ITALIC)
					   td.style.fontStyle = "italic";
					else
					   td.style.fontStyle = "normal";
					if(colorItem.fflags & _F.STRIKE)
					   td.style.textDecoration = "line-through";
					if(colorItem.fflags & _F.UNDERLINE)
					   td.style.textDecoration = "underline";
				}
			}
		}
	}
	function SetRangeFormat(type,isset,parrSel)
	{
		type = typeof type !== 'undefined' ? type : _F.BOLD;
		isset = typeof isset !== 'undefined' ? isset : false;
		parrSel = typeof parrSel !== 'undefined' ? parrSel : _htable.getSelected();
		if(typeof parrSel === "undefined") return;
		var ArrsSel = GetAllArrs(parrSel);
		for(var j=0; j < ArrsSel.length; j++)
		{
			 var currArr = ArrsSel[j];
			 var startx,starty,endx,endy,startyval;		
			 startx = currArr[0];
			 starty = currArr[1];
			 endx   = currArr[2];
			 endy   = currArr[3];
			 var bskip = false;
			 if((j > 0) && (type < 16))
			 	bskip = true;
			 startyval = starty;
			 while(startx <= endx)
			 {
			    //find if row exists
				 if(_arrColors[startx] !== undefined)	//has row				
				 {
				 	 var rowArr = _arrColors[startx];
					 starty = startyval;					
				     while(starty <= endy)
				     {
					 	 if(rowArr[starty] !== undefined) //has cell
						 {
						 	 var colorItem = rowArr[starty];
							 if(isset)
							 {
							 	 if(colorItem.fflags !== undefined)
								 {
									 colorItem.fflags |= type;	
								 }
								 else
								 {
							 		 colorItem.fflags = type;
								 }
								    
							 }else
							 {
							 	if(colorItem.fflags !== undefined){
							 		colorItem.fflags &= ~type;
								 }
							 }
						 }else
						 {//add cell to array
						 	if(isset)
							{
							 	 var colorItemNew = {};
								 colorItemNew.fflags = type;
								 rowArr[starty] = colorItemNew;
							}
						 }
					     starty++;
					 }
				 }else
				 {//add row to array
				 	if(isset)
					{
					     var rowArr = [];
						 starty = startyval;					
					     while(starty <= endy)
					     {
						 	var colorItemNew = {};
							colorItemNew.fflags = type;
							rowArr[starty] = colorItemNew;
						    starty++;
						 }
						 _arrColors[startx] = rowArr;
				    }
			     }	
			     startx++;
			 }//go down a row
		}
		RefreshTable();
	}
		
	function isBlank(str) 
	{
    	return (!str || /^\s*$/.test(str));
	}
	function FindString(sfrom,sbegin,send,barr,ibegin)
	{
		ibegin = typeof ibegin !== 'undefined' ? ibegin : 0;
		barr = typeof barr !== 'undefined' ? barr : false;
		if(barr)
		{//find all rows in array
			var retarr = [];
			var iend = 0;
			var iwhatlength = -1;
			var irow = -1;
			var srow = "";
			while(true)
			{
				ibegin = sfrom.indexOf(sbegin,ibegin);
		 		if( ibegin == -1) return retarr;
				ibegin = ibegin + sbegin.length
				iend = sfrom.indexOf(send,ibegin);
				if( iend == -1) return retarr;
				srow = sfrom.substring(ibegin,iend);
				irow = parseInt(srow,10);
				if(irow >= 0 && irow < 5000)
				{
					ibegin = iend + 1;
					iend = sfrom.indexOf("{/}",ibegin);
					if( iend == -1) return retarr;
					retarr[irow] = sfrom.substring(ibegin,iend);
				}
				ibegin = iend + 3;
			}
		}else
		{
			var retstr = "";
			var iend = -1;
			var iwhatlength = -1;
			ibegin = sfrom.indexOf(sbegin,ibegin);
	 		if( ibegin == -1) return retstr;
			ibegin = ibegin + sbegin.length
			iend = sfrom.indexOf(send,ibegin);
			if( iend == -1) return retstr;
			retstr = sfrom.substring(ibegin,iend);
			return retstr;	
		}
	}
	
	function UpdateRows(start,count)
	{
		var counttemp = count;
		if(start < _arrColors.length)
		{
			if((start + count) >= _arrColors.length)
			{
				counttemp = _arrColors.length - start;
			}
			_arrColors.splice(start,counttemp);
		}
		counttemp = count;
		if(start < _arrSpecial.length)
		{
			if((start + count) >= _arrSpecial.length)
			{
				counttemp = _arrSpecial.length - start;
			}
			_arrSpecial.splice(start,counttemp);
		}
		counttemp = count;
		while(counttemp !== 0)
		{
			for(var i=0; i < _arrMerge.length; i++)
			{
				if(_arrMerge[i] === undefined) continue;
					var arrMerge = _arrMerge[i];
					if(arrMerge[2] >= ((start-1) + counttemp))
					{
						if(arrMerge[0] <= ((start-1) + counttemp))
						{
							_arrMerge.splice(i,1);
							i--;
						}
					}
		 	 }
			 counttemp--;
		}
	}
	function UpdateColumns(start,count)
	{
		var counttemp = count;
		for(var ii=0 ; ii < _arrColors.length ; ii++)
		{
			if(_arrColors[ii] === undefined) continue;
			counttemp = count;
			var arrRow = _arrColors[ii];
			if(start < arrRow.length)
			{
				if((start + count) >= arrRow.length)
				{
					counttemp = arrRow.length - start;
				}
				arrRow.splice(start,counttemp);
			}
		}
		for(var j=0 ; j < _arrSpecial.length ; j++)
		{
			if(_arrSpecial[j] === undefined) continue;
			counttemp = count;
			var arrRow = _arrSpecial[j];
			if(start < arrRow.length)
			{
				if((start + count) >= arrRow.length)
				{
					counttemp = arrRow.length - start;
				}
				arrRow.splice(start,counttemp);
			}
		}
		counttemp = count;
		while(counttemp !== 0)
		{
			for(var i=0; i < _arrMerge.length; i++)
			{
				if(_arrMerge[i] === undefined) continue;
				var arrMerge = _arrMerge[i];
				if(arrMerge[3] >= ((start-1) + counttemp))
				{
					if(arrMerge[1] <= ((start-1) + counttemp))
					{
						_arrMerge.splice(i,1);
						i--;
					}
				}
		 	 }
			 counttemp--;
		}
	}
	
		
	function UpdateRowsCreate(start,count)
	{
		var counttemp = count;
		if(start < _arrColors.length)
		{
			_arrColors.splice(start,0,[]);
		}
		counttemp = count;
		if(start < _arrSpecial.length)
		{
			_arrSpecial.splice(start,0,[]);
		}
		counttemp = count;
		while(counttemp !== 0)
		{
			for(var i=0; i < _arrMerge.length; i++)
			{
				if(_arrMerge[i] === undefined) continue;
					var arrMerge = _arrMerge[i];
					if(arrMerge[2] >= ((start-1) + counttemp))
					{
						if(arrMerge[0] <= ((start-1) + counttemp))
						{
							_arrMerge.splice(i,1);
							i--;
						}
					}
		 	 }
			 counttemp--;
		}
	}
	
	function UpdateColumnsCreate(start,count)
	{
		var counttemp = count;
		for(var ii=0 ; ii < _arrColors.length ; ii++)
		{
			if(_arrColors[ii] === undefined) continue;
			counttemp = count;
			var arrRow = _arrColors[ii];
			if(start < arrRow.length)
			{
				arrRow.splice(start,0,{});
			}
		}
		for(var j=0 ; j < _arrSpecial.length ; j++)
		{
			if(_arrSpecial[j] === undefined) continue;
			counttemp = count;
			var arrRow = _arrSpecial[j];
			if(start < arrRow.length)
			{
				arrRow.splice(start,0,{});
			}
		}
		counttemp = count;
		while(counttemp !== 0)
		{
			for(var i=0; i < _arrMerge.length; i++)
			{
				if(_arrMerge[i] === undefined) continue;
				var arrMerge = _arrMerge[i];
				if(arrMerge[3] >= ((start-1) + counttemp))
				{
					if(arrMerge[1] <= ((start-1) + counttemp))
					{
						_arrMerge.splice(i,1);
						i--;
					}
				}
		 	 }
			 counttemp--;
		}
	}
		//init
		(function(){
			 //clean up
			  $("div.w3exvtfdel").remove();    
			  $("div.sp-container").remove();        		 
			  $("div[aria-describedby='w3exvtf-dialogstyles']").remove();
		      //show table
             _dataItems.push([" ", " "," "," "]);
             _dataItems.push([" "," "," "," "]);
             _dataItems.push([" "," "," "," "]);
			 _dataItems.push([" "," "," "," "]);
						 
            	$(_thetable).handsontable({
              	data: _dataItems,
      			startRows: 4,
 				startCols: 4,
      			contextMenu: ["row_above", "row_below", "col_left", "col_right", "remove_row", "remove_col"],
      			outsideClickDeselects: false,
                fillHandle:false,
                cells: function (row, col, prop) {
	                var cellProperties = {};
	                cellProperties.renderer = cellRenderer;
	                return cellProperties;
                },
			    afterSelectionEnd: UpdateButtonStatus,
			    beforeRemoveRow: UpdateRows,
			    beforeRemoveCol: UpdateColumns,
				afterCreateRow: UpdateRowsCreate,
				afterCreateCol: UpdateColumnsCreate
            });//END:show table
			_htable = $(_thetable).handsontable('getInstance');			
			//show color picker
			$("#w3exvtf-custom").spectrum({
				color:'#ffffff',
				className: "full-spectrum",
				appendTo: "#w3exvtf-topparent",
				showPalette: true,
				showInput: true,
				showSelectionPalette: true,
				maxPaletteSize: 10,
				preferredFormat: "hex",
				change: ChangeColor,
                palette: _arrPalette
          	});
			$('#w3exvtf-colsm').click(function(){
				var Cols = _htable.countCols();
				Cols--;
				if(Cols > 0)
				{
					_htable.alter('remove_col',Cols);
				}
	    	});
        	$('#w3exvtf-colsp').click(function(){
        		var Cols = _htable.countCols();
        		_htable.alter('insert_col',Cols);
        	});
        	$('#w3exvtf-rowsm').click(function(){
   				var Rows = _htable.countRows();
				Rows--;
				if(Rows > 0)
        		{
					_htable.alter('remove_row',Rows);
        		}
			});
        	$('#w3exvtf-rowsp').click(function(){
				var Rows = _htable.countRows();
				_htable.alter('insert_row',Rows);
			});
			$('#w3exvtf-cellmin').button().click(function(){
				if(_cellsize === 1 || _cellsize === 0)
				{
					$(this).button('disable');
				}else if(_cellsize === 3)
				{
					$('#w3exvtf-cellplus').button('enable');	
				}
				if(_cellsize !== 0)
					_cellsize--;
				RefreshTable();
			});
			$('#w3exvtf-cellplus').button().click(function(){
				if(_cellsize === 2 || _cellsize === 3)
				{
					$(this).button('disable');
				}else if(_cellsize === 0)
				{
					$('#w3exvtf-cellmin').button('enable');	
				}
				if(_cellsize !== 3)
					_cellsize++;
				RefreshTable();
			});
			$('#w3exvtf-cellplus').button("enable");
			$('#w3exvtf-cellmin').button("disable");
			$(_but.remcolor).button().click(function(){
				parrSel = _htable.getSelected();
				if(typeof parrSel === "undefined") return;
				var ArrsSel = GetAllArrs(parrSel);
				for(var j=0; j < ArrsSel.length; j++)
				{
					var currArr = ArrsSel[j];
					var startx,starty,endx,endy,startyval;		
					startx = currArr[0];
					starty = currArr[1];
					endx   = currArr[2];
					endy   = currArr[3];
					startyval = starty;
					while(startx <= endx)
					{
						if(_arrColors[startx] !== undefined)			
						{
							var rowArr = _arrColors[startx];
							starty = startyval;					
						    while(starty <= endy)
						    {
								if(rowArr[starty] !== undefined)
								{
									var colorItem = rowArr[starty];
								    if(colorItem.color !== undefined)
									{
										delete colorItem.color;
									}	
									if(colorItem.fontColor !== undefined)
									{
										delete colorItem.fontColor;
									}								
								}
							    starty++;
							}
						}
					    startx++;
					}
				}
				$(_but.remcolor).button("disable");
				RefreshTable();
			});
			$(_but.remcolor).button("disable");
			$(_but.italic).button().click(function(e){
				var isset = $(_but.italic).prop('checked');
				SetRangeFormat(_F.ITALIC,isset);
			});
			$(_but.strike).button().click(function(e){
				var isset = $(_but.strike).prop('checked');
				SetRangeFormat(_F.STRIKE,isset);
			});
			$(_but.underline).button().click(function(e){
				var isset = $(_but.underline).prop('checked');
				SetRangeFormat(_F.UNDERLINE,isset);
			});
			$(_but.bold).button().click(function(e){
				var isset = $(_but.bold).prop('checked');
				SetRangeFormat(_F.BOLD,isset);
        	 });
			$(_but.up).button({
	        	text: false,
	       		icons: { primary: "ui-icon-arrowthick-1-n" , secondary: null }}).click(function(e)
				{
					HandleMove("up");
				});
			$(_but.down).button({
	        	text: false,
	        	icons: { primary: "ui-icon-arrowthick-1-s" , secondary: null }}).click(function(e)
				{
				 	HandleMove("down");
				});
			$(_but.left).button({
	        	text: false,
	        	icons: { primary: "ui-icon-arrowthick-1-w" , secondary: null }}).click(function(e)
				{
					HandleMove("left");
				});
			$(_but.right).button({
	        	text: false,
	        	icons: { primary: "ui-icon-arrowthick-1-e" , secondary: null }}).click(function(e)
				{
					HandleMove("right");
				});
				
			$('#w3exvtf-gendata').button().click(function(){
				var text = $('#w3exvtf-importdata').val();
				if(isBlank(text)) return;
				var delimiter =  $('#w3exvtf-delimiter').val();
				if(!delimiter) return;
				var iscomma = false;
				if(delimiter == ",") iscomma = true; 
				var split = text.split('\n');
				var lines = [];
				for (var i = 0; i < split.length; i++)
					if (split[i]) lines.push(split[i]);
			    if(lines.length == 0)
					return;
				var oldrows = _htable.countRows();
				var oldcols = _htable.countCols();
			    var biggestval =0;
				for (var i = 0; i < lines.length; i++)
				{
					var row = lines[i];
					var datarow = [];
					_dataItems[i] = datarow;
					var rowitems = [];
					rowitems = row.split(delimiter);
					for(var j=0; j < rowitems.length ; j++)
					{
						if(iscomma){
							var cellitem = rowitems[j];
							if(cellitem.indexOf("\"") == 0)
								cellitem = cellitem.substring(1,cellitem.length);
							if(cellitem.indexOf("\"") == (cellitem.length -1))
								cellitem = cellitem.substring(0,cellitem.length -1);
							cellitem = cellitem.replace(/""/g,"\"");
							datarow[j] = cellitem;
						}else
							datarow[j] = rowitems[j];
					}
					if(j > biggestval)
					{
						biggestval = j;	
					}
				}
				if(biggestval == 0) return;
				if(_dataItems.length > i)
				{
					_dataItems.splice(i,_dataItems.length);
				}
				var Cols = _htable.countCols();
				while(Cols < biggestval)
			  	{
					_htable.alter('insert_col',null);
					Cols++;
				}
				if(oldcols > Cols)
					UpdateColumns(Cols,oldcols - Cols);
				Cols = _htable.countRows();
				if(oldrows > Cols)
					UpdateRows(Cols,oldrows - Cols);
				RefreshTable();
			});
			$('#w3exvtf-inserttable').button().click(function(){
				var textshortcode = GenerateShortcode();
				W3Ex.vtfModuleOut.FillText(textshortcode); 
			});
			$('#w3exvtf-genshortcode').button().click(function(){
				GenerateFromShortcode();
			});
		})();
    
    var custom_uploader;
 
	tb_position = function() {
		var isIE6 = typeof document.body.style.maxHeight === "undefined";
		jQuery("#TB_window").css({marginLeft: '-' + parseInt((TB_WIDTH / 2),10) + 'px', width: TB_WIDTH + 'px'});
		if ( ! isIE6 ) { // take away IE6
			jQuery("#TB_window").css({marginTop: '-' + parseInt((TB_HEIGHT / 2),10) + 'px'});
		}
    };
	
})(jQuery);
