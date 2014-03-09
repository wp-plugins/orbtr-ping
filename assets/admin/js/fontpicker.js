(function($){
jQuery.fn.fontPicker = function(options) {

	var settings = $.extend( {
		'hide_fallbacks' : false,
		'selected' : function(style) {},
		'initial' : ''
	}, options),
		html = '<div id="fontpicker"><div id="fontSelect"><span>Arial</span><div class="arrow-down"></div><ul><li>Arial,Helvetica,sans-serif</li><li>Arial Black,Arial Black,Gadget,sans-serif</li><li>Comic Sans MS,Comic Sans MS,cursive</li><li>Courier New,Courier New,Courier,monospace</li><li>Georgia,Georgia,serif</li><li>Impact,Charcoal,sans-serif</li><li>Lucida Console,Monaco,monospace</li><li>Lucida Sans Unicode,Lucida Grande,sans-serif</li><li>Palatino Linotype,Book Antiqua,Palatino,serif</li><li>Tahoma,Geneva,sans-serif</li><li>Times New Roman,Times,serif</li><li>Trebuchet MS,Helvetica,sans-serif</li><li>Verdana,Geneva,sans-serif</li><li>Gill Sans,Geneva,sans-serif</li></ul></div><p class="style-actions"><a class="style" id="bold" href="#" title="Bold">B</a><a class="style" id="italic" href="#" title="Italic">I</a><a class="style" id="underline" href="#" title="Underline">U</a></p><div id="fontSizeSelect"><span>24px</span><div class="arrow-down"></div><ul><li>10px</li><li>12px</li><li>14px</li><li>16px</li><li>18px</li><li>20px</li><li>22px</li><li>24px</li><li>26px</li><li>28px</li><li>30px</li><li>36px</li><li>42px</li><li>50px</li></ul></div><p class="font_preview"> Preview Text </p><div style="text-align: right; padding-top: 10px;"><a href="#" class="cancelfont">Cancel</a><a href="#" class="savefont">Choose</a></div></div>',
		button_html = '<div class="fontpicker"><a href="#">Choose Font&hellip;</a><span></span></div>'
	;
	
	var picker = '',
		preview = '',
		getStyles = function(text) {
			var styletag=text;
			var stylestemp=styletag.split(';');
			var styles={};
			var c='';
			for (var x in stylestemp) {
				c=stylestemp[x].split(':');
				styles[$.trim(c[0])]=$.trim(c[1]);
			}	
			return styles;
		}
	;
	
	return this.each(function() {

		var input = $(this),
			button = button_html,
			initial = getStyles(input.val())
		;
		
		if (!initial['font-family'])
		{
			initial['font-family'] = '';	
		}
		
		input.hide();
		input.after(button);
		input
			.next('.fontpicker')
			.find('span')
				.attr('style', input.val())
				.text(initial['font-family'].substr(0, initial['font-family'].indexOf(',')))
		;
		input.next('.fontpicker').find('a').click(function(){
			var styles = getStyles(input.val());
			picker = $(html).appendTo($(this).parent().parent()).hide();
			preview = $('.font_preview');
			
			if (!styles['font-family'])
			{
				styles['font-family'] = 'Arial,Helvetica,sans-serif';
			}
			
			if (!styles['font-size'])
			{
				styles['font-size'] = '12px';
			}
			
			if (styles['font-weight'] == 'bold')
			{
				$('#bold').addClass('checked');
				preview.css('font-weight', styles['font-weight']);
			}
			else
			{
				preview.css('font-weight', 'normal');
			}
			
			if (styles['font-style'])
			{
				$('#italic').addClass('checked');
				preview.css('font-style', styles['font-style']);
			}
			
			if (styles['text-decoration'])
			{
				$('#underline').addClass('checked');
				preview.css('text-decoration', styles['text-decoration']);
			}
			
			$('#fontSelect').fontSelector({
				'hide_fallbacks' : true,
				'initial' : styles['font-family'],
				'selected' : function(style) {
					$('.font_preview').css({'font-family': style});
				}
			});
			
			$('#fontSizeSelect').fontSizeSelector({
				'hide_fallbacks' : true,
				'initial' : styles['font-size'],
				'selected' : function(style) {
					$('.font_preview').css({'font-size': style});
				}
			});
			
			$('p a.style').click(function(){
				var that = $(this);
				var style = that.attr('id'); // This will be bold, italic or underline.
				if (that.hasClass('checked'))
				{
					switch(style) 
					{
						case 'bold':
							preview.css('font-weight', 'normal');
							break;
						case 'italic':
							preview.css('font-style', '');
							break;
						case 'underline':
							preview.css('text-decoration', '');
							break;
						default:
							break;	
					}
					that.removeClass('checked');	
				}
				else
				{
					switch(style) 
					{
						case 'bold':
							preview.css('font-weight', style);
							break;
						case 'italic':
							preview.css('font-style', style);
							break;
						case 'underline':
							preview.css('text-decoration', style);
							break;
						default:
							break;	
					}
					that.addClass('checked');	
				}
				return false;
			});
			
			picker
				.find('.savefont')
					.click(function() {
						//console.log($('.font_preview').attr('style'));
						input.val($('.font_preview').attr('style'));
						//console.log(input.val());
						var s = getStyles(input.val());
						if (!s['font-family'])
						{
							s['font-family'] = '';	
						}
						input
							.next('.fontpicker')
							.find('span')
								.attr('style', input.val())
								.text(s['font-family'].substr(0, s['font-family'].indexOf(',')))
						;
						preview.remove();
						picker.remove();
						return false;	
					})
			;
			
			picker
				.find('.cancelfont')
					.click(function(){
						preview.remove();
						picker.remove();	
						return false;
					})
			;
			
			$(document).mouseup(function (e)
			{
				var container = picker;
			
				if (container.has(e.target).length === 0)
				{
					preview.remove();
					container.remove();
				}
			});
			
			picker.css({top: $(this).position().top + $(this).height(), left: $(this).position().left + 8}).show();
			return false;
		});
		
	});
}
})(jQuery);