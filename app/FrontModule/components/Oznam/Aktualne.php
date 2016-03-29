<?php
namespace App\FrontModule\Components\Oznam;
use Nette;
use DbTable;

/**
 * Komponenta pre zobrazenie aktualnych oznamov pre FRONT modul
 * Posledna zmena(last change): 15.01.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.4
 *
 */
class AktualneOznamyControl extends Nette\Application\UI\Control {
  /** @var \Nette\Database\Table\Selection */
  private $oznam;
  /** @var array Texty pre výpis */
  private $texty = [
      "h2"    =>"Aktuality",
      "viac"  =>"viac",
      "title" =>"Zobrazenie celého obsahu.",
                        ];
  private $nastavenie;

  /** @param DbTable\Oznam $oznam  */
  public function __construct(DbTable\Oznam $oznam) {
    parent::__construct();
    $this->oznam = $oznam->aktualne();
    
  }

  /** @param array $nastavenie
   * @return \App\FrontModule\Components\Oznam\AktualneOznamyControl
   */
  public function setNastavenie($nastavenie) {
    $this->nastavenie = $nastavenie;
    return $this;
  }

  /** Nastavenie textov pre komponentu
   * @param array $t Texty pre komponentu
   * @return \App\FrontModule\Components\Oznam\AktualneOznamyControl
   */
  public function setTexty($t) {
    if (is_array($t) && count($t)) { $this->texty = array_merge ($this->texty, $t); }
    return $this;
  }
  
  public function render() {
    $this->template->setFile(__DIR__ . '/Aktualne.latte');
    $this->template->oznamy = $this->oznam;
    $this->template->nastavenie = $this->nastavenie;
    $this->template->texty = $this->texty;
    $this->template->render();
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
				$vysledok = $rozloz[0].\Nette\Utils\Html::el('a class="cely_clanok"')->href($servise->link("this"))->title($servise->texty["title"])
                ->setHtml('&gt;&gt;&gt; '.$servise->texty["viac"]).'<div class="ostatok">'.$rozloz[1].'</div>';
			}
      return $vysledok;
    });
    $template->addFilter('textreg', function ($text, $id_registracia, $max_id_reg) {
      for ($i = $max_id_reg; $i>=0; $i--) {
        $znacka_zac = "#REG".$i."#"; //Pociatocna znacka
        $znacka_kon = "#/REG".$i."#";//Koncova znacka
        $poloha = strpos($text, $znacka_zac);       //Nájdem pociatocnu znacku
        if (!($poloha === false)) {                 //Ak som našiel
          $poloha_kon = strpos($text, $znacka_kon); //Nájdem koncovu znacku
          if (!($poloha_kon === false)) {           //Ak som našiel
            if ($i > $id_registracia) {             //Som nad mojou dovolenou urovnou
              $text = substr($text, 0, $poloha).substr($text, $poloha_kon+strlen($znacka_kon));
            } else {
              //Vypusť pociatocnu a koncovu znacku
              $text = substr($text, 0, $poloha).substr($text, $poloha+strlen($znacka_zac), $poloha_kon-$poloha-strlen($znacka_zac))
                      .substr($text, $poloha_kon+strlen($znacka_kon));
            }
          }
			  }
      }
      return $text;
    });
    return $template;
	}
}

interface IAktualneOznamyControl {
  /** @return AktualneOznamyControl */
  function create();
}