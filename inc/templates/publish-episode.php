<?php
/**
 * Template page for publish/edit episode
 */

$show_id 		= cfm_get_show_id();
$episode_id 	= isset( $_GET['eid'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['eid'] ) ) : 0;
$is_edit 		= 0 != $episode_id ? true : false;
$post_status 	= get_post_status( $episode_id );
$user_shows 	= get_user_meta( get_current_user_id(), 'cfm_user_shows', true );

if ( ! cfm_is_show_exists( $show_id ) ) {
	wp_die( '<p>Show does not exists.</p>', '', array( 'link_url' => esc_url( admin_url( 'admin.php?page=pw-dashboard' ) ), 'link_text' => 'Return to Dashboard' ) );
}

if ( $is_edit && ( 'trash' == $post_status || false === $post_status ) ) {
	wp_die( '<p>Episode does not exists.</p>', '', array( 'link_url' => esc_url( admin_url( 'admin.php?page=pw-dashboard' ) ), 'link_text' => 'Return to Dashboard' ) );
}

if ( ! current_user_can( 'manage_options' ) && (  empty( $user_shows ) || ( ! empty( $user_shows ) && ! in_array( $show_id, $user_shows ) ) ) ) {
	wp_die( '<p>Sorry, you are not allowed to access this page.</p>', '', array( 'link_url' => esc_url( admin_url( 'admin.php?page=pw-dashboard' ) ), 'link_text' => 'Return to Dashboard' ) );
}
?>

