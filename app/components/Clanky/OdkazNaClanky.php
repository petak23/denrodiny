<?php
namespace PeterVojtech\Clanky;
use Nette;

/**
 * Komponenta pre zobrazenie odkazu na iny clanok
 * Posledna zmena(last change): 29.12.2015
 * 
 * @author  Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link  http://petak23.echo-msz.sk
 * @version 1.0.3
 */
class OdkazNaClankyControl extends Nette\Application\UI\Control {
  
  /** @var string Text, ktory sa ukaze ak sa nenajde dany clanok  */
  private $textNotFoundClanok = 'Article not found! msg: %s';
  /** @var string Nazov aktualneho modulu  */
  private $textModul = ":Front:";
  /** @var array Texty pre sablonu */
  private $texty = [
    "to_foto_galery"  => "Odkaz na fotogalériu:",
    "to_article"  => "Odkaz na článok:",
    "neplatny"  => "Článok už nie je platný!",
    "platil_do" => "Platil do: ",
    "platny_do" => "Článok platí do: "];
  /** @var boolean Zobrazenie odkazu pre zmazanie komponenty */
  private $del_link = FALSE;

  /** Nastavenie textov pre sablonu pre ine jazyky
   *  @param array $texts texty do sablony
   */
  public function setTexts($texts) {
    if (is_array($texts)) { $this->texty = array_merge($this->texty, $texts);}
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
    $this->textModul = ":".$modul.":";
  }
  
  /** Nastavenie zobrazenia odkazu pre zmazanie 
   * @param boolean $del_link zobrazenie odkazu
   */
  public function setDelLink($del_link = FALSE) {
    $this->del_link = $del_link;
  }

  /** Render funkcia pre vypisanie odkazu na clanok 
  * @param array $p
  * @see Nette\Application\Control#render()
  */
  public function render($p = array()) {
    if (isset($p["id_hlavne_menu"]) && (int)$p["id_hlavne_menu"]) { //Mam id_clanok
      $pthis = $this->presenter;
      $pom_hlm = $pthis->hlavne_menu_lang->findOneBy(array("id_lang"=>$pthis->language_id, "id_hlavne_menu"=>$p["id_hlavne_menu"]));
      if ($pom_hlm === FALSE) { $chyba = "hlavne_menu_lang = ".$p["id_hlavne_menu"];}
      if ($this->del_link) {
        $search = $pthis->clanok_komponenty->findOneBy(array("parametre"=>$p["id_hlavne_menu"].",".(isset($p["template"]) ? $p["template"] : "")));
        $this->template->del_link_id = $search !== FALSE ? $search->id : NULL;
      } 
    } else { $chyba = "id_hlavne_menu"; } //Nemam id_clanok
    
    if (isset($chyba)) { //Je nejaka chyba
      $this->template->setFile(__DIR__ . '/OdkazNaClanky_error.latte');
      $this->template->text = sprintf($this->textNotFoundClanok, $chyba);
    } else { //Vsetko je OK
      $this->template->setFile(__DIR__ . "/OdkazNaClanky".(isset($p["template"]) ? "_".$p["template"] : "").".latte");
      $this->template->nazov = $pom_hlm->nazov;
      $this->template->datum_platnosti = $pom_hlm->hlavne_menu->datum_platnosti;
      $this->template->avatar = $pom_hlm->hlavne_menu->avatar;
      $this->template->anotacia = isset($pom_hlm->id_clanok_lang) ? $pom_hlm->clanok_lang->anotacia : NULL;
      $this->template->texty = $this->texty;
      $this->template->odkaz = $this->textModul.$pom_hlm->hlavne_menu->druh->presenter.":default";
      $this->template->id_hlavne_menu = $p["id_hlavne_menu"];
    }
    $this->template->render();
  }
  
  /**
    * Komponenta Confirmation Dialog pre delete News
    * @return Nette\Application\UI\Form
    */
  public function createComponentConfirmForm() {
    $form = new \PeterVojtech\Confirm\ConfirmationDialog($this->presenter->getSession('news'));
    $form->addConfirmer(
      'delete', // názov signálu bude confirmDelete!
      array($this, 'confirmedDelete'), // callback na funkciu pri kliknutí na YES
      array($this, 'questionDelete') // otázka
    );
    return $form;
  }
  
  /**
  * Zostavenie otázky pre ConfDialog s parametrom
  * @param Nette\Utils\Html $dialog
  * @param array $params
  * @return string $question
  */
  public function questionDelete($dialog, $params) {
     $dialog->getQuestionPrototype();
     return sprintf("Naozaj chceš zmazať odkaz na článok: %s?", isset($params['nazov']) ? "'".$params['nazov']."'" : "");
  }
  
  /** Funkcia pre spracovanie signálu vymazavania
	  * @param int $id - id polozky v hlavnom menu
		* @param string $druh - blizsia specifikacia, kde je to potrebne
		*/
	function confirmedDelete($id)	{
		//Vstupna kontrola
    if (!(isset($id) && $id)) { $this->error("Id položky nie je nastavené!"); }
    if (!$this->del_link) {$this->error("Neoprávnený prístup. Pokus o vymazanie odkazu na článok! Id=".$id);}
    $pthis = $this->presenter;
    $pthis->clanok_komponenty->zmaz($id);
    $pthis->flashMessage("Vymazane", "dobre");
    $pthis->redirect("this");
  }
}