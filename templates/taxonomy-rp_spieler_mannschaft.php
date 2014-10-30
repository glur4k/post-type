<?php
/*
 * Template fuer die Anzeige einer einzelnen Mannschaft
 */

$headerSep = get_template_directory_uri() . "/images/content-header-image-sep.png";
get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep" style="background-image: url(<?php echo $headerSep; ?>);"></div>
      <div class="page">
        <?php
          $mannschaft = get_query_var('term');
        ?>

        <?php if (function_exists('z_taxonomy_image_url') && z_taxonomy_image_url() !== false): ?>
          <p class="rp_mannschafts_bild">
            <img alt="Bild der Mannschaft: <?php echo ParserUtils::konvertiereMannschaftsNamen($mannschaft) ?>" src="<?php echo z_taxonomy_image_url(); ?>" title="Bild der Mannschaft: <?php echo ParserUtils::konvertiereMannschaftsNamen($mannschaft) ?>">
          </p>
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

          $args = array(
            'post_type' => 'rp_spieler',
            'meta_key'=> $mannschaft,
            'orderby' => 'meta_value_num',
            'order' => ASC,
            'posts_per_page' => -1,
            'tax_query' => array(
              array(
                'taxonomy' => 'rp_spieler_mannschaft',
                'field' => 'slug',
                'terms' => $mannschaft
              )
            )
          );

          $the_query = new WP_Query($args);
        ?>

        <?php if ($the_query->have_posts()) : ?>
          <!-- the loop -->
          <?php $count = 1; $last = false; ?>
          <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>

            <?php if ($count === 3) {
              $last = true;
              $count = 1;
            } else {
              $count++;
              $last = false;
            }?>

            <?php echo '<div class="dt-onethird' . ($last ? ' dt-onethirdlast' : '') . '">'; ?>
              <div class="rp_spieler">
                <div class="dt-message dt-message-paragraph" style="text-align: center;">

                <?php if (($url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail')) !== false): ?>
                  <a class="spieler-portrait-wrapper" href="<?php echo get_post_permalink(); ?>"><?php the_post_thumbnail('medium'); ?></a>
                <?php else: ?>
                  <a title="Ausf&uuml;hliche Statistiken zu <?php the_title(); ?>" class="spieler-portrait-wrapper wrapper-no-img" href="<?php echo get_post_permalink(); ?>"><?php echo ParserUtils::baueTitelKuerzel(get_the_title()); ?></a>
                <?php endif; ?>

                <h6><?php echo get_post_meta(get_the_ID(), $mannschaft)[0] ?></h6>
                <h4><?php echo str_replace (" " , "<br>" , get_the_title()); ?></h4>
                <table>
                  <tr>
                    <th>Einsätze</th>
                    <td>X</td>
                  </tr>
                  <tr>
                    <th>Gesamtbilanz</th>
                    <td>X</td>
                  </tr>
                </table>
                <a title="Ausf&uuml;hliche Statistiken zu <?php the_title(); ?>" class="dt-button dt-button-icon dt-button-icon-info" href="<?php echo get_post_permalink(); ?>">Details</a>

                </div>
              </div>
            </div>
          <?php endwhile; ?>
          <!-- end of the loop -->

          <?php // Mannschafts Tabellen ausgeben ?>
          <div class="clearfix"></div>
          <div class="dt-separator-top post-spieler clearfix"><a class="scroll" href="#website-header">TOP</a></div>
          <?php
            // Hole data_tabelle, data_ergebnisse, Link aus mannschaften_daten Tabelle
            $table_name = $wpdb->prefix . 'rp_mannschaften_daten';
            $sql = "SELECT data_tabelle, data_ergebnisse, link FROM $table_name
                    WHERE name = %s";
            $meta = $wpdb->get_row($wpdb->prepare($sql, ParserUtils::konvertiereMannschaftsNamen($mannschaft)), ARRAY_A);
            extract($meta);
          ?>
          <h5 class="dt-message dt-message-info">Die aktuelle Tabelle</h5>
          <?php echo $data_tabelle ?>

          <h5 class="dt-message dt-message-info">Die letzten Ergebnisse der <?php echo ParserUtils::konvertiereMannschaftsNamen($mannschaft) ?></h5>
          <?php echo $data_ergebnisse ?>

          <?php wp_reset_postdata(); ?>
        <?php else : ?>
          <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
        <?php endif; ?>

        <div class="dt-message dt-message-notice" style="text-align: center;"><span style="font-size: xx-small;">
          Die Informationen auf dieser Seite wurden bereitgestellt von Click-TT. Wir &uuml;bernehmen keine Haftung für die Korrektheit der Daten.</span><br>
          F&uuml;r weitere Informationen besuchen Sie bitte die Seite von <a href="<?php echo $link ?>" target="_blank">Click-TT</a>.
        </div>
      </div>
    <?php wp_reset_postdata(); ?>
    <?php get_sidebar(); ?>
  <!-- end of content -->
  </section>
<?php get_footer(); ?>
