jQuery(function($){
	var accordian = $('.orbtr-accordian');
	
	accordian
		.find('.trigger')
			.click(function(){
				
				accordian
					.find('.orbtr-content')
						.slideUp('fast')
				;
				
				$('.orbtr-button')
					.removeClass('orbtr-button')
				;
				
				$(this)
					.addClass('orbtr-button')
					.siblings('.orbtr-content')
						.slideDown('fast')
				;
			})
		.first()
			.addClass('orbtr-button')
			.siblings('.orbtr-content')
				.slideDown('fast')
	;
	
	$('#addJetpack')
		.on('click', function() {
			tb_show('Add Jetpack Form', 'admin-ajax.php?action=lp_grunion_form_builder&amp;TB_iframe=true&amp;id=add_form&amp;width=640&amp;height=635', false);
			return false;
		})
	;
	
	var form_type = $('#form_type').val();
	
	if (form_type != 0)
	{
		$('.'+form_type+'_toggle').show();	
	}
	
	$('#form_type')
		.on('change', function() {
			form_type = $(this).val();
			$('.type_toggle').hide();
			if (form_type != 0)
			{
				$('.'+form_type+'_toggle').show();	
			}
		})
	;
	
});
