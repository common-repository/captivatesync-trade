<?php
/**
 * Used to power our CaptivateSync brain.
 */

if ( ! function_exists( 'cfm_is_show_exists' ) ) :
	/**
	 * Check if show id exists
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 *
	 * @return boolean
	 */
	function cfm_is_show_exists( $show_id ) {
		return in_array( $show_id, cfm_get_show_ids() ) ? true : false;
	}
endif;

if ( ! function_exists( 'cfm_limit_characters' ) ) :
	/**
	 * Limit characters
	 *
	 * @since 1.0
	 * @param string  $characters  The entire string.
	 * @param int     $limit  Limit of characters.
	 * @param boolean $readmore  Elipsis needed.
	 *
	 * @return string
	 */
	function cfm_limit_characters( $characters, $limit = 150, $readmore = '...' ) {
		$characters = wp_strip_all_tags( $characters );
		$length     = strlen( $characters );
		if ( $length <= $limit ) {
			return $characters;
		} else {
			return substr( $characters, 0, $limit ) . $readmore;
		}
	}
endif;

if ( ! function_exists( 'cfm_get_show_page' ) ) :
	/**
	 * Get show
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 * @param string $option  Option.
	 *
	 * @return page
	 */
	function cfm_get_show_page( $show_id, $option ) {

		$shows    = cfm_get_shows();
		$show_ids = array();

		if ( ! empty( $shows ) ) {
			foreach ( $shows as $show ) {
				$show_ids[ $show['id'] ] = $show['index_page'];
			}
		}

		$index_page = ( cfm_is_show_exists( $show_id ) ) ? $show_ids[ $show_id ] : '0';

		if ( 'slug' === $option ) {
			$page = ( '0' == $index_page || '' == $index_page ) ? 'captivate-podcast' : get_post_field( 'post_name', $index_page );
		} else {
			$page = $index_page;
		}

		return $page;
	}
endif;

if ( ! function_exists( 'cfm_get_shows' ) ) :
	/**
	 * Get shows
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	function cfm_get_shows() {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';
		$results    = $wpdb->get_results( "SELECT DISTINCT(show_id) FROM $table_name" );

		$shows = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				$title             = cfm_get_show_info( $result->show_id, 'title' );
				$artwork           = cfm_get_show_info( $result->show_id, 'artwork' ) ? cfm_get_show_info( $result->show_id, 'artwork' ) : CFMH_URL . 'captivate-sync-assets/img/captivate-default.jpg';
				$last_synchronised = cfm_get_show_info( $result->show_id, 'last_synchronised' );
				$index_page        = cfm_get_show_info( $result->show_id, 'index_page' );
				$author  		   = cfm_get_show_info( $result->show_id, 'author' );
				$feed_url          = cfm_get_show_info( $result->show_id, 'feed_url' );

				$shows[] = array(
					'id'                => $result->show_id,
					'title'             => $title,
					'artwork'           => $artwork,
					'last_synchronised' => $last_synchronised,
					'index_page'        => $index_page,
					'author'       	    => $author,
					'feed_url'          => $feed_url,
				);
			}
		}

		return $shows;
	}
endif;

if ( ! function_exists( 'cfm_get_show_id' ) ) :
	/**
	 * Get show ID
	 *
	 * @since 1.0
	 *
	 * @return string
	 */
	function cfm_get_show_id() {

		$current_screen = get_current_screen();

		if ( isset( $_GET['show_id'] ) ) {
			$show_id = sanitize_text_field( wp_unslash( $_GET['show_id'] ) );
		}
		else {
			if ( null !== $current_screen && strpos( $current_screen->id, 'cfm-hosting-podcast-episodes_' ) !== false ) {
				$show_id = substr( $current_screen->id, 49 );
			} else {
				$shows		= cfm_get_shows();
				$show_id 	= ! empty( $shows ) ? $shows[0]['id'] : '';
			}
		}

		return $show_id;
	}
endif;

if ( ! function_exists( 'cfm_get_show_ids' ) ) :
	/**
	 * Get show IDs
	 *
	 * @since 1.0
	 *
	 * @return array
	 */
	function cfm_get_show_ids() {

		$shows    = cfm_get_shows();
		$show_ids = array();

		if ( ! empty( $shows ) ) {

			foreach ( $shows as $show ) {
				$show_ids[] = $show['id'];
			}
		}

		return $show_ids;
	}
endif;

if ( ! function_exists( 'cfm_update_show_info' ) ) :
	/**
	 * Update show information
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 * @param string $option  The option.
	 * @param string $value  The value.
	 *
	 * @return void
	 */
	function cfm_update_show_info( $show_id, $option, $value ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE show_id = %s AND cfm_option = %s", $show_id, $option ) );

		if ( ! empty( $results ) ) {

			$wpdb->update(
				$table_name,
				array(
					'cfm_option' => $option,
					'cfm_value'  => $value,
					'show_id'    => $show_id,
				),
				array(
					'cfm_option' => $option,
					'show_id'    => $show_id,
				)
			);
		} else {

			$wpdb->insert(
				$table_name,
				array(
					'cfm_option' => $option,
					'cfm_value'  => $value,
					'show_id'    => $show_id,
				)
			);
		}
	}
endif;

if ( ! function_exists( 'cfm_get_show_info' ) ) :
	/**
	 * Get show information
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 * @param string $option  The option.
	 *
	 * @return information
	 */
	function cfm_get_show_info( $show_id, $option ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT cfm_value FROM $table_name WHERE show_id = %s AND cfm_option = %s", $show_id, $option ) );

		return ! empty( $row ) ? $row->cfm_value : '';
	}
endif;

if ( ! function_exists( 'cfm_remove_show_info' ) ) :
	/**
	 * Remove show information
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 *
	 * @return info deleted
	 */
	function cfm_remove_show_info( $show_id ) {

		global $wpdb;
		$table_name = $wpdb->prefix . 'cfm_shows';

		$row = $wpdb->get_row( $wpdb->prepare( "DELETE FROM $table_name WHERE show_id = %s", $show_id ) );

		return ! empty( $row ) ? $row->cfm_value : '';
	}
endif;

if ( ! function_exists( 'cfm_upload_file' ) ) :
	/**
	 * Upload file to Captivate
	 *
	 * @since 1.0
	 * @param string $file_path  The file path.
	 * @param string $show_id  The show ID.
	 *
	 * @return artwork url
	 */
	function cfm_upload_file( $file_path, $show_id ) {

		$boundary = hash( 'sha256', uniqid( '', true ) );

		$payload = '';

		$file_contents = false;

		$file_contents = cfm_image_get_contents( $file_path );

		if ( function_exists( 'finfo' ) ) {
			$file_info = new finfo( FILEINFO_MIME_TYPE );
			$mime_type = $file_info->buffer( $file_contents );
		}
		else {
			$file_info = getimagesize( $file_path );
			$mime_type = $file_info['mime'];
		}

		$base_name = basename( $file_path );

		if ( false !== $file_contents ) {

			// Upload the file.
			if ( $file_path ) {
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="file"; filename="' . $base_name . '"' . "\r\n";
				$payload .= 'Content-Type: ' . $mime_type . "\r\n";
				$payload .= "\r\n";
				$payload .= $file_contents;
				$payload .= "\r\n";
			}

			$payload .= '--' . $boundary . '--';

			$request = wp_remote_post( CFMH_API_URL . '/shows/' . $show_id . '/artwork',
				array(
					'timeout' => 500,
					'body'    => $payload,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
						'content-type'  => 'multipart/form-data; boundary=' . $boundary,
					),
				)
			);

			$body = json_decode( $request['body'] );

			// Returns the url of the uploaded file.
			return ! empty( $body->artwork ) ? $body->artwork->artwork_url : '';

		}

	}
