
(function($) {

	$(document).ready(function() {
		
		// GENERATE USERNAME IF BLANK FROM FIRST AND LAST NAME
		$("#people_input_username").focus(function() {
		  if ($("#people_input_username").val().trim() == "") {;
			  $("#people_input_username").val($("#people_input_first_name").val().trim().toLowerCase().replace(/[^a-z\s]/gi, '').substr(0, 1)+$("#people_input_last_name").val().trim().toLowerCase().replace(/[^a-z\s]/gi, ''));
		  }
		});
		
	});	
	
	$(function() {
		$( "input.datepicker" ).datepicker();
	});	
	$(function() {
		$('input.timepicker').timepicker({ timeFormat: 'h:mmp' });
	});
	
	function phone_function(id) {
		$("#"+id+" input.phonepicker").intlTelInput({
			utilsScript: "/js/phoneutils.js"
		  });
		  
		var countryData = $.fn.intlTelInput.getCountryData(),
		  telInput = $("#"+id+" input.phonepicker"),
		  addressDropdown = $("#"+id+" #address-country");
		  curvalue = $("#"+id+" input.country_code").val();
		  telInput.change(function() {
		  $("#"+id+" .cntcode").html("+"+$("#"+id+" .country-list .active").attr("data-dial-code"));
		  $("#"+id+" input.country_code").val($("#"+id+" .country-list .active").attr("data-country-code"));
		  });
	};
	
	function update_phone(id)
	{
		curvalue = $("#"+id+" input.country_code").val();
		telInput = $("#"+id+" input.phonepicker");
		if (curvalue != "")
			{
				telInput.intlTelInput("selectCountry", curvalue);
				$("#"+id+" .cntcode").html("+"+$("#"+id+" .country-list .active").attr("data-dial-code"));
			}
			else
			{
				telInput.intlTelInput("selectCountry", "us");
				$("#"+id+" .cntcode").html("+1");
			}
	}
	
	$(document).ready(function() {
		if ($('#phoneone').length != 0)
		{
			phone_function("phoneone");
			update_phone("phoneone");
		}
		if ($('#phonetwo').length != 0)
		{
			phone_function("phonetwo");
			update_phone("phonetwo");
		}
		if ($('#phonethree').length != 0)
		{
			phone_function("phonethree");
			update_phone("phonethree");
		}
		if ($('#phonefour').length != 0)
		{
			phone_function("phonefour");
			update_phone("phonefour");
		}
		if ($('#phonefive').length != 0)
		{
			phone_function("phonefive");
			update_phone("phonefive");
		}
	});
	
	function batch_value_url(q) { return '/admin/search/batch/'+ $(this).parents('.chosen-container').siblings('.js-batch-condition').val(); }
	$.fn.batch_add = function() {
		this.each(function() {
			var $this = $(this);
			$this.on('change', '.js-batch-condition', function() { $(this).siblings('.js-batch-value').val('').trigger("chosen:updated"); });
			$this.on('click', '.js-batch-remove', function() {
				$(this).parent().remove();
				$this.find('.js-batch-table').trigger('batch:update');
			});
			$this.find('.js-batch-add-filter').data('batch_count', 0).click(function() {
				var count = $(this).data('batch_count') + 1;
				$(this).data('batch_count', count);
				var $filter = $(this).parent().parent().children('.js-batch-filter');
			
				var condition = $filter.children('.js-batch-condition').clone().prop('disabled', false);
				condition.attr('id', condition.attr('id').replace('_0_', '_'+count+'_') ).attr('name', condition.attr('name').replace('[0]', '['+count+']') );
				
				var value = $filter.children('.js-batch-value').clone().prop('disabled', false);
				value.attr('id', value.attr('id').replace('_0_', '_'+count+'_') ).attr('name', value.attr('name').replace('[0]', '['+count+']') );
				
				var type = $filter.children('.js-batch-type').clone().prop('disabled', false);
				type.attr('id', type.attr('id').replace('_0_', '_'+count+'_') ).attr('name', type.attr('name').replace('[0]', '['+count+']') );
				
				var clude = $filter.children('.js-batch-clude').clone().prop('disabled', false);
				clude.attr('id', clude.attr('id').replace('_0_', '_'+count+'_') ).attr('name', clude.attr('name').replace('[0]', '['+count+']') );
			
				var ele = $('<div/>').append( condition ).append(' ').append( clude ).append(' ').append( value ).append(' ').append( type ).append(' <i class="icon-remove js-batch-remove"></i>');
				$filter.before( ele );
			
				value.ajaxChosen({ headers: { 'DZ-Output': 'json' }, type: 'POST' }, { generateUrl: batch_value_url }, { allow_single_deselect: true });
				
				$this.find('.js-batch-table').trigger('batch:update');
			
				return false;
			});
			$this.find('.js-batch-table').on('batch:update', function() {
				var $this = $(this);
				var $form = $(this).parents('form');
				$.ajax({
					url: document.location.pathname+"_ajax",
					type: 'POST',
					data: $form.serialize(),
					success: function(data) {
						$this.find('tbody').html( data );
					}
				});
			});
			if ($this.find('.js-batch-table').length) {
				$this.on('change', 'select', function() {
					$this.find('.js-batch-table').trigger('batch:update');
				});
			}
			$('.js-batch-add-filter').click();
		});
	};
})(jQuery);

