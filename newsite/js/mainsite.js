jQuery(function($) {
		
	function isValidEmailAddress(emailAddress) {
		var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
		return pattern.test(emailAddress);
	};

	$( document ).ready(function() {
				
		
	});
	
	function display_modal(e) {
		
		var url = $(this).prop('href').split('#');
		
		var that = this;
		$.ajax({
			url: url[0],
			headers: { 'DZ-Output': 'modal' },
			success: function(data) {
				var $elements = output_modal.call(that, data);
				
				if (url[1]) {
					$elements.find('.nav-tabs a[href="#'+url[1]+'"]').tab('show');
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				if (jqXHR.responseText && jqXHR.responseText.length > 0) {
					output_modal.call( that, jqXHR.responseText );
				}
			}
		});
		
		e.preventDefault();
	}
	
	function link_click(event) {
		var $this = $(this);
		var message = $this.data('confirm') === true ? 'Are you sure you want to delete this item?' : $(this).data('confirm');
		if ($this.data('remote')) {
			if (!$this.data('confirm') || window.confirm(message)) {
				$.ajax({
					url: $this.prop('href'),
					headers: { 'DZ-Output': 'json' },
					success: function(data) {
						$.process_json_response(data, $this.parents('.modal-body'));
					}
				});
			}
			return false;
		}
		else if ($this.data('confirm')) {
			return window.confirm(message);
		}
	}
	
	$.fn.initialize = function() {		
		
		return this;
	};
	
	function reload_main() {
		var $this = $(this);
		var pages = $(this).data('pages');
		var query = document.location.search;
		
		if (pages) {
			if (query === "") { query = "?pages="+pages; }
			else if (query.match(/(\?|&)pages=(.*?)(&|$)/)) { query = query.replace(/(\?|&)pages=(.*?)(&|$)/, '$1pages='+pages+'$3'); }
			else { query = query + "&pages=" + pages; }
		}
		
		var scroll = $(window).scrollTop();
		
		$.ajax({
			url: document.location.pathname + query,
			headers: { 'DZ-Output': 'reload' },
			success: function(data) {
				var contents = $( $.parseHTML(data) );
			
				$this.html(contents);
				contents.initialize();
				$(window).scrollTop(scroll);
			}
		});
	}
	
	$(document)
		.on('click', '[data-modal]', display_modal)
		.on('click', '[data-confirm], [data-remote]', link_click)
		.on('reload', '#main', reload_main)
		.initialize();
		
		$(function(){
		  var hash = window.location.hash;
		  hash && $('ul.nav a[href="' + hash + '"]').tab('show');
		
		  $('.nav-tabs a').click(function (e) {
			$(this).tab('show');
			var scrollmem = $('body').scrollTop();
			window.location.hash = this.hash;
			$('html,body').scrollTop(scrollmem);
		  });
		});
	
	if (window.location.hash != "") {	
		$('.nav-tabs a[href="'+window.location.hash+'"]').tab('show');
	}
	
});
