<?php
/*
Plugin Name: Post Type
Description: Plugin um den Post Type zu testen
Version: 1.0
Author: Sandro Tonon
License: GPLv2
*/

/*
 * Registriere den Post-Type
 */
add_action('init', 'rp_registriere_post_type_spieler');
function rp_registriere_post_type_spieler() {
  /*
   * Fuege Kategorie 'Mannschaft' hinzu
   */
  register_taxonomy(
    'rp_spieler_mannschaft',
    'rp_spieler', array(
      'labels' => array(
        'name' => 'Mannschaften',
        'singular_name' => 'Mannschaft',
        'search_items' => 'Mannschaft suchen',
        'all_items' => 'Alle Mannschaften',
        'add_new_item' => 'Neue Mannschaft hinzufügen',
      ),
      'hierarchical' => true,
      'show_ui' => true,
      'public'=> true,
      'rewrite' => array(
        'slug' => 'spieler',
        'with_front' => true
      )
    )
  );

  /*
   * Registriere den Post Type rp_spieler
   */
  register_post_type('rp_spieler', array(
    'label' => 'Spieler',
    'description' => 'Post Type: Spieler',
    'public' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'capability_type' => 'post',
    'map_meta_cap' => true,
    'hierarchical' => false,
    'rewrite' => array(
      'slug' => 'spieler/%rp_spieler_mannschaft%',
      'with_front' => true
    ),
    'has_archive' => 'spieler',
    'query_var' => true,
    'menu_icon' => 'dashicons-groups',
    'supports' => array(
      'title',
      'editor',
      'custom-fields',
      'revisions',
      'thumbnail',
      'page-attributes',
      'post-formats'),
    'labels' => array(
      'name' => 'Spieler',
      'singular_name' => 'Spieler',
      'menu_name' => 'Spieler',
      'add_new' => 'Spieler hinzufügen',
      'add_new_item' => 'Neuen Spieler hinzufügen',
      'edit' => 'Spieler bearbeiten',
      'edit_item' => 'Edit Spieler',
      'new_item' => 'Neuer Spieler',
      'view' => 'Spieler anzeigen',
      'view_item' => 'View Spieler',
      'search_items' => 'Search Spieler',
      'not_found' => 'Kein Spieler gefunden',
      'not_found_in_trash' => 'Kein Spieler gefunden',
      'parent' => 'Parent Spieler',
    )
  ));

  /*
   * Erstelle den Standard-Taxonomy-Term "Alle Spieler"
   * Beim Insert der Spieler, wird dieser automatisch jedem Spieler zugewiesen
   */
  wp_insert_term('Alle Spieler', 'rp_spieler_mannschaft',
    $args = array(
      'slug' => 'alle-spieler',
      'description' => 'In dieser Kategorie sind alle Spieler des Veriens'
    )
  );
}

/*
 * Erstelle die Seiten "Spieler" und "Alle-Spieler" beim Aktivieren des Plugins
 */
register_activation_hook( __FILE__, 'myplugin_activate' );
function myplugin_activate() {
  /*
   * TODO:
   * Seite "Spieler" und "Alle-Spieler" erstellen
   */
  $spielerSeiteObj = array(
    'post_title'    => 'Spieler',
    'post_content'  => '',
    'post_status'   => 'publish',
    'post_author'   => 1,
    'post_type' => 'page'
  );
  $spielerSeiteId = wp_insert_post($spielerSeiteObj);

  $alleSpielerSeiteObj = array(
    'post_title'    => 'Alle Spieler',
    'post_content'  => '',
    'post_status'   => 'publish',
    'post_author'   => 1,
    'post_type' => 'page',
    'post_parent' => $spielerSeiteId
  );
  $alleSpielerSeiteId = wp_insert_post($alleSpielerSeiteObj);
}

/*
 * Registriere eine eigene Query Variable um die Spieler auf der Alle Spieler Seite sortieren
 * zu koennen
 */
add_filter('query_vars', 'rp_add_sortier_query_variable');
function rp_add_sortier_query_variable($vars) {
  $vars[] = "sortierung";
  return $vars;
}

add_filter('post_type_link', 'rp_spieler_mannschaft_permalinks', 1, 2);
function rp_spieler_mannschaft_permalinks($permalink, $post_id) {
  $mannschaft = get_query_var('rp_spieler_mannschaft');
  if (strpos($permalink, '%rp_spieler_mannschaft%') === FALSE) {
    return $permalink;
  }
  // Get post
  $post = get_post($post_id);
  if (!$post) {
    return $permalink;
  }

  // Get taxonomy terms
  if (term_exists($mannschaft)) {
    $taxonomy_slug = $mannschaft;
  } else {
    $taxonomy_slug = 'alle-spieler';
  }

  $output = str_replace('%rp_spieler_mannschaft%', $taxonomy_slug, $permalink);

  return $output;
}

/*
 * Entferne das 'Mannschaft' aus der Überschrift eines Taxonomy
 */
add_filter('wp_title', 'rp_clean_taxonomy_title');
function rp_clean_taxonomy_title($title) {
  $title = str_replace('Mannschaften &#8211; ', '', $title);
  $title = str_replace('Mannschaften ', '', $title);
  return $title;
}

?>
