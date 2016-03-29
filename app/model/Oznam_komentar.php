<?php
namespace DbTable;

/**
 * Model, ktory sa stara o tabulku oznam_komentar
 * Posledna zmena 01.02.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class Oznam_komentar extends Table {
  /** @var string */
  protected $tableName = 'oznam_komentar';

  public function najdiKomentar($id_oznam) {
    return $this->findBy(['id_oznam'=>$id_oznam])->order('timestamp DESC');
  }
  
  /**
   * @param int $id_oznam
   * @return int
   */
  public function vymazKomentar($id_oznam) {
    return $this->findBy(['id_oznam'=>$id_oznam])->delete();
  }
}