endif;

if ( ! function_exists( 'cfm_clear_tmp_files' ) ) :
	/**
	 * Clear the temp files from file upload.
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	function cfm_clear_tmp_files() {
		$files = glob( CFMH . 'tmp/*' );

		foreach ( $files as $file ) {

			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
	}
endif;

if ( ! function_exists( 'cfm_remove_show' ) ) :
	/**
	 * Hopefully we don't need this one, remove show.
	 *
	 * @since 1.0
	 * @param string $show_id  The show id.
	 *
	 * @return void
	 */
	function cfm_remove_show( $show_id ) {

		cfm_remove_show_info( $show_id );

		// get WP episodes.
		$get_episodes = array(
			'post_type'      => 'captivate_podcast',
			'posts_per_page' => -1,
			'order'          => 'DESC',
			'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
			'meta_query'     => array(
				array(
					'key'     => 'cfm_show_id',
					'value'   => $show_id,
					'compare' => '=',
				),
			),
		);

		$episodes = new WP_Query( $get_episodes );

		$episodes_ids = array();

		if ( $episodes->have_posts() ) :

			while ( $episodes->have_posts() ) :
				$episodes->the_post();
				wp_delete_post( get_the_ID(), false );
			endwhile;

		endif;

	}
endif;

if ( ! function_exists( 'cfm_sync_shows' ) ) :
	/**
	 * Sync up Captivate shows to Captivate Sync. Get it.
	 *
	 * @since 1.0
	 * @param string  $show_id  The show ID.
	 * @param boolean $sync_key  The sync key.
	 *
	 * @return boolean
	 */
	function cfm_sync_shows( $show_id, $sync_key = false ) {

		$get_show = wp_remote_get(
			CFMH_API_URL . '/shows/' . $show_id,
			array(
				'timeout' => 500,
				'headers' => array(
					'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
				),
			)
		);

		// Debugging.
		if ( cfm_is_debugging_on() ) {
			$log_date = date( 'Y-m-d H:i:s', time() );
			$txt = '**SYNC GET CAPTIVATE SHOW - ' . $log_date . '** ' . PHP_EOL . print_r( $get_show, true ) . '**END SYNC GET CAPTIVATE SHOW**';
			$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
		}

		$show = ! is_wp_error( $get_show ) ? json_decode( $get_show['body'] )->show : array();

		if ( ! empty( $show ) ) {

			// update title.
			if ( isset( $show->title ) ) {
				cfm_update_show_info( $show->id, 'title', sanitize_text_field( $show->title ) );
			}

			cfm_update_show_info( $show->id, 'last_synchronised', current_time( 'mysql' ) );

			if ( $sync_key ) {
				cfm_update_show_info( $show->id, 'sync_key', $sync_key );
			}

			// update artwork.
			if ( isset( $show->artwork ) ) {
				cfm_update_show_info( $show->id, 'artwork', sanitize_text_field( $show->artwork ) );
			}

			// update timezone.
			if ( isset( $show->time_zone ) ) {
				cfm_update_show_info( $show->id, 'time_zone', sanitize_text_field( $show->time_zone ) );
			}

			// update default_time.
			if ( isset( $show->default_time ) ) {
				cfm_update_show_info( $show->id, 'default_time', sanitize_text_field( $show->default_time ) );
			}

			// update feed url.
			$get_feed = wp_remote_get(
				CFMH_API_URL . '/shows/' . $show->id . '/feed',
				array(
					'timeout' => 500,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
					),
				)
			);

			// update prefixes.
			if ( ! empty($show->prefixes) && '[]' != $show->prefixes ) {
				cfm_update_show_info( $show->id, 'prefixes', sanitize_text_field( $show->prefixes ) );
			}

			// Debugging.
			if ( cfm_is_debugging_on() ) {
				$log_date = date( 'Y-m-d H:i:s', time() );
				$txt = '**SYNC GET CAPTIVATE FEED URL - ' . $log_date . '** ' . PHP_EOL . print_r( $get_feed, true ) . '**END SYNC GET CAPTIVATE FEED URL**';
				$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
			}

			if ( ! is_wp_error( $get_feed ) && 'Unauthorized' !== $get_feed['body'] && is_array( $get_feed ) ) {
					$feed_url = json_decode( $get_feed['body'] )->feed;

					cfm_update_show_info( $show->id, 'feed_url', $feed_url );
			}

			// get captivate episodes.
			$get_captivate_episodes = wp_remote_get(
				CFMH_API_URL . '/shows/' . $show->id . '/episodes',
				array(
					'timeout' => 500,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
					),
				)
			);

			// Debugging.
			if ( cfm_is_debugging_on() ) {
				$log_date = date( 'Y-m-d H:i:s', time() );
				$txt = '**SYNC GET CAPTIVATE EPISODES - ' . $log_date . '** ' . PHP_EOL . print_r( $get_captivate_episodes, true ) . '**END SYNC GET CAPTIVATE EPISODES**';
				$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
			}

			if ( ! is_wp_error( $get_captivate_episodes ) && 'Unauthorized' != $get_captivate_episodes['body'] && is_array( $get_captivate_episodes ) ) {

				$captivate_episodes = json_decode( $get_captivate_episodes['body'] )->episodes;

				if ( ! empty( $captivate_episodes ) ) {

					$captivate_episodes_data = array();

					foreach ( $captivate_episodes as $captivate_episode ) {
						$captivate_episodes_data[ $captivate_episode->id ? $captivate_episode->id : $capitvate_episode->episodes_id ] = array(
							'id'                 => $captivate_episode->id ? $captivate_episode->id : $capitvate_episode->episodes_id,
							'shows_id'           => $captivate_episode->shows_id,
							'media_id'           => $captivate_episode->media_id,
							'title'              => $captivate_episode->title,
							'itunes_title'       => $captivate_episode->itunes_title,
							'published_date'     => $captivate_episode->published_date,
							'status'             => $captivate_episode->status,
							'episode_art'        => $captivate_episode->episode_art,
							'shownotes'          => $captivate_episode->shownotes,
							'summary'            => $captivate_episode->summary,
							'episode_type'       => $captivate_episode->episode_type,
							'episode_season'     => $captivate_episode->episode_season,
							'episode_number'     => $captivate_episode->episode_number,
							'itunes_subtitle'    => $captivate_episode->itunes_subtitle,
							'author'             => $captivate_episode->author,
							'link'               => $captivate_episode->link,
							'explicit'           => $captivate_episode->explicit,
							'itunes_block'       => $captivate_episode->itunes_block,
							'google_block'       => $captivate_episode->google_block,
							'google_description' => $captivate_episode->google_description,
							'donation_link'      => $captivate_episode->donation_link,
							'donation_text'      => $captivate_episode->donation_text,
							'website_title'      => $captivate_episode->website_title,
							'media_url'          => $captivate_episode->media_url,
							'slug'				 => $captivate_episode->slug,
							'seo_title'			 => $captivate_episode->seo_title,
							'seo_description'    => $captivate_episode->seo_description,
							'episode_private'    => $captivate_episode->episode_private,
							'transcription_html'    => $captivate_episode->transcription_html,
							'transcription_file'    => $captivate_episode->transcription_file,
							'transcription_json'    => $captivate_episode->transcription_json,
							'transcription_text'    => $captivate_episode->transcription_text
						);
					}

					// get WP episodes.
					$get_episodes = array(
						'post_type'      => 'captivate_podcast',
						'posts_per_page' => -1,
						'order'          => 'DESC',
						'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
						'meta_query'     => array(
							array(
								'key'     => 'cfm_show_id',
								'value'   => $show->id,
								'compare' => '=',
							),
						),
					);

					$episodes = new WP_Query( $get_episodes );

					$episodes_ids = array();

					if ( $episodes->have_posts() ) :

						while ( $episodes->have_posts() ) :
							$episodes->the_post();
							$pid = get_the_ID();

							// store WP data to array (for comparison).
							$cfm_episode_id                  = get_post_meta( $pid, 'cfm_episode_id', true );
							$episodes_ids[ $cfm_episode_id ] = get_the_title();

							// update WP episodes.
							if ( array_key_exists( $cfm_episode_id, $captivate_episodes_data ) ) {

								// title.
								$website_title = $captivate_episodes_data[ $cfm_episode_id ]['website_title'];
								$title         = ! empty( $website_title ) ? $website_title : $captivate_episodes_data[ $cfm_episode_id ]['title'];

								// show notes.
								$shownotes = $captivate_episodes_data[ $cfm_episode_id ]['shownotes'];

								// published_date.
								$published_date = $captivate_episodes_data[ $cfm_episode_id ]['published_date'];
								$published_date = date( 'Y-m-d H:i:s', strtotime( $published_date ) );

								$update_post_data = array(
									'ID'           	=> $pid,
									'post_title'   	=> $title,
									'post_content' 	=> $shownotes,
									'post_date' 	=> $published_date,
									'post_date_gmt' => get_gmt_from_date( $published_date, 'Y-m-d H:i:s' ),
									'edit_date' 	=> true,
								);

								if ( 'Published' === $captivate_episodes_data[ $cfm_episode_id ]['status'] ) {
									$update_post_data['post_status'] = 'publish';
								} elseif ( 'Scheduled' === $captivate_episodes_data[ $cfm_episode_id ]['status'] ) {
									$update_post_data['post_status'] = 'future';
								} else {
									$update_post_data['post_status'] = 'draft';
								}

								if ( 1 === $captivate_episodes_data[ $cfm_episode_id ]['episode_private'] ) {
									$update_post_data['post_status'] = 'draft';
								}

								// slug.
								if($captivate_episodes_data[ $cfm_episode_id ]['slug'] && $captivate_episodes_data[ $cfm_episode_id ]['slug'] !== null && $captivate_episodes_data[ $cfm_episode_id ]['slug'] !== '0') {
									$update_post_data['post_name'] = $captivate_episodes_data[ $cfm_episode_id ]['slug'];
								}

								// Update the post into the database.
								wp_update_post( $update_post_data );

								// media_id.
								$media_id = $captivate_episodes_data[ $cfm_episode_id ]['media_id'];
								if ( get_post_meta( $pid, 'cfm_episode_media_id', true ) !== $media_id ) {
									update_post_meta( $pid, 'cfm_episode_media_id', $media_id );
								}

								// media_url.
								$media_url = $captivate_episodes_data[ $cfm_episode_id ]['media_url'];

								if ( get_post_meta( $pid, 'cfm_episode_media_url', true ) !== $media_url ) {
									update_post_meta( $pid, 'cfm_episode_media_url', $media_url );
								}

								// episode_art.
								$episode_art = $captivate_episodes_data[ $cfm_episode_id ]['episode_art'];
								if ( get_post_meta( $pid, 'cfm_episode_artwork', true ) !== $episode_art ) {
									update_post_meta( $pid, 'cfm_episode_artwork', $episode_art );
									delete_post_meta( $pid, 'cfm_episode_artwork_id' );
									delete_post_meta( $pid, 'cfm_episode_artwork_width' );
									delete_post_meta( $pid, 'cfm_episode_artwork_height' );
									delete_post_meta( $pid, 'cfm_episode_artwork_type' );
								}

								// itunes_title.
								$itunes_title = $captivate_episodes_data[ $cfm_episode_id ]['itunes_title'];
								$itunes_title = ! empty( $itunes_title ) ? $itunes_title : $captivate_episodes_data[ $cfm_episode_id ]['title'];
								if ( get_post_meta( $pid, 'cfm_episode_itunes_title', true ) !== $itunes_title ) {
									update_post_meta( $pid, 'cfm_episode_itunes_title', $itunes_title );
								}

								// itunes_subtitle.
								$itunes_subtitle = $captivate_episodes_data[ $cfm_episode_id ]['itunes_subtitle'];
								if ( get_post_meta( $pid, 'cfm_episode_itunes_subtitle', true ) !== $itunes_subtitle ) {
									update_post_meta( $pid, 'cfm_episode_itunes_subtitle', $itunes_subtitle );
								}

								// episode_season.
								$episode_season = $captivate_episodes_data[ $cfm_episode_id ]['episode_season'];
								if ( get_post_meta( $pid, 'cfm_episode_itunes_season', true ) !== $episode_season ) {
									update_post_meta( $pid, 'cfm_episode_itunes_season', $episode_season );
								}

								// episode_number.
								$episode_number = $captivate_episodes_data[ $cfm_episode_id ]['episode_number'];
								if ( get_post_meta( $pid, 'cfm_episode_itunes_number', true ) !== $episode_number ) {
									update_post_meta( $pid, 'cfm_episode_itunes_number', $episode_number );
								}

								// episode_type.
								$episode_type = $captivate_episodes_data[ $cfm_episode_id ]['episode_type'];
								if ( get_post_meta( $pid, 'cfm_episode_itunes_type', true ) !== $episode_type ) {
									update_post_meta( $pid, 'cfm_episode_itunes_type', $episode_type );
								}

								// explicit.
								$explicit = $captivate_episodes_data[ $cfm_episode_id ]['explicit'];
								if ( get_post_meta( $pid, 'cfm_episode_itunes_explicit', true ) !== $explicit ) {
									update_post_meta( $pid, 'cfm_episode_itunes_explicit', $explicit );
								}

								// donation_link.
								$donation_link = $captivate_episodes_data[ $cfm_episode_id ]['donation_link'];
								if ( get_post_meta( $pid, 'cfm_episode_donation_link', true ) !== $donation_link ) {
									update_post_meta( $pid, 'cfm_episode_donation_link', $donation_link );
								}

								// donation_text.
								$donation_text = $captivate_episodes_data[ $cfm_episode_id ]['donation_text'];
								if ( get_post_meta( $pid, 'cfm_episode_donation_label', true ) !== $donation_text ) {
									update_post_meta( $pid, 'cfm_episode_donation_label', $donation_text );
								}

								// seo_title.
								$seo_title = $captivate_episodes_data[ $cfm_episode_id ]['seo_title'];
								if ( get_post_meta( $pid, 'cfm_episode_seo_title', true ) !== $seo_title ) {
									update_post_meta( $pid, 'cfm_episode_seo_title', $seo_title );
								}

								// seo_description.
								$seo_description = $captivate_episodes_data[ $cfm_episode_id ]['seo_description'];
								if ( get_post_meta( $pid, 'cfm_episode_seo_description', true ) !== $seo_description ) {
									update_post_meta( $pid, 'cfm_episode_seo_description', $seo_description );
								}

								// transcriptions.
								$transcription_uploaded = ( null != $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'] && '' != $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'] ) ? 'file' : 'text';
								$transcriptions = array(
									'transcription_uploaded' => $transcription_uploaded,
									'transcription_html' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_html'],
									'transcription_file' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'],
									'transcription_json' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_json'],
									'transcription_text' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_text'],
								);
								update_post_meta( $pid, 'cfm_episode_transcript', $transcriptions );
							}

						endwhile;

					endif;

					// delete from WP.
					$to_delete = array_diff_key( $episodes_ids, $captivate_episodes_data );

					if ( ! empty( $to_delete ) ) {

						foreach ( $to_delete as $delete_id => $episode_title ) {

							$get_episode = array(
								'post_type'      => 'captivate_podcast',
								'posts_per_page' => 1,
								'order'          => 'DESC',
								'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
								'meta_query'     => array(
									array(
										'key'     => 'cfm_episode_id',
										'value'   => $delete_id,
										'compare' => '=',
									),
									array(
										'key'     => 'cfm_migrated_stats',
										'compare' => 'NOT EXISTS',
									),
								),
							);

							$episode = new WP_Query( $get_episode );

							if ( $episode->have_posts() ) :

								while ( $episode->have_posts() ) :
									$episode->the_post();

									wp_trash_post( get_the_ID() );

								endwhile;

							endif;

						}
					}

					// insert to WP.
					$to_insert = array_diff_key( $captivate_episodes_data, $episodes_ids );


					if ( ! empty( $to_insert ) ) {

						foreach ( $to_insert as $result ) {

							$post_title   = ! empty( $result['website_title'] ) ? $result['website_title'] : $result['title'];
							$itunes_title = ! empty( $result['itunes_title'] ) ? $result['itunes_title'] : $result['title'];

							$published_date = date( 'Y-m-d H:i:s', strtotime( $result['published_date'] ) );

							$post_data = array(
								'post_title'   => $post_title,
								'post_content' => $result['shownotes'],
								'post_author'  => cfm_get_show_author( $show_id ),
								'post_type'    => 'captivate_podcast',
								'post_date' 	=> $published_date,
								'post_date_gmt' => get_gmt_from_date( $published_date, 'Y-m-d H:i:s' ),
							);

							if ( $result['slug'] ) {
								$post_data['post_name'] = $result['slug'];
							}

							if ( 'Published' === $result['status'] ) {
								$post_data['post_status'] = 'publish';
							} elseif ( 'Scheduled' === $result['status'] ) {
								$post_data['post_status'] = 'future';
							} else {
								$post_data['post_status'] = 'draft';
							}

							if ( 1 === $result['episode_private'] ) {
								$post_data['post_status'] = 'draft';
							}

							$inserted_pid = wp_insert_post( $post_data );

							update_post_meta( $inserted_pid, 'cfm_show_id', $result['shows_id'] );
							update_post_meta( $inserted_pid, 'cfm_episode_id', $result['id']);
							update_post_meta( $inserted_pid, 'cfm_episode_media_id', $result['media_id'] );
							update_post_meta( $inserted_pid, 'cfm_episode_media_url', $result['media_url'] );
							update_post_meta( $inserted_pid, 'cfm_episode_artwork', $result['episode_art'] );
							update_post_meta( $inserted_pid, 'cfm_episode_itunes_title', $itunes_title );
							update_post_meta( $inserted_pid, 'cfm_episode_itunes_subtitle', $result['itunes_subtitle'] );
							update_post_meta( $inserted_pid, 'cfm_episode_itunes_season', $result['episode_season'] );
							update_post_meta( $inserted_pid, 'cfm_episode_itunes_number', $result['episode_number'] );
							update_post_meta( $inserted_pid, 'cfm_episode_itunes_type', $result['episode_type'] );
							update_post_meta( $inserted_pid, 'cfm_episode_itunes_explicit', $result['explicit'] );
							update_post_meta( $inserted_pid, 'cfm_episode_donation_link', $result['donation_link'] );
							update_post_meta( $inserted_pid, 'cfm_episode_donation_label', $result['donation_text'] );
							update_post_meta( $inserted_pid, 'cfm_episode_seo_title', $result['seo_title'] );
							update_post_meta( $inserted_pid, 'cfm_episode_seo_description', $result['seo_description'] );

							// transcriptions.
							$transcription_uploaded = ( null != $result['transcription_file'] && '' != $result['transcription_file'] ) ? 'file' : 'text';
							$transcriptions = array(
								'transcription_uploaded' => $transcription_uploaded,
								'transcription_html' 	 => $result['transcription_html'],
								'transcription_file' 	 => $result['transcription_file'],
								'transcription_json' 	 => $result['transcription_json'],
								'transcription_text' 	 => $result['transcription_text'],
							);
							update_post_meta( $inserted_pid, 'cfm_episode_transcript', $transcriptions );
						}
					}

				}

			}

			return true;

		}
	}
