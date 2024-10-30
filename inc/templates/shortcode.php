<?php
/**
 * Template page for shortcode
 */
?>

<div class="wrap cfmh cfm-hosting-shortcode">

	<?php require CFMH . 'inc/templates/template-parts/header.php'; ?>
	
	<?php $shows = cfm_get_shows(); $user_shows = get_user_meta( get_current_user_id(), 'cfm_user_shows', true ); ?>
	
	<div id="cfm-message" class="cfm-message"></div>
	
	<div class="cfm-content-wrap">

		<div class="cfm-shows">

			<div class="row">
				
				<div class="col-lg-6">
				
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="shortcode_show">Show</label></th>
								<td>
									<?php
									if ( ! empty( $shows ) ) {
										
										echo '<select name="shortcode_show">';

										foreach ( $shows as $show ) {
											
											if ( current_user_can( 'manage_options' ) || ( ! current_user_can( 'manage_options' ) && ! empty( $user_shows ) && in_array( $show['id'], $user_shows ) ) ) {
												
												echo '<option value="' . $show['id'] . '">' . esc_attr( $show['title'] ) . '</option>';
												
											}
										}
										
										echo '<select/>';
									}
									?>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_layout">Layout</label></th>
								<td>
									<select name="shortcode_layout">
										<option value="list">List</option>
										<option value="grid">Grid</option>
									</select>
								</td>
							</tr>
							<tr id="tr-shortcode-column" class="hidden">
								<th scope="row"><label for="shortcode_column">Column</label></th>
								<td>
									<select name="shortcode_column">
										<option value="2">2</option>
										<option value="3">3</option>
										<option value="4">4</option>
										<option value="5">5</option>
										<option value="6">6</option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_image">Image</label></th>
								<td>
									<select name="shortcode_image">
										<option value="above_title">Above Title</option>
										<option value="below_title">Below Title</option>
										<option value="left" class="shortcode_image_left">Left</option>
										<option value="right" class="shortcode_image_right">Right</option>
										<option value="hide">Hide</option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_content">Content</label></th>
								<td>
									<select name="shortcode_content">
										<option value="excerpt">Excerpt</option>
										<option value="fulltext">Full Text</option>
										<option value="hide">Hide</option>
									</select>
								</td>
							</tr>
							<tr id="tr-shortcode-content-length">
								<th scope="row"><label for="shortcode_content_length">Content Length</label></th>
								<td>
									<input type="number" name="shortcode_content_length" value="55">
								</td>
							</tr>
						</tbody>
					</table>

				</div>
				
				<div class="col-lg-6">
				
					<table class="form-table" role="presentation">
						<tbody>
							<tr>
								<th scope="row"><label for="shortcode_player">Player</label></th>
								<td>
									<select name="shortcode_player">
										<option value="above_content">Above Content</option>
										<option value="below_content">Below Content</option>
										<option value="hide">Hide</option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_link">Link</label></th>
								<td>
									<select name="shortcode_link">
										<option value="hide">Hide</option>
										<option value="show">Show</option>
									</select>
								</td>
							</tr>
							<tr id="tr-shortcode-link-text" class="hidden">
								<th scope="row"><label for="shortcode_link_text">Link Text</label></th>
								<td>
									<input type="text" name="shortcode_link_text" value="Listen to this episode">
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_order">Order</label></th>
								<td>
									<select name="shortcode_order">
										<option value="desc">Descending</option>
										<option value="asc">Ascending</option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_items">Number of Episodes</label></th>
								<td>
									<input type="number" name="shortcode_items" value="10">
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="shortcode_pagination">Pagination</label></th>
								<td>
									<select name="shortcode_pagination">
										<option value="show">Show</option>
										<option value="hide">Hide</option>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
				
				</div>

			</div>
			
			<div class="row mb-4">
				<div class="col-lg-12">
					<div class="cfm-submit mt-4">			
						<button type="button" id="generate_shortcode" name="generate_shortcode" class="btn btn-outline-primary full-md-button">Generate Shortcode</button>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-lg-12">
					<div class="generated_shortcode">
						<div class="row mt-4">
							<div class="col-sm-8"><p class="mt-3"><strong>Paste this shortcode into your website page</strong></p></div>
							<div class="col-sm-4 text-right">
								<a class="clipboard cb-tooltip btn btn-outline-success btn-table mb-2" data-clipboard-target="#clipboard-shortcode" title="" data-original-title="Shortcode copied"><span>Copy</span></a>
							</div>
						</div>
						<div class="row">
							<div class="col-12">
								<div id="clipboard-shortcode" class="border p-3 mt-2">
									[cfm_captivate_episodes show_id=""]
								</div>
							</div>
						</div>
					</div>
				</div>
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
				<button type="button" class="btn btn-primary select-shows" name="selectShows"><i class="hide fas fa-spinner fa-spin"></i> Select &amp; Sync Shows</button>
			</div>
		</div>
	</div>
</div>
