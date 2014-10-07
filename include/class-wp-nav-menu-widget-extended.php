<?php


class WP_Nav_Menu_Widget_Extended extends WP_Nav_Menu_Widget {


	public function widget($args, $instance) {
		// Get menu from theme location
		
		$nav_menu_opts = array( 
		);
		
		if ( ! empty( $instance['nav_menu_location'] ) ) {
			$menu_locations = get_registered_nav_menus();
			if ( isset( $menu_locations[ $instance['nav_menu_location'] ] ) )
				$nav_menu_opts['theme_location'] = $instance['nav_menu_location'];
		}
		
		// No menu. Maybe menu was selected directly
		if ( ! empty($instance['nav_menu']) ) {
			if ( $instance['nav_menu'] < 0 ) {
				$nav_menu_opts['fallback_cb'] = 'wp_page_menu';
				$nav_menu_opts['theme_location'] = '---none-'.time(); 
					// Hacky! If no theme location given WP will just take the first menu with items. 
					// We want it to fall back to page menu here, so we give an invalid theme location.
				$nav_menu = $instance['nav_menu'];
			} else if ( $instance['nav_menu'] > 0 ) {
				$nav_menu_opts['fallback_cb'] = '';
				$nav_menu_opts['menu'] = wp_get_nav_menu_object( $instance['nav_menu'] );
			} else {
				$nav_menu = false;
				$nav_menu_opts['menu'] = false;
			}
		}

		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];

		if ( !empty($instance['title']) )
			echo $args['before_title'] . $instance['title'] . $args['after_title'];
		?><div><?php
			wp_nav_menu($nav_menu_opts);
		?></div><?php
		echo $args['after_widget'];
		
	}
	public function update( $new_instance, $old_instance ) {
		
		$instance = parent::update( $new_instance, $old_instance );
		if ( ! empty( $new_instance['nav_menu_location'] ) && array_key_exists( $new_instance['nav_menu_location'] , get_registered_nav_menus() ) ) {
			$instance['nav_menu_location'] = $new_instance['nav_menu_location'];
		}
		return $instance;
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$nav_menu_location = isset( $instance['nav_menu_location'] ) ? $instance['nav_menu_location'] : '';
		
		$menu_locations = get_registered_nav_menus();
		
		// Get menus
		$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
		if ( ! $menus )
			$menus = array();
		
		array_unshift( $menus , (object) array(
			'term_id' => -1,
			'name' => __('Pages'),
		) );
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu_location'); ?>"><?php _e('Same as in Location:','wp-nav-menu-extension'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu_location'); ?>" name="<?php echo $this->get_field_name('nav_menu_location'); ?>">
				<option value=""><?php _e( '&mdash; Select &mdash;' ) ?></option>
				<?php
					foreach ( $menu_locations as $location => $description ) {
						echo '<option value="' . $location . '"'
							. selected( $nav_menu_location, $location, false )
							. '>'. esc_html( $description ) . '</option>';
					}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
				<option value="0"><?php _e( '&mdash; Select &mdash;' ) ?></option>
		<?php
			foreach ( $menus as $menu ) {
				echo '<option value="' . $menu->term_id . '"'
					. selected( $nav_menu, $menu->term_id, false )
					. '>'. esc_html( $menu->name ) . '</option>';
			}
		?>
			</select>
		</p>
		<script type="text/javascript">
		(function($){
			$(document).on('change','#<?php echo $this->get_field_id('nav_menu_location'); ?>',function(){
				var $nav_menu_select = $('#<?php echo $this->get_field_id('nav_menu'); ?>');
				console.log( $(this).val(),!! $(this).val())
				if ( !! $(this).val() )
					$nav_menu_select.attr('disabled','disabled');
				else 
					$nav_menu_select.removeAttr('disabled');
			})
			$('#<?php echo $this->get_field_id('nav_menu_location'); ?>').trigger('change');
		})(jQuery);
		</script>
		<?php
	}
}
