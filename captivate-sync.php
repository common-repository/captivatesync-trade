<?php
 /**
 Plugin Name:  Captivate Sync&trade;
 Plugin URI:   https://captivate.fm/sync
 Description:  Captivate Sync&trade; is the WordPress podcasting plugin from Captivate.fm. Publish directly from your WordPress site or your Captivate podcast hosting account and stay in-sync wherever you are!
 Version:      2.0.26
 Author:       Captivate Audio Ltd
 Author URI:   https://www.captivate.fm
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'CFMH' ) ) {
	define( 'CFMH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'CFMH_URL' ) ) {
	define( 'CFMH_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'CFMH_VERSION' ) ) {
	define( 'CFMH_VERSION', '2.0.26' );
}

if ( ! defined( 'CFMH_API_URL' ) ) {
	define( 'CFMH_API_URL', 'https://api.captivate.fm' );
}

if ( ! defined( 'CFMH_PLAYER_URL' ) ) {
	define( 'CFMH_PLAYER_URL', 'https://player.captivate.fm' );
}

if ( ! defined( 'CFMH_MSP_INTERVAL' ) ) {
	define( 'CFMH_MSP_INTERVAL', 15 * MINUTE_IN_SECONDS );
}

if ( ! defined( 'CFMH_WP_ERROR' ) ) {
	define( 'CFMH_WP_ERROR', 'wp_error' );
}

// Check if CFM_HOSTING class already exists.
if ( ! class_exists( 'CFM_Hosting' ) ) :

	/**
	 * Main sync class
	 *
	 * @since 1.0
	 */
	class CFM_Hosting {

		private $podcast_hosting_api = CFMH_API_URL;

		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		public function __construct() {
			$this->_init();
		}

		/**
		 * Initialize hooks and includes
		 *
		 * @since 1.0
		 */
		public function _init() {

			// Insert initial data.
			register_activation_hook( __FILE__, array( $this, '_install' ) );

			// Scheduler
			register_activation_hook( __FILE__, array( $this, '_set_scheduler' ) );
			register_deactivation_hook( __FILE__, array( $this, '_clear_scheduler' ) );

			// Hooks, includes and authentication.
			$this->_load_includes();
			$this->_load_hooks();
			$this->_authentication();
			$this->podcast_hosting_api = CFMH_API_URL;

			add_action( 'rest_api_init', function() {
				register_rest_route( 'captivate-sync/v1', '/sync', array(
					'methods'  				=> 'POST',
					'callback' 				=> '_captivate_sync',
					'permission_callback' 	=> function() { return ''; }
				) );
			} );

			function _captivate_sync( $request ) {

                $data     = $request->get_params();
				$sync_key = $data['sync_key'];
				$show_id  = $data['show_id'];
				$episode_id = $data['episode_id'];
				$event_operation = $data['event_operation'];

    			if ( $sync_key && $show_id ) {

    				$current_shows = cfm_get_show_ids();

    				if ( in_array( $show_id, $current_shows ) ) {

    					if ( cfm_get_show_info( $show_id, 'sync_key' ) == $sync_key ) {

    					    if( $episode_id ) {
                                // sync episodes.
							    switch ( $event_operation ) {
    								case 'CREATE':
    									$sync_show = cfm_sync_shows( $show_id );
    									break;
    								case 'UPDATE':
    									$sync_show = cfm_sync_wp_episode( $episode_id );
    									break;
    								case 'DELETE':
    								    $sync_show = cfm_sync_shows( $show_id );
    								    break;
    								default:
    									break;
    							}
                            } else {
                                $sync_show = cfm_sync_shows( $show_id );
                            }

    						if ( false == $sync_show ) {
    							echo 'ERROR: Something went wrong! Please contact the support team.';
    						} else {
                                if( $episode_id ) {
                                    echo 'SUCCESS: Episode has successfully synchronised.';
                                } else {
                                    echo 'SUCCESS: Show has successfully synchronised.';
                                }
    						}
    					} else {

    						echo 'ERROR: Sync key not accepted.';

    					}
    				} else {
    					echo 'ERROR: Show does not exist in current CaptivateSync install.';
    				}
    			}

    		}

		}

		/**
		 * Create database table for shows
		 *
		 * @since 1.0
		 */
		public static function _install() {

			global $wpdb;

			// cfm_shows table.
			$cfm_shows           = $wpdb->prefix . 'cfm_shows';
			$cfm_shows_structure = "
				CREATE TABLE IF NOT EXISTS $cfm_shows(
					id bigint(20) NOT NULL AUTO_INCREMENT,
					show_id varchar(40) NOT NULL,
					cfm_option varchar(100) NOT NULL,
					cfm_value longtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '',
					PRIMARY KEY (id)
				) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
			";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			dbDelta( $cfm_shows_structure );

			// clear plugin update notice.
			update_option( 'cfm_plugin_updated', '1' );

		}

		/**
		 * Set scheduler
		 *
		 * @since 1.0
		 */
		public static function _set_scheduler() {

			// Set schedule to get new episodes from captivate and insert to WP
			if ( ! wp_next_scheduled( 'cfm_sync_new_episodes' ) ) {
				wp_schedule_event( time(), 'hourly', 'cfm_sync_new_episodes' );
			}

		}

		/**
		 * Clear scheduler
		 *
		 * @since 1.0
		 */
		public static function _clear_scheduler() {

			// Clear schedule to get new episodes from captivate and insert to WP
			wp_clear_scheduled_hook( 'cfm_sync_new_episodes' );

		}

		/**
		 * Load includes
		 *
		 * @since 1.0
		 */
		private function _load_includes() {

			include_once CFMH . 'inc/functions.php';
			include_once CFMH . 'inc/class-cfmh-hosting-data.php';
			include_once CFMH . 'inc/class-cfmh-hosting-sync-front.php';
			include_once CFMH . 'inc/class-cfmh-hosting-shortcode.php';

			if ( is_admin() ) :
				include_once CFMH . 'inc/class-cfmh-hosting-dashboard-admin.php';
				include_once CFMH . 'inc/class-cfmh-hosting-sync.php';
				include_once CFMH . 'inc/class-cfmh-hosting-sync-process.php';
				include_once CFMH . 'inc/class-cfmh-hosting-publish-episode.php';
			endif;
		}

		/**
		 * Load hooks
		 *
		 * @since 1.0
		 */
		private function _load_hooks() {

			add_action( 'init', array( 'CFMH_Hosting_Data', 'register' ) );
			add_action( 'init', array( 'CFMH_Hosting_Data', 'unregister' ), 100 );

			// publish missed scheduled episodes
			add_action( 'init', array( $this, 'publish_missed_scheduled' ), 0 );

			// set show page.
			add_action( 'pre_get_posts', array( 'CFMH_Hosting_Sync_Front', 'index_page' ), 100 );

			// captivate_podcast rewrite slug.
			add_filter( 'register_post_type_args', array( 'CFMH_Hosting_Sync_Front', 'register_post_type_args' ), 10, 2 );

			// add player to episodes.
			add_filter( 'the_excerpt', array( 'CFMH_Hosting_Sync_Front', 'content_filter' ), 11 );
			add_filter( 'the_content', array( 'CFMH_Hosting_Sync_Front', 'content_filter' ), 11 );

			// remove captivate_podcast edit link.
			add_filter( 'edit_post_link', array( 'CFMH_Hosting_Sync_Front', 'edit_post_link' ) );

			// meta data.
			add_action( 'wp_head', array( 'CFMH_Hosting_Sync_Front', 'add_meta_data' ), 1 );

			// rss feed.
			add_action( 'wp_head', array( 'CFMH_Hosting_Sync_Front', 'add_show_feed_rss' ), 1 );

			// player api.
			add_action( 'wp_enqueue_scripts', array( 'CFMH_Hosting_Sync_Front', 'assets' ) );

			// transcription.
			add_filter( 'the_content', array( 'CFMH_Hosting_Sync_Front', 'content_transcript' ), 11 );

			// add custom field to episodes.
			add_filter( 'the_content', array( 'CFMH_Hosting_Sync_Front', 'pw_content_filter' ), 11 );

			// auto-timestamp.
			add_filter( 'the_content', array( 'CFMH_Hosting_Sync_Front', 'content_auto_timestamp' ), 12 );

			// shortcode.
			add_shortcode( 'cfm_captivate_episodes', array( 'CFM_Hosting_Shortcode', 'episodes_list' ) );

			// Get new episodes from captivate and insert to WP
			add_action( 'cfm_sync_new_episodes', array( $this, 'get_new_episodes' ) );

			if ( is_admin() ) :

				// check if user logged in
				add_action( 'admin_init', array( 'CFMH_Hosting_Dashboard_Admin', 'captivate_check_login' ) );

				// restrictions.
				add_action( 'current_screen', array( 'CFMH_Hosting_Dashboard_Admin', 'restrict_other_admin_pages' ) );

				// user credentials.
				add_action( 'wp_ajax_get-shows', array( 'CFMH_Hosting_Dashboard_Admin', 'get_shows' ) );
				add_action( 'admin_post_form_create_credentials', array( 'CFMH_Hosting_Dashboard_Admin', 'create_credentials' ) );
				add_action( 'wp_ajax_remove-credentials', array( 'CFMH_Hosting_Dashboard_Admin', 'remove_credentials' ) );

				// show settings.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Dashboard_Admin', 'assets' ) );
				add_action( 'admin_menu', array( 'CFMH_Hosting_Dashboard_Admin', 'menus' ) );

				// set show page.
				add_action( 'wp_ajax_set-show-page', array( 'CFMH_Hosting_Dashboard_Admin', 'set_show_page' ) );

				// set show author.
				add_action( 'wp_ajax_set-show-author', array( 'CFMH_Hosting_Dashboard_Admin', 'set_show_author' ) );

				// set display episodes.
				add_action( 'wp_ajax_set-display-episodes', array( 'CFMH_Hosting_Dashboard_Admin', 'set_display_episodes' ) );

				// delete episode.
				add_action( 'wp_ajax_trash-episode', array( 'CFMH_Hosting_Dashboard_Admin', 'delete_episode' ) );

				// publish episode.
				add_action( 'admin_post_form_publish_episode', array( 'CFMH_Hosting_Publish_Episode', 'publish_episode_save' ) );
				add_action( 'wp_ajax_add-webcategory', array( 'CFMH_Hosting_Publish_Episode', 'add_webcategory' ) );
				add_action( 'wp_ajax_add-tags', array( 'CFMH_Hosting_Publish_Episode', 'add_tags' ) );

				// sync show.
				add_action( 'admin_enqueue_scripts', array( 'CFMH_Hosting_Sync', 'assets' ) );

				// sync show process.
				add_action( 'wp_ajax_select-shows', array( 'CFMH_Hosting_Sync_process', 'select_shows' ) );
				add_action( 'wp_ajax_sync-shows', array( 'CFMH_Hosting_Sync_process', 'sync_shows' ) );

				// user podcast management.
				add_action( 'edit_user_profile', array( 'CFMH_Hosting_Dashboard_Admin', 'add_user_podcast_management' ) );
				add_action( 'edit_user_profile_update', array( 'CFMH_Hosting_Dashboard_Admin', 'update_user_podcast_management' ) );

				// admin footer.
				add_action( 'admin_footer', array( 'CFMH_Hosting_Dashboard_Admin', 'admin_footer' ) );

				// admin notices.
				add_action( 'admin_notices', array( 'CFMH_Hosting_Dashboard_Admin', 'plugin_update_notice' ) );

				// extend timeout.
				add_filter( 'http_request_timeout', 'CFMH_timeout_extend' );
				function CFMH_timeout_extend( $time ) {
					// Default timeout is 5.
					return 500;
				}

			endif;

		}

		/**
		 * Generate user authentication
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private function _authentication() {

			if ( is_admin() ) :

				if ( ! get_transient( 'cfm_authentication_token' ) && get_option( 'cfm_authentication_id' ) ) {

					$request = wp_remote_post(
						$this->podcast_hosting_api . '/authenticate/pw',
						array(
							'timeout' => 500,
							'body' => array(
								'username' => get_option( 'cfm_authentication_id' ),
								'token'    => get_option( 'cfm_authentication_key' ),
							),
						)
					);

					// Debugging.
					if ( cfm_is_debugging_on() ) {
						$log_date = date( 'Y-m-d H:i:s', time() );
						$txt = '**AUTHENTICATION - ' . $log_date . '** ' . PHP_EOL . print_r( $request, true ) . '**END AUTHENTICATION**';
						$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
					}

					if ( ! is_wp_error( $request ) && 'Unauthorized' != $request['body'] && is_array( $request ) ) {

						$request = json_decode( $request['body'] );

						set_transient( 'cfm_authentication_token', $request->user->token, 3600 * 24 * 7 );

						return $request->user->token;

					} else {
						set_transient( 'cfm_authentication_token', 'FAILED', 3600 );
					}
				}

			endif;
		}

		/**
		 * Check timestamp from transient and publish all missed scheduled episodes
		 *
		 * @since 1.1.0
		 * @return none
		 */
		public static function publish_missed_scheduled() {

			$last_scheduled_missed_time = get_transient( 'wp_scheduled_missed_time' );

			$time = current_time( 'timestamp', 0 );

			if ( false !== $last_scheduled_missed_time && absint( $last_scheduled_missed_time ) > ( $time - CFMH_MSP_INTERVAL ) ) {
				return;
			}

			set_transient( 'wp_scheduled_missed_time', $time, CFMH_MSP_INTERVAL );

			global $wpdb;

			$sql_query = "
				SELECT
				ID
				FROM {$wpdb->posts}
				WHERE ( ( post_date > 0 && post_date <= %s ) )
				AND post_status = 'future'
				AND post_type = 'captivate_podcast'
				LIMIT 0, %d
			";

			$sql = $wpdb->prepare( $sql_query, current_time( 'mysql', 0 ), 5 );

			$scheduled_post_ids = $wpdb->get_col( $sql );

			if ( ! count( $scheduled_post_ids ) ) {
				return;
			}

			foreach ( $scheduled_post_ids as $scheduled_post_id ) {
				if ( ! $scheduled_post_id ) {
					continue;
				}

				wp_publish_post( $scheduled_post_id );
			}
		}

		/**
		 * Get new episodes
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function get_new_episodes() {

			$current_shows = cfm_get_show_ids();

			foreach ( $current_shows as $show_id ) {

				$sync_show = cfm_get_new_episodes( $show_id );

			}

		}

	}

	new CFM_Hosting();

endif;
