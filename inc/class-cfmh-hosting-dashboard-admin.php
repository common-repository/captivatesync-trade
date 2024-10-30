<?php
/**
 * Used for admin data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Dashboard_Admin' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Hosting Dashboard Admin class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Dashboard_Admin {


		/**
		 * Enqueueu assets
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$all_screens = array(
				'toplevel_page_cfm-hosting-podcasts',
				'admin_page_cfm-hosting-podcasts',
				'captivate-sync_page_cfm-hosting-podcasts',

				'toplevel_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',

				'toplevel_page_cfm-hosting-edit-episode',
				'admin_page_cfm-hosting-edit-episode',
				'captivate-sync_page_cfm-hosting-edit-episode',

				'toplevel_page_cfm-hosting-shortcode',
				'admin_page_page_cfm-hosting-shortcode',
				'captivate-sync_page_cfm-hosting-shortcode',

				'toplevel_page_cfm-hosting-podcast-episodes',
				'admin_page_cfm-hosting-podcast-episodes',
				'captivate-sync_page_cfm-hosting-podcast-episodes',

				'toplevel_page_cfm-hosting-credentials',
				'admin_page_cfm-hosting-credentials',
				'captivate-sync_page_cfm-hosting-credentials',

				'toplevel_page_cfm-hosting-migration',
				'admin_page_cfm-hosting-migration',
				'captivate-sync_page_cfm-hosting-migration',
			);

			if ( in_array( $current_screen->id, $all_screens ) || ( 0 === strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) ) ) :

				// vendors.
				wp_register_style( 'cfmsync-bootstrap', CFMH_URL . 'vendor/bootstrap/bootstrap.min.css', array(), '4.3.1', 'all' );
				wp_register_style( 'cfmsync-google-fonts', '//fonts.googleapis.com/css?family=Poppins:300,400,500,700' );
				wp_register_style( 'cfmsync-font-awesome', CFMH_URL . 'vendor/fontawesome-pro/css/all.css', array(), '5.11.2', 'all' );
				wp_register_script( 'cfmsync-clipboard', CFMH_URL . 'vendor/clipboard/clipboard.min.js', array( 'jquery' ), '2.0.0', true );

				// cfm.
				wp_enqueue_script( 'cfmsync-functions', CFMH_URL . 'captivate-sync-assets/js/functions-min.js', array(), CFMH_VERSION, true );
				wp_register_script( 'cfmsync', CFMH_URL . 'captivate-sync-assets/js/admin-min.js', array(), CFMH_VERSION, true );

				wp_localize_script(
					'cfmsync',
					'cfmsync',
					array(
						'CFMH'          		=> CFMH,
						'CFMH_URL'      		=> CFMH_URL,
						'CFMH_ADMINURL' 		=> admin_url(),
						'CFMH_SHOWID'   		=> cfm_get_show_id(),
						'CFMH_CURRENT_SCREEN'   => $current_screen->id,
						'ajaxurl'       		=> admin_url( 'admin-ajax.php' ),
						'ajaxnonce'     		=> wp_create_nonce( '_cfm_nonce' ),
					)
				);

				wp_enqueue_style( 'cfmsync-bootstrap' );
				wp_enqueue_style( 'cfmsync-google-fonts' );
				wp_enqueue_style( 'cfmsync-font-awesome' );
				wp_enqueue_script( 'cfmsync-clipboard' );

				wp_enqueue_script( 'bootstrap-js', CFMH_URL . 'vendor/bootstrap/bootstrap.bundle.min.js', array(), '4.3.1', false );

				if ( ! class_exists( 'PW_Admin_UI' ) ) :
					wp_enqueue_script( 'clipboard' );
				endif;

				wp_register_style( 'cfmsync', CFMH_URL . 'captivate-sync-assets/css/admin-min.css', array(), CFMH_VERSION, 'all' );

				// cfm.
				wp_enqueue_script( 'cfmsync' );
				wp_enqueue_style( 'cfmsync' );

			endif;

			$publish_episode_screens = array(
				'toplevel_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',

				'toplevel_page_cfm-hosting-edit-episode',
				'admin_page_cfm-hosting-edit-episode',
				'captivate-sync_page_cfm-hosting-edit-episode',
			);

			if ( in_array( $current_screen->id, $publish_episode_screens ) ) :

				wp_enqueue_media();
				wp_enqueue_script( 'quilljs', CFMH_URL . 'vendor/quill/quill.min.js', array(), '1.3.6' );
				wp_enqueue_style( 'quilljs', CFMH_URL . 'vendor/quill/quill.snow.css', array(), '1.3.62' );
				wp_enqueue_script( 'quilljs-script', CFMH_URL . 'captivate-sync-assets/js/quilljs-min.js', array(), '1.3.6' );

				wp_enqueue_style( 'jquery-ui-theme', CFMH_URL . 'vendor/jquery-ui/jquery-ui.min.css', array(), '1.12.1' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_enqueue_script( 'dropzone', CFMH_URL . 'vendor/dropzone/dropzone.min.js', array(), '5.7.0' );
				wp_enqueue_style( 'dropzone', CFMH_URL . 'vendor/dropzone/dropzone.min.css' );

				wp_enqueue_script( 'savestorage', CFMH_URL . 'captivate-sync-assets/js/local-storage-min.js', array(), CFMH_VERSION );

				wp_register_script(
					'cfm_script',
					CFMH_URL . 'captivate-sync-assets/js/publish-episode-min.js',
					array( 'jquery' ),
					CFMH_VERSION
				);

				wp_localize_script(
					'cfm_script',
					'cfm_script',
					array(
						'cfm_url'   => CFMH_API_URL,
						'cfm_token' => get_transient( 'cfm_authentication_token' ),
					)
				);

				wp_enqueue_script( 'cfm_script' );

			endif;

			$data_tables = array(
				'toplevel_page_cfm-hosting-podcast-episodes',
				'admin_page_cfm-hosting-podcast-episodes',
				'captivate-sync_page_cfm-hosting-podcast-episodes',
			);

			if ( in_array( $current_screen->id, $data_tables ) || ( strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) === 0 ) ) :

				wp_enqueue_style( 'cfm-data-tables', CFMH_URL . 'vendor/datatables/jquery.dataTables.min.css', array(), '1.10.19' );
				wp_enqueue_style( 'cfm-data-tables-style', CFMH_URL . 'captivate-sync-assets/css/data-tables.css', array(), '1.10.19' );
				wp_enqueue_script( 'cfm-data-tables', CFMH_URL . 'vendor/datatables/jquery.dataTables.min.js', array(), '1.10.19', true );
				wp_enqueue_script( 'cfm-data-tables-js', CFMH_URL . 'captivate-sync-assets/js/data-tables-min.js', array(), '1.10.19', true );

			endif;

			$generate_shortcode_screens = array(
				'toplevel_page_cfm-hosting-shortcode',
				'admin_page_cfm-hosting-shortcode',
				'captivate-sync_page_cfm-hosting-shortcode',
			);

			if ( in_array( $current_screen->id, $generate_shortcode_screens ) ) :

				wp_enqueue_script( 'cfmsync-generate-shortcode', CFMH_URL . 'captivate-sync-assets/js/generate-shortcode-min.js', array(), CFMH_VERSION, true );

			endif;

		}

		/**
		 * Restrict admin pages
		 *
		 * @since 1.0
		 */
		public static function restrict_other_admin_pages() {

			$current_screen = get_current_screen();

			if ( 'edit-captivate_podcast' == $current_screen->id || 'captivate_podcast' == $current_screen->id  ) {
				if ( ! class_exists( 'PW_Admin_UI' ) || class_exists( 'PW_Admin_UI' ) && 'customersupport' != pwaui_current_user_login() ) {
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) ); exit;
				}
			}

		}

		/**
		 * Redirect if unauthenticated.
		 *
		 * Redirect my shows to authentication if not logged in.
		 *
		 * @return void
		 * @since 1.0
		 */
		public static function captivate_check_login() {

		    global $pagenow;

		    # Check current admin page.
		    if ( $pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'cfm-hosting-podcasts' ) {

		    	if( ! cfm_is_logged_in() ) {

			        wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials' ) );
			        exit;

			    }

		    }

		}

		/**
		 * Admin menus
		 *
		 * @since 1.0
		 */
		public static function menus() {

			$shows = cfm_get_shows();
			$user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true );

			$main_menu_slug = ! empty( $shows ) ? 'cfm-hosting-publish-episode' : 'cfm-hosting-podcasts';
			$main_menu_sub  = ! empty( $shows ) ? 'publish_episode' : 'my_podcasts';

			if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
				add_menu_page( 'Captivate Sync&trade;', 'Captivate Sync&trade;', 'edit_posts', $main_menu_slug, array( 'CFMH_Hosting_Dashboard_Admin', $main_menu_sub ), CFMH_URL . 'captivate-sync-assets/img/menu-icon.png' );
			}

			if ( ! empty( $shows ) ) {
				if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
					add_submenu_page( $main_menu_slug, 'Publish A New Episode', 'Publish Episode', 'edit_posts', 'cfm-hosting-publish-episode', array( 'CFMH_Hosting_Dashboard_Admin', 'publish_episode' ), null );
					add_submenu_page( 'options.php', 'Edit podcast episode', 'Edit Episode', 'edit_posts', 'cfm-hosting-edit-episode', array( 'CFMH_Hosting_Dashboard_Admin', 'publish_episode' ), null );
				}
			}

			if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
				add_submenu_page( $main_menu_slug, 'My Shows', 'My Shows', 'edit_posts', 'cfm-hosting-podcasts', array( 'CFMH_Hosting_Dashboard_Admin', 'my_podcasts' ), null );
			}

			if ( ! empty( $shows ) ) {
				foreach ( $shows as $show ) {
					if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {
						add_submenu_page( $main_menu_slug, $show['title'], $show['title'], 'edit_posts', 'cfm-hosting-podcast-episodes_' . $show['id'], array( 'CFMH_Hosting_Dashboard_Admin', 'my_podcast_episodes' ), null );
					}
				}
			}

			if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && cfm_is_user_has_show() ) ) {
				add_submenu_page( $main_menu_slug, 'Shortcode Builder', 'Shortcode Builder', 'edit_posts', 'cfm-hosting-shortcode', array( 'CFMH_Hosting_Dashboard_Admin', 'shortcode' ), null );
			}

			if ( cfm_is_logged_in() ) {
				add_submenu_page( $main_menu_slug, 'Categories', 'Categories', 'manage_categories', admin_url( 'edit-tags.php?taxonomy=captivate_category' ), null );
				add_submenu_page( $main_menu_slug, 'Tags', 'Tags', 'manage_categories', admin_url( 'edit-tags.php?taxonomy=captivate_tag' ), null );
			}

			if ( ! class_exists( 'PW_Admin_UI' ) || class_exists( 'PW_Admin_UI' ) && 'customersupport' == pwaui_current_user_login() ) :
				add_submenu_page( $main_menu_slug, 'Authentication', 'Authentication', 'manage_options', 'cfm-hosting-credentials', array( 'CFMH_Hosting_Dashboard_Admin', 'user_credentials' ), null );
			endif;

		}

		/**
		 * Podcasts template
		 *
		 * @since 1.0
		 */
		public static function my_podcasts() {
			include CFMH . 'inc/templates/podcasts.php';
		}

		/**
		 * Episodes template
		 *
		 * @since 1.0
		 */
		public static function my_podcast_episodes() {

			include CFMH . 'inc/templates/episodes.php';
		}

		/**
		 * Shortcode template
		 *
		 * @since 1.2.0
		 */
		public static function shortcode() {
			include CFMH . 'inc/templates/shortcode.php';
		}

		/**
		 * Publish episode template
		 *
		 * @since 1.0
		 */
		public static function publish_episode() {

			$shows = cfm_get_shows();

			$shows_count = count( $shows );

			if ( ! empty( $shows ) && $shows_count > 1 && ! isset( $_GET['show_id'] ) ) {
				include CFMH . 'inc/templates/podcasts.php';
			} else {
				include CFMH . 'inc/templates/publish-episode.php';
			}

		}

		/**
		 * User authentication template
		 *
		 * @since 1.0
		 */
		public static function user_credentials() {

			include CFMH . 'inc/templates/user-credentials.php';
		}

		/**
		 * Create authentication
		 *
		 * @since 1.0
		 * @return redirect
		 */
		public static function create_credentials() {

			if ( ! isset( $_POST['_sec'] ) || ! wp_verify_nonce( $_POST['_sec'], '_sec_action' ) ) {

				wp_die( __( "Cheatin' uh?" ) );
				exit;
			} else {

				$auth_id  = sanitize_text_field( $_POST['auth_id'] );
				$auth_key = sanitize_text_field( $_POST['auth_key'] );

				if ( empty( $auth_id ) || empty( $auth_key ) ) {
					wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials&response=2' ) );
				} else {
					if ( get_option( 'cfm_authentication_id' ) ) {
						wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials&response=3' ) );
					} else {
						update_option( 'cfm_authentication_id', $auth_id );
						update_option( 'cfm_authentication_key', $auth_key );

						if ( get_transient( 'cfm_authentication_token' ) ) {
							wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials&response=4' ) );
						} else {
							$request = wp_remote_post(
								CFMH_API_URL . '/authenticate/pw',
								array(
									'timeout' => 500,
									'body' => array(
										'username' => $auth_id,
										'token'    => $auth_key,
									),
								)
							);

							// Debugging.
							if ( cfm_is_debugging_on() ) {
								$log_date = date( 'Y-m-d H:i:s', time() );
								$txt = '**CREATE CREDENTIALS - ' . $log_date . '** ' . PHP_EOL . print_r( $request, true ) . '**END CREATE CREDENTIALS**';
								$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
							}

							if ( ! is_wp_error( $request ) && 'Unauthorized' != $request['body'] && is_array( $request ) ) {

								$request = json_decode( $request['body'] );

								set_transient( 'cfm_authentication_token', $request->user->token, 3600 * 24 * 7 );

								update_option( 'cfm_authentication_date_added', current_time( 'mysql' ) );

								// create own db table for subsite.
								if ( is_multisite() ) {
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
								}

								wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials&response=1' ) );

							} else {
								set_transient( 'cfm_authentication_token', 'FAILED', 3600 );

								wp_redirect( admin_url( 'admin.php?page=cfm-hosting-credentials&response=5' ) );
							}
						}
					}
				}
			}

		}

		/**
		 * Get shows
		 *
		 * @since 1.0
		 * @return array | string
		 */
		public static function get_shows() {

			$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( ! get_transient( 'cfm_authentication_token' ) ) {

					$output = '<strong>ERROR:</strong> No authorisation.';

				} else {

					$current_shows = cfm_get_show_ids();

					$response = wp_remote_request(
						CFMH_API_URL . '/users/' . get_option( 'cfm_authentication_id' ) . '/shows/',
						array(
							'timeout' => 500,
							'method'  => 'GET',
							'headers' => array(
								'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
							),
						)
					);

					// Debugging.
					if ( cfm_is_debugging_on() ) {
						$log_date = date( 'Y-m-d H:i:s', time() );
						$txt = '**GET SHOWS - ' . $log_date . '** ' . PHP_EOL . print_r( $response, true ) . '**END GET SHOWS**';
						$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
					}

					if ( ! is_wp_error( $response ) && 'Unauthorized' != $response['body'] && is_array( $response ) ) {

						$response = json_decode( $response['body'] );

						foreach ( $response->shows as $id => $show ) {
							if ( in_array( $show->id, $current_shows ) ) {
								$response->shows[ $id ]->enabled = true;
							}
						}

						$output = json_encode( $response->shows );

					} else {

						$output = '<strong>ERROR:</strong> Cannot get shows.';

					}
				}
			}

			echo $output;

			wp_die();

		}

		/**
		 * Delete episode
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function delete_episode() {

			$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';

			if ( current_user_can( 'delete_others_posts' ) ) {

				$pid = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';

				if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], 'trash_post_' . $pid ) ) {

					wp_trash_post( $pid );

					$cfm_episode_id = get_post_meta( $pid, 'cfm_episode_id', true );

					$remove_episode = wp_remote_request(
						CFMH_API_URL . '/episodes/' . $cfm_episode_id,
						array(
							'timeout' => 500,
							'method'  => 'DELETE',
							'headers' => array(
								'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
							),
						)
					);

					// Debugging.
					if ( cfm_is_debugging_on() ) {
						$log_date = date( 'Y-m-d H:i:s', time() );
						$txt = '**DELETE EPISODE - ' . $log_date . '** ' . PHP_EOL . print_r( $remove_episode, true ) . '**END DELETE EPISODE**';
						$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
					}

					if ( ! is_wp_error( $remove_episode ) && 'Unauthorized' != $remove_shows['body'] && is_array( $remove_episode ) ) {

						$output = 'success';

					}
					else {
						$output = 'captivate_error';
					}

				}
			}

			echo $output;

			wp_die();

		}

		/**
		 * Set show page
		 *
		 * @since 1.0
		 * @return string
		 */
		public static function set_show_page() {

			$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) && isset( $_POST['page_id'] ) ) {

					$page_id =  sanitize_text_field( wp_unslash( $_POST['page_id'] ) );

					$index_page_info = array();

					$sync_slug = ( $page_id != '0' ) ? get_bloginfo( 'url' ) . '/' . get_post_field( 'post_name', $page_id ) . '/' : get_bloginfo( 'url' ) . '/captivate-podcast/';

					$index_page_info['captivate_sync_url'] = $sync_slug;

					$update_index_page = wp_remote_request(
						CFMH_API_URL . '/shows/' . $_POST['show_id'] . '/sync/url',
						array(
							'timeout' => 500,
							'body'    => $index_page_info,
							'method'  => 'PUT',
							'headers' => array(
								'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
							),
						)
					);

					// Debugging.
					if ( cfm_is_debugging_on() ) {
						$log_date = date( 'Y-m-d H:i:s', time() );
						$txt = '**SYNC INDEX PAGE URL - ' . $log_date . '** ' . PHP_EOL . print_r( $update_index_page, true ) . '**END SYNC INDEX PAGE URL**';
						$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
					}

					if ( ! is_wp_error( $update_index_page ) && 'Unauthorized' != $update_index_page['body'] && is_array( $update_index_page ) ) {

						cfm_update_show_info( $_POST['show_id'], 'index_page', $page_id );

						$output = 'success';

					}

				}

			}

			echo $output;

			wp_die();

		}

		/**
		 * Set show author
		 *
		 * @since 1.1.4
		 * @return string
		 */
		public static function set_show_author() {

			$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) && isset( $_POST['author_id'] ) ) {

					cfm_update_show_info( $_POST['show_id'], 'author', $_POST['author_id'] );

					$output = 'success';

				}

			}

			echo $output;

			wp_die();

		}

		/**
		 * Set display episodes
		 *
		 * @since 1.1.4
		 * @return string
		 */
		public static function set_display_episodes() {

			$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				if ( isset( $_POST['show_id'] ) && isset( $_POST['display_episodes'] ) ) {

					cfm_update_show_info( $_POST['show_id'], 'display_episodes', $_POST['display_episodes'] );

					$output = 'success';

				}

			}

			echo $output;

			wp_die();

		}

		/**
		 * Remove authentication
		 *
		 * @since 1.0
		 * @return array | string
		 */
		public static function remove_credentials() {

			$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';

			if ( isset( $_POST['_nonce'] ) && wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {

				// delete user credentials.
				delete_option( 'cfm_authentication_id' );
				delete_option( 'cfm_authentication_key' );
				delete_transient( 'cfm_authentication_token' );
				update_option( 'cfm_authentication_date_removed', current_time( 'mysql' ) );

				// delete all shows.
				global $wpdb;
				$table_name = $wpdb->prefix . 'cfm_shows';
				$delete     = $wpdb->query( "TRUNCATE TABLE $table_name" );

				$output = 'success';

			}

			echo $output;

			wp_die();

		}

		/**
		 * Add podcast management to edit user profile
		 *
		 * @since 1.1.4
		 * @return html
		 */
		public static function add_user_podcast_management( $user ) {

			if ( user_can( $user->ID, 'manage_options' ) )
				return false;

			$shows = cfm_get_shows();
			$user_shows = get_user_meta( $user->ID, 'cfm_user_shows', true );

			if ( ! empty( $shows ) ) {

				echo '<h3>Podcast Management</h3>';

				echo '<table class="form-table"><tr>';

					echo '<th scope="row">User Shows</th>';
					echo '<td>';
						foreach ( $shows as $show ) {

							$checked = '';
							if ( ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) {
								$checked = ' checked="checked"';
							}

							echo '<p><label><input type="checkbox" name="user_show[]" value="' . esc_attr( $show['id'] ) . '"' . $checked . '> ' . esc_html( $show['title'] ) . '</label></p>';

						}
					echo '</td>';

				echo '</tr></table>';
			}

		}

		/**
		 * Update podcast management in edit user profile
		 *
		 * @since 1.1.4
		 * @return html
		 */
		public static function update_user_podcast_management( $user_id ) {

			if ( ! current_user_can( 'edit_user' ) )
				return false;

			update_user_meta( $user_id, 'cfm_user_shows', $_POST['user_show'] );

		}

		/**
		 * Update podcast management in edit user profile
		 *
		 * @since 1.2.3
		 * @return html
		 */
		public static function admin_footer() {

			echo '<div id="cfm-toast-container" class="cfm-toast-container"><div class="cfm-toaster"></div></div>';
		}

		/**
		 * Admin notices
		 *
		 * @since 2.0.0
		 * @return html
		 */
		public static function plugin_update_notice() {

		    $user = wp_get_current_user();
		    if ( get_option( 'cfm_plugin_updated' ) != '1' ) {
		    	echo '<div class="notice notice-warning"><p>Captivate Sync™ has been updated to version 2.0. To experience the new features, you may need to disable and re-enable the plugin.</p></div>';
		    }
		}

	}

endif;