endif;

if ( ! function_exists( 'cfm_sync_wp_episode' ) ) :
	/**
	 * Sync up Captivate episode to Captivate Sync. Get it.
	 *
	 * @since 1.0
	 * @param string  $episode_id  The episode ID from Captivate.
	 * @param boolean $sync_key  The sync key.
	 *
	 * @return boolean
	 */
function cfm_sync_wp_episode( $episode_id, $syncKey = false ) {

	$get_episode = wp_remote_get( CFMH_API_URL . '/episodes/' . $episode_id, array(
		'timeout' => 500,
		'headers' => array(
			'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
		),
	));

	// Debugging.
	if ( cfm_is_debugging_on() ) {
		$log_date = date( 'Y-m-d H:i:s', time() );
		$txt = '**SYNC EPISODE - ' . $log_date . '** ' . PHP_EOL . print_r( $get_episode, true ) . '**END SYNC EPISODE**';
		$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
	}

	$episode = ! is_wp_error( $get_episode ) ? json_decode( $get_episode['body'] )->episode : array();

	if ( ! empty ( $episode ) ) {

		$captivate_episode_data = array(
			'id' 					=> $episode_id,
			'shows_id' 				=> $episode->shows_id,
			'media_id' 				=> $episode->media_id,
			'title' 				=> $episode->title,
			'itunes_title' 			=> $episode->itunes_title,
			'published_date' 		=> $episode->published_date,
			'status' 				=> $episode->status,
			'episode_art' 			=> $episode->episode_art,
			'shownotes' 			=> $episode->shownotes,
			'summary' 				=> $episode->summary,
			'episode_type' 			=> $episode->episode_type,
			'episode_season' 		=> $episode->episode_season,
			'episode_number' 		=> $episode->episode_number,
			'itunes_subtitle' 		=> $episode->itunes_subtitle,
			'author' 				=> $episode->author,
			'link' 					=> $episode->link,
			'explicit' 				=> $episode->explicit,
			'itunes_block' 			=> $episode->itunes_block,
			'google_block' 			=> $episode->google_block,
			'google_description' 	=> $episode->google_description,
			'donation_link' 		=> $episode->donation_link,
			'donation_text' 		=> $episode->donation_text,
			'website_title' 		=> $episode->website_title,
			'media_url' 			=> $episode->media_url,
			'slug'				 	=> $episode->slug,
			'seo_title'			 	=> $episode->seo_title,
			'seo_description'    	=> $episode->seo_description,
			'episode_private'    	=> $episode->episode_private,
			'transcription_html'    => $episode->transcription_html,
			'transcription_file'    => $episode->transcription_file,
			'transcription_json'    => $episode->transcription_json,
			'transcription_text'    => $episode->transcription_text
		);

		// get WP episode
		$get_episode = array(
			'post_type'  		=> 'captivate_podcast',
            'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
			'posts_per_page'  	=> 1,
			'meta_query' 		=> array(
				array(
					'key'     	=> 'cfm_episode_id',
					'value'   	=> $episode_id,
					'compare' 	=> '=',
				),
			),
		);

		$episode = new WP_Query( $get_episode );

		if ( $episode->have_posts() ) :

            while ( $episode->have_posts() ) : $episode->the_post();

				$pid = get_the_ID();

				// title.
				$website_title = $captivate_episode_data['website_title'];
				$title         = ! empty( $website_title ) ? $website_title : $captivate_episode_data['title'];

				// show notes.
				$shownotes = $captivate_episode_data['shownotes'];

				// published_date.
				$published_date = $captivate_episode_data['published_date'];

				$update_post_data = array(
					'ID'           	=> $pid,
					'post_title'   	=> $title,
					'post_content' 	=> $shownotes,
					'post_date' 	=> date( 'Y-m-d H:i:s', strtotime( $published_date ) ),
					'post_date_gmt' => get_gmt_from_date( $published_date, 'Y-m-d H:i:s' ),
					'edit_date' 	=> true,
				);

				if ( 'Published' === $captivate_episode_data['status'] ) {
					$update_post_data['post_status'] = 'publish';
				} elseif ( 'Scheduled' === $captivate_episode_data['status'] ) {
					$update_post_data['post_status'] = 'future';
				} else {
					$update_post_data['post_status'] = 'draft';
				}

				if($captivate_episode_data['slug'] && $captivate_episode_data['slug'] !== null && $captivate_episode_data['slug'] !== '0') {
					$update_post_data['post_name'] = $captivate_episode_data['slug'];
				}

				if ( 1 === $captivate_episode_data['episode_private'] ) {
					$update_post_data['post_status'] = 'draft';
				}

				// Update the post into the database.
				wp_update_post( $update_post_data );

				// media_id.
				$media_id = $captivate_episode_data['media_id'];
				if ( get_post_meta( $pid, 'cfm_episode_media_id', true ) !== $media_id ) {
					update_post_meta( $pid, 'cfm_episode_media_id', $media_id );
				}

				// media_url.
				$media_url = $captivate_episode_data['media_url'];

				if ( get_post_meta( $pid, 'cfm_episode_media_url', true ) !== $media_url ) {
					update_post_meta( $pid, 'cfm_episode_media_url', $media_url );
				}

				// episode_art.
				$episode_art = $captivate_episode_data['episode_art'];
				if ( get_post_meta( $pid, 'cfm_episode_artwork', true ) !== $episode_art ) {
					update_post_meta( $pid, 'cfm_episode_artwork', $episode_art );
					delete_post_meta( $pid, 'cfm_episode_artwork_id' );
					delete_post_meta( $pid, 'cfm_episode_artwork_width' );
					delete_post_meta( $pid, 'cfm_episode_artwork_height' );
					delete_post_meta( $pid, 'cfm_episode_artwork_type' );
				}

				// itunes_title.
				$itunes_title = $captivate_episode_data['itunes_title'];
				$itunes_title = ! empty( $itunes_title ) ? $itunes_title : $captivate_episode_data['title'];
				if ( get_post_meta( $pid, 'cfm_episode_itunes_title', true ) !== $itunes_title ) {
					update_post_meta( $pid, 'cfm_episode_itunes_title', $itunes_title );
				}

				// itunes_subtitle.
				$itunes_subtitle = $captivate_episode_data['itunes_subtitle'];
				if ( get_post_meta( $pid, 'cfm_episode_itunes_subtitle', true ) !== $itunes_subtitle ) {
					update_post_meta( $pid, 'cfm_episode_itunes_subtitle', $itunes_subtitle );
				}

				// episode_season.
				$episode_season = $captivate_episode_data['episode_season'];
				if ( get_post_meta( $pid, 'cfm_episode_itunes_season', true ) !== $episode_season ) {
					update_post_meta( $pid, 'cfm_episode_itunes_season', $episode_season );
				}

				// episode_number.
				$episode_number = $captivate_episode_data['episode_number'];
				if ( get_post_meta( $pid, 'cfm_episode_itunes_number', true ) !== $episode_number ) {
					update_post_meta( $pid, 'cfm_episode_itunes_number', $episode_number );
				}

				// episode_type.
				$episode_type = $captivate_episode_data['episode_type'];
				if ( get_post_meta( $pid, 'cfm_episode_itunes_type', true ) !== $episode_type ) {
					update_post_meta( $pid, 'cfm_episode_itunes_type', $episode_type );
				}

				// explicit.
				$explicit = $captivate_episode_data['explicit'];
				if ( get_post_meta( $pid, 'cfm_episode_itunes_explicit', true ) !== $explicit ) {
					update_post_meta( $pid, 'cfm_episode_itunes_explicit', $explicit );
				}

				// donation_link.
				$donation_link = $captivate_episode_data['donation_link'];
				if ( get_post_meta( $pid, 'cfm_episode_donation_link', true ) !== $donation_link ) {
					update_post_meta( $pid, 'cfm_episode_donation_link', $donation_link );
				}

				// donation_text.
				$donation_text = $captivate_episode_data['donation_text'];
				if ( get_post_meta( $pid, 'cfm_episode_donation_label', true ) !== $donation_text ) {
					update_post_meta( $pid, 'cfm_episode_donation_label', $donation_text );
				}

				// seo_title.
				$seo_title = $captivate_episode_data['seo_title'];
				if ( get_post_meta( $pid, 'cfm_episode_seo_title', true ) !== $seo_title ) {
					update_post_meta( $pid, 'cfm_episode_seo_title', $seo_title );
				}

				// seo_description.
				$seo_description = $captivate_episode_data['seo_description'];
				if ( get_post_meta( $pid, 'cfm_episode_seo_description', true ) !== $seo_description ) {
					update_post_meta( $pid, 'cfm_episode_seo_description', $seo_description );
				}

				// transcriptions.
				$transcription_uploaded = ( null != $captivate_episode_data['transcription_file'] && '' != $captivate_episode_data['transcription_file'] ) ? 'file' : 'text';
				$transcriptions = array(
					'transcription_uploaded' => $transcription_uploaded,
					'transcription_html' 	 => $captivate_episode_data['transcription_html'],
					'transcription_file' 	 => $captivate_episode_data['transcription_file'],
					'transcription_json' 	 => $captivate_episode_data['transcription_json'],
					'transcription_text' 	 => $captivate_episode_data['transcription_text'],
				);
				update_post_meta( $pid, 'cfm_episode_transcript', $transcriptions );

			endwhile;

		endif;

		return true;

	}
}
endif;

