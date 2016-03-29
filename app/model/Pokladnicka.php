<?php
namespace DbTable;
use Nette;

/**
 * Model starajuci sa o tabulku pokladnicka
 * @copyright (c) 2014, Ing. Peter VOJTECH ml.
 * @version 1.0.1
 * Posledna zmena 25.06.2014
 */
class Pokladnicka extends Table
{
  /** @var string */
  protected $tableName = 'pokladnicka';

  /**
   * Vrati vsetky polozky v poradi od najnovsej
   * @return \Nette\Database\Table\Selection
   */
  public function pokladnicka()
  {
    return $this->getTable()->order('created ASC');
  }
}
