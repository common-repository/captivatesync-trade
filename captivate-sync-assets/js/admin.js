jQuery( document ).ready( function( $ ) {

	// tooltip init
	$( 'body' ).tooltip({
		selector: '.cfmsync-tooltip'
	});

	/**
	 * Clipboard
	 */
	var clipboard = new ClipboardJS('.clipboard');

	clipboard.on('success', function(e) {
		$(e.trigger).addClass('fade').tooltip('show');
		e.clearSelection();
	});

	clipboard.on('error', function(e) {
		var data_clip = $(e.trigger).data('clipboard-text');
		$(e.trigger).attr( 'title', data_clip ).tooltip('fixTitle').addClass('fadeError').tooltip('show');
	});

	/**
	 * Clipboard Tooltip
	 */
	$('.cb-tooltip').tooltip({
		placement: 'top',
		trigger: 'manual',
		title: 'Copied!',
	}).tooltip('hide');

	$('.cb-tooltip').on('shown.bs.tooltip', function () {
		var fadeTime = 4294967295;

		if ( $('.cb-tooltip.fade').length ) {
			fadeTime = 2000;
		}

		if ( $('.cb-tooltip.fadeError').length ) {
			fadeTime = 10000;
		}

		var that = $(this);

		var element = that[0];
		if(element.myShowTooltipEventNum == null){
			element.myShowTooltipEventNum = 0;
		}else{
			element.myShowTooltipEventNum++;
		}
		var eventNum = element.myShowTooltipEventNum;

		setTimeout(function(){
			if(element.myShowTooltipEventNum == eventNum){
				that.tooltip('hide');
				that.removeClass('fade');
			}
			// else skip timeout event
		}, fadeTime);
	});

    $(document).on( 'click', 'button[name=syncShows]', function(e) {

		e.preventDefault();

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'sync-shows',
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function( response ) {

				$('button[name=syncShows]').prop('disabled', true);

				$( '#cfm-message' ).html( '<p>Syncing shows and episodes...</p>' ).fadeIn();

			},
			success: function( response ) {

				if ( 'success' == response ) {
					$( '#cfm-message' ).html( '<p>Sync complete!</p>' );
				}
				else {
					$( '#cfm-message' ).html( '<p>' + response + '</p>' );
				}

				location.reload(true);
			}
		} );

		e.preventDefault();

    });

    $(document).on( 'click', 'button[name=CFMPickShows]', function(e) {

    	e.preventDefault();

    	$.ajax({
    		url: cfmsync.ajaxurl,
    		type: 'post',
    		data: {
    			action: 'get-shows',
    			_nonce: cfmsync.ajaxnonce
    		},
    		success: function( response ) {

    			if(response != "null") {

	    			var CFMshows = JSON.parse(response);
	    			var showsListing = "";

	    			if(CFMshows.length >= 1) {
		    			for (var i=0; i < CFMshows.length; ++i) {
		    				var checked = CFMshows[i].enabled ? 'checked' : '';
		    				showsListing += "<li class='cfm_show_selectors cfm_show_" + CFMshows[i].id + "'><input type='checkbox' " + checked + " id='cfm_show_" + CFMshows[i].id + "' value='" + CFMshows[i].id + "' name='showsToSync'> <label for='cfm_show_" + CFMshows[i].id + "'>" + CFMshows[i].title + "</label><div class='cfm_error-status'></div></li>";
			    			if ( i == (CFMshows.length - 1) ) {
			    				$( '.cfm-sync-shows' ).html( showsListing );
						    	$('#SyncShows').modal('show');
			    			}
		    			}
		    		} else {
		    			$('.cfm-sync-add-show').show();
		    		}

		    	} else {
		    		$(".select-shows").hide();
		    		$(".cfm-sync-shows").hide();
		    		$('.cfm-sync-add-show').show();
			    	$('#SyncShows').modal('show');
		    	}

    		}

    	} );

    	e.preventDefault();

    });

	$(document).on( 'click', 'button[name=selectShows]', function(e) {

		e.preventDefault();

		$this = $(this);

		let selectedShows = [];

		$.each($("input[name='showsToSync']:checked"), function(){
			selectedShows.push($(this).val());
		});

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'select-shows',
				shows: selectedShows,
				_nonce: cfmsync.ajaxnonce
			},
			beforeSend: function( response ) {

				$("button[name=selectShows]").prop('disabled', true);

				$(".cfm_show_selectors input").prop('disabled', true);

				$this.html('<i class="fas fa-spinner fa-spin"></i> Processing...');

				$( '.cfm-sync-progress' ).html( '<p>Syncing shows and episodes...</p>' ).fadeIn();

			},
			success: function( response ) {

				var syncResponse = JSON.parse(response);

				if(!syncResponse.return) {
					$( '.cfm-sync-progress' ).html( '<p>Shows already selected successfully.</p>' );
					$this.html('Select &amp; Sync Shows');
				} else {

					var totalSuccess = syncResponse.return.length;

					$(".cfm_show_selectors input").attr('disabled', 'disabled');

	    			for (var i=0; i < syncResponse.return.length; ++i) {
	    				if(syncResponse.return[i].success == false) {
		    				$( '.cfm_show_' + syncResponse.return[i].id ).addClass('cfm-failed');
		    				$( '.cfm_show_' + syncResponse.return[i].id + ' .cfm_error-status').html(syncResponse.return[i].error);
		    			} else {
		    				totalSuccess = totalSuccess - 1;
		    			}
	    			}

					$this.html('Select &amp; Sync Shows');

					if ( totalSuccess == 0 ) {
						$( '.cfm-sync-progress' ).html( '<p>Shows and episodes synced successfully.</p>' );
					} else {
						$( '.cfm-sync-progress' ).html( "<p>It looks like we've ran into a few issues whilst selecting these shows to sync.</p>" );
					}

				}

				location.reload(true);
			}
		} );

		e.preventDefault();

	});

	$(document).on( 'change', 'select[name=page_for_show]', function(e) {

		e.preventDefault();

		var s_id = $(this).prop('id'),
			show_id = s_id.split('_')[1],
			page_id = $(this).val();

		$(document).disableFields('input[name=display_episodes]');
		$(document).disableFields('select[name=page_for_show]');
		$(document).disableFields('select[name=author_for_show]');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'set-show-page',
				_nonce: cfmsync.ajaxnonce,
				show_id: show_id,
				page_id: page_id
			},
			success: function( response ) {

				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Podcast episodes will appear on this page, now');
				}
				else {
					cfmsync_toaster('error', response);
				}

				setTimeout(function(){
					$(document).enableFields('input[name=display_episodes]');
					$(document).enableFields('select[name=page_for_show]');
					$(document).enableFields('select[name=author_for_show]');
				}, 5000);
			}
		} );

		e.preventDefault();

    });

	$(document).on( 'change', 'select[name=author_for_show]', function(e) {

		e.preventDefault();

		var s_id = $(this).prop('id'),
			show_id = s_id.split('_')[1],
			author_id = $(this).val();

		$(document).disableFields('input[name=display_episodes]');
		$(document).disableFields('select[name=page_for_show]');
		$(document).disableFields('select[name=author_for_show]');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'set-show-author',
				_nonce: cfmsync.ajaxnonce,
				show_id: show_id,
				author_id: author_id
			},
			success: function( response ) {

				if ( 'success' == response ) {
					cfmsync_toaster('success', 'Show author has been set successfully');
				}
				else {
					cfmsync_toaster('error', response);
				}

				setTimeout(function(){
					$(document).enableFields('input[name=display_episodes]');
					$(document).enableFields('select[name=page_for_show]');
					$(document).enableFields('select[name=author_for_show]');
				}, 5000);
			}
		} );

		e.preventDefault();

    });

	$(document).on( 'change', 'input[name=display_episodes]', function(e) {

		e.preventDefault();

		var s_id = $(this).prop('id'),
			show_id = s_id.split('_')[1],
			display_episodes = ( this.checked ) ? '1' :'0';

		$(document).disableFields('input[name=display_episodes]');
		$(document).disableFields('select[name=page_for_show]');
		$(document).disableFields('select[name=author_for_show]');

		$.ajax({
			url: cfmsync.ajaxurl,
			type: 'post',
			data: {
				action: 'set-display-episodes',
				_nonce: cfmsync.ajaxnonce,
				show_id: show_id,
				display_episodes: display_episodes
			},
			success: function( response ) {

				if ( 'success' == response ) {
					if ( display_episodes == '0' ) {
						cfmsync_toaster('success', 'Episodes will not appear on the selected page');
					}
					else {
						cfmsync_toaster('success', 'Episodes will now appear on the selected page');
					}
				}
				else {
					cfmsync_toaster('error', response);
				}

				setTimeout(function(){
					$(document).enableFields('input[name=display_episodes]');
					$(document).enableFields('select[name=page_for_show]');
					$(document).enableFields('select[name=author_for_show]');
				}, 5000);
			}
		} );

		e.preventDefault();

    });

	$(document).on( 'click', '#cfm-datatable-episodes a.cfm-trash-episode', function(e) {

		e.preventDefault();

		var post_id = $(this).data('post-id'),
			_nonce = $(this).data('nonce'),
			parent = $(this).parent().parent();

		if ( confirm( "Are you sure you want to delete this episode? This episode will be removed from your Captivate account too." ) ) {

			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'trash-episode',
					_nonce: _nonce,
					post_id: post_id,
				},
				beforeSend: function( response ) {
					parent.css({
						"background-color": "#ff3333"
					}, 500);

				},
				success: function( response ) {

					if ( 'success' == response ) {
						parent.fadeOut(500, function() {
							parent.remove();
						});
					}
					else if ( 'captivate_error' == response ) {
						parent.fadeOut(500, function() {
							parent.remove();
						});

						alert("Episode moved to trash on Podcast Websites. It is not deleted on Captivate or do not exists.");
					}
					else {
						parent.css({
							"background-color": "#ffffff"
						});

						alert("Something went wrong. Please contact support.");
					}

				}
			} );

		}

		e.preventDefault();

    });

	$(document).on( 'click', 'button[name=removeCredentials]', function(e) {

		e.preventDefault();

		if ( confirm( "Are you sure you want to remove authentication on this website? User credentials, shows, and episodes will be removed from this site." ) ) {

			$.ajax({
				url: cfmsync.ajaxurl,
				type: 'post',
				data: {
					action: 'remove-credentials',
					_nonce: cfmsync.ajaxnonce
				},
				beforeSend: function( response ) {

					$( '#cfm-message' ).html( '<p>Removing user credentials, shows, and episodes...</p>' ).fadeIn();

				},
				success: function( response ) {

					if ( 'success' == response ) {
						$( '#cfm-message' ).html( '<p>User credentials credentials, shows, and episodes removed successfully.</p>' );
						$( '.cfm-content-wrap').hide();
					}
					else {
						$( '#cfm-message' ).html( '<p>' + response + '</p>' );
					}
				}
			} );

		}

		e.preventDefault();

    });

    /**
	 * Clear publish saved data
	 */
	$(document).on( 'click', '.cfm-show-wrap .cfm-clear-publish-data', function(e) {

		e.preventDefault();

		var $this = $(this),
			s_id = $this.closest( ".cfm-show-wrap" ).prop('id'),
			show_id = s_id.split('_')[1];

		if ( confirm( "Are you sure you want to clear the publish episode auto-save data on this show? All fields on publish episode screen for this show will be emptied." ) ) {

			// LOCALSTORAGE local-storage.js - clear.
			var key = show_id + '_cfm-form-publish-episode_save_storage';
			localStorage.removeItem(key);

			// LOCALSTORAGE custom - clear.
			localStorage.removeItem(show_id + '_featured_image_url_local');
			localStorage.removeItem(show_id + '_post_content_wp_local');
			localStorage.removeItem(show_id + '_shownotes_local');
			localStorage.removeItem(show_id + '_shownotes_local_html');

			cfmsync_toaster('success', 'Publish episode data cleared successfully.');
			$this.blur();

		}

		e.preventDefault();

    });

	$.fn.disableFields = function(field_attr) {

		if (field_attr != "") {
			$(field_attr).each(function() {
				$(this).prop('disabled', true);
			});

		}

	}

	$.fn.enableFields = function(field_attr) {

		if (field_attr != "") {

			var fields = $(field_attr);

			fields.each(function() {
				$(this).prop('disabled', false);
			});

		}

	}

});