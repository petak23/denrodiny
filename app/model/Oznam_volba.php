<?php
namespace DbTable;

/**
 * Model, ktory sa stara o tabulku oznam_volba
 * Posledna zmena 26.01.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Oznam_volba extends Table {
  /** @var string */
  protected $tableName = 'oznam_volba';

  /**
   * Pole moznych volieb
   * @return array
   */
  public function volby() {
    return $this->findAll()->fetchPairs("id", "volba");
  }
}