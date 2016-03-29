<?php

namespace DbTable;
use Nette\Database;
/**
 * Model, ktory sa stara o tabulku verzie
 * Posledna zmena 17.12.2015
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Verzie extends Table {
  /** @var string */
  protected $tableName = 'verzie';

  /**
   * Vrati vsetky verzie v poradi od najnovsej
   * @return \Nette\Database\Table\Selection
   */
  public function vsetky() {
      return $this->getTable()->order('cislo DESC');
  }

  /**
   * Vrati najnovsiu verziu
   * @return \Nette\Database\Table\ActiveRow|FALSE
   */
  public function posledna() {
      return $this->vsetky()->limit(1)->fetch();
  }
  
  /**
   * 
   * @param Nette\Utils\ArrayHash $values
   * @return Nette\Database\Table\ActiveRow|FALSE
   * @throws Nette\Database\DriverException
   */
  public function ulozVerziu($values) {
    try {
      $id = isset($values->id) ? $values->id : 0;
      unset($values->posli_news, $values->id);
      return $this->uloz($values, $id);
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    }
  }
}
