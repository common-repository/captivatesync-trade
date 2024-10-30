<?php
/**
 * Template page for shows list
 */
?>

<div class="wrap cfmh cfm-hosting-podcasts">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

	<?php $shows = cfm_get_shows(); $user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true ); ?>

	<div id="cfm-message" class="cfm-message"></div>

	<div class="cfm-content-wrap">

		<div class="cfm-shows">

			<div class="row">

				<?php if ( current_user_can( 'manage_options' ) ) : ?>
					<div class="col-12">

						<div class="mb-4">

							<?php
							if ( isset( $_GET['page'] ) && ( 'cfm-hosting-publish-episode' != sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) ) :

								if ( ! empty( $shows ) ) {
									echo '<button name="syncShows" class="btn btn-secondary btn-sm mr-4">Manually Sync Show Data</button>';
								}
								?>

								<button type="button" name="CFMPickShows" class="btn btn-secondary btn-sm">Add/Remove Shows</button>

							<?php endif; ?>

						</div>

					</div>
				<?php endif; ?>

				<?php

				if ( ! empty( $shows ) ) {

					foreach ( $shows as $show ) {

						if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {
						?>

						<div class="col-lg-6 mb-4">

							<div id="show_<?php echo esc_attr( $show['id'] ); ?>" class="cfm-show-wrap">

								<div class="media show-object">

									<div class="media-body">

										<div class="row">

											<div class="col-sm-4 mb-4 mb-sm-0">
												<img class="img-fluid mb-2" src="<?php echo esc_attr( $show['artwork'] ); ?>" alt="<?php echo esc_attr( $show['title'] ); ?>'s Artwork" width="160" height="160">

												<button class="btn btn-secondary cfmsync-tooltip cfm-clear-publish-data" aria-hidden="true" title="" data-placement="bottom" data-original-title="Clear publish saved data.">Clear saved data</button>
											</div>

											<div class="col-sm-8">
												<h5 class="mt-0 mb-1"><?php echo esc_html( cfm_limit_characters( $show['title'], 30 ) ); ?></h5>

												<div class="small last-sync">
													<strong>Last Sync:</strong> <?php echo esc_html( gmdate( 'Y-m-d h:ia', strtotime( $show['last_synchronised'] ) ) ); ?>
												</div>

												<div class="row mt-2">
													<div class="col-lg-6 mb-2 mb-lg-0">
														<?php
														wp_dropdown_pages(
															array(
																'name' => 'page_for_show',
																'id'   => 'show_' . $show['id'],
																'show_option_none' => __( 'Page Mapping' ),
																'option_none_value' => '0',
																'class' => 'form-control',
																'selected' => cfm_get_show_info(
																	$show['id'],
																	'index_page'
																),
															)
														);
														?>
													</div>

													<div class="col-lg-6 mb-2 mb-lg-0">
														<?php
														$query_users_ids_by_role = [
															'fields' => ['id'],
															'role__in' => ['administrator', 'editor', 'author'],
														];
														$array_of_users = get_users( $query_users_ids_by_role );
														$array_of_users_ids = array_map( function ( $user ) {
															return $user->id;
														}, $array_of_users );
														$users_ids_list = implode( ',', $array_of_users_ids );

														wp_dropdown_users(
															array(
																'name' => 'author_for_show',
																'id'   => 'author_' . $show['id'],
																'show'   => 'display_name_with_login',
																'show_option_none' => __( 'Author' ),
																'option_none_value' => '0',
																'class' => 'form-control',
																'include' => $users_ids_list,
																'selected' => cfm_get_show_info(
																	$show['id'],
																	'author'
																),
															)
														);
														?>
													</div>
												</div>

												<div class="mt-2">
													<?php
													// always '1' if not exists/empty or checked.
													$display_episodes = cfm_get_show_info( $show['id'], 'display_episodes' ) == '0' ? '0' : '1';
													?>

													<label><input type="checkbox" name="display_episodes" id="<?php echo 'display_' . esc_attr( $show['id'] ); ?>" value="1" <?php checked( $display_episodes, '1' ); ?>> Display episodes on the selected page?</label>
												</div>

												<hr>

												<div class="d-flex justify-content-between">
													<a target="_blank" href="https://my.captivate.fm/dashboard/podcast/<?php echo esc_attr( $show['id'] ); ?>/settings" class="btn btn-sm btn-secondary float-left mr-1">Show Settings</a>

													<a href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-publish-episode&show_id=' . $show['id'] ) ); ?>" class="btn btn-sm btn-primary float-right ml-1">Publish Episode</a>
												</div>

											</div>
										</div>

									</div>

								</div>
							</div>
						</div>

						<?php } ?>
					<?php } ?>

				<?php } else { ?>

					<div class="col-12">

						<div class="alert alert-warning">
							No shows synchronized to this website, yet.
						</div>

					</div>


				<?php } ?>

			</div>

		</div>

		<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

	</div><!--/ .cfm-content-wrap -->

</div><!--/ .wrap -->

<!-- Modal -->
<div class="modal fade" id="SyncShows" tabindex="-1" role="dialog">
	<div class="modal-dialog  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="SyncShowsLabel">My Shows</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="cfm-sync-progress"></div>
				<ul class="cfm-sync-shows"></ul>
				<div class="cfm-sync-add-show" style="display: none;">
					You haven’t created any podcasts with Captivate yet. <a href="https://my.captivate.fm/dashboard/shows/new-podcast" target="_blank">Create your first show</a>.
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary float-left" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary select-shows" name="selectShows">Select &amp; Sync Shows</button>
			</div>
		</div>
	</div>
</div>
