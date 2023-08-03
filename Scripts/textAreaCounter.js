/*
 * jQuery Textarea Characters Counter Plugin v 2.0
 * Examples and documentation at: http://roy-jin.appspot.com/jsp/textareaCounter.jsp
 * Copyright (c) 2010 Roy Jin
 * Version: 2.0 (11-JUN-2010)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Requires: jQuery v1.4.2 or later
 */
(function($){  
	$.fn.textareaCount = function(options, fn) 
	{   
		var defaults = {  
			maxCharacterSize: -1,
			maxWordSize: -1,  
			originalStyle: 'originalTextareaInfo',
			warningStyle: 'warningTextareaInfo',  
			warningNumber: 20,
			displayFormat: '#input caracteres | #words palabras'
		};  
		var options = $.extend(defaults, options);
		
		var container = $(this);
		
		$("<div class='charleft'>&nbsp;</div>").insertAfter(container);
		
		//create charleft css
		var charLeftCss = {
			'width' : container.width()
		};
		
		var charLeftInfo = getNextCharLeftInformation(container);
		charLeftInfo.addClass(options.originalStyle);
		charLeftInfo.css(charLeftCss);
		
		var numInput = 0;
		var maxCharacters = options.maxCharacterSize;
		var numLeft = 0;
		var numWords = 0;
		var maxWords = options.maxWordSize;
				
		container.bind	('keyup', function(event)
								{
									limitTextAreaByWordCount();
									limitTextAreaByCharacterCount();
								}
						)
				 .bind('mouseover', function(event)
				 					{
										setTimeout(	function()
														{
															limitTextAreaByWordCount();
															limitTextAreaByCharacterCount();
														}, 10
													);
									}
						)
				 .bind('paste', function(event)
				 				{
									
									setTimeout	(	function()
													{
														
														limitTextAreaByWordCount();
														limitTextAreaByCharacterCount();
													}, 10
												);
								}
						);
		
		function limitTextAreaByWordCount()
		{
			var nPalabras=countWord(getCleanedWordString(container.val()));	
			if(nPalabras>maxWords)
			{

				cortarPalabras(container.val());	
			}
		}
		
		function limitTextAreaByCharacterCount()
		{
			charLeftInfo.html(countByCharacters());
			if(typeof fn != 'undefined')
			{
				fn.call(this, getInfo());
			}
			return true;
		}
		
		function countByCharacters()
		{
			var content = container.val();
			var contentLength = content.length;
			
			if(options.maxCharacterSize > 0)
			{
				//If copied content is already more than maxCharacterSize, chop it to maxCharacterSize.
				if(contentLength >= options.maxCharacterSize) 
				{
					content = content.substring(0, options.maxCharacterSize); 				
				}
				
				var newlineCount = getNewlineCount(content);
				
				var systemmaxCharacterSize = options.maxCharacterSize - newlineCount;
				if (!isWin())
				{
					 systemmaxCharacterSize = options.maxCharacterSize
				}
				if(contentLength > systemmaxCharacterSize)
				{
					//avoid scroll bar moving
					var originalScrollTopPosition = this.scrollTop;
					container.val(content.substring(0, systemmaxCharacterSize));
					this.scrollTop = originalScrollTopPosition;
				}
				charLeftInfo.removeClass(options.warningStyle);
				if(systemmaxCharacterSize - contentLength <= options.warningNumber)
				{
					charLeftInfo.addClass(options.warningStyle);
				}
				
				numInput = container.val().length + newlineCount;
				if(!isWin())
				{
					numInput = container.val().length;
				}
			
				numWords = countWord(getCleanedWordString(container.val()));
				
				numLeft = maxCharacters - numInput;
			} 
			else 
			{
				//normal count, no cut
				var newlineCount = getNewlineCount(content);
				numInput = container.val().length + newlineCount;
				if(!isWin()){
					numInput = container.val().length;
				}
				numWords = countWord(getCleanedWordString(container.val()));
			}
			return formatDisplayInfo();
		}
		
		function formatDisplayInfo()
		{
			var format = options.displayFormat;
			format = format.replace('#input', numInput);
			format = format.replace('#words', numWords);
			//When maxCharacters <= 0, #max, #left cannot be substituted.
			if(maxCharacters > 0)
			{
				format = format.replace('#max', maxCharacters);
				format = format.replace('#left', numLeft);
			}
			if(maxWords>0)
			{
				format = format.replace('#maxWord', maxWords);
			}
			return format;
		}
		
		function getInfo()
		{
			var info = 
			{
				input: numInput,
				max: maxCharacters,
				left: numLeft,
				words: numWords
			};
			return info;
		}
		
		function getNextCharLeftInformation(container)
		{
				return container.next('.charleft');
		}
		
		function isWin()
		{
			var strOS = navigator.appVersion;
			if (strOS.toLowerCase().indexOf('win') != -1)
			{
				return true;
			}
			return false;
		}
		
		function getNewlineCount(content)
		{
			var newlineCount = 0;
			for(var i=0; i<content.length;i++)
			{
				if(content.charAt(i) == '\n')
				{
					newlineCount++;
				}
			}
			return newlineCount;
		}
		
		function getCleanedWordString(content)
		{
			var fullStr = content + " ";
			var initial_whitespace_rExp = /^[^A-Za-z0-9áéíóúÁÉÍÓÚÑñ]+/gi;
			var left_trimmedStr = fullStr.replace(initial_whitespace_rExp, "");
			var non_alphanumerics_rExp = rExp = /[^A-Za-z0-9áéíóúÁÉÍÓÚÑñ]+/gi;
			var cleanedStr = left_trimmedStr.replace(non_alphanumerics_rExp, " ");
			var splitString = cleanedStr.split(" ");
			return splitString;
		}
		
		function countWord(cleanedWordString)
		{
			var word_count = cleanedWordString.length-1;
			return word_count;
		}
		
		function cortarPalabras(content)
		{
			
			var fullStr = content + " ";
			var initial_whitespace_rExp = /^[^A-Za-z0-9áéíóúÁÉÍÓÚÑñ]+/gi;
			var left_trimmedStr = fullStr.replace(initial_whitespace_rExp, "");
			var non_alphanumerics_rExp = rExp = /[^A-Za-z0-9áéíóúÁÉÍÓÚÑñ]+/gi;
			var x;
			var caracter;
			var nPalabras=0;
			var cadenaFinal='';
			var palabra='';
			for(x=0;x<left_trimmedStr.length;x++)
			{
				caracter=left_trimmedStr.substr(x,1);
				palabra+=caracter;	
				if(non_alphanumerics_rExp.test(caracter))
				{
					if(palabra.trim()!='')
					{
						nPalabras++;
						if(nPalabras>maxWords)
							break;	
					}
					cadenaFinal+=palabra;
					palabra='';
				}
				
			}
			container.val(cadenaFinal.trim());
		}
	};  
})(jQuery); 