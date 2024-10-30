jQuery( document ).ready(
	function( $ ) {

			$( document ).on(
				'click',
				'button[name=createShow]',
				function(e) {

					e.preventDefault();

					$.ajax(
						{
							url: cfm.ajaxurl,
							type: 'post',
							data: {
								action: 'create-pw-show',
								_nonce: cfm.ajaxnonce
							},
							beforeSend: function( response ) {

								$( 'html, body' ).animate(
									{
										scrollTop: 0
									},
									900
								);

								$( '#cfm-message' ).html( '<p>Creating show... Please don\'t leave this page.</p>' );

							},
							success: function( response ) {

								// console.log(response);.

								if ( 'success' == response ) {
									  $( 'button[name=createShow]' ).prop( 'disabled', true );
									  $( '#cfm-message' ).html( '<p>Show successfully created</p>' );
								} else {
									 $( '#cfm-message' ).html( '<p>' + response + '</p>' );
								}

							}
						}
					);

					e.preventDefault();

				}
			);

			$( document ).on(
				'click',
				'button[name=migrateShow]',
				function(e) {

					e.preventDefault();

					$.ajax(
						{
							url: cfm.ajaxurl,
							type: 'post',
							data: {
								action: 'import-show-data',
								_nonce: cfm.ajaxnonce
							},
							beforeSend: function( response ) {

								$( 'html, body' ).animate(
									{
										scrollTop: 0
									},
									900
								);

								$( '#cfm-message' ).html( '<p>Migrating your show information... Please don\'t leave this page.</p>' );
							},
							success: function( response ) {

								// console.log(response);.

								if ( 'success' == response ) {
									$( 'button[name=migrateShow]' ).prop( 'disabled', true );
									$( '#cfm-message' ).html( '<p>Show information successfully migrated.</p>' );
								} else {
									$( '#cfm-message' ).html( '<p>' + response + '</p>' );
								}

							}
						}
					);

					e.preventDefault();

				}
			);

			$( document ).on(
				'click',
				'button[name=migrateEpisodes]',
				function(e) {

					e.preventDefault();

					$.ajax(
						{
							url: cfm.ajaxurl,
							type: 'post',
							data: {
								action: 'import-episodes',
								_nonce: cfm.ajaxnonce
							},
							beforeSend: function( response ) {

								$( 'html, body' ).animate(
									{
										scrollTop: 0
									},
									900
								);

								$( '#cfm-message' ).html( '<p>Migrating your episodes... Please don\'t leave this page.</p>' );
							},
							success: function( response ) {

								// console.log(response);.

								if ( 'success' == response ) {
									$( 'button[name=migrateEpisodes]' ).prop( 'disabled', true );
									$( '#cfm-message' ).html( '<p>Episodes successfully migrated.</p>' );
								} else {
									$( '#cfm-message' ).html( '<p>' + response + '</p>' );
								}

							}
						}
					);

					e.preventDefault();

				}
			);

			$( document ).on(
				'click',
				'button[name=migrateFeed]',
				function(e) {

					e.preventDefault();

					$.ajax(
						{
							url: cfm.ajaxurl,
							type: 'post',
							data: {
								action: 'migrate-feed',
								_nonce: cfm.ajaxnonce
							},
							beforeSend: function( response ) {

								$( 'html, body' ).animate(
									{
										scrollTop: 0
									},
									900
								);

								$( '#cfm-message' ).html( '<p>Migrating feed URL... Please don\'t leave this page.</p>' );
							},
							success: function( response ) {

								// console.log(response);.

								if ( 'success' == response ) {
									$( 'button[name=migrateFeed]' ).prop( 'disabled', true );
									$( '#cfm-message' ).html( '<p>Feed URL successfully migrated</p>' );
								} else {
									$( '#cfm-message' ).html( '<p>' + response + '</p>' );
								}

							}
						}
					);

					e.preventDefault();

				}
			);

	}
);
