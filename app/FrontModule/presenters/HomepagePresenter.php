<?php
namespace App\FrontModule\Presenters;

use Language_support;

/**
 * Prezenter pre homepage.
 * 
 * Posledna zmena(last change): 03.02.2016
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.9
 */
class HomepagePresenter extends \App\FrontModule\Presenters\BasePresenter {
  /**
   * @inject
   * @var Language_support\Homepage
   */
  public $texty_presentera;
 
  // -- Komponenty
  /** @var \App\FrontModule\Components\Clanky\IAktualneClankyControl @inject */
  public $aktualneClankyControlFactory;
  /** @var \App\FrontModule\Components\Clanky\IAktualnyProjektControl @inject */
  public $aktualnyProjektControlFactory;
  
  /** Vychodzie nestavenia */
	protected function startup() {
    parent::startup();
    //Len na to aby som vedel zobraziť odkaz na aktuality
    $this->template->aktuality = $this->hlavne_menu->findBy(["datum_platnosti >= '".StrFTime("%Y-%m-%d",strtotime("0 day"))."'",
                                                             "id_registracia <= ".(($this->user->isLoggedIn()) ? $this->user->getIdentity()->id_registracia : 0),
                                                             "id_nadradenej = ".($this->template->id_nadradeny_aktuality = 1)]);
  }
  
  /** Zakladna akcia */
  public function actionDefault() {
    $nastavenie = $this->context->parameters;
    //Ak je presmerovanie povolene v configu
    if ($nastavenie['homepage_redirect']) {
      $pom = explode(" ", $nastavenie['homepage_redirect']);
      if (count($pom)>1) { 
        $this->redirect(301, $pom[0], $pom[1]);
      } else { 
        $this->redirect(301, $pom[0]);
      }
    }
  }
  
  public function renderDefault() {
    $this->template->fotogalery = [
      "01" => ["prosby", "OMŠA"],
      "02" => ["kapela", "OMŠA"],
      "03" => ["stánky", "PONUKA"],
      "04" => ["rodiny", "PROGRAM"],
      "05" => ["erko", "DIVADLO"],
      "06" => ["deti", "DIVÁCI"],
      "07" => ["sestry", "PROGRAM"],
      "08" => ["posedenie", "AREÁL"],
      "09" => ["ukážka", "POŽIARNICI"],
      "10" => ["muži", "RODINA"],
      "11" => ["spolu", "PROGRAM"],
      "12" => ["ženy", "RODINA"],
      "13" => ["zábava", "PROGRAM"],
      "14" => ["tombola", "PROGRAM"],
      "15" => ["cukor a soľ", "PROGRAM"],
      "16" => ["spoločenstvo", "DIVÁCI"],
      "17" => ["mestečko", "SKAUTI"],
      "18" => ["informácie", "SKAUTI"],
      "19" => ["mestečko", "SKAUTI"],
      "20" => ["deti", "RODINA"],
      "21" => ["podsada", "SKAUTI"],
      "22" => ["dielne", "PROGRAM"],
      "23" => ["spoločenstvo", "DIVÁCI"],
      "24" => ["posedenie", "DIVÁCI"],
      "25" => ["požiarnici", "ATRAKCIA"],
      "26" => ["miesto", "DEŇ RODINY"],
    ];
  }
  
  /** Akcia pri presmerovani z nedovoleneho pristupu */
  public function actionNotAllowed() {
    $this->setView("Default");
  }
  
	/** Komponenta pre zobrazenie clanku
   * @return \App\FrontModule\Components\Clanky\ZobrazClanokControl
   */
  public function createComponentAktualnyProjekt() {
    $aktualny_projekt = $this->aktualnyProjektControlFactory->create();
    $aktualny_projekt->setTexts(["text_title_image"  =>$this->trLang('base_text_title_image'),
                                 "h2"                =>$this->trLang('base_aktualny_projekt')])
                     ->setLanguage_id($this->language_id)
                     ->setAvatarPath($this->avatar_path);
		return $aktualny_projekt;
  }
  
  /** Komponenta pre zobrazenie aktualnych clankov 
   * @return \App\FrontModule\Components\Clanky\AktualneClankyControl
   */
  public function createComponentAktualneClanky() {
    $aktualne_clanky = $this->aktualneClankyControlFactory->create();
    $aktualne_clanky->setTexts(['h2'=>    $this->trLang('base_aktualne_h2'),
                                'title'=> $this->trLang("base_title"),
                                'viac' => $this->trLang("base_viac")])
		                ->setAvatarPath($this->avatar_path);
		return $aktualne_clanky;
  }
  
}