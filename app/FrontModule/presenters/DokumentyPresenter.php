<?php
namespace App\FrontModule\Presenters;

use Language_support;
/**
 * Prezenter pre smerovanie na dokumenty.
 * Posledna zmena(last change): 20.07.2015
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015, Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.3a
 */

class DokumentyPresenter extends \App\FrontModule\Presenters\BasePresenter {
	/**
   * @inject
   * @var Language_support\Dokumenty
   */
  public $texty_presentera;

  /** Vychodzie nestavenia */
	protected function startup() {
    parent::startup();
    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect('Homepage:', sprintf($this->trLang('base_nie_je_opravnenie'), $this->action), 'danger');
    }
	}
  
  /** Vychodzia akcia
   * @param int $id Id dokumentu na zobrazenie
   */
	public function actionDefault($id) {
		if (($dokument = $this->dokumenty->find($id)) === FALSE) {
      $this->error($this->trLang('dokument_not_found')); 
    } 
		$dokument->update(array('pocitadlo'=>$dokument->pocitadlo + 1));

		$this->redirectUrl("http://".$this->nazov_stranky."/".$dokument->subor);
		exit;
	}
}