<?php
/**
 * User authentication template
 */
?>

<div class="wrap cfmh cfm-hosting-credentials">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>
		
	<div id="cfm-message" class="cfm-message"></div>
	
	<?php
	$response = isset( $_GET['response'] ) ? sanitize_text_field( wp_unslash( $_GET['response'] ) ) : 0;
	if ( 2 == $response ) {
		echo '<div class="row"><div class="col-12"><div class="cfm-error"><p><strong>ERROR:</strong> Please fill in the required fields.</p></div></div></div>';}

	if ( 3 == $response ) {
		echo '<div class="row"><div class="col-12"><div class="cfm-error"><p><strong>ERROR:</strong> Authentication ID and key already exists.</p></div></div></div>';}

	if ( 4 == $response ) {
		echo '<div class="row"><div class="col-12"><div class="cfm-error"><p><strong>ERROR:</strong> Authentication token already exists.</p></div></div></div>';}
	?>
	
	<div class="cfm-content-wrap">
	
		<?php if ( ! cfm_is_logged_in() ) : ?>
	
			<div class="row">
				
				<div class="col-lg-3"></div>
				
				<div class="col-lg-6">
					
					<div class="media show-object">
						<div class="media-body">
							<form id="cfm-form-generate-credentials" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							
								<?php wp_nonce_field( '_sec_action', '_sec' ); ?>
								<input type="hidden" name="action" value="form_create_credentials">
								
								<div class="form-group">
									<label for="auth_id">User ID</label>
									<input type="text" class="form-control" id="auth_id" name="auth_id">
								</div>
								
								<div class="form-group">
									<label for="auth_key">API Key</label>
									<input type="text" class="form-control" id="auth_key" name="auth_key">
								</div>
								
								<button type="submit" name="createCredentials" class="btn btn-outline-success btn-sm">Authenticate website</button>

							</form>

						</div>

					</div>
					
				</div>
				
				<div class="col-lg-3"></div>

			</div>

			<div class="cfm-tutorial-link">
				<a href="https://help.captivate.fm/en/articles/3440133-how-to-find-your-captivate-api-details" target="_blank">How to find your API details</a>
			</div>
		
		<?php else : ?>
		
			<div class="row">
				
				<div class="col-lg-3"></div>
				
				<div class="col-lg-6 text-center">
				
					<div class="media show-object">
						<div class="media-body">
							<h5 class="mt-0 mb-1">Authenticated on <?php echo esc_html( gmdate( 'F j, Y H:ia', strtotime( get_option( 'cfm_authentication_date_added' ) ) ) ); ?></h5>

							<button type="submit" name="removeCredentials" class="btn btn-outline-success btn-sm mt-4">Remove User</button>
						</div>

					</div>
				
				</div>
				
				<div class="col-lg-3"></div>
				
			</div>
		
		<?php endif; ?>
	
	</div><!--/ .cfm-content-wrap -->

	<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>
	
</div><!--/ .wrap -->
