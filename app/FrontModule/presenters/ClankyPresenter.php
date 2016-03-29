<?php
namespace App\FrontModule\Presenters;

use Nette\Application\UI\Multiplier;
use DbTable, Language_support;

/**
 * Prezenter pre vypisanie clankov.
 * Posledna zmena(last change): 17.03.2016
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.7
 */

class ClankyPresenter extends \App\FrontModule\Presenters\BasePresenter {
	/** 
   * @inject
   * @var DbTable\Clanok_komponenty */
	public $clanok_komponenty;

  /**
   * @inject
   * @var Language_support\Clanky */
  public $texty_presentera;
  
  /** @var \App\FrontModule\Components\Clanky\IKomentarControl @inject */
  public $komentarControlControlFactory;
  /** @var \App\FrontModule\Components\Clanky\IPrilohyClanokControl @inject */
  public $prilohyClanokControlFactory;
  
	/** @var \Nette\Database\Table\ActiveRow|FALSE */
	public $zobraz_clanok = FALSE;

  /** Vychodzie nastavenia */
	protected function startup() {
    parent::startup();
    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect('Homepage:notAllowed', sprintf($this->trLang('base_nie_je_opravnenie'), $this->action), 'danger');
    }
  }

  /** Zobrazenie konkretneho clanku
   * @param int $id Id hlavneho menu clanku
   */
	public function actionDefault($id = 0) {
    if (($this->zobraz_clanok = $this->hlavne_menu_lang->getOneArticleId($id, $this->language_id, $this->id_reg)) === FALSE) {
      $this->setView("notFound");
    } else { 
      if (isset($this->zobraz_clanok->hlavne_menu->redirect_id)) { //Ak mám presmerovanie na podclanok
        $this->redirect("Clanky:", $this->zobraz_clanok->hlavne_menu->redirect_id);              
      }
    }
	}
  
  /** Render pre zobrazenie clanku */
	public function renderDefault()	{
    $this->template->komentare_povolene =  $this->udaje_webu["komentare"] && ($this->user->isAllowed('Front:Clanky', 'komentar') && $this->zobraz_clanok->hlavne_menu->komentar) ? $this->zobraz_clanok->id_hlavne_menu : 0;
		$this->template->h2 = $this->trLang('h2').$this->zobraz_clanok->nazov;
    $this->template->uroven = $this->zobraz_clanok->hlavne_menu->uroven+2;
    $this->template->avatar = $this->zobraz_clanok->hlavne_menu->avatar;
    $this->template->clanok_view = $this->zobraz_clanok->id_clanok_lang == NULL ? FALSE : TRUE;
    $this->template->viac_info = "";//$this->trLang('viac_info');
    //Zisti, ci su k clanku priradene komponenty
    $this->template->komponenty = $this->clanok_komponenty->getKomponenty($this->zobraz_clanok->id_hlavne_menu, $this->nastavenie["komponenty"]);
	}

  /** Render v prípade nenájdenia článku */
  public function renderNotFound() {
    $this->template->h2 = $this->trLang('nenajdeny_clanok_h2');
    $this->template->text = [
      1 => $this->trLang('ospravedln_clanok'),
      2 => $this->trLang('ospravedln_clanok_1')];
  }

  /** Komponenta pre komentare k clanku
   * @return Multiplier
   */
	public function createComponentKomentar() {
		return new Multiplier(function ($id_hlavne_menu) {
      $komentar = $this->komentarControlControlFactory->create();
      $komentar->setParametre($id_hlavne_menu);
			return $komentar;
		});
		
	}

  /** Komponenta pre zobrazenie clanku
   * @return \App\FrontModule\Components\Clanky\ZobrazClanokControl
   */
  public function createComponentUkazTentoClanok() {
    $ukaz_clanok = New \App\FrontModule\Components\Clanky\ZobrazClanokControl($this->zobraz_clanok);
    $ukaz_clanok->setTexts([
      "not_found"         => $this->trLang('base_template_not_found'),
      "platnost_do"       => $this->trLang('base_platnost_do'),
      "zadal"             => $this->trLang('base_zadal'),
      "zobrazeny"         => $this->trLang('base_zobrazeny'),  
      "anotacia"          => $this->trLang('base_anotacia'),
      "viac"              => $this->trLang('base_viac'),
      "text_title_image"  => $this->trLang("base_text_title_image"),
      ]);
    $ukaz_clanok->setClanokHlavicka($this->udaje_webu['clanok_hlavicka']);
    return $ukaz_clanok;
  }

	/** Komponenta pre zobrazenie priloh
   * @return \App\FrontModule\Components\Clanky\PrilohyClanokControl
   */
  public function createComponentPrilohy() {
    $prilohy = $this->prilohyClanokControlFactory->create();
    $prilohy->setNastav($this->zobraz_clanok->id_hlavne_menu, $this->avatar_path)
            ->setTexts([
                "not_found"   =>$this->trLang('base_not_found'),
                "h3" 					=>"",
                "nie_je"	    =>"",
                ]);
    return $prilohy;
  }
}