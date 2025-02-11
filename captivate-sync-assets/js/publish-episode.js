Dropzone.autoDiscover = false;

jQuery( document ).ready(
	function($) {

		/**
		 * Current screens
		 */
		var publish_episode_screens = ['toplevel_page_cfm-hosting-publish-episode', 'admin_page_cfm-hosting-publish-episode', 'captivate-sync_page_cfm-hosting-publish-episode'],
			edit_episode_screens = ['toplevel_page_cfm-hosting-edit-episode', 'admin_page_cfm-hosting-edit-episode', 'captivate-sync_page_cfm-hosting-edit-episode'];

		/**
		 * Save form data locally - on keyup and every 6 hours
		 */
		if( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1) {
			$('#cfm-form-publish-episode').cfmLocalStorage({exclude_name: ['_sec','_wp_http_referer'], interval: 43200000});
		}

		/**
		 * Audio uploader
		 */
		var show_id    = $( 'input[name=show_id]' ).val(),
		media_id       = $( 'input[name=media_id]' ),
		media_url      = $( 'input[name=media_url]' ),
		media_size     = $( 'input[name=media_size]' ),
		media_type     = $( 'input[name=media_type]' ),
		media_duration = $( 'input[name=media_duration]' ),
		player         = document.getElementById( 'audio-player' );

		$('#podcast-dropzone').dropzone({
			autoProcessQueue: true,
			uploadMultiple: false,
			parallelUploads: 1,
			maxFiles: 1,
			maxFilesize: 300,
			timeout: 500000,
			url: cfm_script.cfm_url + '/shows/' + show_id + '/media',
			acceptedFiles: '.mp3',
			addRemoveLinks: false,
			clickable: '#upload-audio',
			dictDefaultMessage: '<div class="upload-icon"><i class="fal fa-cloud-upload fa-3x" aria-hidden="true"></i></div><div class="upload-click-text">Drag & drop files <br> or <strong>choose files</strong><br><br><small>Please use an MP3 file with a fixed bitrate!</small></div>',

			init: function() {
				var podcastDropzone = this;

				existingFile = media_url.val();

				if ( existingFile ) {

					var mockFile = {
						name: existingFile.replace( /^.*[\\\/] / , '' ),
						size: 1,
						status: 'success',
						accepted: true,
						processing: true
					};

					podcastDropzone.files.push( mockFile );
				}

				podcastDropzone.on(
					'addedfile',
					function(file) {

						var fileSize 	= file.size,
							filesCount  = podcastDropzone.files.length;

						if ( fileSize > 314572800 ) { // 300MB
							alert( "Max file size exceeded (300MB)." );
						}

						// remove other files.
						if ( filesCount > 1 ) {
							$.each(
								podcastDropzone.files,
								function(index, file) {
									if ( index < filesCount - 1 ) {
										podcastDropzone.removeFile( file );
									}
								}
							);
						}
					}
				);

				podcastDropzone.on(
					'sending',
					function(file, xhr, formData) {

						xhr.setRequestHeader( "Authorization", "Bearer " + cfm_script.cfm_token );

					}
				);

				podcastDropzone.on(
					'processing',
					function( file, response ) {

						// show episode fields.
						$( '#cfm-episode-uploader' ).fadeOut(
							100,
							function () {

								// show preloader.
								$( '#cfm-episode-upload-preloader' ).show();
								$( '#cfm-episode-upload-preloader .cfm-episode-upload-message' ).html( ' <p>Uploading your audio</p>' );
								$( '#cfm-episode-upload-preloader .cfm-episode-upload-progress' ).show();

								$( '#cfm-episode-details' ).fadeIn(
									500,
									function() {
										$( 'html, body' ).animate( { scrollTop: $( '#cfm-episode-upload-preloader' ).offset().top }, 1000 );
									}
								);
							}
						);

					}
				);

				podcastDropzone.on(
					'uploadprogress',
					function(file, progress, bytesSent) {

						$( '#cfm-episode-upload-preloader .cfm-episode-upload-progress .progress-bar' ).css( 'width', progress + '%' );

					}
				);

				podcastDropzone.on(
					'success',
					function( file, response ) {

						var media           = response['media'],
							file_url        = media['media_url'],
							filename        = file.name;

						media_url.val( file_url );
						media_id.val( media['id'] );
						media_size.val( media['media_size'] );
						media_duration.val( media['media_duration'] );
						media_type.val( media['media_type'] );
						$( 'input[name=media_id]' ).trigger( 'change' );

						$( '#cfm-episode-upload-preloader .cfm-episode-upload-message' ).html( ' <p><span class="text-success"><i class="fas fa-check"></i></span> Successfully uploaded media file <strong>' + filename + '</strong> to this episode</p>' );
						$( '#cfm-episode-upload-preloader .cfm-episode-upload-progress' ).fadeOut();

						$( '#cfm-episode-details .cfm-submit button[name=episode_publish]' ).prop( 'disabled', false );

						// show uploaded audio.
						$( '.cfm-field.cfm-episode-audio' ).show();
						$( '.cfm-field.cfm-episode-audio .uploaded-audio-name' ).html( '<i class="fas fa-file-audio"></i> ' + filename );
						$( '#audio-player source' ).prop( 'src', file_url );
						player.load();

						// show replace audio option.
						$( '.cfm-field.cfm-episode-audio-replace' ).show();
						$( '#audio-replace' ).prop( 'checked', false );

						// remove upload error if any
						$( '#upload-audio' ).removeClass( 'cfm-field-error' );
						$( '#upload-audio-error' ).remove();

						// move uploader to episode details.
						$( '#cfm-episode-uploader' ).appendTo( '#cfm-episode-details .cfm-episode-audio-upload' );

						// reset uploader.
						podcastDropzone.removeAllFiles( true );
					}
				);

				podcastDropzone.on(
					'error',
					function( file, response ) {

						$( '#cfm-episode-upload-preloader .cfm-episode-upload-message' ).html( '<p><span class="text-danger"><i class="fas fa-times"></i></span> Media file upload error</p>' );
						$( '#cfm-episode-upload-preloader .cfm-episode-upload-progress' ).fadeOut();

						// show the inline uploader.
						$( '#cfm-episode-uploader' ).show();
						$( '.cfm-field.cfm-episode-audio-upload' ).show();

						podcastDropzone.removeAllFiles( true );
					}
				);

			}
		});

		$( '#upload-skip' ).click(
			function () {
				$( '#cfm-episode-uploader' ).fadeOut(
					100,
					function () {
						// move uploader to episode details.
						$( this ).appendTo( '#cfm-episode-details .cfm-episode-audio-upload' ).show();
						$( '.cfm-field.cfm-episode-audio-upload' ).show();

						$( '#cfm-episode-details' ).fadeIn( 300 );
					}
				);
			}
		);

		$( document ).on(
			'click',
			'.cfm-field.cfm-episode-audio .uploaded-audio-play',
			function(e) {

				if ( $( this ).hasClass( 'playing' ) ) {
					method = 'pause';
					$( this ).removeClass( 'playing' );
					$( this ).removeClass( 'fa-pause-circle' );
					$( this ).addClass( 'fa-play-circle' );
				} else {
					method = 'play';
					$( this ).addClass( 'playing' );
					$( this ).removeClass( 'fa-play-circle' );
					$( this ).addClass( 'fa-pause-circle' );
				}

				player[method]();

			}
		);

		$( document ).on(
			'change',
			'#audio-replace',
			function(e) {
				if ( this.checked ) {
                    $( '.cfm-field.cfm-episode-audio-upload, #cfm-episode-uploader, #upload-audio' ).show();
                } else {
                    $( '.cfm-field.cfm-episode-audio-upload, #cfm-episode-uploader, #upload-audio' ).hide();
                }

				// reset uploader.
				Dropzone.forElement( "#podcast-dropzone" ).removeAllFiles( true );
			}
		);

		/**
		 * Display a different episode title on Apple Podcasts?
		 */
		$( '#post_title_check' ).change(
			function(){
				if ($( '#post_title_check:checked' ).length == $( '#post_title_check' ).length) {
					$( '.cfm-field.cfm-itunes-episode-title' ).fadeIn( 200 );
				} else {
					$( '.cfm-field.cfm-itunes-episode-title' ).fadeOut( 200 );
				}
			}
		);

		$( '.btn-number' ).click(
			function(e){
				e.preventDefault();

				fieldName      = $( this ).attr( 'data-field' );
				type           = $( this ).attr( 'data-type' );
				var input      = $( "input[name='" + fieldName + "']" );
				var currentVal = parseInt( input.val() );
				if ( ! isNaN( currentVal )) {
					if (type == 'minus') {

						if (currentVal > input.attr( 'min' )) {
							input.val( currentVal - 1 ).change();
						}
						if (parseInt( input.val() ) == input.attr( 'min' )) {
							$( this ).attr( 'disabled', true );
						}

					} else if (type == 'plus') {

						if (currentVal < input.attr( 'max' )) {
							input.val( currentVal + 1 ).change();
						}
						if (parseInt( input.val() ) == input.attr( 'max' )) {
							$( this ).attr( 'disabled', true );
						}

					}
				} else {
					input.val( 0 );
				}
			}
		);
		$( '.input-number' ).focusin(
			function(){
				$( this ).data( 'oldValue', $( this ).val() );
			}
		);
		$( '.input-number' ).change(
			function() {

				minValue     = parseInt( $( this ).attr( 'min' ) );
				maxValue     = parseInt( $( this ).attr( 'max' ) );
				valueCurrent = parseInt( $( this ).val() );

				name = $( this ).attr( 'name' );
				if (valueCurrent >= minValue) {
					$( ".btn-number[data-type='minus'][data-field='" + name + "']" ).removeAttr( 'disabled' )
				} else {
					alert( 'Sorry, the minimum value was reached' );
					$( this ).val( $( this ).data( 'oldValue' ) );
				}
				if (valueCurrent <= maxValue) {
					$( ".btn-number[data-type='plus'][data-field='" + name + "']" ).removeAttr( 'disabled' )
				} else {
					alert( 'Sorry, the maximum value was reached' );
					$( this ).val( $( this ).data( 'oldValue' ) );
				}

			}
		);
		$( ".input-number" ).keydown(
			function (e) {
				// Allow: backspace, delete, tab, escape, enter and ..
				if ($.inArray( e.keyCode, [46, 8, 9, 27, 13, 190] ) !== -1 ||
				// Allow: Ctrl+A.
				(e.keyCode == 65 && e.ctrlKey === true) ||
				// Allow: home, end, left, right.
				(e.keyCode >= 35 && e.keyCode <= 39)) {
					 // let it happen, don't do anything.
					 return;
				}
				// Ensure that it is a number and stop the keypress.
				if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
					e.preventDefault();
				}
			}
		);

		/**
		 * Date and time picker
		 */
		function change_publish_button(datetime) {
			var d1 = new Date();
			var d2 = new Date( datetime );

			if (d1 > d2) {
				$( '.cfm-submit button[name=episode_publish]' ).html( "Publish Episode" );
				$( '.cfm-submit button[name=episode_update]' ).html( "Update Episode" );
			} else {
				$( '.cfm-submit button[name=episode_publish]' ).html( "Schedule Episode" );
				$( '.cfm-submit button[name=episode_update]' ).html( "Schedule Episode" );
			}
		}

		$( "#publish_date" ).datepicker(
			{
				changeMonth: true,
				changeYear: true,
				showOtherMonths: true,
				selectOtherMonths: true,
				defaultDate: new Date(),
				dateFormat: 'mm/dd/yy',
				dayNamesMin: [ "Su", "Mo", "Tu", "We", "Th", "Fr", "Sa" ],
				onSelect: function(date) {

					change_publish_button( date + ' ' + $( '#publish_time' ).val() );

				}
			}
		);

		$( "#publish_date" ).prop( "autocomplete", "off" );

		$( document ).on(
			'click',
			'.cfm-timepicker .dropdown-menu a.dropdown-item',
			function(e) {

				var val = $( this ).text();
				change_publish_button( $( "#publish_date" ).val() + ' ' + val );

				$( '#publish_time' ).val( val );
			}
		);

		/**
		 * Artwork image uploader
		 */
		$( document ).on(
			'click',
			'#artwork-dropzone',
			function(e) {

				e.preventDefault();
				var image_frame;
				if ( image_frame ) {
					image_frame.open();
				}

				// Define image_frame as wp.media object.
				image_frame = wp.media(
					{
						title: 'Select Episode Cover Art',
						multiple : false,
						library : {
							type : 'image',
						}
					}
				);

				image_frame.on(
					'select',
					function() {

						var selection  = image_frame.state().get( 'selection' );
						var artwork_id = 0;

						if ( artwork_id == 0 ) {
							selection.each(
								function(attachment) {
									artwork_id = attachment['id'];
								}
							);
						}

						if ( artwork_id != 0) {

							var media_attachment = image_frame.state().get('selection').first().toJSON();

							if ( media_attachment.url ) {

								$( '#episode-artwork' ).val( media_attachment.url );
								$( '#episode-artwork-id' ).val( artwork_id );
								$( '#artwork-preview' ).attr( 'src', media_attachment.url ).hide().fadeIn( 650 );

								$( '#episode-artwork-width' ).val( media_attachment.width );
								$( '#episode-artwork-height' ).val( media_attachment.height );
								$( '#episode-artwork-type' ).val( media_attachment.mime );
								$( '#episode-artwork-filesize' ).val( media_attachment.filesizeInBytes );

								$( '#episode-artwork' ).trigger( 'change' );
							}
						}

					}
				);

				image_frame.on(
					'open',
					function() {
						// On open, get the id from the hidden input.
						// and select the appropiate images in the media manager.
						var selection = image_frame.state().get( 'selection' );
						ids           = $( '#episode-artwork-id' ).val().split( ',' );
						ids.forEach(
							function(id) {
								attachment = wp.media.attachment( id );
								attachment.fetch();
								selection.add( attachment ? [ attachment ] : [] );
							}
						);

					}
				);

				image_frame.open();
			}
		);

		/**
		 * Featured image uploader
		 */
		$( document ).on(
			'click',
			'#featured-image-upload',
			function(e) {

				e.preventDefault();
				var image_frame;
				if ( image_frame ) {
					image_frame.open();
				}

				// Define image_frame as wp.media object.
				image_frame = wp.media(
					{
						title: 'Select Website Featured Image',
						multiple : false,
						library : {
							type : 'image',
						}
					}
				);

				image_frame.on(
					'select',
					function() {
						// On close, get selections and save to the hidden input.
						// plus other AJAX stuff to refresh the image preview.
						var selection  = image_frame.state().get( 'selection' );
						var gallery_id = 0;

						if ( gallery_id == 0 ) {
							selection.each(
								function(attachment) {
									gallery_id = attachment['id'];
								}
							);
						}

						if ( gallery_id != 0) {

							var media_attachment = image_frame.state().get('selection').first().toJSON();

							if ( media_attachment.url ) {

								$( '#featured_image' ).val( gallery_id );
								$( '#featured-image-preview' ).addClass( 'active' );
								$( '#featured-image-preview' ).attr( 'src', media_attachment.url ).hide().fadeIn( 650 );
								$( '#featured-image-upload' ).val( 'Remove featured image' );
								$( '#featured-image-upload' ).prop( 'id', 'featured-image-remove' );

								$( '#featured_image' ).trigger( 'change' );

								// LOCALSTORAGE - save featured image data.
								if( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1) {
									localStorage.setItem(cfmsync.CFMH_SHOWID + '_featured_image_url_local', media_attachment.url);
								}

							}
						}


					}
				);

				image_frame.open();
			}
		);

		$( document ).on(
			'click',
			'#featured-image-remove',
			function(e) {
				$( '#featured_image' ).val( '0' );
				$( '#featured-image-preview' ).fadeOut();
				$( '#featured-image-remove' ).val( 'Set featured image' );
				$( '#featured-image-remove' ).prop( 'id', 'featured-image-upload' );

				$( '#featured_image' ).trigger( 'change' );

				// LOCALSTORAGE - remove featured image data.
				localStorage.removeItem(cfmsync.CFMH_SHOWID + '_featured_image_url_local');
			}
		);

		/**
		 * Change content editor
		 */
		$( document ).on(
			'click',
			'#enable_wordpress_editor',
			function(e) {
				if ( this.checked ) {
					$( '.cfm-show-captivate-editor' ).addClass('hidden');
					$( '.cfm-show-wordpress-editor' ).removeClass('hidden');
				} else {
					$( '.cfm-show-captivate-editor' ).removeClass('hidden');
					$( '.cfm-show-wordpress-editor' ).addClass('hidden');
				}
			}
		);

		/**
		 * Submit validation
		 */
		$(window).keydown(function(e) {
			// prevent form submission on enter.
			if ( e.keyCode == 13 && e.target.tagName.toLowerCase() != 'textarea' ) {
				e.preventDefault();
				return false;
			}
		});

		var clicked_button = null;
		$( document ).on('submit', '#cfm-form-publish-episode', function(e) {

			var $this = $('#' + clicked_button),
				$this_html = $this.html();

			$('button[type=submit]').prop('disabled', true);
			$this.html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
			$('#episode-cancel').addClass('disabled');

			var post_title 	 = $( '#post_title' ).val(),
			shownotes        = $( 'textarea[name=post_content]' ).val(),
			seo_description  = $('#seoDescription').val(),
			wordpress_editor_shownotes = tinymce.activeEditor.getContent(),
			media_id         = $( 'input[name=media_id]' ).val(),
			errors           = 0;

			if ( media_id == '' && clicked_button != "episode_draft") {
				$( '#upload-audio' ).addClass( 'cfm-field-error' );
				if ( ! $( '#upload-audio-error' ).length ) {
					$( '<div id="upload-audio-error" class="cfm-field-error-text">You must upload an audio for your episode.</div>' ).insertAfter( '#upload-audio' );
				}
				errors += 1;
			}
			if ( post_title == '' ) {
				$( '#post_title' ).addClass( 'cfm-field-error' );
				if ( ! $( '#post_title-error' ).length ) {
					$( '<div id="post_title-error" class="cfm-field-error-text">You must enter a title for your episode.</div>' ).insertAfter( '#post_title' );
				}
				errors += 1;
			}
			if ( ( shownotes == '' || shownotes == '<p><br></p>' ) && $('.cfm-show-captivate-editor').is(":visible") ) {
				$( '#cfm-field-wpeditor' ).addClass( 'cfm-field-error' );
				$( '.cfm-show-description .ql-toolbar.ql-snow' ).addClass( 'cfm-field-error' );
				if ( ! $( '#captivate-shownotes-error' ).length ) {
					$( '<div id="captivate-shownotes-error" class="cfm-field-error-text">You must enter show notes for your episode.</div>' ).insertAfter( '#cfm-field-wpeditor' );
				}
				errors += 1;
			}
			if ( wordpress_editor_shownotes == '' && $('.cfm-show-wordpress-editor').is(":visible") ) {
				$( '#wp-post_content_wp-wrap' ).addClass( 'cfm-field-error' );
				if ( ! $( '#wp-shownotes-error' ).length ) {
					$( '<div id="wp-shownotes-error" class="cfm-field-error-text">You must enter show notes for your episode.</div>' ).insertAfter( '#wp-post_content_wp-wrap' );
				}
				errors += 1;
			}

			console.log(seo_description.length);

			if ( seo_description.length > 300 ) {
				$('#seoDescription').addClass('is-invalid');
				if ( ! $( '#seoDescription-error' ).length ) {
					$( '<div id="seoDescription-error" class="cfm-field-error-text">SEO Description: length must be less than or equal to 300 characters long.</div>' ).insertAfter( '.cfm-seo-description-count' );
				}
				errors += 1;
			}

			var artwork_id = $( '#episode-artwork-id' ).val(),
				artwork_width = $( '#episode-artwork-width' ).val(),
				artwork_height = $( '#episode-artwork-height' ).val(),
				artwork_type = $( '#episode-artwork-type' ).val();
				artwork_filesize = $( '#episode-artwork-filesize' ).val();
			if ( artwork_id != '' && ( artwork_width != artwork_height || ( artwork_width < 1400 || artwork_width > 3000 ) || ( artwork_height < 1400 || artwork_height > 3000 ) || artwork_filesize > 500000 || ( artwork_type != "image/jpeg" && artwork_type != "image/jpg" && artwork_type != "image/png" ) ) ) {
				if ( ! $( '#upload-artwork-error' ).length ) {
					$( '<div id="upload-artwork-error" class="cfm-field-error-text mb-4">Your artwork must be a square jpeg/png minimum of 1400x1400 pixels in size (max 3000x3000) and less than 500kb in filesize.</div>' ).insertAfter( '.cfm-artwork-upload' );
				}
				errors += 1;
			}

			if ( errors > 0 ) {

				$('html, body').animate({
					scrollTop: $("#cfm-episode-details").offset().top
				}, 1000);

				$('button[type=submit]').prop('disabled', false);
				$('#episode-cancel').removeClass('disabled');
				$this.html($this_html);

				return false;
			}
		});
		$( document ).on('click', '#episode_draft', function(e) {
		    clicked_button = 'episode_draft';
		    $('input[name="submit_action"]').val('draft');
		});
		$( document ).on('click', '#episode_update', function(e) {
		    clicked_button = 'episode_update';
		    $('input[name="submit_action"]').val('update');
		});
		$( document ).on('click', '#episode_publish', function(e) {
		    clicked_button = 'episode_publish';
		    $('input[name="submit_action"]').val('publish');
		});

		$( document ).on(
			'keyup',
			'#post_title',
			function(e) {
				if ( $(this).val() != '' ) {
					$(this).removeClass( 'cfm-field-error' );
					$( '#post_title-error' ).remove();
				}
			}
		);

		$ ( document ).on(
			'keyup',
			'#seoDescription',
			function(e) {
				var seo_description_width = $(this).val().length < 155 ? $(this).val().length / 155 * 100 : 100;
				var seo_description_color = "orange";
				if(seo_description_width >= 50 && seo_description_width <= 99) {
					seo_description_color = "#29ab57";
				} else if(seo_description_width >= 100) {
					seo_description_color = "#dc3545";
				}
				$('.cfm-seo-description-progress').css( "background-color", seo_description_color );
				$('.cfm-seo-description-progress').css( "width", seo_description_width + '%' );

			}
		);

		/**
		 * Generate slug
		 */
		$( document ).on(
			'focus',
			'#post_title.post-title-empty',
			function(e) {
				$( this ).blur(
					function() {
						if ( $(this).hasClass( 'post-title-empty' ) ) {
							var post_name = convertToSlug( $( this ).val() );

							$( '#post_name' ).val( post_name );
							$( '#new_post_name' ).val( post_name );

							if ( $(this).val() != '' ) {

								$(this).removeClass( 'post-title-empty' );
							}
						}
					}
				);
			}
		);

		/**
		 * Edit slug
		 */
		$( document ).on(
			'click',
			'#cfm-edit-slug',
			function(e) {

				var new_post_name = convertToSlug( $( '#post_name' ).val() );

				if ( $( this ).hasClass( "active" ) ) {

					if ( new_post_name == '' ) {
						post_title = convertToSlug( $( '#post_title' ).val() );
						$( '#post_name' ).val( post_title );
					} else {
						$( '#post_name' ).val( new_post_name );
					}

					$( '#new_post_name' ).val( new_post_name );

					$( '#post_name' ).prop( 'disabled', true );
					$( this ).text( 'Edit' );
					$( this ).removeClass( 'active' );

				} else {
					$( '#post_name' ).prop( 'disabled', false );
					$( '#post_name' ).focus();
					$( this ).text( 'Save Permalink' );
					$( this ).addClass( 'active' );
				}
			}
		);

		function convertToSlug(Text) {
			return Text
			.toLowerCase()
			.replace( / /g,'-' )
			.replace( /[^\w-]+/g,'' );
		}

		/**
		 * Add category
		 */
		$( document ).on(
			'click',
			'#add_website_category',
			function(e) {

				e.preventDefault();

				var category_parent   = $( '#category_parent' ).val(),
				category_parent_level = $( '#category_parent :selected' ).prop( 'class' );
				category              = $( '#website_category' ).val();

				if ( category != '' ) {

					$.ajax(
						{
							url: cfmsync.ajaxurl,
							type: 'post',
							dataType: 'json',
							data: {
								action: 'add-webcategory',
								category_parent: category_parent,
								category: category,
								_nonce: cfmsync.ajaxnonce
							},
							success: function( response ) {


								if ( 'error' == response ) {
									alert( "Something went wrong. Please contact support." );
								} else {

									$( '.cfm-website-categories-wrap > ul' ).prepend( response.cat_checklist );

									$( '.cfm-category-parent' ).html( response.cat_parent );

									$( '#category_parent' ).prop( "selectedIndex", 0 );
									$( '#website_category' ).val( "" );
								}
							}
						}
					);
				}

				e.preventDefault();

			}
		);

		/**
		 * Add tags
		 */
		$( document ).on(
			'click',
			'#add_website_tags',
			function(e) {

				e.preventDefault();

				var tags = $( '#website_tags' ).val(),
					tags_array = tags.split(","),
					tags_input = [],
					tags_input_lower = [],
					tags_existing = [];

				for (i=0;i<tags_array.length;i++){
					tags_input_lower.push($.trim(tags_array[i].toLowerCase()));
				}

				$('.cfm-website-tags-wrap ul li label').each( function() {
					var tags_check = $.trim($(this).text().toLowerCase());

					// check mark existing tags
					if($.inArray(tags_check, tags_input_lower) !== -1) {
						$(this).find('input[type="checkbox"]').prop('checked', true);
					}

					tags_existing.push(tags_check);
				});

				// get new tags
				for (i=0;i<tags_array.length;i++){
					var new_tags_lower = $.trim(tags_array[i].toLowerCase());

					if($.inArray(new_tags_lower, tags_existing) == -1) {
						tags_input.push($.trim(tags_array[i]));
					}

				}

				if ( tags_input.length !== 0 ) {

					$.ajax(
						{
							url: cfmsync.ajaxurl,
							type: 'post',
							data: {
								action: 'add-tags',
								tags: tags_input.toString(),
								_nonce: cfmsync.ajaxnonce
							},
							success: function( response ) {
								if ( 'error' == response ) {
									alert( "Something went wrong. Please contact support." );
								} else {
									$( '.cfm-website-tags-wrap > ul' ).prepend( response );

									$( '#website_tags' ).val( "" );
								}
							}
						}
					);
				}
				else {
					$( '#website_tags' ).val( "" );
				}

				e.preventDefault();

			}
		);

		/**
		 * Transcript defaults
		 */
		var transcript_add_default = '<a id="transcript-add" data-toggle="modal" data-target="#transcript-modal" data-backdrop="static" data-keyboard="false" href="#">Add a transcript to this episode </a>',
			transcript_upload_default = '<div class="transcript-text">Have a transcript file? Upload it directly... </div><a id="upload-transcript" href="javascript: void(0);"><i class="fal fa-cloud-upload" aria-hidden="true"></i> Upload File</a>';

		/**
		 * Transcript upload
		 */
		$( document ).on(
			'click',
			'#upload-transcript',
			function(e) {
				$('#transcriptFile').focus().trigger('click');
			}
		);

		/**
		 * Transcript update
		 */
		$( document ).on(
			'click',
			'#update-transcript',
			function(e) {
				var	transcript_file = $('#transcriptFile'),
					transcript_text = $('#transcriptText').val();

				if (transcript_file.get(0).files.length === 0) {
					if ('' != transcript_text) {
						var transcript_text_new = '<strong>' + cfm_truncate(transcript_text, 20) + '</strong> <a id="cfm-transcript-edit" class="float-right" data-toggle="modal" data-target="#transcript-modal" data-backdrop="static" data-keyboard="false" href="#">Edit</a><div class="mt-2"><a id="transcript-remove" class="transcript-remove text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a></div>';
					}
					else {
						var transcript_text_new = transcript_add_default;
					}

					$('#transcript_current').val(transcript_text);
					$('#transcript_type').val('text');
				}
				else {
					var filename = transcript_file.val().replace(/C:\\fakepath\\/i, '');

					var transcript_text_new = '<strong>' + filename + '</strong> <a id="cfm-transcript-edit" class="float-right" data-toggle="modal" data-target="#transcript-modal" data-backdrop="static" data-keyboard="false" href="#">Replace</a><div class="mt-2"><a id="transcript-remove" class="transcript-remove text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a></div>';

					$('#transcript_current').val(filename);
					$('#transcript_type').val('file');
				}

				$('#transcript_updated').val('1');

				$('.cfm-episode-transcription .cmf-transcript-wrap').html(transcript_text_new);
				$("#transcript-modal").modal('hide');
			}
		);

		/**
		 * Transcript cancel
		 */
		$( document ).on(
			'click',
			'#cancel-transcript',
			function(e) {
				var transcript_current = $('#transcript_current').val(),
					transcript_type = $('#transcript_type').val();

				if ('file' == transcript_type) {
					$('#transcriptText').val('');
					$('.transcript-upload-box').html('<div class="transcript-text">File uploaded: <strong>' + transcript_current + '</strong></div><a id="remove-transcript-file" class="text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a>');
					$('#transcriptText').prop('disabled', true);
					$('.transcript-upload-box').removeClass('disabled');
				}
				else {
					$('#transcriptText').val(transcript_current);
					$('.transcript-upload-box').html(transcript_upload_default);
					$('.transcript-upload-box').addClass('disabled');
					$('#transcriptText').prop('disabled', false);
				}
			}
		);

		/**
		 * Transcript remove
		 */
		$( document ).on(
			'click',
			'#transcript-remove',
			function(e) {
				$('#transcriptText').val('');
				$('#transcriptFile').val('');
				$('#transcript_current').val('');
				$('#transcript_updated').val('1');
				$('#transcriptText').prop('disabled', false);
				$('.transcript-upload-box').removeClass('disabled');

				$('.cfm-episode-transcription .cmf-transcript-wrap').html(transcript_add_default);
				$('.transcript-upload-box').html(transcript_upload_default);
			}
		);

		/**
		 * Enable/disable upload/text
		 */
		$( document ).on(
			'change keyup',
			'#transcriptText',
			function(e) {
				if ($(this).val() != '') {
					$('.transcript-upload-box').addClass('disabled');
				}
				else {
					$('.transcript-upload-box').removeClass('disabled');
				}
			}
		);
		$( document ).on(
			'change',
			'#transcriptFile',
			function(e) {
				if ($(this).get(0).files.length === 0) {
					$('#transcriptText').prop('disabled', false);

					$('.transcript-upload-box').html(transcript_upload_default);
				}
				else {
					var filename = $(this).val().replace(/C:\\fakepath\\/i, '');

					$('#transcriptText').prop('disabled', true);

					$('.transcript-upload-box').html('<div class="transcript-text">File uploaded: <strong>' + filename + '</strong></div><a id="remove-transcript-file" class="text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a>');
				}
			}
		);

		/**
		 * Transcript file remove
		 */
		$( document ).on(
			'click',
			'#remove-transcript-file',
			function(e) {
				$('#transcriptFile').val('');
				$('#transcriptFile').trigger('change');
			}
		);

		/**
		 * LOCALSTORAGE - save shownotes wordpress editor
		 */
		if( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1) {
			setInterval(function () {
				const enable_wordpress_editor_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'enable_wordpress_editor');
				if ( 'on' == enable_wordpress_editor_local ) {
					tinymce.triggerSave();
		            var content_html =  '';

		            if($('#wp-post_content_wp-wrap').hasClass('html-active')){ // We are in text mode
						content_html =  $("#post_content_wp").val();
					} else { // We are in tinyMCE mode
					    var activeEditor = tinymce.get('post_content_wp');
					    if(activeEditor!==null){ // Make sure we're not calling setContent on null

					   	content_html =  activeEditor.getContent();
					    }
					}

					localStorage.setItem(cfmsync.CFMH_SHOWID + '_post_content_wp_local', content_html);
		        }
	        }, 5*1000);
		}

		/**
		 * LOCALSTORAGE - populate fields
		 */
		$(window).load(function() {

		    if( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, publish_episode_screens) !== -1) {

		     	// show episode details.
				const post_title_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'post_title');
				const shownotes_local_html = localStorage.getItem(cfmsync.CFMH_SHOWID + '_shownotes_local_html');
				const enable_wordpress_editor_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'enable_wordpress_editor');
				const post_content_wp_local = localStorage.getItem(cfmsync.CFMH_SHOWID + '_post_content_wp_local');
				const media_url_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'media_url');

				if ( ( '' != post_title_local && undefined !== post_title_local && null !== post_title_local ) || ( null !== shownotes_local_html && '' != shownotes_local_html && '<p><br></p>' != shownotes_local_html ) || ( 'on' == enable_wordpress_editor_local && ( '' != post_content_wp_local && undefined !== post_content_wp_local && null !== post_content_wp_local ) ) || ( '' != media_url_local && undefined !== media_url_local && null !== media_url_local ) ) {
					$( '#upload-skip' ).trigger('click');
				}

				// populate post_content_wp.
				if ( 'on' == enable_wordpress_editor_local ) {
					$( '#enable_wordpress_editor' ).trigger('click');
				}

				if ( 'on' == enable_wordpress_editor_local && ( '' != post_content_wp_local && undefined !== post_content_wp_local && null !== post_content_wp_local ) ) {

					if($('#wp-post_content_wp-wrap').hasClass('html-active')){ // We are in text mode
					   $('#post_content_wp').val(post_content_wp_local);
					} else { // We are in tinyMCE mode
					    var activeEditor = tinymce.get('post_content_wp');
					    if(activeEditor!==null){ // Make sure we're not calling setContent on null
					        activeEditor.setContent(post_content_wp_local);
					    }
					}
				}

				// show audio.
				if ( '' != media_url_local && undefined !== media_url_local && null !== media_url_local ) {

					var filename = media_url_local.split('/').pop().split('#')[0].split('?')[0];

					$( '#upload-audio' ).hide();
					$( '.cfm-field.cfm-episode-audio-upload' ).hide();
					$( '.cfm-field.cfm-episode-audio' ).show();
					$( '.cfm-field.cfm-episode-audio-replace' ).show();
					$( '.cfm-field.cfm-episode-audio .uploaded-audio-name' ).html( '<i class="fas fa-file-audio"></i> ' + filename );
					$( '#audio-player source' ).prop( 'src', media_url_local );
					player.load();

					$( '#cfm-episode-details .cfm-submit button[name=episode_publish]' ).prop( 'disabled', false );
				}

				// populate artwork.
				const artwork_url_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'episode_artwork');
				if ( '' != artwork_url_local && undefined !== artwork_url_local && null !== artwork_url_local ) {
					$( '#artwork-preview' ).attr( 'src', artwork_url_local );
				}

				// populate featured image.
				const featured_image_url_local = localStorage.getItem(cfmsync.CFMH_SHOWID + '_featured_image_url_local');
				if ( '' != featured_image_url_local && undefined !== featured_image_url_local && null !== featured_image_url_local ) {
					$( '#featured-image-preview' ).addClass( 'active' );
					$( '#featured-image-preview' ).attr( 'src', featured_image_url_local );
					$( '#featured-image-upload' ).val( 'Remove featured image' );
					$( '#featured-image-upload' ).prop( 'id', 'featured-image-remove' );
				}

				// show apple podcasts title if checked.
				const itunes_title_local = $(document).cfmGetLocalStorage('cfm-form-publish-episode', 'itunesTitle');
				if ( null === itunes_title_local || '' == itunes_title_local ) {
					$('#post_title_check').prop('checked', false);
				}
				else {
					$('#post_title_check').prop('checked', true);
					$('#cfm-episode-details .cfm-itunes-episode-title').fadeIn();
				}

				// cleat tags and categories input.
				$('#category_parent').val('-1');
				$('#website_category').val('');
				$('#website_tags').val('');

			}

			if ( $.inArray( cfmsync.CFMH_CURRENT_SCREEN, edit_episode_screens) !== -1 ) {

				var submit_action = cfm_get_url_vars()["action"],
					eid = cfm_get_url_vars()["eid"];

				// LOCALSTORAGE - clear all
				if ('published' == submit_action) {
					// local-storage.js
					var key = cfmsync.CFMH_SHOWID + '_cfm-form-publish-episode_save_storage';
					localStorage.removeItem(key);

					// custom.
					localStorage.removeItem(cfmsync.CFMH_SHOWID + '_featured_image_url_local');
					localStorage.removeItem(cfmsync.CFMH_SHOWID + '_post_content_wp_local');
					localStorage.removeItem(cfmsync.CFMH_SHOWID + '_shownotes_local');
					localStorage.removeItem(cfmsync.CFMH_SHOWID + '_shownotes_local_html');

					// Update URL to remove response and action params
					var new_url = cfmsync.CFMH_ADMINURL + 'admin.php?page=cfm-hosting-edit-episode&show_id=' + cfmsync.CFMH_SHOWID + '&eid=' + eid;
					setTimeout( function() {
						window.history.pushState(null, null, new_url );
					}, 2000 );
				}
			}

		});

	}
);
