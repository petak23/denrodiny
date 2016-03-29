<?php
namespace App\FrontModule\Presenters;

use DbTable, Language_support;
/**
 * Prezenter pre vypis stavu pokladnicky.
 * Posledna zmena(last change): 20.07.2015
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.3
 *
 * Akcie: Default - zobrazenie obsahu pokladnicky
 */
class PokladnickaPresenter extends \App\FrontModule\Presenters\BasePresenter {
  /** 
   * @inject
   * @var DbTable\Pokladnicka */
	public $pokladnicka;
  /**
   * @inject
   * @var Language_support\Pokladnicka
   */
  public $texty_presentera;

	protected function startup()	{
	    parent::startup();
      // Kontrola prihlasenia - ak >0 ok a ACL
      if (!$this->user->isLoggedIn()) {
        $this->flashRedirect(array('User:', array('backlink'=>$this->storeRequest())), $this->trLang('base_nie_je_opravnenie1').'<br/>'.$this->trLang('base_prihlaste_sa'), 'danger,n');
      }
      // Kontrola ACL
      if (!$this->user->isAllowed($this->name, $this->action)) {
        $this->flashRedirect('Homepage:', sprintf($this->trLang('base_nie_je_opravnenie'), $this->action), 'danger');
      }
	}

	public function renderDefault()	{
		$this->template->h2 = $this->trLang('h2').':';
    $this->template->suma = $this->trLang('suma');
    $this->template->ucel = $this->trLang('ucel');
    $this->template->datum = $this->trLang('datum');
    $this->template->suma_za_rok = $this->trLang('suma_za_rok');
    $this->template->aktualny_stav = $this->trLang('aktualny_stav');
		$this->template->pokladnicka = $this->pokladnicka->pokladnicka();
	}
}
