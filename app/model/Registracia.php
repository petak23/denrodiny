<?php
namespace DbTable;

/**
 * Model starajuci sa o tabulku registracia
 * Posledna zmena 10.02.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class Registracia extends Table {
  /** @var string */
  protected $tableName = 'registracia';

  /** Hlada urovne registracie uzivatela v rozsahu od do
    * @param int $min
    * @param int $max
    * @return \Nette\Database\Table\Selection
    */
  public function hladaj_urovne($min, $max) {
    return $this->findBy(array('id>='.$min, 'id<='.$max));
  }

  /** Dava vsetky urovne registracie do poľa role=>id
    * @return array
    */
  public function vsetky_urovne_array() {
    return $this->findAll()->fetchPairs('role', 'id');
  }
  
  /** Hodnoty id=>nazov pre formulare
   * @param int $id_reg Uroven registracie
   * @return array
   */
  public function urovneReg($id_reg) {
    return $this->hladaj_urovne(0, $id_reg)->fetchPairs('id', 'nazov');
  }
    
}
