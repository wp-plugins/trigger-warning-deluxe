jQuery(function($){
	$('#trigger-warning-deluxe-edit-box #twd-toggle-trigger').change(function(e){
		$('#trigger-warning-deluxe-edit-box .field-row :input').prop('disabled', ! this.checked);
		$('#trigger-warning-deluxe-edit-box .field-rows').toggle(this.checked);
	});

    $('.twd-color-field').wpColorPicker();

	$('#trigger-warning-deluxe-edit-box #twd-toggle-trigger').change();
})