<?php
/**
 * Header template
 */

$page_slug = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
?>


<div class="cfm-page-heading">
	<div class="row align-items-center mb-5">

		<div class="col-md-6 order-md-1 order-2">
		
			<h2 class="cfm-page-title"><?php echo esc_html( get_admin_page_title() ); ?></h2>

		</div>
		
		<div class="col-md-6 order-1">

			<a target="_blank" href="https://my.captivate.fm/"><img src="<?php echo esc_url( CFMH_URL ); ?>captivate-sync-assets/img/captivate-sync-black.png" class="float-right" width="220px" height="auto"></a>
			
		</div>

	</div>
</div>