if ( ! function_exists( 'cfm_get_new_episodes' ) ) :
	/**
	 * Get new episodes from Captivate
	 *
	 * @since 1.0
	 * @param string  $show_id  The show ID.
	 * @param boolean $sync_key  The sync key.
	 *
	 * @return boolean
	 */
	function cfm_get_new_episodes( $show_id ) {

		$get_show = wp_remote_get(
			CFMH_API_URL . '/shows/' . $show_id,
			array(
				'timeout' => 500,
				'headers' => array(
					'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
				),
			)
		);

		// Debugging.
		if ( cfm_is_debugging_on() ) {
			$log_date = date( 'Y-m-d H:i:s', time() );
			$txt = '**NEW EPISODES GET CAPTIVATE SHOW - ' . $log_date . '** ' . PHP_EOL . print_r( $get_show, true ) . '**END NEW EPISODES GET CAPTIVATE SHOW**';
			$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
		}

		$show = ! is_wp_error( $get_show ) ? json_decode( $get_show['body'] )->show : array();

		if ( ! empty( $show ) ) {

			cfm_update_show_info( $show->id, 'last_auto_sync', current_time( 'mysql' ) );

			// get captivate episodes.
			$get_captivate_episodes = wp_remote_get(
				CFMH_API_URL . '/shows/' . $show->id . '/episodes',
				array(
					'timeout' => 500,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
					),
				)
			);

			// Debugging.
			if ( cfm_is_debugging_on() ) {
				$log_date = date( 'Y-m-d H:i:s', time() );
				$txt = '**NEW EPISODES GET CAPTIVATE EPISODES - ' . $log_date . '** ' . PHP_EOL . print_r( $get_captivate_episodes, true ) . '**END NEW EPISODES GET CAPTIVATE EPISODES**';
				$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
			}

			if ( ! is_wp_error( $get_captivate_episodes ) && 'Unauthorized' != $get_captivate_episodes['body'] && is_array( $get_captivate_episodes ) ) {

				$captivate_episodes = json_decode( $get_captivate_episodes['body'] )->episodes;

				if ( ! empty( $captivate_episodes ) ) {

					$captivate_episodes_data = array();

					foreach ( $captivate_episodes as $captivate_episode ) {
						$captivate_episodes_data[ $captivate_episode->id ? $captivate_episode->id : $capitvate_episode->episodes_id ] = array(
							'id'                 => $captivate_episode->id ? $captivate_episode->id : $capitvate_episode->episodes_id,
							'shows_id'           => $captivate_episode->shows_id,
							'media_id'           => $captivate_episode->media_id,
							'title'              => $captivate_episode->title,
							'itunes_title'       => $captivate_episode->itunes_title,
							'published_date'     => $captivate_episode->published_date,
							'status'             => $captivate_episode->status,
							'episode_art'        => $captivate_episode->episode_art,
							'shownotes'          => $captivate_episode->shownotes,
							'summary'            => $captivate_episode->summary,
							'episode_type'       => $captivate_episode->episode_type,
							'episode_season'     => $captivate_episode->episode_season,
							'episode_number'     => $captivate_episode->episode_number,
							'itunes_subtitle'    => $captivate_episode->itunes_subtitle,
							'author'             => $captivate_episode->author,
							'link'               => $captivate_episode->link,
							'explicit'           => $captivate_episode->explicit,
							'itunes_block'       => $captivate_episode->itunes_block,
							'google_block'       => $captivate_episode->google_block,
							'google_description' => $captivate_episode->google_description,
							'donation_link'      => $captivate_episode->donation_link,
							'donation_text'      => $captivate_episode->donation_text,
							'website_title'      => $captivate_episode->website_title,
							'media_url'          => $captivate_episode->media_url,
							'slug'				 => $captivate_episode->slug,
							'seo_title'			 => $captivate_episode->seo_title,
							'seo_description'    => $captivate_episode->seo_description,
							'episode_private'    	=> $captivate_episode->episode_private,
							'transcription_html'    => $captivate_episode->transcription_html,
							'transcription_file'    => $captivate_episode->transcription_file,
							'transcription_json'    => $captivate_episode->transcription_json,
							'transcription_text'    => $captivate_episode->transcription_text
						);
					}

					// get WP episodes.
					$get_episodes = array(
						'post_type'      => 'captivate_podcast',
						'posts_per_page' => -1,
						'order'          => 'DESC',
						'post_status'    => array( 'publish', 'draft', 'future', 'private' ),
						'meta_query'     => array(
							array(
								'key'     => 'cfm_show_id',
								'value'   => $show->id,
								'compare' => '=',
							),
						),
					);

					$episodes = new WP_Query( $get_episodes );

					$episodes_ids = array();

					if ( $episodes->have_posts() ) :

						while ( $episodes->have_posts() ) :
							$episodes->the_post();
							$pid = get_the_ID();

							// store WP data to array (for comparison).
							$cfm_episode_id                  = get_post_meta( $pid, 'cfm_episode_id', true );
							$episodes_ids[ $cfm_episode_id ] = get_the_title();

							// update WP episodes.
							if ( array_key_exists( $cfm_episode_id, $captivate_episodes_data ) ) {

								// update episodes if status is different.
								if ( 'Published' === $captivate_episodes_data[ $cfm_episode_id ]['status'] ) {
									$captivate_status = 'publish';
								} elseif ( 'Scheduled' === $captivate_episodes_data[ $cfm_episode_id ]['status'] ) {
									$captivate_status = 'future';
								} else {
									$captivate_status = 'draft';
								}

								if ( $captivate_status != get_post_status() ) {

									// title.
									$website_title = $captivate_episodes_data[ $cfm_episode_id ]['website_title'];
									$title         = ! empty( $website_title ) ? $website_title : $captivate_episodes_data[ $cfm_episode_id ]['title'];

									// show notes.
									$shownotes = $captivate_episodes_data[ $cfm_episode_id ]['shownotes'];

									// published_date.
									$published_date = $captivate_episodes_data[ $cfm_episode_id ]['published_date'];
									$published_date = date( 'Y-m-d H:i:s', strtotime( $published_date ) );

									$update_post_data = array(
										'ID'           	=> $pid,
										'post_title'   	=> $title,
										'post_content' 	=> $shownotes,
										'post_date' 	=> $published_date,
										'post_date_gmt' => get_gmt_from_date( $published_date, 'Y-m-d H:i:s' ),
										'edit_date' 	=> true,
									);

									if ( 'Published' === $captivate_episodes_data[ $cfm_episode_id ]['status'] ) {
										$update_post_data['post_status'] = 'publish';
									} elseif ( 'Scheduled' === $captivate_episodes_data[ $cfm_episode_id ]['status'] ) {
										$update_post_data['post_status'] = 'future';
									} else {
										$update_post_data['post_status'] = 'draft';
									}

									if ( 1 === $captivate_episodes_data[ $cfm_episode_id ]['episode_private'] ) {
										$update_post_data['post_status'] = 'draft';
									}

									// slug.
									if($captivate_episodes_data[ $cfm_episode_id ]['slug'] && $captivate_episodes_data[ $cfm_episode_id ]['slug'] !== null && $captivate_episodes_data[ $cfm_episode_id ]['slug'] !== '0') {
										$update_post_data['post_name'] = $captivate_episodes_data[ $cfm_episode_id ]['slug'];
									}

									// Update the post into the database.
									wp_update_post( $update_post_data );

									// media_id.
									$media_id = $captivate_episodes_data[ $cfm_episode_id ]['media_id'];
									if ( get_post_meta( $pid, 'cfm_episode_media_id', true ) !== $media_id ) {
										update_post_meta( $pid, 'cfm_episode_media_id', $media_id );
									}

									// media_url.
									$media_url = $captivate_episodes_data[ $cfm_episode_id ]['media_url'];

									if ( get_post_meta( $pid, 'cfm_episode_media_url', true ) !== $media_url ) {
										update_post_meta( $pid, 'cfm_episode_media_url', $media_url );
									}

									// episode_art.
									$episode_art = $captivate_episodes_data[ $cfm_episode_id ]['episode_art'];
									if ( get_post_meta( $pid, 'cfm_episode_artwork', true ) !== $episode_art ) {
										update_post_meta( $pid, 'cfm_episode_artwork', $episode_art );
										delete_post_meta( $pid, 'cfm_episode_artwork_id' );
										delete_post_meta( $pid, 'cfm_episode_artwork_width' );
										delete_post_meta( $pid, 'cfm_episode_artwork_height' );
										delete_post_meta( $pid, 'cfm_episode_artwork_type' );
									}

									// itunes_title.
									$itunes_title = $captivate_episodes_data[ $cfm_episode_id ]['itunes_title'];
									$itunes_title = ! empty( $itunes_title ) ? $itunes_title : $captivate_episodes_data[ $cfm_episode_id ]['title'];
									if ( get_post_meta( $pid, 'cfm_episode_itunes_title', true ) !== $itunes_title ) {
										update_post_meta( $pid, 'cfm_episode_itunes_title', $itunes_title );
									}

									// itunes_subtitle.
									$itunes_subtitle = $captivate_episodes_data[ $cfm_episode_id ]['itunes_subtitle'];
									if ( get_post_meta( $pid, 'cfm_episode_itunes_subtitle', true ) !== $itunes_subtitle ) {
										update_post_meta( $pid, 'cfm_episode_itunes_subtitle', $itunes_subtitle );
									}

									// episode_season.
									$episode_season = $captivate_episodes_data[ $cfm_episode_id ]['episode_season'];
									if ( get_post_meta( $pid, 'cfm_episode_itunes_season', true ) !== $episode_season ) {
										update_post_meta( $pid, 'cfm_episode_itunes_season', $episode_season );
									}

									// episode_number.
									$episode_number = $captivate_episodes_data[ $cfm_episode_id ]['episode_number'];
									if ( get_post_meta( $pid, 'cfm_episode_itunes_number', true ) !== $episode_number ) {
										update_post_meta( $pid, 'cfm_episode_itunes_number', $episode_number );
									}

									// episode_type.
									$episode_type = $captivate_episodes_data[ $cfm_episode_id ]['episode_type'];
									if ( get_post_meta( $pid, 'cfm_episode_itunes_type', true ) !== $episode_type ) {
										update_post_meta( $pid, 'cfm_episode_itunes_type', $episode_type );
									}

									// explicit.
									$explicit = $captivate_episodes_data[ $cfm_episode_id ]['explicit'];
									if ( get_post_meta( $pid, 'cfm_episode_itunes_explicit', true ) !== $explicit ) {
										update_post_meta( $pid, 'cfm_episode_itunes_explicit', $explicit );
									}

									// donation_link.
									$donation_link = $captivate_episodes_data[ $cfm_episode_id ]['donation_link'];
									if ( get_post_meta( $pid, 'cfm_episode_donation_link', true ) !== $donation_link ) {
										update_post_meta( $pid, 'cfm_episode_donation_link', $donation_link );
									}

									// donation_text.
									$donation_text = $captivate_episodes_data[ $cfm_episode_id ]['donation_text'];
									if ( get_post_meta( $pid, 'cfm_episode_donation_label', true ) !== $donation_text ) {
										update_post_meta( $pid, 'cfm_episode_donation_label', $donation_text );
									}

									// seo_title.
									$seo_title = $captivate_episodes_data[ $cfm_episode_id ]['seo_title'];
									if ( get_post_meta( $pid, 'cfm_episode_seo_title', true ) !== $seo_title ) {
										update_post_meta( $pid, 'cfm_episode_seo_title', $seo_title );
									}

									// seo_description.
									$seo_description = $captivate_episodes_data[ $cfm_episode_id ]['seo_description'];
									if ( get_post_meta( $pid, 'cfm_episode_seo_description', true ) !== $seo_description ) {
										update_post_meta( $pid, 'cfm_episode_seo_description', $seo_description );
									}

									// transcriptions.
									$transcription_uploaded = ( null != $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'] && '' != $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'] ) ? 'file' : 'text';
									$transcriptions = array(
										'transcription_uploaded' => $transcription_uploaded,
										'transcription_html' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_html'],
										'transcription_file' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_file'],
										'transcription_json' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_json'],
										'transcription_text' 	 => $captivate_episodes_data[ $cfm_episode_id ]['transcription_text'],
									);
									update_post_meta( $pid, 'cfm_episode_transcript', $transcriptions );

								}
							}

						endwhile;

					endif;

					// insert to WP.
					$to_insert = array_diff_key( $captivate_episodes_data, $episodes_ids );

					if ( ! empty( $to_insert ) ) {

						foreach ( $to_insert as $result ) {

							// get published/scheduled episodes only.
							if ( 'Published' === $result['status'] || 'Scheduled' === $result['status'] ) {

								$post_title   = ! empty( $result['website_title'] ) ? $result['website_title'] : $result['title'];
								$itunes_title = ! empty( $result['itunes_title'] ) ? $result['itunes_title'] : $result['title'];

								$post_data = array(
									'post_title'   => $post_title,
									'post_content' => $result['shownotes'],
									'post_author'  => cfm_get_show_author( $show_id ),
									'post_type'    => 'captivate_podcast',
								);

								if ( $result['slug'] ) {
									$post_data['post_name'] = $result['slug'];
								}

								if ( 'Published' === $result['status'] ) {
									$post_data['post_status'] = 'publish';
								} elseif ( 'Scheduled' === $result['status'] ) {
									$post_data['post_status'] = 'future';
								} else {
									$post_data['post_status'] = 'draft';
								}

								$post_data['post_date']     = date( 'Y-m-d H:i:s', strtotime( $result['published_date'] ) );
								$post_data['post_date_gmt'] = get_gmt_from_date( $result['published_date'], 'Y-m-d H:i:s' );

								$inserted_pid = wp_insert_post( $post_data );

								update_post_meta( $inserted_pid, 'cfm_show_id', $result['shows_id'] );
								update_post_meta( $inserted_pid, 'cfm_episode_id', $result['id']);
								update_post_meta( $inserted_pid, 'cfm_episode_media_id', $result['media_id'] );
								update_post_meta( $inserted_pid, 'cfm_episode_media_url', $result['media_url'] );
								update_post_meta( $inserted_pid, 'cfm_episode_artwork', $result['episode_art'] );
								update_post_meta( $inserted_pid, 'cfm_episode_itunes_title', $itunes_title );
								update_post_meta( $inserted_pid, 'cfm_episode_itunes_subtitle', $result['itunes_subtitle'] );
								update_post_meta( $inserted_pid, 'cfm_episode_itunes_season', $result['episode_season'] );
								update_post_meta( $inserted_pid, 'cfm_episode_itunes_number', $result['episode_number'] );
								update_post_meta( $inserted_pid, 'cfm_episode_itunes_type', $result['episode_type'] );
								update_post_meta( $inserted_pid, 'cfm_episode_itunes_explicit', $result['explicit'] );
								update_post_meta( $inserted_pid, 'cfm_episode_donation_link', $result['donation_link'] );
								update_post_meta( $inserted_pid, 'cfm_episode_donation_label', $result['donation_text'] );
								update_post_meta( $inserted_pid, 'cfm_episode_seo_title', $result['seo_title'] );
								update_post_meta( $inserted_pid, 'cfm_episode_seo_description', $result['seo_description'] );

								// transcriptions.
								$transcription_uploaded = ( null != $result['transcription_file'] && '' != $result['transcription_file'] ) ? 'file' : 'text';
								$transcriptions = array(
									'transcription_uploaded' => $transcription_uploaded,
									'transcription_html' 	 => $result['transcription_html'],
									'transcription_file' 	 => $result['transcription_file'],
									'transcription_json' 	 => $result['transcription_json'],
									'transcription_text' 	 => $result['transcription_text'],
								);
								update_post_meta( $inserted_pid, 'cfm_episode_transcript', $transcriptions );

							}
						}
					}

				}

			}

			return true;

		}
	}
