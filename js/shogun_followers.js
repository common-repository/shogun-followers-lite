
if(typeof jQuery!="undefined") {
	(function($) {
        $( document ).ready(function(e) {
			$('[name="follower[]"]').change(function() {
				message_check(this);
			});
			
			$('#cb-select-all-1, #cb-select-all-2').change(function() {
				check_all_followers();
			});
			
			$('#message').bind('input propertychange', function() {
				check_all_followers();
			});
			
			function check_all_followers() {
				names = $('[name="follower[]"]');
				for (var key in names) {
					if (names.hasOwnProperty(key)) {
						message_check(names[key]);
					}
				}
			}
			
			function message_check(self) {
				message_error_class = 'message_error';
				follower_error_class = 'follower_error';
				
				follower = jQuery.parseJSON(self.value);
				row = $(self).closest('tr');
				if (self.checked) {
					message = $('#message').val();
					if ( process_message(message, follower).length <= 500) {
						$(row).removeClass(follower_error_class);
					} else {
						$(row).addClass(follower_error_class);
						addErrorNotification();
					}
				} else if (follower != null) {
					$(row).removeClass(follower_error_class);
				}
				
				if( $('.follower_error').length == 0 ) {
					removeErrorNotification();
				}
			}
		});
		
		function addErrorNotification() {
			if( $('#message_error_notification').length == 0) {
				$('#message_instructions').after(message_error_notice());
			}
		}

		function removeErrorNotification() {
			$('#message_error_notification').remove();
		}
		
    })(jQuery);
}

function check_message(message, user) {
	message = message.replace('[name]', user.name);
	message = message.replace('[screen_name]', '@' + user.screen_name);
	return message;
}

function process_message(message, user) {
	message = message.replace('[name]', user.name);
	message = message.replace('[screen_name]', '@' + user.screen_name);
	return message;
}

function message_error_notice() {
	html = '<div id="message_error_notification" class="error">' +
		'<p>Your message is too long.  Some recipients will have their message cut short.</p>' +
	'</div>';
	
	return html;
}