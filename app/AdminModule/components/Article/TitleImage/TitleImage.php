<?php
namespace App\AdminModule\Components\Article\TitleImage;

use Nette;
use DbTable;

/**
 * Komponenta pre titulku polozky(titulny obrazok a nadpis).
 * 
 * Posledna zmena(last change): 04.03.2016
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 */

class TitleImageControl extends Nette\Application\UI\Control {

  /** @var Nette\Database\Table\ActiveRow $clanok Info o clanku */
  private $clanok;
  /** @var string $avatar_path */
  private $avatar_path;
  /** @var string $www_dir */
  private $www_dir;
  
  /** @var EditTitleImageFormFactory */
	public $editTitleImage;

  /**
   * @param DbTable\Hlavne_menu_lang $hlavne_menu_lang
   * @param EditTitleImageFormFactory $editTitleImageFormFactory */
  public function __construct(EditTitleImageFormFactory $editTitleImageFormFactory) {
    parent::__construct();
    $this->editTitleImage = $editTitleImageFormFactory;
  }
  
  /** Nastavenie komponenty
   * @param Nette\Database\Table\ActiveRow $clanok
   * @param string $avatar_path
   * @return \App\AdminModule\Components\Article\TitleArticleControl */
  public function setTitle(Nette\Database\Table\ActiveRow $clanok, $avatar_path, $www_dir) {
    $this->clanok = $clanok;
    $this->avatar_path = $avatar_path;
    $this->www_dir = $www_dir;
    return $this;
  }
  
  /** 
   * Render 
   * @param array $params Parametre komponenty - [admin_links, komentare, aktualny_projekt_enabled, zobraz_anotaciu]*/
	public function render($params) {
    $this->template->setFile(__DIR__ . '/TitleImage.latte');
    $this->template->clanok = $this->clanok;
    $this->template->admin_links = $params['admin_links'];
		$this->template->render();
	}
  
  /** 
   * Komponenta formulara pre zmenu vlastnika.
   * @return Nette\Application\UI\Form */
  public function createComponentEditTitleImageForm() {
    $form = $this->editTitleImage->create($this->avatar_path, $this->www_dir);
    $form->setDefaults(["id" => $this->clanok->id_hlavne_menu,
                        "old_avatar" => $this->clanok->hlavne_menu->avatar]);
    $form['uloz']->onClick[] = function ($button) { 
      $this->presenter->flashOut(!count($button->getForm()->errors), 'this', 'Zmena bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
		};
    return $this->presenter->_vzhladForm($form);
  }
}

interface ITitleImageControl {
  /** @return TitleImageControl */
  function create();
}