<div class="wrap cfmh cfm-hosting-publish-episode">

	<?php
	$artwork_id      = get_post_meta( $episode_id, 'cfm_episode_artwork_id', true );
	$artwork_url     = get_post_meta( $episode_id, 'cfm_episode_artwork', true );
	$artwork_url     = ( $artwork_url ) ? $artwork_url : cfm_get_show_info( $show_id, 'artwork' );
	$featured_image  = get_the_post_thumbnail_url( $episode_id, 'medium' );

	$artwork_width   	 = get_post_meta( $episode_id, 'cfm_episode_artwork_width', true );
	$artwork_height  	 = get_post_meta( $episode_id, 'cfm_episode_artwork_height', true );
	$artwork_type    	 = get_post_meta( $episode_id, 'cfm_episode_artwork_type', true );
	$artwork_filesize    = get_post_meta( $episode_id, 'cfm_episode_artwork_filesize', true );

	$post_title      = get_the_title( $episode_id );
	$post_name       = get_post_field( 'post_name', $episode_id );
	$post_author     = get_post_field( 'post_author', $episode_id );
	$post_excerpt 	 = get_post_field( 'post_excerpt', $episode_id );
	$comment_status	 = get_post_field( 'comment_status', $episode_id );
	$ping_status 	 = get_post_field( 'ping_status', $episode_id );
	$editor_type 	 = get_post_meta( $episode_id, 'cfm_enable_wordpress_editor', true);
	$custom_field 	 = get_post_meta( $episode_id, 'cfm_episode_custom_field', true);
	$itunes_title    = get_post_meta( $episode_id, 'cfm_episode_itunes_title', true );
	$itunes_subtitle = get_post_meta( $episode_id, 'cfm_episode_itunes_subtitle', true );
	$itunes_season   = get_post_meta( $episode_id, 'cfm_episode_itunes_season', true );
	$itunes_number   = get_post_meta( $episode_id, 'cfm_episode_itunes_number', true );
	$itunes_type     = get_post_meta( $episode_id, 'cfm_episode_itunes_type', true );
	$itunes_explicit = get_post_meta( $episode_id, 'cfm_episode_itunes_explicit', true );
	$donation_link   = get_post_meta( $episode_id, 'cfm_episode_donation_link', true );
	$donation_label  = get_post_meta( $episode_id, 'cfm_episode_donation_label', true );
	$seo_title       = get_post_meta( $episode_id, 'cfm_episode_seo_title', true );
	$seo_description = get_post_meta( $episode_id, 'cfm_episode_seo_description', true );

	$media_id        = get_post_meta( $episode_id, 'cfm_episode_media_id', true );
	$media_url       = get_post_meta( $episode_id, 'cfm_episode_media_url', true );
	$media_type      = get_post_meta( $episode_id, 'cfm_episode_media_type', true );
	$media_size      = get_post_meta( $episode_id, 'cfm_episode_media_size', true );
	$media_duration  = get_post_meta( $episode_id, 'cfm_episode_media_duration', true );

	$image_id 				= get_post_meta( $episode_id, '_thumbnail_id', true );
	$seo_description_width  = $seo_description ? (strlen($seo_description) / 155 * 100) : 0;
	$seo_description_width  = $seo_description_width >= 100 ? 100 : $seo_description_width;
	$seo_description_color  = "orange";

	if ( $seo_description_width >= 50 && $seo_description_width <= 99 ) {
		$seo_description_color = "#29ab57";
	} else if ( $seo_description_width >= 100 ) {
		$seo_description_color = "#dc3545";
	}

	$transcript 	= get_post_meta( $episode_id, 'cfm_episode_transcript', true);
	$is_transcript  = is_array( $transcript ) && ( ( null != $transcript['transcription_file'] && '' != $transcript['transcription_file'] ) || ( null != $transcript['transcription_text'] && '' != $transcript['transcription_text'] ) ) ? true : false;
	?>

	<div class="container-fluid">

		<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>

		<div class="cfm-content-wrap">

			<div class="row">
				<div class="col-12 collapse-all">
					<form id="cfm-form-publish-episode" name="cfm-form-publish-episode" enctype="multipart/form-data" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

						<?php wp_nonce_field( '_sec_action_' . $episode_id, '_sec' ); ?>

						<input type="hidden" name="action" value="form_publish_episode">
						<input type="hidden" name="post_id" value="<?php echo esc_attr( $episode_id ); ?>">
						<input type="hidden" name="show_id" value="<?php echo esc_attr( $show_id ); ?>">
						<input type="hidden" name="media_id" value="<?php echo esc_attr( $media_id ); ?>">
						<input type="hidden" name="media_url" value="<?php echo esc_attr( $media_url ); ?>">
						<input type="hidden" name="media_type" value="<?php echo esc_attr( $media_type ); ?>">
						<input type="hidden" name="media_size" value="<?php echo esc_attr( $media_size ); ?>">
						<input type="hidden" name="media_duration" value="<?php echo esc_attr( $media_duration ); ?>">

						<input type="hidden" name="submit_action" value="draft">

						<?php
						$response = isset( $_GET['response'] ) ? sanitize_text_field( wp_unslash( $_GET['response'] ) ) : 0;

						if ( 1 == $response ) {
							echo '<div class="row"><div class="col-12"><div class="cfm-error alert alert-danger"><strong>ERROR:</strong> Please fill in the required fields.</div></div></div>';}

						if ( 2 == $response ) {
							echo '<div class="row"><div class="col-12"><div class="cfm-success alert alert-info">Episode published and synchronized to your Captivate account, too. <a href="' . esc_url( get_permalink($episode_id) ).'" target="_blank">View Episode</a></div></div></div>';}

						if ( 3 == $response ) {
							echo '<div class="row"><div class="col-12"><div class="cfm-success alert alert-info">Episode updated and synchronized to your Captivate account, too. <a href="' . esc_url( get_permalink($episode_id) ).'" target="_blank">View Episode</a></div></div></div>';}

						if ( 4 == $response ) {
							echo '<div class="row"><div class="col-12"><div class="cfm-error alert alert-danger"><strong>ERROR:</strong> You haven\'t got the right access to this show.</div></div></div>';}

						if ( 5 == $response ) {
							echo '<div class="row"><div class="col-12"><div class="cfm-error alert alert-danger"><strong>ERROR:</strong> There\'s no selected show.</div></div></div>';}

						if ( 6 == $response ) {
							echo '<div class="row"><div class="col-12"><div class="cfm-error alert alert-danger"><strong>ERROR:</strong> API error, please contact support with error code 12.</div></div></div>';}
						?>

						<div id="cfm-episode-upload-preloader" class="hidden">
							<div class="cfm-episode-upload-message"></div>
							<div class="cfm-episode-upload-progress"><div class="progress-bar"></div></div>
						</div>

						<!-- Podcast Uploader - Publish -->
						<?php if ( ! $is_edit ) : ?>
						<div id="cfm-episode-uploader" class="cfm-episode-uploader">
							<div class="row">
								<div class="col-12">
									<div class="podcast-uploader clearfix">

										<div id="podcast-dropzone" class="dropzone podcast-dropzone hidden">
											<div class="fallback hidden">
												<input name="file" type="file" />
											</div>
										</div>

										<div class="upload-actions">
											<button id="upload-audio" type="button" class="btn btn-outline-success">Upload Audio File</button>
											<a id="upload-skip" class="btn btn-outline-success" href="javascript: void(0);">Skip audio upload for now</a>
										</div>

									</div>
								</div>
							</div>
						</div>
						<?php endif; ?>
						<!-- /Podcast Uploader -->

						<div id="cfm-episode-details" class="hidden cfm-episode-details mt-4"<?php echo $is_edit ? ' style="display: block;"' : ''; ?>>

							<div class="row">
								<div class="col-lg-4 order-2 order-lg-1">
									<div class="cfm-episode-settings-left pr-lg-5">

										<div class="cfm-field cfm-artwork-upload mb-4">

												<img id="artwork-preview" src="<?php echo esc_attr( $artwork_url ); ?>" width="400" height="400" class="img-fluid">

												<div id="artwork-dropzone">
													<span><i class="fas fa-plus-circle " aria-hidden="true"></i>Add episode specific cover art</span>
												</div>

												<input type="hidden" name="episode_artwork" id="episode-artwork" value="<?php echo esc_attr( $artwork_url ); ?>" class="regular-text" />
												<input type="hidden" name="episode_artwork_id" id="episode-artwork-id" value="<?php echo esc_attr( $artwork_id ); ?>" class="regular-text" />
												<input type="hidden" name="episode_artwork_width" id="episode-artwork-width" value="<?php echo esc_attr( $artwork_width ); ?>" class="regular-text" />
												<input type="hidden" name="episode_artwork_height" id="episode-artwork-height" value="<?php echo esc_attr( $artwork_height ); ?>" class="regular-text" />
												<input type="hidden" name="episode_artwork_type" id="episode-artwork-type" value="<?php echo esc_attr( $artwork_type ); ?>" class="regular-text" />
												<input type="hidden" name="episode_artwork_filesize" id="episode-artwork-filesize" value="<?php echo esc_attr( $artwork_filesize ); ?>" class="regular-text" />

										</div>

										<div class="cfm-field cfm-episode-audio<?php echo ( $is_edit && $media_url ) ? '' : ' hidden'; ?>">
											<label>CURRENT AUDIO FILE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="This is the audio file that will be played for this episode."></i></label>

											<p><i class="far fa-play-circle uploaded-audio-play"></i> <span><a class="uploaded-audio-name" href="#"><i class="fas fa-file-audio"></i> <?php echo ( $is_edit && $media_url ) ? esc_html( basename( $media_url ) ) : ''; ?></span></a></p>

											<audio id="audio-player" class="hidden" preload="none" controls="">
												<source src="<?php echo ( $is_edit && $media_url ) ? esc_attr( $media_url ) : ''; ?>" type="audio/mp3"/>
											</audio>
										</div>

										<div class="cfm-field cfm-episode-audio-replace<?php echo ( $is_edit && $media_url ) ? '' : ' hidden'; ?>">
											<label class="label-checkbox">
												<input id="audio-replace" type="checkbox" class="form-checkbox">
												Replace the audio file on this episode?
											</label>
										</div>

										<div class="cfm-field cfm-episode-audio-upload<?php echo ( $is_edit && $media_url ) ? ' hidden' : ''; ?>">
											<!-- Podcast Uploader - Edit -->
											<?php if ( $is_edit ) : ?>
											<div id="cfm-episode-uploader" class="cfm-episode-uploader">
												<div class="row">
													<div class="col-12">
														<div class="podcast-uploader clearfix">

															<div id="podcast-dropzone" class="dropzone podcast-dropzone hidden">
																<div class="fallback">
																	<input name="file" type="file" />
																</div>
															</div>

															<div class="upload-actions">
																<button id="upload-audio" type="button" class="btn btn-outline-success">Upload Audio File</button>
															</div>

														</div>
													</div>
												</div>
											</div>
											<?php endif; ?>
											<!-- /Podcast Uploader - Edit -->
										</div>

										<div class="cfm-field cfm-episode-transcription mt-4">
											<label>TRANSCRIPTION</label>

											<div class="cmf-transcript-wrap">
												<?php
												$transcript_content = '';
												if ( $is_transcript ) {
													$transcript_content = '';
													if ( 'file' == $transcript['transcription_uploaded'] ) {
							    						$transcript_content = basename( $transcript['transcription_file'] );
													}
													else {
														$transcript_content = $transcript['transcription_text'];
													}

													echo '<strong>' . esc_html( cfm_limit_characters( $transcript_content, $limit = 20, $readmore = '...' ) ) . '</strong> <a id="transcript-edit" class="float-right" data-toggle="modal" data-target="#transcript-modal" data-backdrop="static" data-keyboard="false" href="#">Edit</a><div class="mt-2"><a id="transcript-remove" class="transcript-remove text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a></div>';
												}
												else {
													echo '<a id="transcript-add" data-toggle="modal" data-target="#transcript-modal" data-backdrop="static" data-keyboard="false" href="#">Add a transcript to this episode </a>';
												}
												?>
											</div>

											<textarea name="transcript_current" id="transcript_current" class="hidden"><?php echo esc_attr( $transcript_content ); ?></textarea>
											<input type="hidden" name="transcript_type" id="transcript_type" value="<?php echo $is_transcript ? esc_attr( $transcript['transcription_uploaded'] ) : 'text'; ?>" />
											<input type="hidden" name="transcript_updated" id="transcript_updated" value="0" />

											<!-- Transcription Modal -->
											<div class="modal fade modal-slideout" id="transcript-modal" tabindex="-1" role="dialog" aria-hidden="true">
												<div class="modal-dialog modal-dialog-slideout" role="document">
													<div class="modal-content">
														<div class="modal-header">
															<h4 class="modal-title">Transcription</h4>
															<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
														</div>
														<div class="modal-body">
															<div class="mb-4"><strong>Tip:</strong> make sure you follow the sample format below, otherwise your transcription may not appear properly in podcast apps that support this feature. </div>

															<textarea name="transcriptText" id="transcriptText" rows="10" placeholder="Alfred 00:00&#10;Will you be wanting the Batpod, sir?&#10;&#10;Bruce 00:20&#10;In the middle of the day, Alfred? Not very subtle.&#10;&#10;Alfred 00:30&#10;The Lamborghini, then." class="form-control"<?php echo ( $is_transcript && 'file' == $transcript['transcription_uploaded'] ) ? ' disabled="disabled"' : ''; ?>><?php echo ( $is_transcript && 'text' == $transcript['transcription_uploaded'] ) ? esc_attr( $transcript['transcription_text'] ) : ''; ?></textarea>

															<div class="transcript-upload-box<?php echo ( $is_transcript && 'text' == $transcript['transcription_uploaded'] ) ? ' disabled' : ''; ?>">
																<?php
																if ( $is_transcript && 'file' == $transcript['transcription_uploaded'] ) {
																	echo '<div class="transcript-text">File uploaded: <strong>' . basename( $transcript['transcription_file'] ) . '</strong></div><a id="remove-transcript-file" class="text-danger" href="javascript: void(0);"><i class="fal fa-trash-alt"></i> Remove</a>';
																}
																else {
																	echo '<div class="transcript-text">Have a transcript file? Upload it directly... </div><a id="upload-transcript" href="javascript: void(0);"><i class="fal fa-cloud-upload" aria-hidden="true"></i> Upload File</a>';
																}
																?>
															</div>
															<input class="hidden" name="transcriptFile" id="transcriptFile" type="file" onclick="this.value=null;" accept=".srt" />
														</div>
														<div class="modal-footer">
															<button type="button" id="cancel-transcript" class="btn btn-outline-dark" data-dismiss="modal">Cancel</button>
															<button type="button" id="update-transcript" class="btn btn-outline-success float-right">Update Transcript</button>
														</div>
													</div>
												</div>
											</div>
											<!-- /Transcription Modal -->
										</div>

										<div class="cfm-field cfm-episode-publish-date mt-4">
											<label for="publish_date">PUBLISH DATE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Changing the publish date will change the date shown in your feed and may affect the order of your episodes. If the episode is published in the past it will become a published episode."></i></label>

											<div class="cmf-datepicker-wrap">
												<?php
												$show_timezone = cfm_get_show_info( $show_id, 'time_zone' );
												$date_today = new DateTime( 'now', new DateTimeZone( $show_timezone ) );
												$publish_date = $date_today->format( 'm/d/Y' );

												if ( $is_edit ) {
													$publish_date = get_the_date( 'm/d/Y', $episode_id );
												}
												?>
												<input type="text" class="form-control" id="publish_date" name="publish_date" value="<?php echo esc_attr( $publish_date ); ?>">
											</div>
										</div>

										<div class="cfm-field cfm-episode-publish-time mt-4">
											<label for="publish_time">PUBLISH TIME <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="The time that you'd like this episode to publish on the date you have selected."></i></label>

											<div class="row">
												<div class="col-sm-6">

													<div class="cfm-timepicker dropdown show">
														<?php
														$default_publish_time = cfm_get_show_info( $show_id, 'default_time' );

														if ( $is_edit ) {
															$publish_time = get_the_date( 'H:i', $episode_id );
														} else {
															$publish_time = ( $default_publish_time ) ? $default_publish_time : '09:00';
														}
														?>

														<input type="text" class="form-control dropdown-toggle" id="publish_time" name="publish_time" value="<?php echo esc_attr( $publish_time ); ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" autocomplete="off">

														<div class="dropdown-menu" aria-labelledby="publish_time">
															<?php
																$now = new DateTime();
																$end = clone $now;
																$end->modify( '+12 hours' );

																$timeframe = '00:00';

																echo '<a class="dropdown-item">00:00</a>';
																while ( $timeframe <= '23:30' ) {
																	$timeframe = date( 'H:i', strtotime( '+15 minutes', strtotime( $timeframe ) ) );
																	echo '<a class="dropdown-item">' . $timeframe . '</a>';
																}
															?>
														</div>
													</div>

												</div>
											</div>
										</div>

										<div class="cfm-field-heading mt-5">Website Information</div>

										<div class="cfm-field-group cfm-featured-image-upload mt-4">

											<label for="website_featured_image">WEBSITE FEATURED IMAGE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Website Featured Image."></i></label>

											<img id="featured-image-preview" class="img-fluid featured-image-preview<?php echo ( $featured_image ) ? ' active' : ''; ?>" src="<?php echo esc_attr( $featured_image ); ?>" width="400" height="400">

											<input type="hidden" name="featured_image" id="featured_image" value="<?php echo esc_attr( $image_id ); ?>" class="regular-text" />

											<div class="row">
												<!-- <div class="col-xl-1"></div> -->
												<div class="col-xl-12 text-center">
													<input type="button" class="btn btn-outline-success" id="<?php echo ( $featured_image ) ? 'featured-image-remove' : 'featured-image-upload'; ?>" value="<?php echo ( $featured_image ) ? 'Remove featured image' : 'Set featured image'; ?>">
												</div>
												<!-- <div class="col-xl-1"></div> -->
											</div>

										</div>

										<?php if ( current_user_can( 'edit_others_posts' ) ) : ?>
											<div class="cfm-field cfm-episode-author mt-4">
												<label for="post_author">AUTHOR <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Episode author."></i></label>

												<?php
												$author_id = ( $is_edit ) ? (int) $post_author : cfm_get_show_author( $show_id );

												wp_dropdown_users( array(
													'name'	 				=> 'post_author',
													'class' 				=> 'form-control',
													'selected' 				=> $author_id,
													'include_selected' 		=> true
												) );
												?>
											</div>
										<?php endif; ?>

										<div class="cfm-field cfm-field-list-check cfm-episode-website-categories mt-4">
											<label for="website_category">WEBSITE CATEGORIES <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Website Categories."></i></label>

											<div class="cfm-website-categories-wrap">
												<?php
												$cat_post_id = $episode_id;
												$args        = array(
													'descendants_and_self'  => 0,
													'selected_cats'         => false,
													'popular_cats'          => false,
													'walker'                => null,
													'taxonomy'              => 'captivate_category',
													'checked_ontop'         => false,
												);
												echo '<ul>';
													wp_terms_checklist( $cat_post_id, $args );
												echo '</ul>';
												?>
											</div>

											<div class="cfm-category-parent mt-2">
												<?php
												$args = array(
													'show_option_all' => '',
													'show_option_none' => '— Parent Category —',
													'option_none_value' => '-1',
													'orderby' => 'name',
													'order' => 'ASC',
													'show_count' => 0,
													'hide_empty' => 0,
													'child_of' => 0,
													'exclude' => '',
													'include' => '',
													'echo' => 1,
													'selected' => 0,
													'hierarchical' => 1,
													'name' => 'category_parent',
													'id'   => '',
													'class' => 'form-control',
													'depth' => 0,
													'tab_index' => 0,
													'taxonomy' => 'captivate_category',
													'hide_if_empty' => false,
													'value_field' => 'term_id',
												);
												wp_dropdown_categories( $args );
												?>
											</div>

											<div class="input-group mt-2">
												<input type="text" class="form-control" id="website_category" name="website_category" placeholder="Add new category">

												<span class="input-group-append">
													<button type="button" id="add_website_category" class="input-group-text">Add</button>
												</span>
											</div>
										</div>

										<div class="cfm-field cfm-field-list-check cfm-episode-website-tags mt-4">
											<label for="website_tags">WEBSITE TAGS <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Website Tags."></i></label>

											<div class="cfm-website-tags-wrap">
												<?php
												$tag_post_id = $episode_id;
												$args        = array(
													'descendants_and_self'  => 0,
													'selected_cats'         => false,
													'popular_cats'          => false,
													'walker'                => null,
													'taxonomy'              => 'captivate_tag',
													'checked_ontop'         => true,
												);
												echo '<ul>';
													wp_terms_checklist( $tag_post_id, $args );
												echo '</ul>';
												?>
											</div>

											<div class="input-group mt-2">
												<input type="text" class="form-control" id="website_tags" name="website_tags" placeholder="Separate tags with commas">

												<span class="input-group-append">
													<button type="button" id="add_website_tags" class="input-group-text">Add</button>
												</span>
											</div>

										</div>

										<div class="cfm-field cfm-field-list-check cfm-episode-website-discussion mt-4">
											<label for="website_discussion">WEBSITE DISCUSSION <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Enable/disable comments and pingbacks &amp; trackbacks."></i></label>

											<div class="cfm-website-discussion-wrap">
												<?php
												$comment_status_check = $is_edit ? $comment_status : get_default_comment_status( 'captivate_podcast', 'comment' );
												$ping_status_check = $is_edit ? $ping_status : get_default_comment_status( 'captivate_podcast', 'pingback' );
												?>
												<ul>
													<li><label class="selectit"><input type="checkbox" name="website_comment" value="" <?php checked( $comment_status_check, 'open' ); ?>> Allow comments</label></li>

													<li><label class="selectit"><input type="checkbox" name="website_ping" value="" <?php checked( $ping_status_check, 'open' ); ?>> Allow pingbacks &amp; trackbacks</label></li>
												</ul>
											</div>
										</div>

									</div>
								</div>

								<div class="col-lg-8 order-1 order-lg-2">
									<div class="cfm-episode-settings-right">

										<div class="cfm-field cfm-episode-title">
											<label for="post_title">EPISODE TITLE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Used in podcast players, e.g. Apple Podcasts, Spotify; this title will display in all podcast directories and players."></i></label>

											<input type="text" class="form-control<?php echo '' == $post_title ? ' post-title-empty' : ''; ?>" id="post_title" name="post_title" value="<?php echo esc_attr( $post_title ); ?>">
										</div>

										<div class="cfm-field cfm-episode-subtitle mt-4">
											<label for="itunesSubtitle">EPISODE SUBTITLE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Used in some podcast players, non-mandatory."></i></label>

											<input type="text" class="form-control" id="itunesSubtitle" name="itunesSubtitle" value="<?php echo esc_attr( $itunes_subtitle ); ?>">
										</div>

										<div class="cfm-field cfm-itunes-episode-title-check mt-4">
											<label class="label-checkbox">
												<input type="checkbox" class="form-checkbox" id="post_title_check" name="post_title_check" value="" <?php echo ( $is_edit && '' != $itunes_title ) ? 'checked="checked"' : ''; ?>>
												Display a different episode title on Apple Podcasts? <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Use this if you would like to display a different episode title in Apple Podcasts, for example a title without the episode number in it."></i>
											</label>
										</div>

										<div class="cfm-field cfm-itunes-episode-title mt-2 hidden"<?php echo ( $is_edit && '' != $itunes_title ) ? ' style="display: block;"' : ''; ?>>
											<label for="itunesTitle">APPLE PODCASTS TITLE</label>

											<input type="text" class="form-control" id="itunesTitle" name="itunesTitle" value="<?php echo esc_attr( $itunes_title ); ?>">
										</div>

										<div class="cfm-field cfm-show-description mt-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="post_content">EPISODE SHOW NOTES</label>
												</div>

												<div class="col-sm-6">
													<div class="custom-control custom-switch float-right">
														<input name="enable_wordpress_editor" type="checkbox" <?php echo $editor_type == 'on' ? 'checked' : ''; ?> class="custom-control-input" id="enable_wordpress_editor">
														<label class="custom-control-label" for="enable_wordpress_editor">Use WordPress Editor</label>
													</div>
												</div>
											</div>

											<?php
											$content = '';
											if ( $is_edit ) {
												$post    = get_post( $episode_id, OBJECT, 'edit' );
												$content = $post->post_content;
											}
											?>

											<div class="cfm-show-captivate-editor <?php echo $editor_type != 'on' ? '' : 'hidden'; ?>">

												<?php require CFMH . 'inc/templates/template-parts/ql-toolbar.php'; ?>

												<div id="cfm-field-wpeditor"><?php echo wpautop($content); ?></div>

												<textarea name="post_content" id="post_content" class="hidden"><?php echo wpautop($content); ?></textarea>

											</div>

											<div class="cfm-show-wordpress-editor <?php echo $editor_type == 'on' ? '' : 'hidden'; ?>">
												<?php
												$settings = array( 'editor_height' => 250 );
												$editor_id = 'post_content_wp';
												wp_editor( $content, $editor_id, $settings );
												?>
											</div>

										</div>

										<div class="cfm-field cfm-episode-type mt-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="itunesEpisodeType">EPISODE TYPE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Most episodes will be 'normal' episodes, but sometimes you'll create show trailers or bonus content that displays differently depending on the listener's podcast player."></i></label>
												</div>

												<div class="col-sm-6">
													<select class="form-control" name="itunesEpisodeType" id="itunesEpisodeType">
														<option value="full" <?php selected( $itunes_type, 'full' ); ?>>Normal (default)</option>
														<option value="trailer" <?php selected( $itunes_type, 'trailer' ); ?>>Trailer</option>
														<option value="bonus" <?php selected( $itunes_type, 'bonus' ); ?>>Bonus</option>
													</select>
												</div>
											</div>

										</div>

										<div class="cfm-field cfm-season-number mt-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="itunesEpisodeSeason">SEASON NUMBER</label>
												</div>

												<div class="col-sm-6">
													<div class="input-group input-group-number">
														<span class="input-btn-left input-group-prepend">
															<button type="button" class="btn-number input-group-text decrease" disabled="disabled" data-type="minus" data-field="itunesEpisodeSeason">-</button>
														</span>

														<input type="text" id="itunesEpisodeSeason" name="itunesEpisodeSeason" min="0" max="5000" value="<?php echo ( $itunes_season ) ? esc_attr( $itunes_season ) : '0'; ?>" class="form-control input-number">

														<span class="input-btn-right input-group-append">
															<button type="button" class="btn-number input-group-text increase" data-type="plus" data-field="itunesEpisodeSeason">+</button>
														</span>
													</div>
												</div>

											</div>

										</div>

										<div class="cfm-field cfm-episode-number mt-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="itunesEpisodeNumber">EPISODE NUMBER</label>
												</div>

												<div class="col-sm-6">
													<div class="input-group input-group-number">
														<span class="input-btn-left input-group-prepend">
															<button type="button" class="btn-number input-group-text decrease" disabled="disabled" data-type="minus" data-field="itunesEpisodeNumber">-</button>
														</span>

														<input type="text" id="itunesEpisodeNumber" name="itunesEpisodeNumber" min="0" max="5000" value="<?php echo ( $itunes_number ) ? esc_attr( $itunes_number ) : '0'; ?>" class="form-control input-number">

														<span class="input-btn-right input-group-append">
															<button type="button" class="btn-number input-group-text increase" data-type="plus" data-field="itunesEpisodeNumber">+</button>
														</span>
													</div>
												</div>

											 </div>

										</div>

										<div class="cfm-field cfm-episode-explicit mt-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="itunesExplicit">MARK AS EXPLICIT? <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="If many of your episodes contain explicit language, set this to 'yes' in show settings. It's vital that you make sure this is right!"></i></label>
												</div>

												<div class="col-sm-6">
													<select class="form-control" name="itunesExplicit" id="itunesExplicit">
														<option value="0" <?php selected( $itunes_explicit, '0' ); ?>>Use show default</option>
														<option value="explicit" <?php selected( $itunes_explicit, 'explicit' ); ?>>Yes</option>
														<option value="clean" <?php selected( $itunes_explicit, 'clean' ); ?>>No</option>
													</select>
												</div>
											</div>

										</div>

										<div class="cfm-field-heading mt-5">Donations</div>

										<div class="cfm-field cfm-donation-link mt-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="donationLink">DONATION LINK OVERRIDE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Override the default donation link set in show settings for this episode only."></i></label>
												</div>

												<div class="col-sm-6">
													<input type="url" class="form-control" id="donationLink" name="donationLink" placeholder="https://patreon.com/7mm" value="<?php echo esc_attr( $donation_link ); ?>">
												</div>
											</div>

										</div>

										<div class="cfm-field cfm-donation-label mt-4 mb-4">

											<div class="row align-items-center">
												<div class="col-sm-6">
													<label for="donationLabel">DONATION LABEL TEXT OVERRIDE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Override the default donation text set in show settings for this episode only. Note: not widely supported by podcast players, yet."></i></label>
												</div>

												<div class="col-sm-6">
													<input type="text" class="form-control" id="donationLabel" name="donationLabel" placeholder="Support the show" value="<?php echo esc_attr( $donation_label ); ?>">
												</div>
											</div>

										</div>

										<div class="cfm-field-heading mt-5">Episode Page SEO</div>

										<div class="cfm-field cfm-seo-title mt-4">
											<label for="seoTitle">SEO TITLE <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="The title shown in search engine results and social shares (defaults to your episode title if empty)."></i></label>

											<input type="text" class="form-control" id="seoTitle" name="seoTitle" value="<?php echo esc_attr( $seo_title ); ?>">
										</div>

										<div class="cfm-field cfm-seo-description mt-4">
											<label for="seoDescription">SEO DESCRIPTION <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="The description shown in search engine results and social shares."></i></label>

											<textarea class="form-control" id="seoDescription" name="seoDescription"><?php echo esc_attr( $seo_description ); ?></textarea>

											<div class="cfm-seo-description-count">
												<div class="cfm-seo-description-progress" style="width: <?php echo $seo_description_width; ?>%; background: <?php echo $seo_description_color; ?>"></div>
											</div>

										</div>

										<div class="cfm-field cfm-website-excerpt mt-4">
											<label for="post_excerpt">WEBSITE EXCERPT <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="The short description shown on your website."></i></label>
											<textarea rows="4" class="form-control" name="post_excerpt" id="post_excerpt"><?php echo esc_attr( $post_excerpt ); ?></textarea>
										</div>

										<?php if ( class_exists( 'PWFT' ) ) : ?>
										<div class="cfm-field cfm-website-custom-field mt-4">
											<label for="custom_field">WEBSITE CUSTOM FIELD <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="Website custom field."></i></label>
											<textarea rows="4" class="form-control" name="custom_field" id="custom_field"><?php echo esc_attr( $custom_field ); ?></textarea>
										</div>
										<?php endif; ?>

										<div class="cfm-field cfm-episode-slug mt-4 mb-4">
											<label for="post_name">EPISODE PERMALINK <i class="fal fa-info-circle pl-2 cfmsync-tooltip" aria-hidden="true" title="" data-placement="bottom" data-original-title="The page specific URL."></i> <a id="cfm-edit-slug" class="ml-2" href="javascript:void(0);">Edit</a></label>

											<input type="text" class="form-control" id="post_name" name="post_name" value="<?php echo esc_attr( $post_name ); ?>" disabled="disabled">

											<input type="hidden" id="new_post_name" name="new_post_name" value="<?php echo $is_edit ? esc_attr( $post_name ) : ''; ?>">
										</div>

									</div>
								</div>
							</div>

							<div class="cfm-field cfm-publish-options mt-5 mb-5">

								<div class="row">

									<div class="col-md-6 order-2 order-md-1">
										<div class="text-left cfm-submit">
											<a id="episode-cancel" href="<?php echo esc_url( admin_url( 'admin.php?page=cfm-hosting-podcasts' ) ); ?>" class="btn btn-outline-secondary float-left full-md-button">Cancel</a>
										</div>
									</div>

									<div class="col-md-6 order-1 order-md-2">

										<div class="text-right cfm-submit">

											<button type="submit" id="episode_draft" name="episode_draft" class="btn btn-outline-info full-md-button">Save As Draft</button>

											<?php
											if ( $is_edit ) {

												if ( 'future' == $post_status || 'publish' == $post_status ) {
													echo '<button type="submit" id="episode_update" name="episode_update" class="btn btn-outline-primary full-md-button ml-5">Update Episode</button>';
												}
												if ( 'draft' == $post_status ) {
													echo '<button type="submit" id="episode_update" name="episode_update" class="btn btn-outline-primary full-md-button ml-5" >Publish Episode</button>';
												}

											} else {

												echo '<button type="submit" id="episode_publish" name="episode_publish" class="btn btn-outline-primary full-md-button ml-5" disabled="disabled">Publish Episode</button>';

											}
											?>
										</div>

									</div>
								</div>

							</div>

						</div>

					</form>
				</div>
			</div>

		<?php require CFMH . 'inc/templates/template-parts/footer.php'; ?>

		</div><!--/ .cfm-content-wrap -->

	</div><!--/ .container-fluid -->

</div><!--/ .wrap -->
