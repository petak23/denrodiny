<?php
namespace PeterVojtech\Dokumenty;
use Nette;

/**
 * Komponenta pre zobrazenie nahodnej fotky z dokumentov
 * Posledna zmena(last change): 19.08.2015
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */

class NahodnaFotkaControl extends Nette\Application\UI\Control {
  
  /** @var array - texty pre sablonu */
  private $texty = array(
    "notFound"  => "Žiadna fotka sa nenašla!",
    "h4"        => "niečo z galérie...!"
    );
  /** @var Nette\Database\Table\Selection */
  private $dokumenty;

  /**
   * @param Nette\Database\Table\Selection $dokumenty
   */
  public function __construct(Nette\Database\Table\Selection $dokumenty) {
    parent::__construct();
    //Najdem si len obrazky
    $this->dokumenty = $dokumenty->where("pripona", array("jpg", "png", "gif", "bmp"));
  }
  
  /** Nastavenie textov pre sablonu pre ine jazyky
   *  @param array $texts texty do sablony
   */
  public function setTexts($texts) {
    if (is_array($texts)) { $this->texty = array_merge($this->texty, $texts);}
  }

  /** Render funkcia pre vypisanie odkazu na clanok 
  * @see Nette\Application\Control#render()
  */
  public function render() {
    if (count($this->dokumenty)) {//Ak nieco mam, tak najdem nahodny
      $obrazky = $this->dokumenty->limit(1, rand(0, count($this->dokumenty)-1))->fetch();
      $alt = $obrazky->popis;
      $src = $obrazky->subor;
    } else {
      $alt = $this->texty["notFound"];
      $src = "www/images/otaznik.png";
    }
    $this->template->setFile(__DIR__ . '/NahodnaFotka.latte');
    $this->template->h4 = $this->texty["h4"];
    $this->template->alt = $alt;
    $this->template->src = $src;
    $this->template->render();
  }
}