<?php
namespace App\FrontModule\Presenters;

use \Nette\Application\UI\Multiplier;
use DbTable, Language_support;

/**
 * Prezenter pre spravu oznamov.
 * (c) Ing. Peter VOJTECH ml.
 * Posledna zmena(last change): 26.01.2016
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.5a
 *
 * Akcie: Default - zobrazenie vsetkych aktualnych oznamov a rozhodovanie 
 */
class OznamPresenter extends \App\FrontModule\Presenters\BasePresenter {
  /** 
   * @inject
   * @var DbTable\Oznam */
	public $oznam;
  /**
   * @inject
   * @var Language_support\Oznam */
  public $texty_presentera;

	/** @var \Nette\Database\Table\Selection */
	private $aktualne;
  
  // -- Komponenty
  /** @var \App\FrontModule\Components\Oznam\IPotvrdUcastControl @inject */
  public $potvrdUcastControlFactory;
  /** @var \App\FrontModule\Components\Oznam\IKomentarControl @inject */
  public $komentarControlControlFactory;

  /** Akcia pre nacitanie aktualnych oznamov */
	public function actionDefault() {
    //Z DB zisti ako budu oznamy usporiadane
    if (($pomocna = $this->udaje->getKluc("oznam_usporiadanie")) !== FALSE) {
      $oznamy_usporiadanie = (boolean)$pomocna->text;
    } else { $oznamy_usporiadanie = FALSE; }
    $this->aktualne = $this->oznam->aktualne($oznamy_usporiadanie);
    //Ak nie su oznamy najdi 1. clanok cez udaje a ak je tak presmeruj na neho
    if ($this->aktualne->count() == 0) {
      if (($id = $this->udaje->getUdajInt('oznam_prva_stranka')) > 0) {
        $this->flashRedirect(['Clanky:default', $id], $this->trLang('ziaden_aktualny'), 'info');
      } else {
        $this->setView("prazdne");
      }
    }
	}
  
  /** Render pre vypis aktualnych oznamov */
	public function renderDefault() {
    $this->template->plati_do = $this->trLang('plati_do');
    $this->template->zverejnene = $this->trLang('zverejnene');
    $this->template->zobrazeny = $this->trLang('zobrazeny');
    $this->template->h2 = $this->trLang('h2');
    $this->template->oznamy = $this->aktualne;
	}
  
  /** Render pre vypis infa o tom, ze nie su aktualne oznamy */
  public function renderPrazdne() {
    $this->template->h2 = $this->trLang('h2');
    $this->template->ziaden_aktualny = $this->trLang('ziaden_aktualny');
  }

  /** Obsluha potvrdenia ucasti 
   * @return Multiplier */
	public function createComponentPotvrdUcast() {
		return new Multiplier(function ($id_oznam) {
			$potvrd = $this->potvrdUcastControlFactory->create();
			$potvrd->setParametre($id_oznam);
			return $potvrd;
		});
	}

  /** Obsluha komentara
   * @return Multiplier */
	public function createComponentKomentar() {
		return new Multiplier(function ($id_oznam) {
      $komentar = $this->komentarControlControlFactory->create();
      $komentar->setParametre($id_oznam);
			return $komentar;
		});
	}

  protected function createTemplate($class = NULL) {
    $servise = $this;
    $template = parent::createTemplate($class);
    $template->addFilter('obr_v_txt', function ($text) use($servise){
      $rozloz = explode("#", $text);
      $serv = $servise->presenter;
      $vysledok = '';
      $cesta = 'http://'.$serv->nazov_stranky."/";
      foreach ($rozloz as $k=>$cast) {
        if (substr($cast, 0, 2) == "I-") {
          $obr = $serv->dokumenty->find((int)substr($cast, 2));
					if ($obr !== FALSE) {
            $cast = \Nette\Utils\Html::el('a class="fotky" rel="fotky"')->href($cesta.$obr->subor)->title($obr->nazov)
                                  ->setHtml(\Nette\Utils\Html::el('img')->src($cesta.$obr->thumb)->alt($obr->nazov));
					}
        }
        $vysledok .= $cast;
      }
      return $vysledok;
    });
    $template->addFilter('koncova_znacka', function ($text) use($servise){
      $rozloz = explode("{end}", $text);
      $vysledok = $text;
			if (count($rozloz)>1) {		 //Ak som nasiel znacku
				$vysledok = $rozloz[0].\Nette\Utils\Html::el('a class="cely_clanok"')->href($servise->link("this"))->title($servise->trLang("base_title"))
                ->setHtml('&gt;&gt;&gt; '.$servise->trLang("base_viac")).'<div class="ostatok">'.$rozloz[1].'</div>';
			}
      return $vysledok;
    });
    return $template;
	}
}