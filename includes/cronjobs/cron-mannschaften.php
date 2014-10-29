<?php
header('Content-Type: text/html; charset=utf-8');
define('SHORTINIT', true);
require_once('/../vendor/simple_html_dom.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/virtual/wordpress-test/wp-load.php');

define(ABS_PATH, dirname($_SERVER['SCRIPT_NAME']));

/*
 * Startseite: Die Click-TT Seite auf der alle Mannschaften gelistet sind
 */
class CronMannschaften {

  private $vereinsID;
  private $startseite;
  private $tableStartseite;
  private $clubName;

  function __construct() {
    $this->vereinsID = get_option('rp_results_parser_einstellungen')['rp_vereins_id'];
    $this->clubName = get_option('rp_results_parser_einstellungen')['rp_vereins_name'];;
    $this->startseite = 'http://ttvbw.click-tt.de/cgi-bin/WebObjects/nuLigaTTDE.woa/wa/clubTeams?club=' . $this->vereinsID;
    $html = file_get_html($this->startseite);
    $this->tableStartseite = $html->find('table', 0);

    $this->rp_refresh_mannschaften();
  }

  private function rp_refresh_mannschaften() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'rp_mannschaften_daten';

    $query = 'SELECT * FROM ' . $table_name;
    $mannschaften = $wpdb->get_results($query, ARRAY_A);

    foreach ($mannschaften as $key => $mannschaft) {
      $mannschaftsLink = $this->rp_get_mannschaften_link($mannschaft['name']);
      $mannschaften[$key]['link'] = $mannschaftsLink;
      $mannschaften[$key]['gegner'] = $this->rp_get_mannschaften_gegner($mannschaftsLink);
      $mannschaften[$key]['position'] = $this->rp_get_mannschaften_position($mannschaft['name']);
      $mannschaften[$key]['liga'] = $this->rp_get_mannschaften_liga($mannschaft['name']);
      $mannschaften[$key]['data_tabelle'] = $this->rp_get_mannschaften_data_tabelle($mannschaftsLink);
      $mannschaften[$key]['data_ergebnisse'] = $this->rp_get_mannschaften_data_ergebnisse($mannschaft['name'], $mannschaftsLink);
    }

    $this->rp_schreibe_mannschaften_in_datenbank($mannschaften);

    $this->rp_cleanup();

