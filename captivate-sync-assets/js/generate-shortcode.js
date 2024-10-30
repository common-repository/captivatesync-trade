jQuery( document ).ready(
	function($) {

		$( document ).on(
			'change',
			'select[name=shortcode_layout]',
			function(e) {
				if ( 'grid' == $( 'select[name=shortcode_layout]' ).val() ) {
					$('#tr-shortcode-column').fadeIn();
					$('select[name=shortcode_image] .shortcode_image_left').hide();
					$('select[name=shortcode_image] .shortcode_image_right').hide();
					
					if ( $('select[name=shortcode_image').val() == 'left' || $('select[name=shortcode_image').val() == 'right' ) {
						$('select[name=shortcode_image').val("above_title");
					}
				}
				else {
					$('#tr-shortcode-column').fadeOut();
					$('select[name=shortcode_image] .shortcode_image_left').show();
					$('select[name=shortcode_image] .shortcode_image_right').show();
				}
			}
		);
		
		$( document ).on(
			'change',
			'select[name=shortcode_content]',
			function(e) {
				if ( 'excerpt' == $( 'select[name=shortcode_content]' ).val() ) {
					$('#tr-shortcode-content-length').fadeIn();
				}
				else {
					$('#tr-shortcode-content-length').fadeOut();
				}
			}
		);
		
		$( document ).on(
			'change',
			'select[name=shortcode_link]',
			function(e) {
				if ( 'show' == $( 'select[name=shortcode_link]' ).val() ) {
					$('#tr-shortcode-link-text').fadeIn();
				}
				else {
					$('#tr-shortcode-link-text').fadeOut();
				}
			}
		);

		$( document ).on(
			'click',
			'#generate_shortcode',
			function(e) {

				var show_id    			= $( 'select[name=shortcode_show]' ).val(),
					layout    			= $( 'select[name=shortcode_layout]' ).val(),
					column    			= $( 'select[name=shortcode_column]' ).val(),
					image    			= $( 'select[name=shortcode_image]' ).val(),
					content   			= $( 'select[name=shortcode_content]' ).val(),
					content_length   	= $( 'input[name=shortcode_content_length]' ).val(),
					player    			= $( 'select[name=shortcode_player]' ).val(),
					link    			= $( 'select[name=shortcode_link]' ).val(),
					link_text    		= $( 'input[name=shortcode_link_text]' ).val(),
					order    			= $( 'select[name=shortcode_order]' ).val(),
					items    			= $( 'input[name=shortcode_items]' ).val(),
					pagination    		= $( 'select[name=shortcode_pagination]' ).val();

				var shortcode_columns = '';
				if ( 'grid' == layout ) {
					shortcode_columns = 'columns="' + column + '"';
				}
				
				var shortcode_content_length = '';
				if ( 'excerpt' == content ) {
					shortcode_content_length = 'content_length="' + content_length + '"';
				}
				
				var shortcode_link_text = '';
				if ( 'show' == link ) {
					shortcode_link_text = 'link_text="' + link_text + '"';
				}

				var shortcode = '[cfm_captivate_episodes show_id="' + show_id + '" layout="' + layout + '" ' + shortcode_columns + ' image="' + image + '" content="' + content + '" ' + shortcode_content_length + ' player="' + player + '" link="' + link + '" ' + shortcode_link_text + ' order="' + order + '" items="' + items + '" pagination="' + pagination + '"]';

				$('#clipboard-shortcode').html(shortcode);
				
				$('html, body').animate({
					scrollTop: $(".generated_shortcode").offset().top
				}, 700).delay(100);
				
				cfmsync_toaster('success', 'Shortcode updated');
				
			}
		);
			
	}
);
