<?php

namespace DbTable;
/**
 * Model, ktory sa stara o tabulku clanok_komentar
 * Posledna zmena 17.02.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Clanok_komentar extends Table {
  /** @var string */
  protected $tableName = 'clanok_komentar';

  /** Najdenie komentarov
   * @param type $id_hlavne_menu
   * @return type
   */
  public function najdiKomentar($id_hlavne_menu) {
    return $this->findBy(['id_hlavne_menu'=>$id_hlavne_menu])->order('timestamp DESC');
  }
  
  /** Vymazanie komentara
   * @param int $id_hlavne_menu
   * @return int
   */
  public function vymazKomentar($id_hlavne_menu) {
    return $this->findBy(['id_hlavne_menu'=>$id_hlavne_menu])->delete();
  }
}
