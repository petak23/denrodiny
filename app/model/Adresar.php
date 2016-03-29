<?php

namespace DbTable;
use Nette\Database;

/**
 * Model starajuci sa o tabulku adresar
 * Posledna zmena(last change): 17.12.2015
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Adresar extends Table {
  /** @var string */
  protected $tableName = 'adresar';

  /**
   * 
   * @param Nette\Utils\ArrayHash $values
   * @return Nette\Database\Table\ActiveRow|FALSE
   * @throws Nette\Database\DriverException
   */
  public function ulozAdresu($values) {
    try {
      $id = isset($values->id) ? $values->id : 0;
      unset($values->id);
      return $this->uloz($values, $id);
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    }
  }
}