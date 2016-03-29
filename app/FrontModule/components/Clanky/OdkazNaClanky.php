<?php
namespace App\FrontModule\Components\Clanky;
use Nette;

/**
 * Komponenta pre zobrazenie odkazu na iny clanok
 * Posledna zmena(last change): 10.02.2016
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.3a
 */
class OdkazNaClankyControl extends Nette\Application\UI\Control {
  
  /** @var string Text, ktory sa ukaze ak sa nenajde dany clanok  */
  private $textNotFoundClanok = 'Article not found! msg: %s';
  /** @var string Nazov aktualneho modulu  */
  private $textModul = "Front";
  /** @var array Texty pre sablonu */
  private $texty = [
    "to_foto_galery"  => "Odkaz na fotogalériu:",
    "to_article"  => "Odkaz na článok:",
    "neplatny"  => "Článok už nie je platný!",
    "platil_do" => "Platil do: ",
    "platny_do" => "Článok platí do: "];

  /** Nastavenie textov pre sablonu pre ine jazyky
   *  @param array $texts texty do sablony
   */
  public function setTexts($texts) {
    if (is_array($texts)) $this->texty = array_merge($this->texty, $texts);
  }

  /** Nastavenie textu pre nenajdeny clanok
   *  @param string $text text hlasenia
   */
  public function setTextNotFoundClanok($text = "Article not found!") {
    $this->textNotFoundClanok = $text." msg: %s";
  }

  /** Nastavenie modulu do odkazu na clanok
   * @param string $modul
   */
  public function setModul($modul = "Front") {
    $this->textModul = $modul;
  }

  /** Render funkcia pre vypisanie odkazu na clanok 
  * @param array $p Parametre: id_hlavne_menu - id odkazovaneho clanku, template - pouzita sablona
  * @see Nette\Application\Control#render()
  */
  public function render($p = []) {
    if (isset($p["id_hlavne_menu"]) && (int)$p["id_hlavne_menu"]) { //Mam id_clanok
      $pthis = $this->presenter;
      $pom_hlm = $pthis->hlavne_menu_lang->findOneBy(array("id_lang"=>$pthis->language_id, "id_hlavne_menu"=>$p["id_hlavne_menu"]));
      if ($pom_hlm === FALSE) { $chyba = "hlavne_menu_lang = ".$p["id_hlavne_menu"]; }
    } else { //Nemam id_clanok
      $chyba = "id_hlavne_menu";
    }
    if (isset($chyba)) { //Je nejaka chyba
      $this->template->setFile(__DIR__ . '/OdkazNaClanky_error.latte');
      $this->template->text = sprintf($this->textNotFoundClanok, $chyba);
    } else { //Vsetko je OK
			$p_hlm = $pom_hlm->hlavne_menu; //Pre skratenie zapisu
      $this->template->setFile(__DIR__ . "/OdkazNaClanky".(isset($p["template"]) && strlen($p["template"]) ? "_".$p["template"] : "").".latte");
      $this->template->nazov = $pom_hlm->nazov;
      $this->template->datum_platnosti = $p_hlm->datum_platnosti;
      $this->template->avatar = $p_hlm->avatar;
      $this->template->anotacia = isset($pom_hlm->id_clanok_lang) ? $pom_hlm->clanok_lang->anotacia : NULL;
      $this->template->texty = $this->texty;
			$link_presenter = ($this->textModul == "Front") ? ($p_hlm->druh->presenter == "Menu" ? "Clanky" : $p_hlm->druh->presenter) : $p_hlm->druh->presenter;
      $this->template->odkaz = $pthis->link($link_presenter.":default", $p["id_hlavne_menu"]);
      $this->template->absolutna = $p_hlm->absolutna;
    }
    $this->template->render();
  }
}