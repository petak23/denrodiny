<?php
namespace App\AdminModule\Components\Oznam;
use Nette;
use DbTable;

/**
 * Komponenta pre zobrazenie aktualnych oznamov pre ADMIN modul
 * Posledna zmena(last change): 12.01.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 *
 */
class AktualneOznamyControl extends Nette\Application\UI\Control {
  
  /** @var Nette\Database\Table\Selection */
  private $oznam;
  
  /** @var array Texty pre výpis */
  private $texty = array("viac"=>"viac",
                         "title"=>"Zobrazenie celého obsahu.",
                        );

  public function __construct(DbTable\Oznam $oznam) {
    parent::__construct();
    $this->oznam = $oznam->aktualne();
  }

  /** Nastavenie textov pre komponentu
   * @param array $t Texty pre komponentu
   */
  public function setTexty($t) {
    if (is_array($t) && count($t)) { $this->texty = array_merge ($this->texty, $t); }
  }
  
  public function render() {
    $this->template->setFile(__DIR__ . '/Aktualne.latte');
    $this->template->oznamy = $this->oznam;
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
    return $template;
	}
}

interface IAktualneOznamyControl {
  /** @return AktualneOznamyControl */
  function create();
}