(function($) {
	function paginator_click() {
		$(this).parent().trigger('paginator:load');
		return false;
	}
	function paginator_load() {
		var $this = $(this);
		if ($this.data('paginator_loading')) { return; }
		
		$this.data('paginator_loading', true);
		var page = $this.data('page') + 1;
		$this.data('page', page);
		$this.parents('#main').data('pages', page);
		
		$this.children('a').text('Loading...');
		
		var query = document.location.search;
		if (query === "") { query = "?page="+page; }
		else if (query.match(/(\?|&)page=(.*?)(&|$)/)) { query = query.replace(/(\?|&)page=(.*?)(&|$)/, '$1page='+page+'$3'); }
		else { query = query + "&page=" + page; }
		
		$.ajax({
			url: document.location.pathname + query,
			headers: { 'DZ-Output': 'paginator' },
			success: function(data) {
				if (data === "") {
					$this.hide();
				}
				else {
					var $target = $this.siblings('.data-table');
					$target.find('tbody > .spacer').before( data );
					$target.find('.activity-wall').prop('rowspan', $target.find('tbody > tr').length );
					$this.data('paginator_loading', false);
					$this.children('a').text('Scroll to load more');
				}
			}
		});
		
	}
	$.fn.paginator = function(args) {
		if (typeof(args) === 'string') {
			if (args === 'destroy') {
			}
			else { window.console.log('Unknown action passed to paginator()'); }
			return;
		}
		var opts = (args && typeof args === 'object') ? args : {};
		this.on('paginator:load', paginator_load);
		this.children('a').on('click', paginator_click);
		if (!this.data('page')) { this.data('page', 1); }
		this.each(function() {
			var $this = $(this);
			var scrollParent = opts.scrollParent || window;
			$(scrollParent).on('scroll.paginator', function (e) {
				var loadingNextPage = $this.data('paginator_loading');
				if (!loadingNextPage && $this.position().top + $this.height() <= $(this).scrollTop() + $(this).height() + 100 && $this.is(':visible')) {
					$this.trigger('paginator:load');
				}
			}).trigger('scroll.paginator');
		});
	};
})(jQuery);

(function($) {
	$.fn.message = function(message, type, delay) {
		var $this = $(this), alert_class;
		if (delay === undefined) { delay = 15000; }
		
		if (type) { alert_class = 'alert-'+type; }
		else { alert_class = 'alert-info'; }
		
		var element = $('<div/>').addClass('alert').addClass(alert_class).html(message).append('<a class="close" data-dismiss="alert">×</a>').prependTo( $this );
		
		element.hide().slideDown();
		if (delay) { element.delay(delay).slideUp(function() { $(this).remove(); }); }
		return this;
	};
	$.message = function(message, type, delay) {
		$('#content > .container-fluid').message(message, type, delay);
	};

	$.process_json_response = function(data, target) {
		if (!target || !target.length) { target = $('#content > .container-fluid'); }
		if (data.msgs && data.msgs.error) {
			$.each(data.msgs.error, function(i, value) {
				target.message(value, 'error');
			});
		}
		if (data.msgs && data.msgs.success) {
			$.each(data.msgs.success, function(i, value) {
				target.message(value, 'success');
			});
		}
		if (data.msgs && data.msgs.info) {
			$.each(data.msgs.info, function(i, value) {
				target.message(value, 'info');
			});
		}
	};
	
	function display_form_errors($this, errors) {
		var prefix = $this.data('prefix') || '';
		var regexp = new RegExp("^"+prefix+"control_(.*)$");
		$this.find('.control-group.error').each(function(){
			var name = $(this).prop('id').replace( regexp, '$1' );
			if (!errors[name]) {
				$(this).removeClass('error').find('.controls > .help-error').slideUp(function() { $(this).remove(); });
			}
		});
		$.each(errors, function(key, value) {
			var target = $this.find('#'+prefix+'control_'+key);
			target.addClass('error');
			if (!target.find('.controls > .help-error').length) { $('<div/>').addClass("help-block").addClass("help-error").text( value.join(', ') ).appendTo( target.find('.controls') ).hide().slideDown(); }
			else { target.find('.controls > .help-error').text( value.join(', ') ); }
		});
	}
	
	function form_submit(e) {
		var $this = $(this);
		e.preventDefault();
		
		var $submit = $this.find('input[type=submit], button[type=submit]');
		$submit.prop('disabled', true);
		
		$.ajax({
			url: $this.prop('action'),
			data: $this.serialize(),
			type: 'POST',
			headers: { 'DZ-Output': 'json' },
			success: function(data) {
				if (data.success) {
					$.process_json_response(data);
					$this.parents('.modal').modal('hide');
					$('#main').trigger('reload');
				}
				else {
					$.process_json_response(data, $this.find('.modal-body'));
					display_form_errors($this, data.errors || {} );
				}
				$submit.prop('disabled', false);
			},
			error: function() {
				$this.find('.modal-body').message('Sorry, but we experienced an error while trying to process this form.', 'error');
				$submit.prop('disabled', false);
			}
		});
	}
	
	$.fn.form = function() {
		this.on('submit', form_submit);
		return this;
	};
	$.fn.validate = function() {
		var valid = true;
		$(this).find('[data-validate]').each(function() {
			var $this = $(this);
			var passed = true;
			var message = null;
			var validations = $this.data('validate') || '';
			$this.find('input[type=text], input[type=password], textarea, select').each(function() {
				if ($(this).parents('.chosen-container').length) { return; }
				validations.split(',').forEach(function(validation) {
					if (validation === 'required') {
						if ($(this).val() === '') {
							passed = false;
							message = "is required";
						}
					}
					if ($(this).val() !== '' && validation === 'email') {
						if ( !(/[^@]+@[^@]+\.[^@]+/.test($(this).val())) ) {
							passed = false;
							message = "is an invalid email";
						}
					}
				}, this);
			});
			if (passed) {
				$this.removeClass('error');
				$this.find('.help-error').remove();
			}
			else {
				$this.addClass('error');
				$this.find('.help-error').remove();
				$this.find('.controls').append( $('<span>').addClass('help-block').addClass('help-error').text(message) );
			}
			valid = valid && passed;
		});
		return valid;
	};
})(jQuery);

