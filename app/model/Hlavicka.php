<?php

namespace DbTable;

/**
 * Model starajuci sa o tabulku hlavicka
 * Posledna zmena 10.02.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class Hlavicka extends Table {
  /** @var string */
  protected $tableName = 'hlavicka';
  
  /** Hodnoty id=>nazov pre formulare
   * @return array
   */
  public function hlavickaForm() {
    return $this->findAll()->fetchPairs('id', 'nazov');
  }  
}
