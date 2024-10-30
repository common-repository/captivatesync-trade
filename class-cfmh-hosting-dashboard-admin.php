<?php
/**
 * Used for admin data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Dashboard_Admin' ) ) :

	set_time_limit( 0 );

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

			$allowed_screens = array(
				'toplevel_page_cfm-hosting-podcasts',
				'captivate-sync_page_cfm-hosting-podcasts',
				'toplevel_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-edit-episode',
				'admin_page_cfm-hosting-edit-episode',
				'captivate-sync_page_cfm-hosting-podcast-episodes',
				'admin_page_cfm-hosting-podcast-episodes',
				'captivate-sync_page_cfm-hosting-credentials',
				'admin_page_cfm-hosting-credentials',
				'captivate-sync_page_cfm-hosting-migration',
				'admin_page_cfm-hosting-migration',
			);

			if ( in_array( $current_screen->id, $allowed_screens ) || ( 0 === strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) ) ) :

				// vendors.
				wp_register_style( 'cfmsync-bootstrap', CFMH_URL . 'vendor/bootstrap/bootstrap.min.css', array(), '4.3.1', 'all' );
				wp_register_style( 'cfmsync-google-fonts', '//fonts.googleapis.com/css?family=Poppins:300,400,500,700' );
				wp_register_style( 'cfmsync-font-awesome', CFMH_URL . 'vendor/fontawesome-pro/css/all.css', array(), '5.11.2', 'all' );
				wp_register_script( 'cfmsync-clipboard', CFMH_URL . 'vendor/clipboard/clipboard.min.js', array( 'jquery' ), '2.0.0', true );

				// cfm.
				wp_register_script( 'cfmsync', CFMH_URL . 'captivate-sync-assets/js/admin-min.js', array(), CFMH_VERSION, true );

				wp_localize_script(
					'cfmsync',
					'cfmsync',
					array(
						'CFMH'          => CFMH,
						'CFMH_URL'      => CFMH_URL,
						'CFMH_ADMINURL' => admin_url(),
						'CFMH_SHOWID'   => cfm_get_show_id(),
						'ajaxurl'       => admin_url( 'admin-ajax.php' ),
						'ajaxnonce'     => wp_create_nonce( '_cfm_nonce' ),
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

				wp_register_style( 'cfmsync', CFMH_URL . 'captivate-sync-assets/css/admin.css', array(), CFMH_VERSION, 'all' );

				// cfm.
				wp_enqueue_script( 'cfmsync' );
				wp_enqueue_style( 'cfmsync' );

			endif;

			$allowed_screens2 = array(
				'toplevel_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-edit-episode',
			);

			if ( in_array( $current_screen->id, $allowed_screens2 ) ) :

				wp_enqueue_media();
				wp_enqueue_script( 'quilljs', CFMH_URL . 'vendor/quill/quill.min.js', array(), '1.3.6' );
				wp_enqueue_style( 'quilljs', CFMH_URL . 'vendor/quill/quill.snow.css', array(), '1.3.62' );
				wp_enqueue_script( 'quilljs-script', CFMH_URL . 'captivate-sync-assets/js/quilljs-min.js', array(), '1.3.6' );

				wp_enqueue_style( 'jquery-ui-theme', CFMH_URL . 'vendor/jquery-ui/jquery-ui.min.css', array(), '1.12.1' );
				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_enqueue_script( 'dropzone', CFMH_URL . 'vendor/dropzone/dropzone.min.js', array(), CFMH_VERSION );
				wp_enqueue_style( 'dropzone', CFMH_URL . 'vendor/dropzone/dropzone.min.css' );

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
				'toplevel_page_cfm-hosting-podcasts',
				'captivate-sync_page_cfm-hosting-podcasts',
				'toplevel_page_cfm-hosting-publish-episode',
				'captivate-sync_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-publish-episode',
				'admin_page_cfm-hosting-edit-episode',
				'captivate-sync_page_cfm-hosting-podcast-episodes',
			);

			if ( in_array( $current_screen->id, $data_tables ) || ( strpos( $current_screen->id, 'captivate-sync_page_cfm-hosting-podcast-episodes_' ) === 0 ) ) :
				wp_enqueue_style( 'cfm-data-tables', CFMH_URL . 'vendor/datatables/jquery.dataTables.min.css', array(), '1.10.19' );
				wp_enqueue_style( 'cfm-data-tables-style', CFMH_URL . 'captivate-sync-assets/css/data-tables.css', array(), '1.10.19' );
				wp_enqueue_script( 'cfm-data-tables', CFMH_URL . 'vendor/datatables/jquery.dataTables.min.js', array(), '1.10.19', true );
				wp_enqueue_script( 'cfm-data-tables-js', CFMH_URL . 'captivate-sync-assets/js/data-tables-min.js', array(), '1.10.19', true );

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

			$main_menu_slug = ! empty( $shows ) ? 'cfm-hosting-publish-episode' : 'cfm-hosting-podcasts';
			$main_menu_sub  = ! empty( $shows ) ? 'publish_episode' : 'my_podcasts';

			add_menu_page( 'Captivate Sync&trade;', 'Captivate Sync&trade;', 'edit_posts', $main_menu_slug, array( 'CFMH_Hosting_Dashboard_Admin', $main_menu_sub ), CFMH_URL . 'captivate-sync-assets/img/menu-icon.png' );

			if ( ! empty( $shows ) ) {
				add_submenu_page( $main_menu_slug, 'Publish A New Episode', 'Publish Episode', 'edit_posts', 'cfm-hosting-publish-episode', array( 'CFMH_Hosting_Dashboard_Admin', 'publish_episode' ), null );
				add_submenu_page( 'options.php', 'Edit podcast episode', 'Edit Episode', 'edit_posts', 'cfm-hosting-edit-episode', array( 'CFMH_Hosting_Dashboard_Admin', 'publish_episode' ), null );
			}

			add_submenu_page( $main_menu_slug, 'My Shows', 'My Shows', 'edit_posts', 'cfm-hosting-podcasts', array( 'CFMH_Hosting_Dashboard_Admin', 'my_podcasts' ), null );

			if ( ! empty( $shows ) ) {
				foreach ( $shows as $show ) {
					add_submenu_page( $main_menu_slug, $show['title'], $show['title'], 'edit_posts', 'cfm-hosting-podcast-episodes_' . $show['id'], array( 'CFMH_Hosting_Dashboard_Admin', 'my_podcast_episodes' ), null );
				}
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

					$index_page_info = array();
					
					$index_page_info['captivate_sync_url'] = get_permalink( $_POST['page_id'] );
					
					$update_index_page = wp_remote_request(
						CFMH_API_URL . '/shows/' . $_POST['show_id'] . '/sync/url',
						array(
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
						
						cfm_update_show_info( $_POST['show_id'], 'index_page', $_POST['page_id'] );
						
						$output = 'success';

					}
				
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

				// delete all episodes.
				cfm_delete_episodes( $post_type = 'captivate_podcast' );

				$output = 'success';

			}

			echo $output;

			wp_die();

		}

	}

endif;
