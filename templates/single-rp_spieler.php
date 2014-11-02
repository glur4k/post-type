<?php
/*
 * Template fuer einen einzelnen Spieler
 */
?>

<?php
  $zeige = get_query_var('rp_spieler_mannschaft');
?>

<?php get_header(); ?>
  <section id="content" class="clearfix page-widh-sidebar">
    <div class="content-header-sep"></div>
    <div class="page">
      <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <?php if ($src = wp_get_attachment_image_src(get_post_thumbnail_id(), 'post-thumbnail')[0]): ?>
          <a title="<?php the_title() ?>" href="<?php echo $src ?>" class="spieler-portrait-wrapper" rel="modal-window"><?php the_post_thumbnail(); ?></a>
        <?php endif; ?>

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
              <td><strong>blas</strong></td>
              <td>blas</td>
              <td>blas</td>
              <td>blas</td>
              <td>Link</td>
            </tr>
          </tbody>
        </table>

        <h5 class="dt-message dt-message-info">Statistik: Verhältnis von gewonnenen zu verlorenen Spielen</h5>
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
                  name: 'Verloren',
                  data: [3, 4, 4, 2, 5],
                  color: '#F33030'
                }, {
                  name: 'Gewonnen',
                  data: [5, 3, 4, 7, 2],
                  color: '#1f9AE3'
                }]
              });
            });
          //--><!]]>
        </script>

        <?php echo get_the_content(); ?>

      <?php endwhile; ?>
      <?php endif; ?>
    </div>
  <!-- end of content -->
  <?php get_sidebar(); ?>
  </section>
<?php get_footer(); ?>
