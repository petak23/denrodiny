<?php
namespace App\FrontModule\Components\Produkt;
use Nette;
use Nette\Utils\Html;

/**
 * Komponenta pre zobrazenie zoznamu produktov pre FRONT modul
 * Posledna zmena(last change): 17.04.2015
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 *
 */
class ProduktyZoznamControl extends Nette\Application\UI\Control {

  private $produkt_lang_str;
  
  public function __construct($produkt_lang_str) {
    parent::__construct();
    $out = array();
    foreach ($produkt_lang_str as $v) {
      $out[$v["Field"]] = $v["Comment"];
    }
    $this->produkt_lang_str = $out;
    
  }
  
  /** Render funkcia pre vypisanie odkazu na produkty 
  * @param array $p Parametre: id_hlavne_menu - id odkazovaneho clanku
  * @see Nette\Application\Control#render()
  */
  public function render($p = array()) {
    $pthis = $this->presenter;
    $produkty = $pthis->produkt_prepoj->findBy(array("id_hlavne_menu_lang"=>$pthis->zobraz_clanok->id_hlavne_menu));
    $this->template->setFile(__DIR__ . '/Produkty.latte');
    $this->template->back_link_id = $pthis->zobraz_clanok->id_hlavne_menu;
    $this->template->produkty = $produkty;
    if (isset($pthis->params['produkt_view']) && $pthis->params['produkt_view'] > 0) {
      $produkt_images_view = $pthis->produkt_images->findBy(array("id_produkt_lang"=>$pthis->params['produkt_view']));
      $zobraz_produkt = $produkty->where("id_produkt_lang", $pthis->params['produkt_view'])->limit(1)->fetch();
      $this->template->p = isset($zobraz_produkt) ? $zobraz_produkt->produkt_lang : FALSE;
      $this->template->produkt_lang_str = $this->produkt_lang_str;
      $this->template->pi = $produkt_images_view;
    }
    $this->template->render();
  }
  
  /** Signál pre zobrazenie konkrétneho produktu
   * @param int $produkt_view
   */
  public function handleProduktView($produkt_view = 0) {
    $pthis = $this->presenter;
    if (!$this->presenter->isAjax()) {
      $this->presenter->redirect('Clanky:default', array("id"=>$pthis->zobraz_clanok->id_hlavne_menu, "produkt_view"=>$produkt_view ));
    } else {
      $this->redrawControl('');
    }
	}
  
  protected function createTemplate($class = NULL) {
    $template = parent::createTemplate($class);
    $template->addFilter('ciarka', function ($s){
      return str_replace(".",",",$s);
    });
    $template->addFilter('vypis', function ($s, $format = ""){
      if (strlen($format)>2) {
        if ($format[0] == "[") {
          $p = explode("]", $format);
          $j = ($t = strpos($p[0], "J(")) !== FALSE ? substr($p[0], $t+2, strpos($p[0], ")")-$t-2): "";
          $s = Html::el('td')->setHtml($p[1].":").Html::el('td')->setHtml($s."&nbsp".$j);
        }
      } 
      return $s;
    });
    return $template;
	}
  
}