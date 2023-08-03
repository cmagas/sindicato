/*
 * Thickbox 2.0 - One Box To Rule Them All.
 * By Cody Lindley (http://www.codylindley.com)
 * Copyright (c) 2006 cody lindley
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 * Thickbox is built on top of the very light weight jQuery library.
 */
//on page load call TB_init
try
{
	jQuery.noConflict();
}
catch(err)
{
	
}
jQuery(document).ready(TB_init);
var imgLoader = '../Scripts/thickbox/loader.gif';
//add thickbox to href elements that have a class of .thickbox
function TB_init()
{
	jQuery("a.thickbox").click(function(){
	var t = this.title || this.name || null;
	var g = this.rel || false;
	TB_show(t,this.href,g);
	this.blur();
	return false;
	});
}

function TB_show(caption, url, imageGroup, scrol, funcion) 
{//function called when the user clicks on a thickbox link

	try 
	{
		
		if(funcion!=undefined)
			funcionCierreThickBox=funcion;
		
		if (document.getElementById("TB_HideSelect") == null) 
		{
			jQuery("body").append("<iframe id='TB_HideSelect'></iframe><div id='TB_overlay'></div><div id='TB_window'></div>");
		}
		
		if(caption==null)
			{caption=""};
		
		jQuery(window).scroll(TB_position);
 		
		TB_overlaySize();
		
		jQuery("body").append("<div id='TB_load'><img src='"+imgLoader+"' /></div>");
		TB_load_position();
		
		var urlString = /\.jpg|\.jpeg|\.png|\.gif|\.html|\.htm|\.php|\.cfm|\.asp|\.aspx|\.jsp|\.jst|\.rb|\.txt|\.bmp/g;
		var urlType = url.toLowerCase().match(urlString);
		
		if(urlType == '.jpg' || urlType == '.jpeg' || urlType == '.png' || urlType == '.gif' || urlType == '.bmp')
		{//code to show images
				
			TB_PrevCaption = "";
			TB_PrevURL = "";
			TB_PrevHTML = "";
			TB_NextCaption = "";
			TB_NextURL = "";
			TB_NextHTML = "";
			TB_imageCount = "";
			TB_FoundURL = false;
			if(imageGroup)
			{
				TB_TempArray = jQuery("a[@rel="+imageGroup+"]").get();
				for (TB_Counter = 0; ((TB_Counter < TB_TempArray.length) && (TB_NextHTML == "")); TB_Counter++) 
				{
					var urlTypeTemp = TB_TempArray[TB_Counter].href.toLowerCase().match(urlString);
						if (!(TB_TempArray[TB_Counter].href == url)) 
						{						
							if (TB_FoundURL) 
							{
								TB_NextCaption = TB_TempArray[TB_Counter].title;
								TB_NextURL = TB_TempArray[TB_Counter].href;
								TB_NextHTML = "<span id='TB_next'>&nbsp;&nbsp;<a href='#'>Next &gt;</a></span>";
							} 
							else 
							{
								TB_PrevCaption = TB_TempArray[TB_Counter].title;
								TB_PrevURL = TB_TempArray[TB_Counter].href;
								TB_PrevHTML = "<span id='TB_prev'>&nbsp;&nbsp;<a href='#'>&lt; Prev</a></span>";
							}
						} 
						else 
						{
							TB_FoundURL = true;
							TB_imageCount = "Image " + (TB_Counter + 1) +" of "+ (TB_TempArray.length);											
						}
				}
			}

			imgPreloader = new Image();
			imgPreloader.onload = function(){
			
			imgPreloader.onload = null;
				
			// Resizing large images - orginal by Christian Montoya edited by me.
			var pagesize = TB_getPageSize();
			var x = pagesize[0] - 150;
			var y = pagesize[1] - 150;
			var imageWidth = imgPreloader.width;
			var imageHeight = imgPreloader.height;
			if (imageWidth > x) {
				imageHeight = imageHeight * (x / imageWidth); 
				imageWidth = x; 
				if (imageHeight > y) { 
					imageWidth = imageWidth * (y / imageHeight); 
					imageHeight = y; 
				}
			} else if (imageHeight > y) { 
				imageWidth = imageWidth * (y / imageHeight); 
				imageHeight = y; 
				if (imageWidth > x) { 
					imageHeight = imageHeight * (x / imageWidth); 
					imageWidth = x;
				}
			}
			// End Resizing
			TB_WIDTH = imageWidth + 30;
			TB_HEIGHT = imageHeight + 60;
			jQuery("#TB_window").append("<a href='' id='TB_ImageOff' title='Cerrar'><img id='TB_Image' src='"+url+"' width='"+imageWidth+"' height='"+imageHeight+"' alt='"+caption+"'/></a>" + "<div id='TB_caption'>"+caption+"<div id='TB_secondLine'>" + TB_imageCount + TB_PrevHTML + TB_NextHTML + "</div></div><div id='TB_closeWindow'><a href='#' id='TB_closeWindowButton' title='Cerrar'>Cerrar</a></div>"); 		
			jQuery("#TB_closeWindowButton").click(TB_remove);
			
			if (!(TB_PrevHTML == "")) 
			{
				function goPrev()
				{
					if(jQuery(document).unclick(goPrev)){jQuery(document).unclick(goPrev)};
					jQuery("#TB_window").remove();
					jQuery("body").append("<div id='TB_window'></div>");
					jQuery(document).unkeyup();
					TB_show(TB_PrevCaption, TB_PrevURL, imageGroup);
					return false;	
				}
			
				jQuery("#TB_prev").click(goPrev);
				
				jQuery(document).keyup( function(e){ var key = e.keyCode; if(key == 37){goPrev()} });
			}
			
			
			
			if (!(TB_NextHTML == "")) 
			{		
				function goNext(){
					jQuery("#TB_window").remove();
					jQuery("body").append("<div id='TB_window'></div>");
					jQuery(document).unkeyup();
					TB_show(TB_NextCaption, TB_NextURL, imageGroup);				
					return false;	
				}
				
				jQuery("#TB_next").click(goNext);
			
				jQuery(document).keyup( function(e){ var key = e.keyCode; if(key == 39){goNext()} });
			}
			
			TB_position();
			jQuery("#TB_load").remove();
			jQuery("#TB_ImageOff").click(TB_remove);
			jQuery("#TB_window").css({display:"block"}); //for safari using css instead of show
			}
	  
			imgPreloader.src = url;
		}
		
		if(urlType=='.htm'||urlType=='.html'||urlType=='.php'||urlType=='.asp'||urlType=='.aspx'||urlType=='.jsp'||urlType=='.jst'||urlType=='.rb'||urlType=='.txt'||urlType=='.cfm' || (url.indexOf('TB_inline') != -1) || (url.indexOf('TB_iframe') != -1) )
		{//code to show html pages
			
			var queryString = url.replace(/^[^\?]+\??/,'');
			var params = TB_parseQuery( queryString );
			
			TB_WIDTH = (params['width']*1) + 30;
			TB_HEIGHT = (params['height']*1) + 40;
			ajaxContentW = TB_WIDTH - 30;
			ajaxContentH = TB_HEIGHT - 45;
			
			if(url.indexOf('TB_iframe') != -1)
			{				
					urlNoQuery=url.substr(0,url.indexOf('TB_iframe')-1);
					
					jQuery("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+caption+"</div><script type='text/javascript'>document.onkeydown = parent.inicia;</script><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton'>Cerrar</a></div></div><iframe src='"+urlNoQuery+"' id='TB_iframeContent' "+scrol+" style='width:"+(ajaxContentW + 30)+"px;height:"+(ajaxContentH + 18)+"px;'></iframe><script type='text/javascript'></script>");
			}
			else 

			{
					jQuery("#TB_window").append("<div id='TB_title'><div id='TB_ajaxWindowTitle'>"+caption+"</div><script type='text/javascript'>document.onkeydown = parent.inicia;</script><div id='TB_closeAjaxWindow'><a href='#' id='TB_closeWindowButton'>Cerrar</a></div></div><div id='TB_ajaxContent' "+scrol+" style='width:"+ajaxContentW+"px;height:"+ajaxContentH+"px;'></div>");
			}
					
			jQuery("#TB_closeWindowButton").click(TB_remove);
			
				if(url.indexOf('TB_inline') != -1)
				{	
					
					jQuery("#TB_ajaxContent").html(jQuery('#' + params['inlineId']).html());
					TB_position();
					jQuery("#TB_load").remove();
					jQuery("#TB_window").css({display:"block"}); 
				}
				else 
				if(url.indexOf('TB_iframe') != -1)
				{
					
					TB_position();
					jQuery("#TB_load").remove();
					jQuery("#TB_window").css({display:"block"}); 
				}
				else
				{
					
					jQuery("#TB_ajaxContent").load(url, function(){
						TB_position();
						jQuery("#TB_load").remove();
						jQuery("#TB_window").css({display:"block"}); 
					});
				}
			
		}
		
		jQuery(window).resize(TB_position);
		
	} catch(e) {
		alert( e );
	}
}

//helper functions below

function TB_remove() 
{
	
	
	if (typeof (funcionCierreThickBox)!='undefined')
	{
		
		funcionCierreThickBox();
	}
	try
	{		
		
		jQuery("#TB_window").fadeOut("fast",function()
											{
												jQuery('#TB_window,#TB_overlay,#TB_HideSelect').remove();
											}
									);
	}
	catch(ex)
	{
		
		jQuery('#TB_window,#TB_overlay,#TB_HideSelect').remove();
	}
	try
	{
		
		jQuery("#TB_load").remove();
		jQuery(document).unkeyup();
		return false;
	}
	catch(ex)
	{
		

	}
	
}

function TB_position() 
{
	var pagesize = TB_getPageSize();	
	var arrayPageScroll = TB_getPageScrollTop();
	
	jQuery("#TB_window").css({width:TB_WIDTH+"px",left: ((pagesize[0] - TB_WIDTH)/2)+"px", top: (arrayPageScroll[1] + ((pagesize[1]-TB_HEIGHT)/2))+"px" });
	TB_overlaySize();
}

function TB_overlaySize()
{
	if (window.innerHeight && window.scrollMaxY) 
	{	
		yScroll = window.innerHeight + window.scrollMaxY;
	} 
	else 
		if 
		(document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
			yScroll = document.body.scrollHeight;
		} 
		else 
		{ // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
			yScroll = document.body.offsetHeight;
		}
	jQuery("#TB_overlay").css("height",yScroll +"px");
	jQuery("#TB_HideSelect").css("height",yScroll +"px");
}

function TB_load_position() 
{
	var pagesize = TB_getPageSize();
	var arrayPageScroll = TB_getPageScrollTop();

	jQuery("#TB_load")
	.css({left: ((pagesize[0] - 100)/2)+"px", top: (arrayPageScroll[1] + ((pagesize[1]-100)/2))+"px" })
	.css({display:"block"});
}

function TB_parseQuery ( query ) 
{
   var Params = new Object ();
   if ( ! query ) return Params; // return empty object
   var Pairs = query.split(/[;&]/);
   for ( var i = 0; i < Pairs.length; i++ ) {
      var KeyVal = Pairs[i].split('=');
      if ( ! KeyVal || KeyVal.length != 2 ) continue;
      var key = unescape( KeyVal[0] );
      var val = unescape( KeyVal[1] );
      val = val.replace(/\+/g, ' ');
      Params[key] = val;
   }
   
   return Params;
}

function TB_getPageScrollTop()
{
	var yScrolltop;
	if (self.pageYOffset) {
		yScrolltop = self.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){	 // Explorer 6 Strict
		yScrolltop = document.documentElement.scrollTop;
	} else if (document.body) {// all other Explorers
		yScrolltop = document.body.scrollTop;
	}
	arrayPageScroll = new Array('',yScrolltop) 
	return arrayPageScroll;
}

function TB_getPageSize()
{
	var de = document.documentElement;
	var w = window.innerWidth || self.innerWidth || (de&&de.clientWidth) || document.body.clientWidth;
	var h = window.innerHeight || self.innerHeight || (de&&de.clientHeight) || document.body.clientHeight;
	
	arrayPageSize = new Array(w,h) 
	return arrayPageSize;
}

function TB_strpos(str, ch) 
{
for (var i = 0; i < str.length; i++)
if (str.substring(i, i+1) == ch) return i;
return -1;
}

