<?php
/**
 * Template page for episodes list
 *
 */
?>

<div class="wrap cfmh cfm-hosting-podcast-episodes">

	<div class="container-fluid">

		<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

		<div class="cfm-content-wrap mt-3">

			<div class="row">
				<div class="col-12 collapse-all">

					<div class="cfm-table cfm-data-table filter-enabled">
						<table id="cfm-datatable-episodes" class="table">
							<thead>
								<tr>
									<th class="cfm-th-num">#</th>
									<th class="cfm-th-title">Episode</th>
									<th class="cfm-th-date">Date</th>
									<th class="cfm-th-status">Status</th>
									<th class="cfm-th-share" width="20"></th>
									<th class="cfm-th-edit" width="60"></th>
									<th class="cfm-th-view" width="60"></th>
									<?php if ( current_user_can( 'delete_others_posts' ) ) : ?>
										<th class="cfm-th-icon" width="40"></th>
									<?php endif; ?>
								</tr>
							</thead>

							<tbody>
								<?php
								$show_id = cfm_get_show_id();

								$args     = array(
									'post_type'      => 'captivate_podcast',
									'posts_per_page' => -1,
									'order'          => 'DESC',
									'meta_query'     => array(
										array(
											'key'     => 'cfm_show_id',
											'value'   => $show_id,
											'compare' => '=',
										),
									),
								);
								$episodes = new WP_Query( $args );

								if ( $episodes->have_posts() ) {

									while ( $episodes->have_posts() ) {

										$episodes->the_post();
										$pid            = get_the_ID();
										$post_status    = get_post_status();
										$nonce = wp_create_nonce( 'trash_post_' . $pid );
										?>
										<tr>
											<td class="cfm-td-num">
												<?php
												$cfm_episode_itunes_type = get_post_meta( $pid, 'cfm_episode_itunes_type', true );
												$cfm_episode_itunes_number = get_post_meta( $pid, 'cfm_episode_itunes_number', true );

												if ( 'trailer' == $cfm_episode_itunes_type || 'bonus' == $cfm_episode_itunes_type ) {
													echo '<span class="text-capitalize">' . esc_html( $cfm_episode_itunes_type ) . '</span>';
												} else {
													echo '<span>' . esc_html( $cfm_episode_itunes_number ) . '</span>';
												}
												?>
											</td>
											<td class="cfm-td-title">
												<span><?php echo esc_html( get_the_title() ); ?></span>
												<p class="hidden">
													<?php
													if ( 'future' == $post_status ) {
														echo '<span class="text-warning">Scheduled</span>';
													} elseif ( 'publish' == $post_status ) {
														echo '<span class="text-success">Published</span>';
													} else {
														echo '<span class="text-secondary text-capitalize">' . esc_html( $post_status ) . '</span>';
													}
													?>
													<span><?php echo esc_html( get_the_date( 'jS F Y', $pid ) ); ?></span>
													<span>
														<a class="btn btn-secondary" href="#" data-toggle="modal" data-target="#modal-ep-<?php echo  esc_html( $pid ); ?>"><i class="fal fa-share-alt"></i></a>
														<a class="btn btn-light" href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $show_id . '&eid=' . $pid ) ); ?>"><i class="fal fa-edit"></i><span> Edit</span></a>
														<a class="btn btn-light" href="<?php echo esc_url( get_permalink() ); ?>" target="_blank"><i class="far fa-eye"></i><span> View</span></a>
														<?php if ( current_user_can( 'delete_others_posts' ) ) : ?>
															<a class="btn btn-outline-danger cfm-trash-episode" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-post-id="<?php echo esc_attr( $pid ); ?>" href="#"><i class="fal fa-trash-alt"></i></a>
														<?php endif; ?>
													</span>
												</p>
											</td>

											<td class="cfm-td-date" data-sort="<?php echo esc_attr( get_the_date( 'Y-m-d-H:i:s', $pid ) ); ?>"><?php echo esc_html( get_the_date( 'jS F Y', $pid ) ); ?></td>

											<td class="cfm-td-status <?php echo esc_attr( 'cfm-td-status-' . $post_status ); ?>">
												<?php
												if ( 'future' == $post_status ) {
													echo '<span class="text-warning">Scheduled</span>';
												} elseif ( 'publish' == $post_status ) {
													echo '<span class="text-success">Published</span>';
												} else {
													echo '<span class="text-secondary text-capitalize">' . esc_html( $post_status ) . '</span>';
												}
												?>
											</td>

											<td class="cfm-td-btn cfm-td-share"><a class="btn btn-secondary" href="#" data-toggle="modal" data-target="#modal-ep-<?php echo esc_attr( $pid ); ?>"><i class="fal fa-share-alt"></i></a></td>

											<td class="cfm-td-btn cfm-td-edit"><a class="btn btn-light" href="<?php echo esc_url(admin_url( 'admin.php?page=cfm-hosting-edit-episode&show_id=' . $show_id . '&eid=' . $pid ) ); ?>"><i class="fal fa-edit"></i><span> Edit</span></a></td>

											<td class="cfm-td-btn cfm-td-view"><a class="btn btn-light" href="<?php echo esc_url( get_permalink() ); ?>" target="_blank"><i class="far fa-eye"></i><span> View</span></a></td>

											<?php if ( current_user_can( 'delete_others_posts' ) ) : ?>
												<td class="cfm-td-btn cfm-td-delete"><a class="btn btn-outline-danger cfm-trash-episode" data-nonce="<?php echo esc_attr( $nonce ); ?>" data-post-id="<?php echo esc_attr( $pid ); ?>" href="#"><i class="fal fa-trash-alt"></i></a></td>
											<?php endif; ?>

											<!-- Share modal -->
											<div class="modal fade" id="modal-ep-<?php echo esc_attr( $pid ); ?>" tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-lg" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h5 class="modal-title">Share: <?php echo esc_attr( get_the_title() ); ?></h5>
															<button type="button" class="close" data-dismiss="modal" aria-label="Close">
															<span aria-hidden="true">&times;</span>
														</button>
														</div>

														<div class="modal-body">
															<div class="row mt-4 align-items-end">
																<div class="col-sm-8"><h6>Paste this link into your social posts</h6></div>
																<div class="col-sm-4 text-right">
																	<a class="clipboard cb-tooltip btn btn-outline-success btn-table mb-2" data-clipboard-target="#clipboard-ep-link-<?php echo esc_attr( $pid ); ?>" title="Player URL copied"><span>Copy</span></a>
																</div>
															</div>
															<div class="row">
																<div class="col-12">
																	<div id="clipboard-ep-link-<?php echo esc_attr( $pid ); ?>" class="border p-3 mt-2">
																	<?php echo esc_url( get_bloginfo( 'url' ) . '/' . cfm_get_show_page( $show_id, 'slug' ) . '/' . get_post_field( 'post_name', $pid ) ); ?>
																	</div>
																</div>
															</div>

															<div class="row mt-4 align-items-end">
																<div class="col-sm-8"><h6>Embed on another website</h6></div>
																<div class="col-sm-4 text-right">
																	<a class="clipboard cb-tooltip btn btn-outline-success btn-table mb-2" data-clipboard-target="#clipboard-ep-embed-<?php echo esc_attr( $pid ); ?>" title="Website embed code copied"><span>Copy</span></a>
																</div>
															</div>
															<div class="row">
																<div class="col-12">
																	<div id="clipboard-ep-embed-<?php echo esc_attr( $pid ); ?>" class="border p-3 mt-2">
																	<?php echo esc_html( '<div style="width: 100%; height: 200px; margin-bottom: 20px; border-radius: 6px; overflow:hidden;"><iframe style="width: 100%; height: 200px;" frameborder="no" scrolling="no" seamless src="' . CFMH_PLAYER_URL . '/episode/' . get_post_meta( $pid, 'cfm_episode_id', true ) . '"></iframe></div>' ); ?>
																	</div>
																</div>
															</div>

															<div class="row mt-4 align-items-end">
																<div class="col-sm-8"><h6>Direct audio file URL</h6></div>
																<div class="col-sm-4 text-right">
																	<a class="clipboard cb-tooltip btn btn-outline-success btn-table mb-2" data-clipboard-target="#clipboard-ep-audio-<?php echo esc_attr( $pid ); ?>" title="Audio file URL copied"><span>Copy</span></a>
																</div>
															</div>
															<div class="row">
																<div class="col-12">
																	<div id="clipboard-ep-audio-<?php echo esc_attr( $pid ); ?>" class="border p-3 mt-2">
																	<?php echo esc_html( get_post_meta( $pid, 'cfm_episode_media_url', true ) ); ?>
																	</div>
																</div>
															</div>
														</div>

														<div class="modal-footer">
															<button type="button" class="btn btn-warning" data-dismiss="modal">Close</button>
														</div>
													</div>
												</div>
											</div>
											<!-- /Share modal -->
										</tr>
										<?php
									}

									wp_reset_postdata();
								} else {
									$colspan = current_user_can( 'delete_others_posts' ) ? '8' : '7';
									echo '<tr><td colspan="' . $colspan . '">0 episodes found.</td></tr>';
								}

								?>
							</tbody>
						</table>
					</div>

				</div>
			</div>

		<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

		</div><!--/ .cfm-content-wrap -->

	</div><!--/ .container-fluid -->

</div><!--/ .wrap -->
