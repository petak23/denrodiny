<?php

namespace DbTable;

/**
 * Model starajuci sa o tabulku clanok_komponenty
 * Posledna zmena(last change): 12.01.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class Clanok_komponenty extends Table {
  /** @var string */
  protected $tableName = 'clanok_komponenty';

  /** Testuje, jedinecne komponenty ci su uz pridane k clanku. Ak ano vypusti
   * @param array $komponenty Pole dostupnych komponent
   * @param int $id_hlavne_menu Id clanku
   * @return array
   */
  public function testJedinecnosti($komponenty, $id_hlavne_menu) {
    $out = array();
    foreach ($komponenty as $k=>$v) {//kontrola jedinecnych komponent. Ak uz su priradene tak sa vypustia
      if ($v["jedinecna"]) {
        if ($this->findOneBy(["id_hlavne_menu"=>$id_hlavne_menu, "spec_nazov" => $k]) === FALSE) {
          $out[$k] = $v;
        }
      } else {
        $out[$k] = $v;
      }
    }
    return $out;
  }
  
  /** Vracia vsetky komponenty priradene k polozke
   * @param int $id_hlavne_menu Id prislusneho clanku
   * @param array $komponenty Info o komponentach
   * @return array
   */
  public function getKomponenty($id_hlavne_menu, $komponenty) {
    $out = [];
    $pom = $this->getTable()->where("id_hlavne_menu", $id_hlavne_menu);
    foreach ($pom as $value) {
      $tmp_komp = [];
      $tmp_komp['nazov'] = $value->spec_nazov;
      if (isset($value->parametre)) {
        $tnk = explode(",", $komponenty[$value->spec_nazov]['parametre']);
        $ttk = explode(",", $value->parametre);
        $tmp_komp['parametre'] = array_combine ($tnk, $ttk);
      }
      $out[] = $tmp_komp;
    }
    return $out;
  }
}