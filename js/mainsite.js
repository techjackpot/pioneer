jQuery(function($) {
		
	function isValidEmailAddress(emailAddress) {
		var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
		return pattern.test(emailAddress);
	};
	
	$.fn.visible = function(partial) {
    
		var $t            = $(this),
			$w            = $(window),
			viewTop       = $w.scrollTop(),
			viewBottom    = viewTop + $w.height(),
			_top          = $t.offset().top,
			_bottom       = _top + $t.height(),
			compareTop    = partial === true ? _bottom : _top,
			compareBottom = partial === true ? _top : _bottom;
	  
	  return ((compareBottom <= viewBottom) && (compareTop >= viewTop));
  
	};

	$( document ).ready(function() {
				
		// Resive video
		scaleVideoContainer();
	
		initBannerVideoSize('.video-container .poster img');
		initBannerVideoSize('.video-container .filter');
		initBannerVideoSize('.video-container video');
			
		$(window).on('resize', function() {
			scaleVideoContainer();
			scaleBannerVideoSize('.video-container .poster img');
			scaleBannerVideoSize('.video-container .filter');
			scaleBannerVideoSize('.video-container video');
		});
		
		$(this).on('click', '.showmore', function() {
			$(this).hide().siblings('.hideme').slideDown();
			$(this).parents('.letterfrompres').find('.showme').hide();
		});
		
		// SIGNUP FORM
		$('form.contact-form input:password, form.contact-form input:text, form.contact-form textarea, form.contact-form input:radio').each(function(){
			if (this.value == '' && !$(this).is(":password")) this.value = $(this).attr("data-value");
			if (($(this).attr("data-value") != this.value) && !$(this).is(":password")) {
				$(this).addClass( "active" );
			}
		}).focus(function(){
			if ($(this).attr("data-value") == this.value) {
				this.value = '';
			}
				$(this).addClass( "active" );
			
			if($(this).hasClass( "error" )) { $(this).removeClass( "error" ); }
		}).blur(function(){
			if (this.value == '') {
				if (!$(this).is(":password")) this.value = $(this).attr("data-value");
				$(this).removeClass( "active" );
			}
		});
		
		$('form.contact-form').submit(function(){
			
			var requiredflag = false;
			var emailflag = false;
			var termsflag = false;
			
			if ($("form.contact-form .error_msg").is(':visible'))
			{
				$("form.contact-form .error_msg").slideUp();
			}
			
			$(this).find('input, select').each(function(){
				if($(this).attr("data-required") && ($(this).attr("data-value") == $(this).val() || $(this).val().trim() == "")){
					$(this).addClass("error");
					requiredflag = true;
				}
				if($(this).attr("pay-required") && ($(this).attr("data-value") == $(this).val() || $(this).val().trim() == "")){
					$(this).addClass("error");
					if (requiredflag == false) {
						requiredflag = true;
					}
					
				}
				if($(this).attr("data-email") && ($(this).val().trim() != "" && $(this).val().trim() != $(this).attr("data-value")) && !isValidEmailAddress($(this).val())){
					$(this).addClass("error");
					emailflag = true;
				}
			});
			
			$(this).find('input[type="checkbox"]').each(function(){
				if($(this).is(':checked')){
				}
				else {
					$(this).addClass("error");
					termsflag = true;
				}
		
			});
			
			if (requiredflag)
			{
			$("form.contact-form .error_msg").html("Please fill in all required fields.");
			$("form.contact-form .error_msg").slideDown();
			event.preventDefault(); 
			}
			else if (emailflag)
			{
			$("form.contact-form .error_msg").html("Please enter a valid email address.");
			$("form.contact-form .error_msg").slideDown();
			event.preventDefault(); 
			}
			else if (termsflag)
			{
			$("form.contact-form .error_msg").html("You must accept our terms and conditions.");
			$("form.contact-form .error_msg").slideDown();
			event.preventDefault(); 
			}
			else {
				if ($("form.contact-form .error_msg").is(':visible'))
				{
					$("form.contact-form .error_msg").slideUp();
				}
			}
		});
		
		// Dist map
		$('#ziplookup').on('keypress', function (e) {
			if(e.which === 13){
   
				$(this).attr("disabled", "disabled");
			   
				var dataString = 'context='+$(this).text();
				var $disthtml = '';
				var $salehtml = '';
				var $returnthis = '';
				
				$.ajax({
				type: "POST",
				url: "/sec_ajax/vendormapload",
				data: dataString,
				cache: false,
				success: function(response)
				{
					if(response)
					{
						
						var obj = $.parseJSON(response);
						
						if (obj.DIST) {
                            $disthtml = obj.DIST.vendor;
                        }			
						if (obj.SALE) {
                            $salehtml = obj.SALE.vendor;
                        }
						
						$returnthis = $disthtml+' '+$salehtml
						$('.themap').html($returnthis).addClass('active');
					}				
				
				}
				});			   

				$(this).removeAttr("disabled");
			}
		});
		
	});
	
	function scaleVideoContainer() {

		var height = $(window).height();
		var unitHeight = parseInt(height) + 'px';
		$('.homepage-hero-module').css('height',unitHeight);
	
	}
	
	function initBannerVideoSize(element){
		
		$(element).each(function(){
			$(this).data('height', $(this).height());
			$(this).data('width', $(this).width());
		});
	
		scaleBannerVideoSize(element);
	
	}
	
	function scaleBannerVideoSize(element){
	
		var windowWidth = $(window).width(),
			windowHeight = $(window).height(),
			videoWidth,
			videoHeight;
	
		$(element).each(function(){
			var videoAspectRatio = $(this).data('height')/$(this).data('width'),
				windowAspectRatio = windowHeight/windowWidth;
	
			if (videoAspectRatio > windowAspectRatio) {
				videoWidth = windowWidth;
				videoHeight = videoWidth * videoAspectRatio+130;
				$(this).css({'top' : -(videoHeight - windowHeight) / 2 + 'px', 'margin-left' : 0});
			} else {
				videoHeight = windowHeight+130;
				videoWidth = videoHeight / videoAspectRatio;
				$(this).css({'margin-top' : 0, 'margin-left' : -(videoWidth - windowWidth) / 2 + 'px'});
			}
	
			$(this).width(videoWidth).height(videoHeight);
	
			$('.homepage-hero-module .video-container video').addClass('fadeIn animated');
			
	
		});
	}
	
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

var lastScrollTop = 0;

$(window).scroll(function(event) {
  didScroll = true;

	var st = $(this).scrollTop();
    
    if (st > 0) {
        if (st > lastScrollTop) {
            $('.mainlogo').addClass('hidemenow');
        } else {
            // uproll code
            $('.mainlogo').removeClass('hidemenow');
        }
    } else {
        $('.mainlogo').removeClass('hidemenow');
    }
    lastScrollTop = st;
  
  $(".section").each(function(i, el) {
    var el = $(el);
    if (el.visible(true)) {
      el.addClass("appear"); 
    } 
  });
  
});
