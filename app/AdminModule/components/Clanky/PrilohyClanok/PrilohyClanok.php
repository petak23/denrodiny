<?php
namespace App\AdminModule\Components\Clanky\PrilohyClanok;

use Nette;
use DbTable;

/**
 * Komponenta pre spravu priloh clanku.
 * 
 * Posledna zmena(last change): 29.03.2016
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2a
 */

class PrilohyClanokControl extends Nette\Application\UI\Control {

  /** @var DbTable\Dokumenty $clanok Info o clanku */
  public $dokumenty;
  /** @var string $nazov_stranky */
  private $nazov_stranky;
  /** @var Nette\Database\Table\ActiveRow $clanok Info o clanku */
  private $clanok;
  /** @var int */
  private $upload_size;
  /** @var string */
  private $prilohy_adresar;
  /** @var array */
  private $prilohy_images;
  /** &var EditPrilohyFormFactory */
  public $editPrilohyForm;
  /** @var array */
  private $admin_links;


  /**
   * @param DbTable\Dokumenty $dokumenty
   * @param EditPrilohyFormFactory $editPrilohyFormFactory */
  public function __construct(DbTable\Dokumenty $dokumenty, EditPrilohyFormFactory $editPrilohyFormFactory) {
    parent::__construct();
    $this->dokumenty = $dokumenty;
    $this->editPrilohyForm = $editPrilohyFormFactory;
  }
  
  /** Nastavenie komponenty
   * @param Nette\Database\Table\ActiveRow $clanok
   * @param string $nazov_stranky
   * @param int $upload_size
   * @param string $prilohy_adresar
   * @param array $prilohy_images Nastavenie obrazkov pre prilohy
   * @return \App\AdminModule\Components\Clanky\PrilohyClanok\PrilohyClanokControl
   */
  public function setTitle(Nette\Database\Table\ActiveRow $clanok, $nazov_stranky, $upload_size, $prilohy_adresar, $prilohy_images, $admin_links) {
    $this->clanok = $clanok;
    $this->nazov_stranky = $nazov_stranky;
    $this->upload_size = $upload_size;
    $this->prilohy_adresar = $prilohy_adresar;
    $this->prilohy_images = $prilohy_images;
    $this->admin_links = $admin_links;
    return $this;
  }
  
  /** 
   * Render 
   * @param array $params Parametre komponenty - [admin_links]*/
	public function render() {
    $this->template->setFile(__DIR__ . '/PrilohyClanok.latte');
    $this->template->clanok = $this->clanok;
    $this->template->admin_links = $this->admin_links;
    $this->template->dokumenty = $this->dokumenty->findBy(['id_hlavne_menu'=>$this->clanok->id_hlavne_menu]);
		$this->template->render();
	}
  
  /** 
   * Komponenta formulara pre pridanie a editaciu prílohy polozky.
   * @return Nette\Application\UI\Form */
  public function createComponentEditPrilohyForm() {
    $form = $this->editPrilohyForm->create($this->upload_size, $this->prilohy_adresar, $this->prilohy_images);
    $form->setDefaults(["id"=>0, "id_hlavne_menu"=>$this->clanok->id_hlavne_menu, "id_registracia"=>$this->clanok->hlavne_menu->id_registracia]);
    $form['uloz']->onClick[] = function ($button) { 
      $this->presenter->flashOut(!count($button->getForm()->errors), 'this', 'Príloha bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
		};
    return $this->presenter->_vzhladForm($form);
  }
  
  public function handleEditPriloha($id) {
    $this->presenter->redirect('Dokumenty:edit', $id);
  }
  
  public function handleShowInText($id) {
    $priloha = $this->dokumenty->find($id);
    $priloha->update(['zobraz_v_texte'=>(1 - $priloha->zobraz_v_texte)]);
		if (!$this->presenter->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('');
    }
    
  }
}

interface IPrilohyClanokControl {
  /** @return PrilohyClanokControl */
  function create();
}