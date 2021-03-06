(function($) {
	$.fn.ajaxForm = function(options) {
		
		// Gather user options
		var opts = $.extend({}, $.fn.ajaxForm.defaults, options);
		
		return this.each(function() {
			$this = $(this);
			
			// Metadata support
			var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
			
			// First and foremost:  Try to "submit" onClick.
			// TODO: this.
			
			// Handle show/hide if available
			if(o.enableSelector != '' && o.hiddenSelector != '') {
				check = $this.find(o.enableSelector);
				hidden = $this.find(o.hiddenSelector);
				
				// Initial hiding
				if(!check.prop('checked')) {
					hidden.hide();
				}
				
				// Event for showing and hiding
				check.bind('change', {hidden:hidden}, function(e) {
					h = e.data.hidden;
					if($(this).prop('checked')) {
						h.show('fast');
					} else {
						h.hide('fast');
					}
				});
				
				// Do it all again if reset
				$this.find(':reset').bind('click', {hidden:hidden, check:check}, function(e) {
					h = e.data.hidden;
					
					if($(e.data.check).prop('checked')) {
						h.show('fast');
					} else {
						h.hide('fast');
					}
				});
			}
			
			// Show and Hide submit and reset area
			if(o.submitSelector != '') {
				submitArea = $this.find(o.submitSelector);
				submitArea.hide();
				
				$this.find('input').bind('change', {hidden:submitArea}, function(e) {
					e.data.hidden.show('fast');
				});
				
				$this.find(':reset').bind('click', {hidden:submitArea}, function(e) {
					e.data.hidden.hide('fast');
				});
			}
			
			// Actually Submit
			$this.bind('submit', null, function(e) {
				// TODO: Respect GET and POST
				uri = $(this).attr('action');
				$$this = $(this);
				$.post(uri, $(this).serialize(), function(data) {
					try {
						data = eval('(' + data + ')');
					} catch(e) {
                        //console.log(data);
						alert("We're sorry... something went wrong and what you just did wasn't saved.  Check the logs.");
						return;
					}
					
					
					if(!data.id) {
						if(!data.data) {
							alert("We're sorry... something went wrong and what you just did wasn't saved.  Check the logs.");
							return;
						} else {
                            //console.log(data);
                            var err = "";
                            if(data.message != undefined) {
                                err += data.message + "\n\n";
                            }
							err += "Please check the following fields: ";
							for(var d in data.data) {
								err += data.data[d] + " ";
							}
							alert(err);
							$$this.find('input').removeAttr('disabled');
						}
					} else {
						//for(var n in data) {
						//	$$this.find('input[name="'+n+'"]').attr('value', data[n]);
						//}
						$$this.find('input').removeAttr('disabled');
						if(o.submitSelector != '') {
							$$this.find(o.submitSelector).hide('fast');
						}

                        // Remove 'term' and 'name', replace with 'featureId'
                        // TODO: loosen the coupling here
                        par = $$this.find("input[name='featureId'], input[name='name']").parent()
                        $$this.find("input[name='featureId'], input[name='name'], input[name='term']").remove();
                        par.append('<input type="hidden" name="featureId" value="'+data.id+'" />');
					}
				});
				$(this).find('input').prop('disabled', 'disabled');
				e.preventDefault();
				return false;
			});
		});
	}
	
	$.fn.ajaxForm.defaults = {
		'enableSelector' : '',
		'hiddenSelector' : '',
		'submitSelector' : ''
	}
})(jQuery);
