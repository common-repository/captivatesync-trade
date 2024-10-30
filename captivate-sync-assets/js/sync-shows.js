jQuery( document ).ready(
	function( $ ) {

			$( document ).on(
				'click',
				'button[name=syncShows]',
				function(e) {

					e.preventDefault();

					$.ajax(
						{
							url: cfmsync.ajaxurl,
							type: 'post',
							data: {
								action: 'sync-shows',
								_nonce: cfmsync.ajaxnonce
							},
							beforeSend: function( response ) {

								$( 'button[name=syncShows]' ).prop( 'disabled', true );

								$( '#cfm-message' ).html( '<p>Syncing shows and episodes...</p>' ).fadeIn();

							},
							success: function( response ) {

								if ( 'success' == response ) {
									  $( '#cfm-message' ).html( '<p>Sync complete!</p>' );
								} else {
									 $( '#cfm-message' ).html( '<p>' + response + '</p>' );
								}

								location.reload( true );
							}
						}
					);

					e.preventDefault();

				}
			);

	}
);
