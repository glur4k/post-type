<?php
/**
* Eine Klasse mit einer Sammlung von Methoden, die immer gebraucht werden.
*/
class ParserUtils {
  public static function rp_clean_umlaute($string) {
    $ersetze = array("&auml;" => "ae", 'ä' => "ae", "ü" => "ue", "ö" => "oe", "Ä" => "Ae", "Ü" => "Ue", "Ö" => "Oe", "/" => "-", "&amp;" => "&", "&nbsp;" => '', ' ' => '_');
    return strtr(strtolower($string), $ersetze);
  }

  public static function rp_clean_amp($string) {
    $upas = Array("&amp;" => "&");
    return strtr($string, $upas);
  }

  /**
   * [rp_erstelle_valide_spalten_namen description]
   * @param  [type] $spalten [description]
   * @return [type]          [description]
   */
  public static function rp_erstelle_valide_spalten_namen($spalten) {
    $invalideSpalten = array(
      '1' => 'gegner1',
      '2' => 'gegner2',
      '3' => 'gegner3',
      '4' => 'gegner4',
      '5' => 'gegner5',
      '6' => 'gegner6',
      '1+2' => '1und2',
      '3+4' => '3und4',
      '5+6' => '5und6'
    );

    foreach ($spalten as $key => $spalte) {
      if (array_key_exists($key, $invalideSpalten)) {
        $spalten[$invalideSpalten[$key]] = $spalten[$key];
        unset($spalten[$key]);
      }
    }

    return $spalten;
  }


  function array_change_key_name( $orig, $new, &$array )
  {
      if ( isset( $array[$orig] ) )
      {
          $array[$new] = $array[$orig];
          unset( $array[$orig] );
      }
      return $array;
  }

  /**
   * Konvertiert die Mannschaftsnamen in saubere slugs fuer die frontend-URLs und andersrum
   * @param  string $mannschaft Mannschaftsname
   * @return string             sauberer Mannschaftsname
   */
  public static function konvertiereMannschaftsNamen($mannschaft) {
    $falscheNamen = array(
      'Herren' => 'herren',
      'Herren II' => 'herren-2',
      'Herren III' => 'herren-3',
      'Herren IV' => 'herren-4',
      'Damen' => 'damen',
      'Damen II' => 'damen-2',
      'Damen III' => 'damen-3',
      'Damen IV' => 'damen-4',
      'Jugend' => 'jugend',
      'Jugend II' => 'jugend-2',
      'Jugend III' => 'jugend-3',
      'Jugend IV' => 'jugend-4',
      'Schüler' => 'schueler',
      'Schüler II' => 'schueler-2',
      'Mädchen/Schülerinnen U18' => 'maedchen-schuelerinnen',
      'Mädchen/Schülerinnen U18 II' => 'maedchen-schuelerinnen-2'
    );

    if (array_key_exists($mannschaft, $falscheNamen)) {
      $mannschaft = $falscheNamen[$mannschaft];
    } else if (in_array($mannschaft, $falscheNamen)) {
      $mannschaft = array_search($mannschaft, $falscheNamen);
    }

    return $mannschaft;
  }

  /**
   * Baut aus Spieler Name das Kuerzel SP
   * @param string  $titel der Name des Spielers
   * @return string        das Kuerzel "SP"
   */
  public static function baueTitelKuerzel($titel) {
    $strings = explode(' ', $titel);
    foreach ($strings as $index => $string) {
      $strings[$index] = substr($string, 0, 1);
    }
    return implode('', $strings);
  }

  /**
   * Fuegt dem Bilanzwert ein +-Zeichen hinzu, falls > 0
   * @param int $bilanzwert der Name des Spielers
   * @return string         das Kuerzel "SP"
   */
  public static function signBilanzwert($bilanzwert) {
    if ($bilanzwert > 0) {
      return '+' . $bilanzwert;
    }
    return $bilanzwert;
  }

  public static function rpGetGewonneneSpiele($spiele) {
    return substr($spiele, 0, 1);
  }

  public static function rpGetVerloreneSpiele($spiele) {
    return substr($spiele, 2, 1);
  }

  public static function rp_erstelle_namen_fuer_charts_js($name) {
    $falscheNamen = array(
      'gesamt' => 'Gesamte Spiele',
      'gegner1' => 'Gegen Nr. 1',
      'gegner2' => 'Gegen Nr. 1',
      'gegner3' => 'Gegen Nr. 1',
      'gegner4' => 'Gegen Nr. 1',
      '1und2' => 'Gg. erstes Paarkreuz',
      '3und4' => 'Gg. zweites Paarkreuz',
      '5und6' => 'Gg. drittes Paarkreuz',
    );

    if (array_key_exists($name, $falscheNamen)) {
      $name = $falscheNamen[$name];
    } else if (in_array($name, $falscheNamen)) {
      $name = array_search($name, $falscheNamen);
    }

    return $name;
  }
}
?>
