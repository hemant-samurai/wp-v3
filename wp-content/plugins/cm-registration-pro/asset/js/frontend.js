jQuery(function($) {
	
	
	/**
	 * Stop event
	 */
	var stopEvent = function(ev) {
		ev.preventDefault();
		ev.stopPropagation();
	};
	
	
	/**
	 * Change the login button into logout button
	 */
	if (CMREG_Settings.isUserLoggedIn == '1') {
		$('a.cmreg-login-click, .cmreg-login-click a, a[href="#cmreg-login-click"], '
				+ 'a.cmreg-only-login-click, .cmreg-only-login-click a, a[href="#cmreg-only-login-click"]')
				.attr('href', CMREG_Settings.logoutUrl).text(CMREG_Settings.logoutButtonLabel);
		$('a.cmreg-only-registration-click, .cmreg-only-registration-click a, a[href="#cmreg-only-registration-click"]').hide();
	}
	
	
	$(document).on('cmreg:init', function(ev) {
//		console.log('cmreg:init');
		var target = $(ev.target);
		// Init recaptcha
		$('.cmreg-recaptcha', target).each(function() {
			var container = $(this);
			var parameters = {"sitekey" : container.data('sitekey')};
			try {
				var id = grecaptcha.render(container[0], parameters);
				container.data('recaptchaResetId', id);
			} catch (e) {}
		});
	});
	
	
	/**
	 * Called after the login button click when the overlay is ready
	 */
	var loginClick = function() {
		$('body').addClass('cmreg-overlay-visible');
		$('.cmreg-overlay').first().fadeIn('fast', function() {
			$('.cmreg-overlay .cmreg-login-form input[type=email]').focus();
			$('.cmreg-wrapper', $(this)).trigger('cmreg:init');
		});
	};
	
	
	/**
	 * After login button click
	 */
	$('.cmreg-login-click, a[href="#cmreg-login-click"], .cmreg-only-login-click, a[href="#cmreg-only-login-click"], '
			+'.cmreg-only-registration-click, a[href="#cmreg-only-registration-click"]').click(function(ev) {
		if (CMREG_Settings.isUserLoggedIn == '1') return true;
		else stopEvent(ev);
		
		var btn = $(this);
		
		var overlayReady = function() {
			$('.cmreg-overlay').removeClass('cmreg-only-login').removeClass('cmreg-only-registration');
			if (btn.hasClass('cmreg-only-login-click') || btn.attr('href') == '#cmreg-only-login-click') {
				$('.cmreg-overlay').addClass('cmreg-only-login');
			}
			if (btn.hasClass('cmreg-only-registration-click') || btn.attr('href') == '#cmreg-only-registration-click') {
				$('.cmreg-overlay').addClass('cmreg-only-registration');
			}
			loginClick();
		};
		
		if ($('.cmreg-overlay').length == 0) {
			// Load overlay by AJAX
			$.post(CMREG_Settings.ajaxUrl, {action: "cmreg_login_overlay"}, function(response) {
				$('body').append(response);
				initOverlayHandlers();
				overlayReady();
			});
		} else {
			overlayReady();
		}
	});
	

	/**
	 * Login and registration form common handler
	 */
	var formSubmitHandler = function(ev, callback) {
		stopEvent(ev);
		var form = $(this);
		var btn = form.find('button[type=submit]');
		var loader = $('<div/>', {"class": "cmreg-loader-inline"});
		loader.width(btn.width());
		loader.height(btn.height());
		loader.css('padding', btn.css('padding'));
		btn.hide();
		btn.after(loader);
		$.post(form.data('ajax-url'), form.serialize(), function(response) {
			callback(response, form);
		});
	};
	
	
	/**
	 * Called after the overlay is ready
	 */
	var initOverlayHandlers = function() {
	
		/**
		 * Close overlay when clicked at the background
		 */
		$('.cmreg-overlay').off('click.cmreg').on('click.cmreg', function(ev) {
			if (ev.target !== this) return;
			stopEvent(ev);
			$(this).fadeOut('fast');
			$('body').removeClass('cmreg-overlay-visible');
		});
		
		
		/**
		 * Close overlay button click
		 */
		$('.cmreg-overlay-close').click(function() {
			$(this).parents('.cmreg-overlay').fadeOut('fast');
			$('body').removeClass('cmreg-overlay-visible');
		});
		
		
		/**
		 * After submit the login form
		 */
		$('.cmreg-login-form').submit(function(ev) {
			formSubmitHandler.call(this, ev, function(response, form) {
				window.CMREG.Utils.toast(response.msg);
				if (response.success) {
					form.parents('.cmreg-overlay').fadeOut('fast');
					$('body').removeClass('cmreg-overlay-visible');
					if (response.redirect) {
						location.href = response.redirect;
					} else {
						location.reload();
					}
				} else {
					form.find('.cmreg-loader-inline').remove();
					form.find('button[type=submit]').show();
					form.find('.cmreg-recaptcha').each(function() {
						grecaptcha.reset($(this).data('recaptchaResetId'));
					});
				}
			});
		});
		
		
		/**
		 * After submit the registration form
		 */
		$('.cmreg-registration-form').submit(function(ev) {
			formSubmitHandler.call(this, ev, function(response, form) {
				window.CMREG.Utils.toast(response.msg);
				form.find('.cmreg-loader-inline').remove();
				form.find('button[type=submit]').show();
				if (response.success) {
					form.parents('.cmreg-overlay').fadeOut('fast');
					$('body').removeClass('cmreg-overlay-visible');
					if (response.redirect == 'reload') {
						location.reload();
					}
					else if (response.redirect && response.redirect.length > 0) {
						location.href = response.redirect;
					} else {
						//location.reload();
					}
				} else {
					form.find('.cmreg-recaptcha').each(function() {
						grecaptcha.reset($(this).data('recaptchaResetId'));
					});
				}
			});
		});
		
		
		/**
		 * After submit the lost password form
		 */
		$('.cmreg-lost-password-form').submit(function(ev) {
			formSubmitHandler.call(this, ev, function(response, form) {
				window.CMREG.Utils.toast(response.msg);
				form.find('.cmreg-loader-inline').remove();
				form.find('button[type=submit]').show();
			});
		});
		
		
		/**
		 * Show the lost password form
		 */
		$('.cmreg-lost-password-link a').click(function(ev) {
			stopEvent(ev);
			$(this).hide();
			var form = $(this).parents('.cmreg-login').find('.cmreg-lost-password-form');
			form.show();
			form.find('input[type=email]').focus();
		});
		
		/**
		 * Show the invitation form
		 */
		$('.cmreg-invitation-code-field a').click(function(ev) {
			stopEvent(ev);
			$(this).hide();
			$(this).parents('div').first().find('input').show().focus();
		});

	};
	
	
	// Init in case that some elements has been already added to the page
	initOverlayHandlers();
	
	// Init recaptcha for existing shortcodes
	setTimeout(function() {
		$('.cmreg-wrapper').trigger('cmreg:init');
	}, 1000);
	
	
	
});