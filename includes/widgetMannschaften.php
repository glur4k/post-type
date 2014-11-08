<?php

/**
 * Initialisiere das Widget
 */
add_action('widgets_init', 'rp_register_mannschaften_widget');
function rp_register_mannschaften_widget() {
  register_widget('WidgetMannschaften');
}

/**
 * Klasse fuer das Top-X Spieler Widget
 */
class WidgetMannschaften extends WP_Widget {

  // constructor
  function WidgetMannschaften() {
    parent::WP_Widget(
      'rp_widget_mannschaften',
      $name = __('Mannschaftsübersicht', 'wp_widget_plugin'),
      array('description' => __('Dieses Widget zeigt eine Übersicht der Mannschaften als Menp an', 'text_domain'))
    );
  }

  // widget form creation
  function form($instance) {
    // Check values
    if($instance) {
      $title = esc_attr($instance['title']);
    } else {
      $title = '';
    }
    ?>

    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel', 'wp_widget_plugin'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>

    <?php
  }

  // widget update
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    // Fields
    $instance['title'] = strip_tags($new_instance['title']);

    return $instance;
  }

  // widget display
  function widget($args, $instance) {
    extract($args);
    // these are the widget options
    $title = apply_filters('widget_title', $instance['title']);

    echo $before_widget;

    // Display the widget
    echo '<div class="widget-container clearfix widget_nav_menu">';
    // Check if title is set
    if ($title) {
      echo $before_title . $title . $after_title;
    }

    echo $this->hole_mannschaften();
    echo '</div>';
    echo $after_widget;
  }

  function hole_mannschaften() {
    $taxonomies = array('rp_spieler_mannschaft');
    $alleSpielerID = get_term_by('slug', 'alle-spieler', 'rp_spieler_mannschaft');

    $args = array(
      'orderby'           => 'name',
      'order'             => 'ASC',
      'hide_empty'        => false,
      'exclude'           => array(intval($alleSpielerID->term_id))
    );

    $terms = get_terms($taxonomies, $args);

    $output = "<div class='menu-blog-sidebar-container'>";
    $output .= "<ul id='menu-blog-sidebar' class='menu'>";

    foreach ($terms as $term) {
      $output .= "<li class='menu-item menu-item-type-post_type menu-item-object-page current-menu-item page_item current_page_item menu-item-has-children first-child last-child'><a href='/spieler/" . $term->slug . "'>" . $term->name . "</a>";
    }
    $output .= "</li></ul></div>";

    wp_reset_postdata();

    return $output;
  }
}