(function($) {
	
	function activity_comment_keypress(e) {
		var $this = $(this).parent().find('.js-activity-comment');
		e.preventDefault();
		
		if ($this.val() === '') { return; }
		$this.prop('disabled', true);
		
		$.ajax({
			url: '/admin/changelog/comment',
			headers: { 'DZ-Output': 'json' },
			type: 'POST',
			data: { message: $this.val(), name: $this.data('model-name'), id: $this.data('model-id') },
			dataType: 'json',
			success: function(data) {
				$this.prop('disabled', false).val('');
				$this.parent().after( $(data.contents) );
			},
			error: function() {
				$this.prop('disabled', false);
				window.alert("Sorry, we couldn't submit your comment at this time");
			}
		});
	}
	
	function activity_wall_update() {
		
	}
	
	$.fn.activity_wall = function() {
		this.on('activity_wall:update', activity_wall_update);
		this.find('#js-activity-comment-button').on('click', activity_comment_keypress);
		return this;
	};
	
})(jQuery);
(function($) {
	function add_price_tier() {
		var $tier = $(this).parents('.js-price-tiers').find('.js-price-tier-template').clone().removeClass('js-price-tier-template').show();
		var count = $(this).parents('.js-price-tiers').data('tier_count') - 1;
		$(this).parents('.js-price-tiers').data('tier_count', count);
		$tier.find('input').each(function() {
			$(this).prop('disabled', false);
			$(this).prop('id', $(this).prop('id').replace('_0_', '_'+count+'_') );
			$(this).prop('name', $(this).prop('name').replace('[0]', '['+count+']') );
		});
		
		$tier.append(' ').append(' <i class="icon-remove js-price-tier-remove"></i>');
		
		$(this).parent().before( $tier );
		
		return false;
	}
	
	function remove_price_tier() {
		$(this).parent().remove();
		return false;
	}
	
	function delete_price_tier() {
		var name = $(this).parent().find('input').first().prop('name').replace(/\[\w+\]$/, '[_delete]');
		$(this).parents('.js-price-tiers').append('<input type="hidden" name="'+name+'" value="1">');
		$(this).parent().remove();
		return false;
	}
	
	$.fn.price_tiers = function() {
		this.data('tier_count', 0);
		this.find('.js-add-price-tier').on('click', add_price_tier);
		this.on('click', '.js-price-tier-remove', remove_price_tier);
		this.on('click', '.js-price-tier-delete', delete_price_tier);
		return this;
	};
	
})(jQuery);

