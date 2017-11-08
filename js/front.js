jQuery(function($) {
		
	function isValidEmailAddress(emailAddress) {
		var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
		return pattern.test(emailAddress);
	};

	function adjustguidelines() {
		
		for (var i = 1, len = 32; i < len; i++) {
			var maxheight = 0;
			var ourvar = $( '.qsguildlinesgrid .glcell[data-index="'+i+'"' );
			ourvar.each(function( index ) {
				if ($(this).outerHeight() > maxheight) {
                    maxheight = $(this).height();
                }
			});
			
			maxheight = maxheight + 46;
			newmaxheight = maxheight -1;
			
			ourvar.each(function( index ) {
				if (($(this).parent().hasClass('glcol2') || $(this).parent().hasClass('glcol4')) && i == 2) {
					$(this).css("height",newmaxheight+"px");
				}
				else
				{
					$(this).css("height",maxheight+"px");
				}
			});
		}
	}
		
	$('form').attr('autocomplete','off');
	
	function senddata($this) {
        var form = $this.parents('form');
		var prefix = form.attr("data-prefix");
		var context = form.attr("data-context");
		var field = $this.attr("name");
		var val = $this.val();

		var dataString = 'context='+context+'&prefix='+prefix+'&val='+encodeURIComponent(val)+'&field='+field;
		$.ajax({
			type: "POST",
			url: "/sec_ajax/datainputorder",
			data: dataString,
			success: function(){
			}
		});
    }
	
	function senddataorg($this) {
        var form = $this.parents('form');
		var prefix = form.attr("data-prefix");
		var context = form.attr("data-contexttwo");
		var field = $this.attr("name");
		var val = $this.val();
		
		console.log(context);
		
		var dataString = 'context='+context+'&prefix='+prefix+'&val='+encodeURIComponent(val)+'&field='+field;
		$.ajax({
			type: "POST",
			url: "/sec_ajax/datainputorganization",
			data: dataString,
			success: function(response){
				
				console.log(response);
			}
		});
    }

	var typehidearray = {
		'10': ['orderline_control_bolt', 'null'],
		'11': ['strikeblock'],
		'20': ['strikeblock', 'handblock', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'13': ['orderline_control_height', 'strikeblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_bolt', 'hardwarepreps'],
		'14': ['orderline_control_height', 'strikeblock', 'handblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'hardwarepreps'],
		'17': ['orderline_control_width', 'strikeblock', 'orderline_control_assy', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'18': ['orderline_control_width', 'orderline_control_assy', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'19': ['orderline_control_width', 'strikeblock', 'handblock', 'orderline_control_assy', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'22': ['orderline_control_width', 'strikeblock', 'handblock', 'orderline_control_assy', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'25': ['hardwarepreps', 'null'],
		'26': ['nominalblock', 'strikeblock', 'handblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'27': ['orderline_control_width', 'strikeblock', 'orderline_control_assy', 'orderline_control_anc', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'28': ['orderline_control_width', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'39': ['nominalblock', 'strikeblock', 'handblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'29': ['nominalblock', 'strikeblock', 'handblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'31': ['orderline_control_width', 'strikeblock', 'orderline_control_assy', 'orderline_control_anc', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'174': ['orderline_control_width', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'175': ['orderline_control_width', 'strikeblock', 'handblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'176': ['orderline_control_width', 'strikeblock', 'orderline_control_assy', 'orderline_control_anc', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'177': ['orderline_control_width', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
		'all': ['nominalblock', 'orderline_control_width', 'orderline_control_height', 'strikeblock', 'handblock', 'orderline_control_assy', 'orderline_control_anc', 'hingeblock', 'orderline_control_closer', 'orderline_control_bolt', 'hardwarepreps'],
	};
	
	$( document ).ready(function() {
		
		$(this).on('mouseenter', '.togglemenu', function(e) {
			$(this).children('.dropmenu').stop().slideDown();
		}).on('mouseleave', '.togglemenu', function(e) {
			$(this).children('.dropmenu').stop().slideUp();
		}).on('click', '.togglemenu', function(e) {
			$(this).children('.dropmenu').stop().slideToggle();
		});
		
		$(this).on('click', 'body', function() {
			$(this).children('.dropmenu').stop().slideUp();
		});
		
		$(this).on ('change', '.qstimelineselect #frameproduct_id', function () {
			var $days = $('option:selected', this).attr('data-days');
			var $limit = $('option:selected', this).attr('data-limit');
			
			$(".limittext #days").html(''+$days+'');
			$(".limittext #limit").html(''+$limit+'');
		});
		
		$(this).on('change', 'input.shippingthesame', function () {
		if ($(this).is(":checked")) {
			$("#order_input_shipto_name").val($("#order_input_billto_name").val());
			$("#order_input_shipto_address1").val($("#order_input_billto_address1").val());
			$("#order_input_shipto_address2").val($("#order_input_billto_address2").val());
			$("#order_input_shipto_city").val($("#order_input_billto_city").val());
			$("#order_input_shipto_state").val($("#order_input_billto_state").val());
			$("#order_input_shipto_zip").val($("#order_input_billto_zip").val());
		}
		else
		{
			$("#order_input_shipto_name").val("");
			$("#order_input_shipto_address1").val("");
			$("#order_input_shipto_address2").val("");
			$("#order_input_shipto_city").val("");
			$("#order_input_shipto_state").val("");
			$("#order_input_shipto_zip").val("");
		}
			senddata($("#order_input_shipto_name"));
			senddata($("#order_input_shipto_address1"));
			senddata($("#order_input_shipto_address2"));
			senddata($("#order_input_shipto_city"));
			senddata($("#order_input_shipto_state"));
			senddata($("#order_input_shipto_zip"));
		});
		
		$(this).on('blur', '.savetype', function () {
			senddata($(this));
		});
		
		$(this).on('blur', '.savetypeorg', function () {
			senddataorg($(this));
		});
		
		$(this).on('change', '#orderline_input_strike', function () {
			if ($(this).val() == "98") {
				$("#estkdrawing").slideDown();
			}
			else
			{
				$("#estkdrawing").slideUp();
			}
			
			if ($(this).val() == "97") {
				$("#orderline_control_loc").hide();
				$("#orderline_control_second").hide();
			}
			else
			{
				$("#orderline_control_loc").show();
				$("#orderline_control_second").show();
			}
		});
		
		$('#orderline_input_strike').trigger('change');
		
		$(this).on('change', '#orderline_input_type', function () {
			if ($(this).val() == "26" || $(this).val() == "27" || $(this).val() == "28" || $(this).val() == "39" || $(this).val() == "29" || $(this).val() == "31" || $(this).val() == "174") {
				$("#typewarning").slideDown();
			}
			else
			{
				$("#typewarning").slideUp();
			}
			
			if ($(this).val() == "20" || $(this).val() == "25") {
				$("#glazingbead").slideDown();
			}
			else
			{
				$("#glazingbead").slideUp();
			}
			
			if ($(this).val() == "10" || $(this).val() == "11") {
				$("#hardwarelocations").slideDown();
			}
			else
			{
				$("#hardwarelocations").slideUp();
			}
			
			if ($(this).val()) {
                var myval = $(this).val();
				var output = [];
				$.each( typehidearray['all'], function( key, value ) {
					$("#"+value).slideDown();
					
				});				
				$.each( typehidearray[myval], function( key, value ) {
					$("#"+value).slideUp();
					$("#"+value.replace("_control_", "_input_")).val("");
				});
            }
			
			
			
		});
		
		$('#orderline_input_type').trigger('change');
		
		$(this).on('change', '#orderline_input_series', function () {
			if ($(this).val() == "171") {
				$("#backbend").slideDown();
			}
			else
			{
				$("#backbend").slideUp();
			}
		});
		
		$('#orderline_input_series').trigger('change');
		
		$(this).on('change', '#orderline_input_depth', function () {
			if ($(this).val() == "65") {
				$("#specialdepth").slideDown();
			}
			else
			{
				$("#specialdepth").slideUp();
				$("#orderline_input_specialdepth").val("");
			}
		});
		
		$('#orderline_input_depth').trigger('change');
		
		$(this).on('change', '#orderline_input_width', function () {
			if ($(this).val() == "87") {
				$("#specialwidth").slideDown();
			}
			else
			{
				$("#specialwidth").slideUp();
				$("#orderline_input_specialwidth").val("");
			}
		});
		
		$('#orderline_input_width').trigger('change');
		
		$(this).on('change', '#orderline_input_height', function () {
			if ($(this).val() == "94") {
				$("#specialheight").slideDown();
			}
			else
			{
				$("#specialheight").slideUp();
				$("#orderline_input_specialheight").val("");
			}
		});
		
		$('#orderline_input_height').trigger('change');
		
		$(this).on('change', '#orderline_input_hinge', function () {
			if ($(this).val() == "146") {
				$("#orderline_control_hingeqty").hide();
				$("#orderline_control_hingeloc").hide();
			}
			else
			{
				$("#orderline_control_hingeqty").show();
				$("#orderline_control_hingeloc").show();
			}
		});
		
		$('#orderline_input_hinge').trigger('change');
		
		if ( window.location.pathname == '/' ){
			$( ".header" ).hide();
			$( ".main" ).hide();
			$( ".footer" ).hide();
			
			$( ".header" ).fadeIn(1000, function () {
			$( ".main" ).fadeIn(800);
			$( ".footer" ).fadeIn(800);
			});
		}
		
		adjustguidelines();
		
		$(this).on('click', '[data-dismiss="alert"]', function() {
			$(this).parent().remove();
		});
		
		$(this).on('change', '#orderline_input_type', function() {
			if ($(this).val() == '25') {
                $("#unitselected").slideDown();
            }
			else
			{
				$("#unitselected").slideUp();
			}
		});
		
		$('#orderline_input_type').trigger('change');
		
		
		
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
