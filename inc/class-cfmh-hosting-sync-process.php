<?php
/**
 * Used to process shows and episodes sync
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Sync_Process' ) ) :

	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 0 );
	}

	/**
	 * Hosting Sync Process class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Sync_Process {

		/**
		 * Sync shows
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function sync_shows() {

			$current_shows = cfm_get_show_ids();

			foreach ( $current_shows as $show_id ) {

				$sync_show = cfm_sync_shows( $show_id );

				if ( false == $sync_show ) {
					$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';
				}
			}

			if ( ! $output ) {
				$output = 'Sync complete!';
			}

			echo $output;

			wp_die();

		}

		/**
		 * Select shows
		 *
		 * @since 1.0
		 * @return void
		 */
		public static function select_shows() {

			if ( ! isset( $_POST['_nonce'] ) || ! wp_verify_nonce( $_POST['_nonce'], '_cfm_nonce' ) ) {
				$output = '<strong>ERROR:</strong> Something went wrong! Please contact the support team.';
			} else {

				$current_shows 	= cfm_get_show_ids();
				$shows         	= isset( $_POST['shows'] ) ? wp_unslash( $_POST['shows'] ) : array();
				$selected_shows	= array();

				if ( is_array( $shows ) && ! empty( $shows ) ) {
					foreach ( $shows as $id ) {
						$selected_shows[] = sanitize_text_field( $id );
					}
				}

				$to_remove = array_diff( $current_shows, $selected_shows );

				update_option( 'cfm_sync_shows', json_encode( $selected_shows ) );
				$errors           = array();
				$output           = array();
				$output['return'] = false;

				if ( ! empty( $selected_shows ) ) {
					foreach ( $selected_shows as $show_id ) {

						$webhook            = array();
						$webhook['webhook'] = get_site_url( null, '/wp-json/captivate-sync/v1/sync', null );

						if ( in_array( $show_id, $current_shows ) ) {

							cfm_sync_shows( $show_id );

						} else {

							$sync_shows = wp_remote_request(
								CFMH_API_URL . '/shows/' . $show_id . '/sync',
								array(
									'timeout' => 500,
									'method'  => 'PUT',
									'body'    => $webhook,
									'headers' => array(
										'Authorization' => 'Bearer ' . get_transient( 'cfm_authentication_token' ),
									),
								)
							);

							// Debugging.
							if ( cfm_is_debugging_on() ) {
								$log_date = date( 'Y-m-d H:i:s', time() );
								$txt = '**SYNC SELECT SHOWS - ' . $log_date . '** ' . PHP_EOL . print_r( $sync_shows, true ) . '**END SYNC SELECT SHOWS**';
								$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
							}

							if ( ! is_wp_error( $sync_shows ) && 'Unauthorized' !== $sync_shows['body'] && is_array( $sync_shows ) ) {

								$sync_shows = json_decode( $sync_shows['body'] );

								$success[] = array(
									'id'      => $show_id,
									'success' => $sync_shows->success,
									'error'   => false == $sync_shows->success ? $sync_shows->errors[0] : false,
								);

								if ( $sync_shows->success ) {

									cfm_sync_shows( $show_id, $sync_shows->sync_key );

								}
							} else {

								$errors = "Can't connect to Captivate Sync.";

							}
						}
					}
				}

				if ( ! empty( $to_remove ) ) {
					foreach ( $to_remove as $show_id ) {

						$remove_shows = wp_remote_request(
							CFMH_API_URL . '/shows/' . $show_id . '/sync',
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
							$txt = '**SYNC REMOVE SHOWS - ' . $log_date . '** ' . PHP_EOL . print_r( $remove_shows, true ) . '**END SYNC REMOVE SHOWS**';
							$myfile = file_put_contents( CFMH . '/logs.txt', PHP_EOL . $txt . PHP_EOL , FILE_APPEND | LOCK_EX );
						}

						if ( ! is_wp_error( $remove_shows ) && 'Unauthorized' !== $remove_shows['body'] && is_array( $remove_shows ) ) {

							$remove_shows = json_decode( $remove_shows['body'] );

							if ( $remove_shows->success ) {
								cfm_remove_show( $show_id );
							}
						} else {

							$errors = "Can't connect to Captivate Sync.";

						}
					}
				}

				$output['return'] = $success;

			}

			echo json_encode( $output );

			wp_die();

		}

	}

endif;
