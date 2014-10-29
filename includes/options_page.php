<?php

/**
* Die Optionen-Seite
*/
class options_page {

  private $title = 'Results-Parser Einstellungen';
  private $options;
  private $pageTypes;

  public function __construct() {
    add_theme_support('thumbnails', array('post', 'rp_spieler'));
    add_action('admin_menu', array($this, 'rp_admin_menu'));
    add_action('admin_init', array($this, 'rp_page_init'));
  }

  public function rp_admin_menu() {
    $option_page = add_options_page(
      $this->title,
      'Results-Parser',
      'manage_options',
      'results_parser-admin',
      array($this, 'rp_einstellungen_layout')
    );
    // Lade Scripts.js nur auf der Einstellungs-Seite
    add_action('load-' . $option_page, array($this, 'include_rp_js'));
  }

  /*
  * Checkt ob Spieler importiert werden sollen oder nicht und gibt das entsprechende Layout aus
  */
  public function rp_einstellungen_layout() {
    if (isset($_POST['importiere_spieler']) && $_POST['importiere_spieler'] === 'true') {
      $this->rp_layout_spieler_import();
    } else {
      $this->rp_layout_settings();
    }
  }

  /*
   * Gibt das Layout aus, wenn die Spieler geparst werden
   */
  function rp_layout_spieler_import() {
    ?>
    <div class="wrap">
      <h2>Importiere Spieler...</h2>
      <p style="display: inline-block;">Die Spieler werden importiert! Dies kann einige Minuten dauern!</p>
      <div style="display: inline-block; margin-left: 5px;" id="loader"><img src="<?php echo get_admin_url(); ?>images/spinner.gif" alt=""></div>
      <section id="lade-spieler"></section>
    </div>
    <?php
  }

  /*
   * Gibt das normale Einstellungen-Layout aus
   */
  function rp_layout_settings() {
    $this->options = get_option('rp_results_parser_einstellungen');
    $this->pageTypes = array(''=>__('Posts'), 'page' => __('Pages'), __('Theme Templates') => array_flip(get_page_templates()));
    ?>
    <div class="wrap">
      <h2><?php echo esc_html($this->title); ?></h2>
      <form action="options.php" method="post">
        <?php
        settings_fields('rp_results_parser_einstellungen');
        do_settings_sections('results_parser-admin');
        submit_button();
        ?>
      </form>
      <form action="" method="post">
        <?php
        do_settings_sections('results_parser_import-admin');
        ?>
      </form>
    </div >
    <?php
  }

  /*
   * Einstellungen registrieren und hinzufuegen
   */
  public function rp_page_init() {
    register_setting(
      'rp_results_parser_einstellungen', // Option group
      'rp_results_parser_einstellungen', // Option name
      array($this, 'sanitize')      // Sanitize
    );

    // SECTION Allgemeine Einstellungen
    // Allgemeine Einstellungen
    add_settings_section(
      'rp_einstellungen',
      'Generelle Einstellungen',
      array($this, 'rp_einstellungen_beschreibungs_text'),
      'results_parser-admin'
    );

    // VereinsID
    add_settings_field(
      'rp_vereins_id',
      'Vereins ID',
      array($this, 'rp_einstellungen_vereins_id'),
      'results_parser-admin',
      'rp_einstellungen'
    );

    // SECTION Spieler Import
    // =======================
    // Button um die Spieler zu parsen
    add_settings_section(
      'rp_parse_now',
      'Spieler importieren',
      array($this, 'rp_parse_spieler_text'),
      'results_parser_import-admin'
    );

    add_settings_field(
      'rp_parse_spieler',
      '',
      array($this, 'rp_parse_now'),
      'results_parser_import-admin',
      'rp_parse_now'
    );
  }

  /*
   * Input in den Einstellungen verifizieren
   *
   * @param array $input - enthaelt alle Eingaben
   */
  public function sanitize($input) {
    $new_input = array();
    if (isset($input['rp_vereins_id'])) {
      $new_input['rp_vereins_id'] = absint($input['rp_vereins_id']);

      $startseite = 'http://ttvbw.click-tt.de/cgi-bin/WebObjects/nuLigaTTDE.woa/wa/clubTeams?club=' . $new_input['rp_vereins_id'];
      $html = file_get_html($startseite);
      $heading = $html->find('h1', 0)->plaintext;
      $heading = str_replace('Mannschaften und Ligeneinteilung', '', $heading);
      $heading = trim($heading);

      $new_input['rp_vereins_name'] = $heading;
    }

    return $new_input;
  }

  // CALLBACK FUNKTIONEN UM DIE EINSTELLUNGEN ANZUZEIGEN
  public function rp_einstellungen_beschreibungs_text() {
    echo 'Gebe hier die Einstellungen f&uuml;r den Results-Parser an.';
  }

  public function rp_einstellungen_vereins_id() {
    $value = isset($this->options['rp_vereins_id']) ? esc_attr($this->options['rp_vereins_id']) : '';
    echo '<input type="text" id="rp_vereins_id" name="rp_results_parser_einstellungen[rp_vereins_id]" value="' . $value . '"></input>';
  }

  public function rp_parse_spieler_text() {
    echo 'Klicke auf den Button um die Spieler des Vereines mit der angegebenen ID zu importieren.';
  }

  public function rp_parse_now() {
    echo '<input type="hidden" name="importiere_spieler" value="true" />';
    echo '<input type="submit" name="rp_results_parser_einstellungen[rp_parse_spieler]" id="rp_parse_spieler" class="button" value="Importiere Spieler"></input>';
  }

  public function include_rp_js() {
    if (isset($_POST['importiere_spieler']) && $_POST['importiere_spieler'] === 'true') {
      wp_enqueue_script('rp_ajax', plugins_url('../js/rp_ajax.js', __FILE__), array('jquery'), '1', true);

      $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

      $params = array(
        'ajaxurl' => admin_url('admin-ajax.php', $protocol)
      );

      wp_localize_script('rp_ajax', 'rp_ajax', $params);
    }
  }
}
?>