    return true;
  }

  private function rp_get_mannschaften_link($mannschaft) {
    $table = $this->tableStartseite;
    $zeilennummer = $this->rp_finde_passende_zeile_startseite($table, $mannschaft);

    if ($zeilennummer === 0) {
      return 'none';
    }

    $zeile = $table->find('tr', $zeilennummer);
    $link = 'http://ttvbw.click-tt.de' . $zeile->find('a', 0)->href;

    return $this->rp_clean_amp($link);
  }

  private function rp_get_mannschaften_gegner($link) {
    $html = file_get_html($link);
    $table = $html->find('table', 0);
    $gegner = count($table->find('tr')) - 2;

    return $gegner;
  }

  private function rp_get_mannschaften_position($mannschaft) {
    $table = $this->tableStartseite;
    $zeilennummer = $this->rp_finde_passende_zeile_startseite($table, $mannschaft);

    $zeile = $table->find('tr', $zeilennummer);
    $position = $zeile->find('td', 3)->innertext;
    $position = preg_replace("/[^0-9]/","", $position);

    return $position;
  }

  private function rp_get_mannschaften_liga($mannschaft) {
    $table = $this->tableStartseite;
    $zeilennummer = $this->rp_finde_passende_zeile_startseite($table, $mannschaft);

    $zeile = $table->find('tr', $zeilennummer);
    $ligaDummy = trim($zeile->find('td', 1)->plaintext);

    $split = explode(' ', $ligaDummy);
    $liga = $split[1];

    return $liga;
  }

  private function rp_get_mannschaften_data_tabelle($link) {
    $html = file_get_html($link);
    $tableDummy = $html->find('table', 0);
    $table = $this->rp_clean_data_tabelle($tableDummy)->outertext;
    return $table;
  }

  /*
  * Aendert das Icon zu dem lokalen,
  * Loescht Links und highlightet die Zeile mit der Club-Mannschaft
  * $table = die Tabelle in der das Icon geaendert werden soll
  */
  private function rp_clean_data_tabelle($table) {
    $aufsteiger_src = ABS_PATH . '/../table-images/up_11x11.gif';
    $relegation_up_src = ABS_PATH . '/../table-images/up_grey_11x11.gif';
    $relegation_down_src = ABS_PATH . '/../table-images/down_grey_11x11.gif';
    $absteiger_src = ABS_PATH . '/../table-images/down_11x11.gif';

    $imgs = $table->find(img);
    if ($imgs) {
      foreach ($imgs as $img) {
        if (strpos($img->src, 'up_11x11.gif') !== false) {
          $img->src = $aufsteiger_src;
        } else if (strpos($img->src, 'up_grey_11x11.gif') !== false) {
          $img->src = $relegation_up_src;
        } else if (strpos($img->src, 'down_grey_11x11.gif') !== false) {
          $img->src = $relegation_down_src;
        } else if (strpos($img->src, 'down_11x11.gif') !== false) {
          $img->src = $absteiger_src;
        }
      }
    }

    // Loesche Links
    foreach ($table->find(a) as $link) {
      $link->outertext = $link->plaintext;
    }

    // Highlight Club-Zeile
    foreach ($table->find('tr') as $row) {
      $cols = $row->find('td');
      if (strpos($cols[2], $this->clubName) !== false) {
        foreach ($cols as $col) {
          $col->style = 'font-weight:bold;';
        }
        break;
      }
      $rank++;
    }

    return $table;
  }

  private function rp_get_mannschaften_data_ergebnisse($mannschaftsName, $link) {
    $html = file_get_html($link);
    $table = $html->find('table', 0);

    // Ermittle Nummer der Mannschaft, um die richtige Mannschaft in der Tabelle auszulesen
    // wenn mehrere des gleichen Clubs in der selben Liga spielen
    $regulaereNummern = array('II', 'III');
    $mannschaftsName = explode(' ', $mannschaftsName);
    $nummer = array_pop($mannschaftsName);
    if (!in_array($nummer, $regulaereNummern)) {
      $nummer = '';
    } else {
      $nummer = ' ' . $nummer;
    }

    // Ermittle die Zeilennummer
    $zeilennummer = $this->rp_finde_passende_zeile_mannschaft($table, $this->clubName . $nummer);

    $zeile = $table->find('tr', $zeilennummer);
    $link = 'http://ttvbw.click-tt.de' . $zeile->find('a', 0)->href;

    $html = file_get_html($this->rp_clean_amp($link));

    $tableDummy = $html->find('table', 1);
    $table = $this->rp_clean_data_ergebnisse($tableDummy)->outertext;

    $html;
    unset($html);

    return $table;
  }

  private function rp_clean_data_ergebnisse($table) {
    $checked_src = ABS_PATH . '/../table-images/check.gif';

    // Clean Links
    foreach ($table->find(a) as $link) {
      $link->outertext = $link->plaintext;
    }

    // Remove wrong/overhead informations
    foreach ($table->find('tr') as $row) {
      $heads = $row->getElementsByTagName('th');
      if ($heads) {
        // Fix table-heads
        $heads[0]->colspan = null;
        $heads[0]->innertext = 'Tag</th><th>Datum</th><th>Zeit';
        // Remove informations
        $heads[1]->outertext = '';
        $heads[2]->outertext = '';
        $heads[6]->outertext = '';
      } else {
        $tds = $row->getElementsByTagName('td');
        $tds[3]->outertext = '';
        $tds[4]->outertext = '';
        $tds[8]->outertext = '';
      }
    }
    // Ersetze das Image
    foreach ($table->find('img') as $img) {
      $img->src = $checked_src;
    }
    return $table;
  }

  private static function rp_clean_amp($string) {
    $upas = Array("&amp;" => "&");
    return strtr($string, $upas);
  }

  private static function rp_finde_passende_zeile_startseite($table, $mannschaft) {
    $isKlasse = false;
    $zeile = 0;

    foreach ($table->find('tr') as $row) {
      if (strpos($row->innertext, 'Spielklassen') !== false) {
        $isKlasse = true;
      } else if (strpos($row->innertext, 'Pokal') !== false) {
        $isKlasse = false;
      } else {
        if ($isKlasse && $zeile === 1) {
          $zeile++;
          continue;
        } else if ($isKlasse && $zeile >= 2) {
          if (strpos($row->find('td', 1)->innertext, 'Relegation') !== false) {
            continue;
          }
          if (strcmp($row->find('td', 0)->innertext, $mannschaft) === 0) {
            return $zeile;
          }
        }
      }
      $zeile++;
    }

    return 0;
  }

  private static function rp_finde_passende_zeile_mannschaft($table, $clubName) {
    $name = substr(strstr($clubName," "), 1);
    $alternativerClubName = 'SG-' . $name;

    $zeile = 0;

    foreach ($table->find('tr') as $row) {
      if (strpos($row->find('td', 2)->innertext, $clubName) !== false ||
          strpos($row->find('td', 2)->innertext, $alternativerClubName) !== false) {
        return $zeile;
      }
      $zeile++;
    }

    return 0;
  }

  private function rp_schreibe_mannschaften_in_datenbank($mannschaften) {
    global $wpdb;
    $wpdb->show_errors();
    $table_name = $wpdb->prefix . 'rp_mannschaften_daten';

    // setze alle Links auf 'none'

    $ids = $wpdb->get_var("SELECT COUNT('id') FROM $table_name");

    if ($ids > 0) {
      for ($i = 1; $i <= $ids; $i++) {
        $wpdb->update(
          $table_name,
          array('link' => 'none'),
          array('id' => $i)
        );
      }
    }

    foreach ($mannschaften as $mannschaft) {
      $wpdb->replace($table_name, $mannschaft);
    }

    $wpdb->print_error();
  }

  private function rp_cleanup() {
    unset($this->html);

    $this->tableStartseite->clear();
    unset($this->tableStartseite);
  }
}

$cron = new CronMannschaften();
unset($cron);
?>
