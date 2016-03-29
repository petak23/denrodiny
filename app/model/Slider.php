<?php

namespace DbTable;
use Nette;

/**
 * Model, ktory sa stara o tabulku slider
 * Posledna zmena 16.03.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Slider extends Table {
  /** @var string */
  protected $tableName = 'slider';

  /**
   * Vrati vsetky polozky z tabulky slider usporiadane podla "usporiadaj"
   * @param string $usporiadaj - názov stlpca, podla ktoreho sa usporiadava a sposob
   * @return Nette\Database\Table\Selection */
  function getSlider($usporiadaj = 'poradie ASC') {
		return $this->findAll()->order($usporiadaj);//->limit($pocet);
	}

}
