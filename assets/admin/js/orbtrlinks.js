var orbtrlinks;

(function($){
	
	orbtrlinks = {
		init : function(data) {
			var mergeTag = '';
			
			if (data) {
				mergeTag = decodeURIComponent(data.mergeTag);
			}
			
			$('#link-options')
				.append('<div class="orbtr-links"><a href="#" class="button append_orbtr orbtr-button">Append ORBTR Tracking</a></div>')
			;
			
			$('.append_orbtr')
				.on('click', function(e) {
					var el = $('#url-field'),
						text = ''
					;
                
                    if (!el.length) el = $('#wp-link-url');
                    text = el.val();
                
					separator = text.indexOf('?') !== -1 ? "&amp;" : "?";
					el.val(text + separator + mergeTag);
					return false;
				})
			;
		}
	};
		
	$.ajax({
		url: 'admin.php',
		data: {getMergeTag: true},
		dataType: "json",
		success: function(data) {
			orbtrlinks.init(data);		
		}
	});	

})(jQuery);