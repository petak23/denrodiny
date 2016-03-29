<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use DbTable;

/**
 * Prezenter pre nastavenie debaty.
 * 
 * Posledna zmena(last change): 07.07.2015
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */

class DebataPresenter extends \App\AdminModule\Presenters\BasePresenter {
	/** 
   * @inject
   * @var DbTable\Debata */
	public $debata;

  /** Vychodzia akcia
   * @param int $id Id polozky na zobrazenie
   */
	public function actionDefault() {
		
	}

}