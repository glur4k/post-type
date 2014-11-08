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
          // Hole data_tabelle, data_ergebnisse, link, begegnungen aus mannschaften_daten Tabelle
          $mannschaft = get_query_var('term');

          $table_name = $wpdb->prefix . 'rp_mannschaften_daten';
          $sql = "SELECT data_tabelle, data_ergebnisse, link, gegner, begegnungen, position, siege, unentschieden, niederlagen, punkte FROM $table_name
                  WHERE name = %s";
          $meta = $wpdb->get_row($wpdb->prepare($sql, ParserUtils::konvertiereMannschaftsNamen($mannschaft)), ARRAY_A);
          extract($meta);
        ?>

        <?php if (function_exists('z_taxonomy_image_url') && z_taxonomy_image_url() !== false): ?>
          <p class="rp_mannschafts_bild">
            <img alt="Bild der Mannschaft: <?php echo ParserUtils::konvertiereMannschaftsNamen($mannschaft) ?>" src="<?php echo z_taxonomy_image_url(); ?>" title="Bild der Mannschaft: <?php echo ParserUtils::konvertiereMannschaftsNamen($mannschaft) ?>">
          </p>
        <?php endif ?>

        <table class="rp_spieler-mannschafts-uebersicht">
          <thead>
            <tr>
              <th>Tabellenplatz</th>
              <th>Siege</th>
              <th>Unentschieden</th>
              <th>Niederlagen</th>
              <th>Punkte</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><?php echo $position . "/" . ($gegner + 1)?></td>
              <td><?php echo $siege ?></td>
              <td><?php echo $unentschieden ?></td>
              <td><?php echo $niederlagen ?></td>
              <td><?php echo $punkte ?></td>
            </tr>
          </tbody>
        </table>

        <?php
          // Erstelle das Spieler-Query
          $args = array(
            'post_type' => 'rp_spieler',
            'meta_key'=> 'rang',
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

        <h5 class="dt-message dt-message-info">Mannschaftsaufstellung</h5>

        <?php if ($the_query->have_posts()) : ?>
          <!-- the loop -->
          <?php $count = 1; $last = false; ?>
          <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <?php
              // Duotive-One-Third Logik
              if ($count === 3) {
                $last = true;
                $count = 1;
              } else {
                $count++;
                $last = false;
              }
            ?>

            <?php
              // Hole Daten des Spielers aus rp_spieler_daten
              $table_name = $wpdb->prefix . 'rp_spieler_daten';
              $sql = "SELECT einsaetze, bilanzwert FROM $table_name
                      WHERE post_id = %d";
              $metaSpieler = $wpdb->get_row($wpdb->prepare($sql, get_the_ID()), ARRAY_A);
              extract($metaSpieler);
            ?>

            <?php echo '<div class="dt-onethird' . ($last ? ' dt-onethirdlast' : '') . '">'; ?>
              <div class="rp_spieler">
                <div class="dt-message dt-message-paragraph" style="text-align: center;">

                <?php if (($url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail')) !== false): ?>
                  <a class="spieler-portrait-wrapper" href="<?php echo get_post_permalink(); ?>"><?php the_post_thumbnail(array(140, 140)); ?></a>
                <?php else: ?>
                  <a title="Ausf&uuml;hliche Statistiken zu <?php the_title(); ?>" class="spieler-portrait-wrapper portrait-wrapper-no-img" href="<?php echo get_post_permalink(); ?>"><?php echo ParserUtils::baueTitelKuerzel(get_the_title()); ?></a>
                <?php endif; ?>

                <h6><?php echo get_post_meta(get_the_ID(), 'rang', true); ?></h6>
                <h4><?php echo str_replace (" " , "<br>" , get_the_title()); ?></h4>
                <table>
                  <tr>
                    <th>Einsätze</th>
                    <td><?php echo $einsaetze ?></td>
                  </tr>
                  <tr>
                    <th>Gesamtbilanz</th>
                    <td><?php echo ParserUtils::signBilanzwert($bilanzwert) ?></td>
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
          <h5 class="dt-message dt-message-info">Die aktuelle Tabelle</h5>
          <?php echo $data_tabelle ?>

          <h5 class="dt-message dt-message-info">Die letzten Ergebnisse der <?php echo ParserUtils::konvertiereMannschaftsNamen($mannschaft) ?></h5>
          <?php echo $data_ergebnisse ?>

          <?php wp_reset_postdata(); ?>
        <?php else : ?>
          <p><?php _e('Leider keine spieler in dieser Mannschaft gefunden.'); ?></p>
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
