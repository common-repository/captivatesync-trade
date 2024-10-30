<?php
/**
 * Used to enqueue the sync process JS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'CFMH_Hosting_Sync' ) ) :

	/**
	 * Hosting Sync class
	 *
	 * @since 1.0
	 */
	class CFMH_Hosting_Sync {

		/**|
		 * Enqueue sync JS
		 *
		 * @since 1.0
		 */
		public static function assets() {

			$current_screen = get_current_screen();

			$allowed_screens = array( 'toplevel_page_cfm-hosting-podcasts' );

			if ( in_array( $current_screen->id, $allowed_screens ) ) :

				// cfm sync js.
				wp_enqueue_script( 'cfm-sync', CFMH_URL . 'captivate-sync-assets/js/sync-shows-min.js', array( 'jquery' ), CFMH_VERSION, true );

			endif;

		}

	}

endif;