endif;

if ( ! function_exists( 'cfm_is_logged_in' ) ) :
	/**
	 * Is the user logged in?
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	function cfm_is_logged_in() {
		if ( get_option( 'cfm_authentication_id' ) && get_option( 'cfm_authentication_key' ) && get_transient( 'cfm_authentication_token' ) ) {
			return true;
		} else {
			return false;
		}
	}
endif;

if ( ! function_exists( 'cfm_is_debugging_on' ) ) :
	/**
	 * Is the debugging on?
	 *
	 * @since 1.0
	 *
	 * @return boolean
	 */
	function cfm_is_debugging_on() {
		return ( '1' == get_option( 'cfm_debugging' ) ) ? true : false;
	}
endif;

if ( ! function_exists( 'cfm_is_user_has_show' ) ) :
	/**
	 * Is the user show not empty and exists in cfm_shows?
	 *
	 * @since 1.3
	 *
	 * @return boolean
	 */
	function cfm_is_user_has_show() {

		$shows = cfm_get_shows();
		$user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true );

		$show_exists = array();
		if ( ! empty( $shows ) && ! empty( $user_shows ) ) {
			$show_exists = count(array_intersect_key($shows, $user_shows));
		}

		if ( empty( $show_exists ) ) {
			return false;
		}
		else {
			return true;
		}
	}
