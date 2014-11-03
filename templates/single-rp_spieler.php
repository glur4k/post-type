<?php
/*
 * Template fuer einen einzelnen Spieler
 */
?>

<?php
  $mannschaft = get_query_var('rp_spieler_mannschaft');
?>

<?php get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
    <div class="page">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php if ($src = wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail')[0]): ?>
          <a title="<?php the_title() ?>" href="<?php echo $src ?>" class="spieler-portrait-wrapper" rel="modal-window"><?php the_post_thumbnail(); ?></a>
        <?php endif; ?>

        <?php
          // Wenn der Taxonomy Term "alle-spieler" ist, erstelle Array mit allen Mannschaften und gebe fuer
          // jedes Element die Daten in der For-Schleife aus
          // Ansonsten: Erstelle Array mit einem Eintrag und gib die Daten aus
          $clickTTID = get_post_meta(get_the_ID(), 'click_tt_id', true);
        ?>
        <?php
          $mannschaften = array();
          if ($mannschaft === 'alle-spieler') {
            // Hole Mannschaften
            $table_name = $wpdb->prefix . 'rp_spieler_daten';
            $sql = "SELECT mannschaft FROM $table_name
                    WHERE click_tt_id = %d";
            $results = $wpdb->get_results($wpdb->prepare($sql, intval($clickTTID)), OBJECT);
            foreach ($results as $result) {
              $mannschaften[] = $result->mannschaft;
            }
          } else {
            $mannschaften[] = ParserUtils::konvertiereMannschaftsNamen($mannschaft);
          }
        ?>

        <?php foreach ($mannschaften as $key => $mannschaft): ?>
          <?php
            $table_name = $wpdb->prefix . 'rp_spieler_daten';
            $sql = "SELECT position, einsaetze, bilanzwert, link, gesamt, gegner1, gegner2, gegner3, gegner4, 1und2, 3und4, 5und6 FROM $table_name
                    WHERE mannschaft = %s
                    AND click_tt_id = %d";
            $data = $wpdb->get_row($wpdb->prepare($sql, $mannschaft, intval($clickTTID)), ARRAY_A);

            // Erstelle das Array fuer die Charts
            $forCharts = $data;
            foreach ($forCharts as $key => $value) {
              if (is_null($forCharts[$key]) || strlen($value) !== 3) {
                unset($forCharts[$key]);
              } elseif (substr_compare($value, ':', 1, 1) !== 0) {
                unset($forCharts[$key]);
              }
            }

            extract($data);
          ?>

          <h5 class="dt-message dt-message-info">Statistiken in der Mannschaft: <?php echo $mannschaft ?></h5>
          <table>
            <thead>
              <tr>
                <th>Mannschaft</th>
                <th>Position</th>
                <th>Eins&auml;tze</th>
                <th>Bilanzwert</th>
                <th>Click-TT</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong><?php echo $mannschaft ?></strong></td>
                <td><strong><?php echo $position ?></strong></td>
                <td><strong><?php echo $einsaetze ?></strong></td>
                <td><strong><?php echo ParserUtils::signBilanzwert($bilanzwert) ?></strong></td>
                <td><a href="<?php echo $link ?>" target="_blank" title="Neues Fenster: Link zu Click-TT-Daten von <?php echo the_title() ?> in der Mannschaft <?php echo $mannschaft ?>">Link</a></td>
              </tr>
            </tbody>
          </table>

          <h6>Statistik: Verhältnis von gewonnenen zu verlorenen Spielen</h6>
          <div class="myChart" style="min-width: 310px; max-width: 800px; height: 350px; margin: 0 auto"></div>

          <script type="text/javascript">
            <!--//--><![CDATA[//><!--
              Highcharts.setOptions({
                chart: {
                  backgroundColor: 'rgba(0, 0, 0, 0)'
                }
              });
              $(function () {
                $('.myChart').highcharts({
                  chart: {
                    type: 'bar'
                  },
                  credits: {
                    enabled: false
                  },
                  title: {
                    text: false
                  },
                  xAxis: {
                    categories: ['Gesamte Spiele', 'Gg. Nr. 1', 'Gg. Nr. 2', 'Gg. erstes Paarkreuz', 'Gg. zweites Paarkreuz']
                  },
                  yAxis: {
                    min: 0,
                    title: {
                      text: 'Spiele insgesamt'
                    }
                  },
                  plotOptions: {
                    series: {
                      stacking: 'normal'
                    }
                  },
                  legend: {
                    reversed: true
                  },
                  series: [{
                    name: 'Verlorene Spiele',
                    data: [3, 4, 4, 2, 5],
                    color: '#F33030'
                  }, {
                    name: 'Gewonnene Spiele',
                    data: [5, 3, 4, 7, 2],
                    color: '#1f9AE3'
                  }]
                });
              });
            //--><!]]>
          </script>
          <?php if (count($mannschaften) > 1 && $key + 1 < count($mannschaften)) : ?>
            <div class="dt-separator-top"><a class="scroll" href="#website-header">TOP</a></div>
          <?php endif; ?>

        <?php endforeach; ?>

        <?php echo get_the_content(); ?>

      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  <!-- end of content -->
  <?php get_sidebar(); ?>
  </section>
<?php get_footer(); ?>
