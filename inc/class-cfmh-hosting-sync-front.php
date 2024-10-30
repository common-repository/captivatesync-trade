<?php
/**
 * User for front-end output/data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Sync_Front' ) ) :

	/**
	 * Hosting Sync Front class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Sync_Front {

		static $page_id = 0;

		/**
		 * Enqueueu assets
		 *
		 * @since 1.1
		 */
		public static function assets() {

			wp_enqueue_script( 'cfmsync-player-api', CFMH_URL . 'captivate-sync-assets/js/player-api-min.js', array(), CFMH_VERSION, true );

			if ( is_singular( 'captivate_podcast' ) ) {
				wp_enqueue_script( 'cfmsync-player-js', CFMH_URL . 'captivate-sync-assets/js/player-js-min.js', array( 'jquery' ), CFMH_VERSION, true );
				wp_enqueue_style( 'cfmsync-front-style', CFMH_URL . 'captivate-sync-assets/css/front-min.css' );
			}

			wp_enqueue_style( 'cfmsync-shortcode', CFMH_URL . 'captivate-sync-assets/css/shortcode-min.css', array(), CFMH_VERSION );
		}

		/**
		 * Index page
		 *
		 * @since 1.0
		 * @param string $query  Query to search.
		 * @return query_set
		 */
		public static function index_page( $query ) {

			if ( empty( self::$page_id ) ) {
				self::$page_id = $query->queried_object_id;
			}

			$shows       = cfm_get_shows();
			$index_pages = array();

			if ( ! empty( $shows ) ) {
				foreach ( $shows as $show ) {
					if ( '' != $show['index_page'] ) {
						$index_pages[ $show['index_page'] ] = $show['id'];
					}
				}
			}

			if ( array_key_exists( self::$page_id, $index_pages ) && $query->is_main_query() && ! is_admin() ) {

				$theme = wp_get_theme();
				$show_id = $index_pages[ self::$page_id ];

				if ( cfm_get_show_info( $show_id, 'display_episodes' ) != '0' ) {

					// target Divi theme.
					if ( 'Divi' == $theme->name || 'Divi' == $theme->parent_theme ) {
						add_filter( 'template_include', 'cfm_index_page_template', 999 );

						/**
						 * Index page for divi
						 *
						 * @since 1.2.3
						 * @param string $template  Template for index.
						 * @return new template
						 */
						function cfm_index_page_template( $template ) {

							$index_page_template = locate_template( array( 'captivate.php', 'archive.php', 'index.php' ) );

							if ( '' != $index_page_template ) {
								return $index_page_template;
							}

							return $template;

						}

						$query->is_post_type_archive = true;

					}
					else {
						$query->is_archive	= true;
					}

					$query->is_page     = false;
					$query->is_singular	= false;
					$query->set( 'post_type', 'captivate_podcast' );
					$query->set( 'meta_key', 'cfm_show_id' );
					$query->set( 'meta_value', $show_id );

					add_filter( 'pre_option_page_for_posts', array( 'CFMH_Hosting_Sync_Front', 'pre_option_page_for_posts_function' ) );
					add_filter( 'pre_option_show_on_front', array( 'CFMH_Hosting_Sync_Front', 'pre_option_show_on_front_function' ) );

					/**
					 * Index page title
					 *
					 * @since 1.1.3
					 * @param array $title
					 * @return $title | $site_name
					 */
					add_filter( 'pre_get_document_title', 'index_page_title', 999 );
					function index_page_title( $title ) {

						return get_the_title( CFMH_Hosting_Sync_Front::$page_id ) . ' | ' . get_bloginfo( 'name' );

					}

					/**
					 * Archive page title
					 *
					 * @since 1.1.3
					 * @param array $title
					 * @return $title
					 */
					add_filter( 'get_the_archive_title', 'archive_page_title', 999 );
					function archive_page_title( $title ) {

						return get_the_title( CFMH_Hosting_Sync_Front::$page_id );

					}

				}

			}

		}

		/**
		 * Page for posts
		 *
		 * @since 1.0
		 * @return int
		 */
		public static function pre_option_page_for_posts_function() {
			return self::$page_id;
		}

		/**
		 * Page for front
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function pre_option_show_on_front_function() {
			return 'page';
		}

		/**
		 * Rewrite captivate_podcast slug
		 *
		 * @since 1.0
		 * @param array  $args  Arguements.
		 * @param string $post_type  Post type.
		 * @return array
		 */
		public static function register_post_type_args( $args, $post_type ) {

			if ( 'captivate_podcast' === $post_type ) {

				$post_slug = basename( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );
				$post_id   = ( $post = get_page_by_path( $post_slug, OBJECT, 'captivate_podcast' ) ) ? $post->ID : 0;

				$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );

				$args['rewrite']['slug'] = cfm_get_show_page( $cfm_show_id, 'slug' );

				if ( ! is_admin() ) {
					flush_rewrite_rules();
				}
			}

			return $args;
		}

		/**
		 * Modify content output
		 *
		 * @since 1.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_filter( $content ) {

			if ( ! class_exists( 'PWFT' ) ) {

				$output    = '';
				$post_id   = get_the_ID();
				$post_type = get_post_type( $post_id );

				if ( 'captivate_podcast' == $post_type ) {

					$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );
					$cfm_episode_media_id = get_post_meta( $post_id, 'cfm_episode_media_id', true );

					if ( $cfm_episode_media_id ) {
						$output .= '<div class="cfm-player-iframe" style="width: 100%; height: 200px; margin-bottom: 20px; border-radius: 6px; overflow:hidden;"><iframe style="width: 100%; height: 200px;" frameborder="no" scrolling="no" seamless allow="autoplay" src="' . CFMH_PLAYER_URL . '/episode/' . $cfm_episode_id . '"></iframe></div>';
					}
					else {
						if ( is_user_logged_in() ) {
							$output .= '<div class="cfm-player-iframe" style="width: 100%; margin-bottom: 20px; border-radius: 6px; overflow:hidden; border: 1px solid #d6d6d6;"><div class="cfm-sorry-text">Sorry, there\'s no audio file uploaded to this episode yet.</div></div>';
						}
					}

					$output .= $content;

				}
				else {
					$output .= $content;
				}
				return $output;
			}
			else {
				return $content;
			}

		}

		/**
		 * Show transcription.
		 *
		 * @since 2.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_transcript( $content ) {

			$output = $content;

			if ( is_singular( 'captivate_podcast' ) ) {

                $post_id   = get_the_ID();
                $transcript = get_post_meta( $post_id, 'cfm_episode_transcript', true );

                if ( is_array( $transcript ) && ! empty( $transcript ) ) {

                	if ( ( null != $transcript['transcription_text'] && '' != $transcript['transcription_text'] ) || ( null != $transcript['transcription_html'] && '' != $transcript['transcription_html'] ) ) {

	                    if ( null != $transcript['transcription_html'] && '' != $transcript['transcription_html'] ) {

	                    	$html = curl_init( $transcript['transcription_html'] );
	                        curl_setopt( $html, CURLOPT_RETURNTRANSFER, TRUE );
	                        curl_setopt( $html, CURLOPT_FOLLOWLOCATION, TRUE );
	                        curl_setopt( $html, CURLOPT_AUTOREFERER, TRUE );
	                        $transcript_content = curl_exec( $html );
	                    } else {
	                        $array_of_lines = preg_split( '/\r\n|\r|\n/', $transcript['transcription_text'] );
	                        $transcript_content = '';

	                        foreach ( $array_of_lines as $line ) {

	                            preg_match( '/([a-zA-Z\W]{1,15}[a-zA-Z\W]{0,15})([0-9]{0,2}:?[0-9]{2}:?[0-9][0-9][ ]*)/', $line, $output_array );

	                            if ( $output_array ) {
	                                $transcript_content .= '<cite>'. trim( $output_array[1] ) . ':</cite><time> ' . $output_array[2] . '</time>';
	                            }
	                            else {
	                                $transcript_content .= '' != $line ? '<p>' . $line . '</p>' : '';
	                            }
	                        }
	                    }

	                    $output .= '<div class="cfm-transcript">';
	                        $output .= '<h5 class="cfm-transcript-title">Transcript</h5>';
	                        $output .= '<div class="cfm-transcript-content">' . $transcript_content . '</div>';
	                    $output .= '</div>';
	                }

                }

            }

			return $output;

		}

		/**
		 * Modify content output
		 *
		 * @since 2.0.2
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function pw_content_filter( $content ) {

			$output = $content;

			if ( class_exists( 'PWFT' ) && is_singular( 'captivate_podcast' ) ) {

				$cfm_episode_custom_field = get_post_meta( get_the_ID(), 'cfm_episode_custom_field', true );

				if ( $cfm_episode_custom_field ) {
					$output .= '<div id="cfm-custom-field" class="cfm-custom-field">' . $cfm_episode_custom_field . '</div>';
				}
			}

			return $output;

		}

		/**
		 * Modify content output to add the auto-timestamp
		 *
		 * @since 1.0
		 * @param string $content  Contents.
		 * @return string
		 */
		public static function content_auto_timestamp( $content ) {

			$output = '';

			if ( is_singular( 'captivate_podcast' ) ) {

				// auto-timestamp pattern.
                $pattern = '/(?:[0-5]\d|2[0-3]):(?:[0-5]\d):?(?:[0-5]\d)?/';

				$found_timestamp = preg_replace_callback(
					$pattern,
					function ($m) {
						  return empty($m[1]) ? '<a href="javascript: void(0);" class="cp-timestamp" data-timestamp="'. $m[0] . '">'. $m[0] . '</a>' : $m[0];
					},
					$content
				);

				if ( $found_timestamp ) {
					$output = $found_timestamp;
				}

			} else {
				$output .= $content;
			}

			return $output;

		}

		/**
		 * Edit post link
		 *
		 * @since 1.0
		 * @param string $link Link for episode.
		 * @return string
		 */
		public static function edit_post_link( $link ) {
			global $post;
			$post_id     = $post->ID;
			$cfm_show_id = get_post_meta( $post_id, 'cfm_show_id', true );

			$captivate_edit_link = '<a class="post-edit-link" href="' . esc_url( admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $cfm_show_id . '&eid=' . $post_id ) ) . '">Edit <span class="screen-reader-text">' . $post->post_title . '</span></a>';

			return ( 'captivate_podcast' === get_post_type() ) ? $captivate_edit_link : $link;
		}

		/**
		 * Add twitter card
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function add_meta_data() {

			if ( is_singular( 'captivate_podcast' ) ) {

				global $post;
				$post_id = $post->ID;

				$cfm_show_id 			= get_post_meta( $post_id, 'cfm_show_id', true );
				$cfm_episode_id        	= get_post_meta( $post_id, 'cfm_episode_id', true );
				$cfm_episode_title     	= get_the_title( $post_id );
				$cfm_episode_shownotes 	= cfm_limit_characters( get_the_excerpt(), 140, '' );
				$cfm_episode_content   	= cfm_limit_characters( get_the_excerpt(), 152, '' );
				$cfm_episode_artwork   	= get_post_meta( $post_id, 'cfm_episode_artwork', true );
				$cfm_episode_artwork   	= ( $cfm_episode_artwork ) ? $cfm_episode_artwork : cfm_get_show_info( $cfm_show_id, 'artwork' );

				$og_image 						= ( has_post_thumbnail( $post_id ) ) ? get_the_post_thumbnail_url( $post_id,  'full' ) : $cfm_episode_artwork;
				$cfm_episode_seo_title   		= get_post_meta( $post_id, 'cfm_episode_seo_title', true );
				$cfm_episode_seo_description   	= get_post_meta( $post_id, 'cfm_episode_seo_description', true );

				$cfm_episode_media_url = get_post_meta( $post_id, 'cfm_episode_media_url', true );

				// twitter data.
				echo '	<meta property="twitter:card" content="player" />' . "\n";
				echo '	<meta property="twitter:player" content="' . CFMH_PLAYER_URL . '/episode/' . esc_attr( $cfm_episode_id ) . '/twitter/">' . "\n";
				echo '	<meta name="twitter:player:width" content="540">' . "\n";
				echo '	<meta name="twitter:player:height" content="177">' . "\n";
				echo '	<meta property="twitter:title" content="' . esc_attr($cfm_episode_seo_title ? $cfm_episode_seo_title : $cfm_episode_title ) . '">' . "\n";
				echo '	<meta property="twitter:description" content="' . esc_attr($cfm_episode_seo_description ? $cfm_episode_seo_description : $cfm_episode_shownotes ) . '">' . "\n";
				echo '	<meta property="twitter:site" content="@CaptivateAudio">' . "\n";
				echo '	<meta property="twitter:image" content="' . esc_attr( $og_image ) . '" />' . "\n";

				// og data.
				if ( $cfm_episode_seo_title || $cfm_episode_title ) {
					echo '	<meta property="og:title" content="' . esc_attr($cfm_episode_seo_title ? $cfm_episode_seo_title : $cfm_episode_title ) . '">' . "\n";
				}
				echo '	<meta property="og:description" content="' . esc_attr($cfm_episode_seo_description ? $cfm_episode_seo_description : $cfm_episode_content . '...' ) . '">' . "\n";
				echo '	<meta property="description" content="' . esc_attr($cfm_episode_seo_description ? $cfm_episode_seo_description : $cfm_episode_content . '...' ) . '">' . "\n";
				echo '	<meta property="og:image" content="' . esc_attr( $og_image ) . '" />' . "\n";

				// og audio.
				if ( $cfm_episode_media_url ) {
					echo '	<meta property="og:audio" content="' . esc_attr( cfm_add_media_prefixes ( $cfm_show_id, $cfm_episode_media_url ) ) . '" />' . "\n";
					echo '	<meta property="og:audio:type" content="audio/mpeg">' . "\n";
				}

			}

		}

		/**
		 * Add show feed rss
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function add_show_feed_rss() {
			$shows = cfm_get_shows();

			if ( ! empty( $shows ) ) {

				$queried_object = get_queried_object();
				$queried_object_id = $queried_object ? $queried_object->ID : 'CFM_NULL';

				foreach ( $shows as $show ) {
					if ( $queried_object_id == $show['index_page'] ) {
						echo '<link rel="alternate" type="application/rss+xml" title="RSS feed for ' . esc_attr( $show['title'] ) . '" href="' . esc_url( $show['feed_url'] ) . '" />' . "\n";
					}
				}

			}
		}

	}

endif;
