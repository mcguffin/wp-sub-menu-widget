<?php


class Sub_Menu_Widget extends WP_Widget {


	public function __construct() {
		$widget_ops = array( 'description' => __('Add a submenu to your sidebar.','wp-sub-menu-widget') );
		parent::__construct( 'sub_menu', __('Submenu','wp-sub-menu-widget'), $widget_ops );
	}

	public function widget($args, $instance) {
		// Get menu from theme location
		
		$nav_menu_opts = array( 
			'falback_cb' => array(&$this,'_pages_menu'),
			'echo' => false,
		);
		
		$nav_menu = false;
		
		if ( ! empty($instance['nav_menu']) ) {
			if ( ! is_numeric( $instance['nav_menu'] ) ) { // theme location
				$menu_locations = get_nav_menu_locations(); 
				$menus = get_registered_nav_menus();
				// location exists
				if ( isset( $menus[ $instance['nav_menu'] ] ) ) {
					$nav_menu_opts['theme_location'] = $instance['nav_menu'];
					if (  $menu_locations[ $instance['nav_menu'] ] ) {
						// actual nav menu
						$nav_menu_opts['walker'] = new Walker_Sub_Menu();
					} else {
						// force pages fallback
						$nav_menu_opts['theme_location'] = '---none-'.time(); 
						$nav_menu_opts['fallback_cb'] = array(&$this,'_pages_menu');
						// Hacky! If no theme location given WP will just take the first menu with items. 
						// We want it to fall back to page menu here, so we give an invalid theme location.
					}
				}
				$nav_menu = $instance['nav_menu'];
			} else if ( $instance['nav_menu'] == -1 ) { // pages selected
				// force fallback
						// force pages fallback
				$nav_menu_opts['theme_location'] = '---none-'.time(); 
				$nav_menu_opts['fallback_cb'] = array(&$this,'_pages_menu');
				$nav_menu = $instance['nav_menu'];
			} else if ( $instance['nav_menu'] > 0 ) {  // some menu selected
				$nav_menu_opts['menu'] = wp_get_nav_menu_object( $instance['nav_menu'] );
				$nav_menu_opts['walker'] = new Walker_Sub_Menu();
				$nav_menu = $instance['nav_menu'];
			}
		}
		
		if ( ! $nav_menu ) {
			return;
		}
		
		
		$menu_contents = wp_nav_menu($nav_menu_opts);
		
		if ( trim(strip_tags($menu_contents)) ) {
			/** This filter is documented in wp-includes/default-widgets.php */
			$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
			echo $args['before_widget'];

			if ( !empty($instance['title']) )
				echo $args['before_title'] . $instance['title'] . $args['after_title'];
		
			?><div><?php
				echo $menu_contents;
			?></div><?php
			echo $args['after_widget'];
		}
	}
	public function update( $new_instance, $old_instance ) {
		
		$instance = array();
		if ( ! empty( $new_instance['title'] ) ) {
			$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		}
		if ( ! empty( $new_instance['nav_menu'] ) ) {
			$instance['nav_menu'] = $new_instance['nav_menu'];
		}
		return $instance;
	}

	public function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		
		$menu_locations = get_registered_nav_menus();
		
		// Get menus
		$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
		if ( ! $menus )
			$menus = array();
		
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select Menu:'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
				<option value="0"><?php _e( '&mdash; Select &mdash;' ) ?></option>
				<option value="-1" <?php selected( $nav_menu, -1, true ) ?>><?php _e( 'Pages' ) ?></option>
				<optgroup label="<?php _e( 'Theme locations' ) ?>">
				<?php
					foreach ( $menu_locations as $location => $description ) {
						echo '<option value="' . $location . '"'
							. selected( $nav_menu, $location, false )
							. '>'. esc_html( $description ) . '</option>';
					}
				?></optgroup>
				<optgroup label="<?php _e('Menus') ?>"><?php
					foreach ( $menus as $menu ) {
						echo '<option value="' . $menu->term_id . '"'
							. selected( $nav_menu, $menu->term_id, false )
							. '>'. esc_html( $menu->name ) . '</option>';
					}
				?></optgroup>
			</select>
		</p>
		<?php
	}
	
	function _pages_menu( $args ) {
		$args['walker'] = new Walker_Sub_Menu_Page();
		return wp_page_menu( $args );
		// wp_list_pages
	}
	
}
