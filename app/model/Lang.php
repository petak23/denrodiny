<?php

namespace DbTable;
use Nette;

/**
 * Model, ktory sa stara o tabulku lang
 * (c) Ing. Peter VOJTECH ml.
 * Posledna zmena 23.01.2014
 */
class Lang extends Table
{
  /** @var string */
  protected $tableName = 'lang';

  /**
   * Vracia vsetky akceptovane jazyky
   * @return \Nette\Database\Table\Selection
   */
  public function getAkceptovane()
  {
    return $this->findBy(array("prijaty"=>1));
  }
}
