jQuery(function($){
	$('body.single-post .trigger-warning-deluxe.post-warning').dialog({
		dialogClass: 'trigger-warning-dialog',
		modal: true,
		draggable: false,
		resizable: false,
		hide: true,
		show: true,
		open: function(){
			$('.ui-widget-overlay').addClass('trigger-warning-overlay');
		}
	});

	$('.trigger-warning-deluxe.inline-warning').each(function(i, e){
		$(this).addClass('veiled');
		$('<strong/>').addClass('reveal').css('cursor', 'pointer').attr('title', 'reveal content').text(this.title).prependTo(this);
	});

	$('.trigger-warning-deluxe.inline-warning')
	.on('click', '.reveal', function(e){
		$(this).parent()
		.removeClass('veiled')
		.children('.warning, .reveal')
			.remove();
	})
	.appendTo('.trigger-warning-deluxe.inline-warning');
})