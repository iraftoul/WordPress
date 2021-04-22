(function ($) {
	$(document).ready(function() {
		$('*').each(function () {
			for (var i = 0; i < this.attributes.length; i++) {
				var a = this.attributes[i];
				if (a.value.indexOf('<mnp-mark>') !== -1)
					a.value = a.value.replace(/<\/?mnp-mark>/g, '');
			}
		});
		var wrapperHtml = $('.mnp-title-wrapper').html();
		$('mnp-mark').each(function () {
			var $this = $(this);
			$this.replaceWith(wrapperHtml.replace('{title}', $this.text()));
		});
		$('.mnp-title-wrapper').remove();
	});
})(jQuery);