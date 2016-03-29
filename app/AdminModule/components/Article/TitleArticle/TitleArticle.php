<?php
namespace App\AdminModule\Components\Article\TitleArticle;

use Nette;
use DbTable;

/**
 * Komponenta pre vytvorenie hlavičky polozky.
 * 
 * Posledna zmena(last change): 04.03.2016
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.4
 */

class TitleArticleControl extends Nette\Application\UI\Control {
	/** @var DbTable\Hlavne_menu_lang */
	public $hlavne_menu_lang;
  
  /** @var Nette\Database\Table\ActiveRow|FALSE Nadradena polozka menu */
	public $hlavne_menu_nadradeny;
 
  /** @var Nette\Database\Table\ActiveRow $clanok Info o clanku */
  private $clanok;
  /** @var string $odkaz Odkaz */
  private $odkaz;
  /** @var boolean $zobraz_anotaciu Zobrazenie anotacie polozky*/
  private $zobraz_anotaciu;
  /** @var boolean $aktualny_projekt_enabled Povolenie aktualneho projektu */
  private $aktualny_projekt_enabled;
  /** @var boolean $komentare Povolenie komentarov */
  private $komentare;

  /** @var ZmenVlastnikaFormFactory */
	public $zmenVlastnika;
  /** @var ZmenUrovenRegistracieFormFactory */
	public $zmenUrovenRegistracie;
  /** @var ZmenDatumPlatnostiFormFactory */
	public $zmenDatumPlatnosti;

  /**
   * @param DbTable\Hlavne_menu_lang $hlavne_menu_lang
   * @param ZmenVlastnikaFormFactory $zmenVlastnikaFormFactory
   * @param ZmenUrovenRegistracieFormFactory $zmenUrovenRegistracieFormFactory */
  public function __construct(DbTable\Hlavne_menu_lang $hlavne_menu_lang, 
                              ZmenVlastnikaFormFactory $zmenVlastnikaFormFactory, 
                              ZmenUrovenRegistracieFormFactory $zmenUrovenRegistracieFormFactory,
                              ZmenDatumPlatnostiFormFactory $zmenDatumPlatnostiFormFactory
                             ) {
    parent::__construct();
    $this->hlavne_menu_lang = $hlavne_menu_lang;
    $this->zmenVlastnika = $zmenVlastnikaFormFactory;
    $this->zmenUrovenRegistracie = $zmenUrovenRegistracieFormFactory;
    $this->zmenDatumPlatnosti = $zmenDatumPlatnostiFormFactory;
  }
  
  /** Nastavenie komponenty
   * @param Nette\Database\Table\ActiveRow $clanok
   * @param string $odkaz
   * @param boolean $komentare Povolenie komentarov
   * @param boolean $aktualny_projekt_enabled Povolenie aktualneho projektu
   * @param boolean $zobraz_anotaciu Zobrazenie anotacie polozky
   * @return \App\AdminModule\Components\Article\TitleArticleControl */
  public function setTitle(Nette\Database\Table\ActiveRow $clanok, $odkaz, $komentare = FALSE, $aktualny_projekt_enabled = FALSE, $zobraz_anotaciu = FALSE) {
    $this->clanok = $clanok;
    $this->odkaz = $odkaz;
    $this->komentare = $komentare;
    $this->aktualny_projekt_enabled = $aktualny_projekt_enabled;
    $this->zobraz_anotaciu = $zobraz_anotaciu;
    $this->hlavne_menu_nadradeny = $this->hlavne_menu_lang->findOneBy(["id_hlavne_menu" => $this->clanok->hlavne_menu->id_nadradenej]);
    return $this;
  }
  
