<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'CFM_Hosting_Shortcode' ) ) :

class CFM_Hosting_Shortcode  {

    public static function episodes_list( $atts ) {

		$output = '';
		static $i = 0; $i++;

		// attributes
		$a = shortcode_atts( array(
			'show_id' 			=> '',
			'layout' 			=> 'list',
			'columns' 			=> '3',
			'title' 			=> 'show',
			'image' 			=> 'show',
			'player' 			=> 'show',
			'link' 				=> 'show',
			'link_text' 		=> 'Listen to this episode',
			'content' 			=> 'show',
			'content_length' 	=> 55,
			'order' 			=> 'DESC',
			'items' 			=> 10,
			'pagination' 		=> 'show',
		), $atts );

		$atts['uuid'] = uniqid();

		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

		$get_episodes = array(
			'post_type'      => 'captivate_podcast',
			'posts_per_page' => (int) $a['items'],
			'orderby'		 => 'date',
			'order'          => $a['order'],
			'post_status'    => array( 'publish' ),
			'meta_query'     => array(
				array(
					'key'     => 'cfm_show_id',
					'value'   => $a['show_id'],
					'compare' => '=',
				),
			),
			'paged' => $paged,
		);

		$episodes = new WP_Query( $get_episodes );

		if ( $episodes->have_posts() ) :

			$layout_class = $a['layout'] == 'grid' ? 'cfm-episodes-grid' : 'cfm-episodes-list';
			$column_class = $a['layout'] == 'grid' ? ' cfm-episodes-cols-' . $a['columns'] : '';

			$output .= '<div id="cfm-episodes-' . $i . '" class="' . esc_attr( $layout_class ) . esc_attr( $column_class ) . '">';

				while ( $episodes->have_posts() ) :

					$episodes->the_post();
					$post_id = get_the_ID();
					$cfm_episode_id = get_post_meta( $post_id, 'cfm_episode_id', true );
					$featured_image_class = has_post_thumbnail( $post_id ) && ( $a['image'] == 'left' || $a['image'] == 'right' ) && $a['layout'] == 'list' ? ' cfm-has-image-beside' : '';
					$player = '<div class="cfm-episode-player"><div style="width: 100%; height: 200px; margin-bottom: 20px; border-radius: 6px; overflow:hidden;"><iframe style="width: 100%; height: 200px;" frameborder="no" scrolling="no" seamless allow="autoplay" src="https://player.captivate.fm/' . $cfm_episode_id . '"></iframe></div></div>';

					$output .= '<div class="cfm-episode-wrap' . $featured_image_class . '">';

						// featured image left container start.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
							$output .= '<div class="cfm-episode-image-left"><div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, 'medium' ) . '</a></div>';

						// featured image left container end, content right start.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
							$output .= '</div><div class="cfm-episode-content-right">';

						// content left start.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'right' && $a['layout'] == 'list' )
							$output .= '<div class="cfm-episode-content-left">';

						// featured image above title.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'above_title' )
							$output .= '<div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, 'large' ) . '</a></div>';

						// title.
						if ( $a['title'] == 'show' )
							$output .= '<div class="cfm-episode-title"><h2><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2></div>';

						// featured image below title.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'below_title' )
							$output .= '<div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, 'large' ) . '</a></div>';

						// player above content.
						if ( $a['player'] == 'above_content' )
							$output .= $player;

						// content excerpt.
						if ( $a['content'] == 'excerpt' )
							$output .= '<div class="cfm-episode-content">' . wp_trim_words( get_the_excerpt(), $a['content_length'], '...' ) . '</div>';

						// content full text.
						if ( $a['content'] == 'fulltext' )
							$output .= '<div class="cfm-episode-content">' . get_the_content() . '</div>';

						// player below content.
						if ( $a['player'] == 'below_content' )
							$output .= $player;

						// permalink.
						if ( $a['link'] == 'show' )
							$output .= '<div class="cfm-episode-link"><a href="' . get_permalink() . '">' . $a['link_text'] . '</a></div>';

						// content right end.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'left' && $a['layout'] == 'list' )
							$output .= '</div>';

						// content left end, featured image right container start.
						if ( has_post_thumbnail( $post_id ) && $a['image'] == 'right' && $a['layout'] == 'list' )
							$output .= '</div><div class="cfm-episode-image-right"><div class="cfm-episode-image"><a href="' . get_permalink() . '">' . get_the_post_thumbnail( $post_id, 'medium' ) . '</a></div></div>';

					$output .= '</div>';

				endwhile;

			$output .= '</div>';

			// pagination.
			if ( $a['pagination'] == 'show' ) {

				$GLOBALS['wp_query']->max_num_pages = $episodes->max_num_pages;
				$pagination = get_the_posts_pagination( array(
				   'mid_size' => 1,
				   'prev_text' => __( 'Previous' ),
				   'next_text' => __( 'Next' ),
				   'screen_reader_text' => __( 'Episodes navigation' )
				) );

				$output .= '<div class="cfm-episodes-pagination">' . $pagination . '</div>';
			}

			wp_reset_postdata();

		else :

			$output .= '<div><p>Nothing found. Please check your show id.</p></div>';

		endif;

		return $output;

    }

}

endif;