jQuery(function($) {
	
	function remove_modal() { $(this).remove(); }
	
	function output_modal(text) {
		var $elements = $( $.parseHTML(text) );
		
		$('<div class="modal fade" />').append($elements).appendTo('body').initialize().on('hidden.bs.modal', remove_modal).modal('show', this);
		
		return $elements;
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
	
	function select_all_change() {
		$(this).parents('thead').siblings('tbody').find('input[type=checkbox]').prop('checked', $(this).prop('checked') );
	}
	
	function people_input_type_id() {
		if ($(this).val() === '2') {
			$(this).parents('.control-group').first().siblings('#people_control_group_id').show().find('select').prop('disabled', false);
		}
		else {
			$(this).parents('.control-group').first().siblings('#people_control_group_id').hide().find('select').prop('disabled', true);
		}
	}
	
	function export_table_click() {
		var url = $(this).data('url') + '&' + $(this).parents('.modal-content').first().find('input').serialize();
		
		window.location = url;
		
		return false;
	}
	
	function export_table_dialog() {
		var $elements = $( $.parseHTML('<div class="modal-dialog"> <div class="modal-content"> <div class="modal-header"> <button type="button" class="close" data-dismiss="modal">×</button> <h4 class="modal-title">Export Table</h4> </div> <div class="modal-body"> </div> <div class="modal-footer"> <button type="button" class="btn btn-default" data-dismiss="modal">Close</button> <button type="button" class="btn btn-primary pull-left">Export</button> </div> </div> </div>') );
		
		$.each($(this).data('fields'), function(key, item) {
			$elements.find('.modal-body').append(
				$('<label>').addClass('checkbox').text(' '+item[1]).prepend(
					$('<input>').attr('type', 'checkbox').val(1).attr('name', 'field['+item[0]+']').prop('checked', item[2])
				)
			);
		});
		
		$elements.find('.btn-primary').data('url', $(this).prop('href')).click(export_table_click);
		
		$('<div class="modal fade" />').append($elements).appendTo('body').initialize().on('hidden.bs.modal', remove_modal).modal('show', this);
		
		return false;
	}
	
	function print_dialog() {
		var my_window = window.open( $(this).prop('href'), "print", "status=1,width=850,height=1100" );
		return false;
	}
	
	
	var memorize_typeahead = {
		source: function(query, process) {
			var $element = this.$element;
			var table = $element.data('table');
			
			if ($element.data('cache')) {
				return $element.data('cache');
			}
			else {
				$.getJSON(
					"/admin/typeahead",
					{ table: table },
					function(data) {
						$element.data('cache', data);
						return process(data);
					}
				);
				return [];
			}
		},
		updater: function(item) {
			var terms = $(this.$element).val().split(',');
			terms.pop();
			terms.push(item);
			terms.push('');
			$.each(terms, function(idx, val) { terms[idx] = $.trim(val); });
			return terms.join(', ');
		},
		matcher: function(item) {
			var terms = this.query.split(',');
			$.each(terms, function(idx, val) { terms[idx] = $.trim(val); });
			var term = $.trim(terms.pop());
			return $.inArray(item, terms) < 0 && item.toLowerCase().indexOf(term.toLowerCase()) >= 0;
		}
	};
	
	var memorize_typeahead_single = {
		source: function(query, process) {
			var $element = this.$element;
			var table = $element.data('table');
			
			if ($element.data('cache')) {
				return $element.data('cache');
			}
			else {
				$.getJSON(
					"/admin/typeahead",
					{ table: table },
					function(data) {
						$element.data('cache', data);
						return process(data);
					}
				);
				return [];
			}

		},
		updater: function(item) {
			return item;
		},
		matcher: function(item) {
			var terms = this.query.split(',');
			$.each(terms, function(idx, val) { terms[idx] = $.trim(val); });
			var term = $.trim(terms.pop());
			return $.inArray(item, terms) < 0 && item.toLowerCase().indexOf(term.toLowerCase()) >= 0;
		}
	};
	
	
	function validate_form(e) {
		if (!$(this).validate()) {
			e.preventDefault();
		}
	}
	
	$.fn.initialize = function() {
		this.find('select.chosen').chosen({ allow_single_deselect: true });
		
		this.find('.js-batch').batch_add();
		this.find('.js-paginator').paginator();
		this.find('.activity-wall').activity_wall();
		this.find('.js-form').form();
			
		this.find('.js-data-table-export').click(export_table_dialog);
		this.find('.js-print-dialog').click(print_dialog);
		this.find('.js-validate').on('submit', validate_form);
				
		this.find('textarea.ckeditor').ckeditor();
		
		this.find('abbr[title]').tooltip();
		
		this.find('.calendar .item').popover({
			trigger: 'hover',
			placement: 'top',
			html: true
		});
		
		this.find('.js-type-memorize').typeahead(memorize_typeahead).attr('autocomplete', 'off');
		
		this.find('.js-type-memorize-single').typeahead(memorize_typeahead_single).attr('autocomplete', 'off');

		this.find('.js-select-all').change(select_all_change);
		
		this.find('#people_input_type_id').change(people_input_type_id);
		
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