<?php

namespace App\AdminModule\Components\Produkt;
use Nette;

/**
 * Komponenta pre zobrazenie zoznamu produktov pre FRONT modul
 * Posledna zmena(last change): 23.04.2015
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 *
 */
class ProduktyZoznamControl extends Nette\Application\UI\Control {
  
  /** Render funkcia pre vypisanie odkazu na produkty 
  * @see Nette\Application\Control#render()
  */
  public function render() {
    $pthis = $this->presenter;
    $ihm = $pthis->zobraz_clanok->id_hlavne_menu;
    $produkty = $pthis->produkt_prepoj->findBy(array("id_hlavne_menu_lang"=>$ihm))->order("produkt_lang.nazov ASC");
    $this->template->setFile(__DIR__ . '/Produkty.latte');
    $this->template->id_hlavne_menu = $ihm;
    $this->template->produkty = $produkty;
    $this->template->render();
  }
}