  /** 
   * Render 
   * @param array $params Parametre komponenty - [admin_links]*/
	public function render($params) {
    $this->template->setFile(__DIR__ . '/TitleArticle.latte');
    $this->template->clanok = $this->clanok;
    $this->template->por_podclanky = $this->hlavne_menu_lang->findBy(["hlavne_menu.id_nadradenej"=>$this->clanok->id_hlavne_menu]);
    $this->template->odkaz = ":".$this->odkaz.":zmenVlastnika";
    $this->template->vlastnik = $params['admin_links']['vlastnik'];
    $this->template->admin_links = $params['admin_links'];
    $this->template->komentare_enabled = $this->komentare;
    $this->template->nadradeny = $this->clanok->hlavne_menu->id_nadradenej !== NULL ? $this->hlavne_menu_nadradeny->hlavne_menu : NULL;
    $this->template->aktualny_projekt_enabled = $this->aktualny_projekt_enabled;
    $this->template->zobraz_anotaciu = $this->zobraz_anotaciu;
		$this->template->render();
	}
  
  /** 
   * Signal pre povolenie/zakazanie komentarov
   * @param int $volba Nastavenie  */
  public function handleKomentare($volba) {
    if ($this->presenter->udaje_webu["komentare"] && $volba>=0 && $volba<=1) {
			$this->clanok->hlavne_menu->update(['komentar'=>$volba]);
		} 
    if (!$this->presenter->isAjax()) {
      $this->presenter->redirect('this');
    } else {
      $this->redrawControl('zobrazClanok-komentare');
    }
  }
  
	/** 
   * Signal pre nastavenie/zrusenie aktualneho projektu 
   * @param int $volba Nastavenie  */
	public function handleAktualnyProjekt($volba) {
    if ($this->presenter->nastavenie["aktualny_projekt_enabled"] && $volba>=0 && $volba<=1) {
      $this->clanok->hlavne_menu->update(['aktualny_projekt'=>$volba]);
    }
    if (!$this->presenter->isAjax()) {
      $this->presenter->redirect('this');
    } else {
      $this->redrawControl('');
    }
	}
   
  /** Signal pre nastavenie priameho clanku */
  public function handlePriamyClanok() {
    $this->hlavne_menu_nadradeny->hlavne_menu->update([
      'redirect_id' => (isset($this->hlavne_menu_nadradeny->hlavne_menu->redirect_id) && $this->clanok->id_hlavne_menu == $this->hlavne_menu_nadradeny->hlavne_menu->redirect_id) ? NULL : $this->clanok->id_hlavne_menu
    ]);
    if (!$this->presenter->isAjax()) {
      $this->presenter->redirect('this');
    } else {
      $this->redrawControl('');
    }
	}
  
  /** 
   * @param Nette\Application\UI\Form $form
   * @return Nette\Application\UI\Form */
  protected function _formMessage($form) {
    $form['uloz']->onClick[] = function ($button) { 
      $this->presenter->flashOut(!count($button->getForm()->errors), 'this', 'Zmena bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
		};
    return $this->presenter->_vzhladForm($form);
  }

  /** 
   * Komponenta formulara pre zmenu vlastnika.
   * @return Nette\Application\UI\Form */
  public function createComponentZmenUrovenRegistracieForm() {
    return $this->_formMessage($this->zmenUrovenRegistracie->create($this->clanok->id_hlavne_menu, $this->clanok->hlavne_menu->id_registracia));
  }
  
  /** 
   * Komponenta formulara pre zmenu urovne registracie.
   * @return Nette\Application\UI\Form */
  public function createComponentZmenVlastnikaForm() {
    return $this->_formMessage($this->zmenVlastnika->create($this->clanok->id_hlavne_menu, $this->clanok->hlavne_menu->id_user_profiles));
  }
  
  /** 
   * Komponenta formulara pre zmenu datumu platnosti.
   * @return Nette\Application\UI\Form */
  public function createComponentZmenDatumPlatnostiForm() {
    return $this->_formMessage($this->zmenDatumPlatnosti->create($this->clanok->id_hlavne_menu, $this->clanok->hlavne_menu->datum_platnosti));
  }
  
  /** Signal pre zmenu zoradenia podclanokv podla poradia od 9 do 1 */
  public function handlePodclankyZoradenie() {
    $this->clanok->hlavne_menu->update(['poradie_podclankov'=>(1 - $this->clanok->hlavne_menu->poradie_podclankov)]);
		if (!$this->presenter->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('');
    }
	}
}

interface ITitleArticleControl {
  /** @return TitleArticleControl */
  function create();
}