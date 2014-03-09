(function($){
jQuery.fn.fontSizeSelector = function(options) {

	var settings = $.extend( {
		'hide_fallbacks' : false,
		'selected' : function(style) {},
		'initial' : ''
	}, options);

	return this.each(function() {

		var root = $(this);
		var ul = $(this).find('ul');
		ul.hide();
		var visible = false;

		if (settings['initial'] != '')
		{
			if (settings['hide_fallbacks'])
				root.find('span').html(settings['initial']);
			else
				root.find('span').html(settings['initial']);

			root.attr('data-size', settings['initial']);
			settings['selected'](settings['initial']);
		}

		ul.find('li').each(function() {
			$(this).attr("data-size", $(this).text());

			if (settings['hide_fallbacks'])
			{
				var content = $(this).text();
				$(this).text(content);
			}
		});

		ul.find('li').click(function() {

			if (!visible)
				return;

			ul.slideUp('fast', function() {
				visible = false;
			});

			root.find('span').html( $(this).text() );
			root.attr('data-size', $(this).attr('data-size'));

			settings['selected']($(this).attr('data-size'));
		});

		$(this).click(function(event) {

			if (visible)
				return;

			event.stopPropagation();
			
			ul.slideDown('fast', function() {
				visible = true;
			});
		});

		$('html').click(function() {
			if (visible)
			{
				ul.slideUp('fast', function() {
					visible = false;
				});
			}
		})
	});
}
})(jQuery);