endif;

if ( ! function_exists( 'cfm_get_show_author' ) ) :
	/**
	 * Get the author show set in cfm_shows
	 *
	 * @since 1.1.4
	 *
	 * @return int $user_id
	 */
	function cfm_get_show_author( $show_id ) {

		$shows    = cfm_get_shows();
		$show_ids = array();

		if ( ! empty( $shows ) ) {
			foreach ( $shows as $show ) {
				$show_ids[ $show['id'] ] = $show['author'];
			}
		}

		$author = ( $show_id ) ? (int) $show_ids[ $show_id ] : 0;

		return ( $author != 0 ) ? $author : get_current_user_id();
	}
endif;

if ( ! function_exists( 'cfm_update_transcript' ) ) :
	/**
	 * Update transcript on Captivate
	 *
	 * @since 2.0
	 * @param string $transcript  The file path or textarea.
	 * @param string $episode_id  The episode ID.
	 * @param boolean $updated.
	 *
	 * @return array
	 */
	function cfm_update_transcript( $transcript, $episode_id ) {

		$payload = '';
		$boundary = hash( 'sha256', uniqid( '', true ) );
		$transcript_wp = array(
			'transcription_uploaded' => 'text',
			'transcription_html' 	 => null,
			'transcription_file' 	 => null,
			'transcription_json' 	 => null,
			'transcription_text' 	 => null,
		);

		if ( is_array( $transcript ) && ! empty( $transcript ) ) {

			$file_contents = false;
			$file_contents = file_get_contents( $transcript['tmp_name'] );
			$mime_type = $transcript['type'];
			$base_name = basename( $transcript['name'] );

			if ( false !== $file_contents ) {

				// Upload the file.
				if ( $transcript ) {
					$payload .= '--' . $boundary;
					$payload .= "\r\n";
					$payload .= 'Content-Disposition: form-data; name="file"; filename="' . $base_name . '"' . "\r\n";
					$payload .= 'Content-Type: ' . $mime_type . "\r\n";
					$payload .= "\r\n";
					$payload .= $file_contents;
					$payload .= "\r\n";
				}

				$payload .= '--' . $boundary . '--';

				$request = wp_remote_post( CFMH_API_URL . '/episodes/' . $episode_id . '/transcript',
					array(
						'timeout' => 500,
						'body'    => $payload,
						'headers' => array(
							'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
							'content-type'  => 'multipart/form-data; boundary=' . $boundary,
						),
					)
				);

				// Debugging.
				if ( cfm_is_debugging_on() ) {
					$log_date = date( 'Y-m-d H:i:s', time() );
					$txt = '**UPDATE TRANSCRIPT FILE - ' . $log_date . '**' . PHP_EOL . print_r( $request, true ) . '**END UPDATE TRANSCRIPT FILE**';
					$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
				}

				$body = json_decode( $request['body'] );

				if ( isset( $body->success ) ) {
					$transcript_wp['transcription_uploaded'] = 'file';
					$transcript_wp['transcription_html'] = $body->episode->transcription_html;
					$transcript_wp['transcription_file'] = $body->episode->transcription_file;
					$transcript_wp['transcription_json'] = $body->episode->transcription_json;
					$transcript_wp['transcription_text'] = $body->episode->transcription_text;
				}

			}
		}
		else {

			if ( $transcript ) {
				$payload .= '--' . $boundary;
				$payload .= "\r\n";
				$payload .= 'Content-Disposition: form-data; name="text"' . "\r\n";
				$payload .= "\r\n";
				$payload .= $transcript;
				$payload .= "\r\n";
			}

			$payload .= '--' . $boundary . '--';

			$request = wp_remote_post( CFMH_API_URL . '/episodes/' . $episode_id . '/transcript',
				array(
					'timeout' => 500,
					'body'    => $payload,
					'headers' => array(
						'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
						'content-type'  => 'multipart/form-data; boundary=' . $boundary,
					),
				)
			);

			// Debugging.
			if ( cfm_is_debugging_on() ) {
				$log_date = date( 'Y-m-d H:i:s', time() );
				$txt = '**UPDATE TRANSCRIPT TEXT - ' . $log_date . '**' . PHP_EOL . print_r( $request, true ) . '**END UPDATE TRANSCRIPT TEXT**';
				$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
			}

			$body = json_decode( $request['body'] );

			if ( isset( $body->success ) ) {
				$transcript_wp['transcription_uploaded'] = 'text';
				$transcript_wp['transcription_html'] = $body->episode->transcription_html;
				$transcript_wp['transcription_file'] = $body->episode->transcription_file;
				$transcript_wp['transcription_json'] = $body->episode->transcription_json;
				$transcript_wp['transcription_text'] = $body->episode->transcription_text;
			}

		}

		return $transcript_wp;

	}
