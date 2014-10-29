<?php
/*
 * Template fuer die Anzeige einer einzelnen Mannschaft
 */

// TODO: Style-Angaben in Stylesheet übernehmen

$headerSep = get_template_directory_uri() . "/images/content-header-image-sep.png";
get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep" style="background-image: url(<?php echo $headerSep; ?>);"></div>
      <div class="page">

        <?php if (function_exists('z_taxonomy_image_url') && z_taxonomy_image_url() !== false): ?>
          <div class="rp_mannschafts_bild">
            <img src="<?php echo z_taxonomy_image_url(); ?>" style="width: 100%; margin-bottom: 30px;">
          </div>
        <?php endif ?>

        <table>
          <tr>
            <th>Bilanz</th>
            <th>Statistik</th>
          </tr>
          <tr>
            <td>Bla</td>
            <td>Blubb</td>
          </tr>
        </table>

        <?php
          // Zeige die einzelnen Spieler an
          $mannschaft = get_query_var('term');

          $args = array(
            'post_type' => 'rp_spieler',
            'tax_query' => array(
              array(
                'taxonomy' => 'rp_spieler_mannschaft',
                'field'    => 'slug',
                'terms'    => $mannschaft
              ),
            ),
          );
          $the_query = new WP_Query($args);
        ?>

        <?php if ($the_query->have_posts()) : ?>
          <!-- the loop -->
          <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>


            <h2><?php the_title(); ?></h2>
            <p><a href="<?php echo get_permalink(); ?>">Link</a></p>


          <?php endwhile; ?>
          <!-- end of the loop -->

          <?php // Mannschafts Tabellen ausgeben ?>
          <hr>
          <h4>Aktuelle Tabelle</h4>

          <?php
            /*
            $table_name = $wpdb->prefix . 'rp_mannschaften_daten';
            $sql = "SELECT * FROM $table_name
                    WHERE mannschaft = %s";
            $meta = $wpdb->get_row($wpdb->prepare($sql, $mannschaft), ARRAY_A);
            extract($meta);
            */
          ?>

          <?php wp_reset_postdata(); ?>
        <?php else : ?>
          <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
        <?php endif; ?>

      </div>
    <?php wp_reset_postdata(); ?>
    <?php get_sidebar(); ?>
  <!-- end of content -->
  </section>
<?php get_footer(); ?>
