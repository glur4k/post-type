<?php

/**
 * Initialisiere das Widget
 */
add_action('widgets_init', 'rp_register_spieler_widget');
function rp_register_spieler_widget() {
  register_widget('WidgetBesteSpieler');
}

/**
 * Klasse fuer das Top-X Spieler Widget
 */
class WidgetBesteSpieler extends WP_Widget {

  // constructor
  function WidgetBesteSpieler() {
    parent::WP_Widget(
      'rp_widget_spieler',
      $name = __('Beste Spieler', 'wp_widget_plugin'),
      array('description' => __('Dieses Widget zeigt die besten Spieler des Plugins', 'text_domain'))
    );
  }

  // widget form creation
  function form($instance) {
    // Check values
    if($instance) {
      $title = esc_attr($instance['title']);
      $anzahl = esc_attr($instance['anzahl']);
    } else {
      $title = '';
      $anzahl = 3;
    }
    ?>

    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Titel', 'wp_widget_plugin'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>

    <p>
      <label for="<?php echo $this->get_field_id('anzahl'); ?>"><?php _e('Anzahl Spieler:', 'wp_widget_plugin'); ?></label>
      <input class="widefat" id="<?php echo $this->get_field_id('anzahl'); ?>" name="<?php echo $this->get_field_name('anzahl'); ?>" type="text" value="<?php echo $anzahl; ?>" />
    </p>
    <?php
  }

  // widget update
  function update($new_instance, $old_instance) {
    $instance = $old_instance;
    // Fields
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['anzahl'] = strip_tags($new_instance['anzahl']);

    return $instance;
  }

  // widget display
  function widget($args, $instance) {
    extract($args);
    // these are the widget options
    $title = apply_filters('widget_title', $instance['title']);
    $anzahl = $instance['anzahl'];

    echo $before_widget;


    // Display the widget
    echo '<div class="widget-container dt-newsflash rp_spieler_widget">';
    // Check if title is set
    if ($title) {
      echo $before_title . $title . $after_title;
    }

    if ($anzahl) {
      echo $this->hole_top_spieler($anzahl);
    }

    echo '</div>';
    echo $after_widget;
  }

  function hole_top_spieler($anzahl) {
    $args = array(
      'post_type' => 'rp_spieler',
      'meta_key'=> 'bilanzwert',
      'orderby' => 'meta_value_num',
      'order' => DESC,
      'posts_per_page' => $anzahl
    );

    $the_query = new WP_Query($args);

    if (!$the_query->have_posts()) {
      return 'Keine Spieler gefunden';
    }

    while ($the_query->have_posts()) {
      $the_query->the_post();

      $name = get_the_title();
      $bilanzwert = ParserUtils::signBilanzwert(get_post_meta(get_the_ID(), 'bilanzwert', true));

      $output .= "<div class='top-spieler post-content'>";
      $output .= "<a href='" . get_permalink($post_id) . "' class='spieler-portrait-wrapper";

      $thumbnail_args = array(
        'alt' => "Bild von " . $name,
        'style' => "border-radius: 50%; display: inline-block; margin-right: 10px;"
      );
      $thumbnail = get_the_post_thumbnail($post_id, array(60, 60), $thumbnail_args);
      if ($thumbnail) {
        $output .= "'>" . $thumbnail . "</a>";
      } else {
        $output .= " portrait-wrapper-no-img'>" . ParserUtils::baueTitelKuerzel($name) . "</a>";
      }

      $output .= "<section class='rp-spieler-content'>";
      $output .= "<h6><a href='" . get_permalink($post_id) . "'>" . ($key + 1) . ". " . $name . "</a></h6><strong>";

      $output .= $bilanzwert . " (" . get_post_meta(get_the_ID(), 'mannschaft', true) . ")</strong>";
      $output .= "</section>";
      $output .= "<a class='more-link' href='" . get_permalink($post_id) . "'>Details</a>";
      $output .= "</div><hr>";
    }

    wp_reset_postdata();

    $output .= "<small>*Gemessen am Bilanzwert</small>";

    return $output;
  }
}