endif;

if ( ! function_exists( 'cfm_image_get_contents' ) ) :
	/**
	 * file_get_contents replacement for image upload.
	 *
	 * @since 2.0.22
	 *
	 * @return string
	 */
	function cfm_image_get_contents( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_URL, $url );
		$data = curl_exec( $ch );
		curl_close( $ch );

		return $data;
	}
endif;

/**
 * Modify episodes permalink depending on index page
 *
 */
add_filter( 'post_type_link', function ( $post_link, $post, $leavename, $sample ) {
	if ( $post->post_type == 'captivate_podcast' ) {

		$cfm_show_id = get_post_meta( $post->ID, 'cfm_show_id', true );
		$cfm_show_page = cfm_get_show_page( $cfm_show_id, 'slug' );

		$post_link = get_bloginfo( 'url' ) . '/' . $cfm_show_page . '/' . $post->post_name;
		$post_link = user_trailingslashit( $post_link );
	}

	return $post_link;
}, 999, 4 );

/**
 * Add third-party analytics prefixes to the media url
 *
 */
function cfm_add_media_prefixes( $show_id, $media_url ) {

	$prefixes = cfm_get_show_info( $show_id, 'prefixes' );
	$prefixes = ! empty( $prefixes ) ? json_decode( $prefixes ) : [];
    $chain_of_prefixes = false;

    if ( count( $prefixes ) > 0 ) {
        $last_char_orig = substr($prefixes[0]->prefixUrl, -1 );
        if ( $last_char_orig != '/' ) {
            $chain_of_prefixes = $prefixes[0]->prefixUrl . '/';
        }
		else {
            $chain_of_prefixes = $prefixes[0]->prefixUrl;
        }
    }

    if ( count( $prefixes ) > 1 ) {
        foreach ( $prefixes as $index => $prefix ) {
            if ( $index != 0 ) {
                $prefix->prefixUrl = str_replace( 'https://', '', $prefix->prefixUrl );
                $prefix->prefixUrl = str_replace( 'http://', '', $prefix->prefixUrl );
                $last_char = substr( $prefix->prefixUrl, -1 );
                if ( $last_char != '/' ) {
                    $chain_of_prefixes = $chain_of_prefixes . $prefix->prefixUrl . '/';
                }
				else {
                    $chain_of_prefixes = $chain_of_prefixes . $prefix->prefixUrl;
                }
            }
        }
    }

    $result = $media_url;
    if ( $chain_of_prefixes ) {
        $result = str_replace( 'https://', $chain_of_prefixes, $result );
    }

    return $result;
}