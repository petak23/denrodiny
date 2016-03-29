<?php
namespace App\AdminModule\Presenters;

use Nette\Application\UI\Form;
use DbTable;

/**
 * Prezenter pre smerovanie na dokumenty a editaciu popisu dokumentu.
 * 
 * Posledna zmena(last change): 07.12.2015
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.3
 */

class DokumentyPresenter extends \App\AdminModule\Presenters\BasePresenter {
	/** 
   * @inject
   * @var DbTable\Dokumenty */
	public $dokumenty;
  
  /** @var \Nette\Database\Table\ActiveRow */
  private $dokument;
  
  // -- Formulare
  /** @var Forms\Article\IEditMenuFormFactory @inject */
//	public $editMenuFormFactory;

  /** Vychodzia akcia
   * @param int $id Id polozky na zobrazenie
   */
	public function actionDefault($id) {
		if (($this->dokument = $this->dokumenty->find($id)) === FALSE) {
      $this->error('Dokument, ktorý hľadáte, sa žiaľ nenašiel!');
    }
		$this->redirectUrl("http://".$this->nazov_stranky."/".$this->dokument->subor);
		exit;
	}

  /** Akcia pre editaciu informacii o dokumente
   * @param int $id Id dokumentu na editaciu
   */
  public function actionEdit($id) {
    if (!(isset($id) && $id)) { 
      return $this->error("Nie je zadané id prílohy na editáciu alebo je nesprávne!"); 
    }
		if (($this->dokument = $this->dokumenty->find($id)) === FALSE) { 
      return $this->error(sprintf("Pre zadané id som nenašiel prílohu! id=' %s'!", $id)); 
    }
    $this["dokumentEditForm"]->setDefaults($this->dokument);
  }
  
  /** Render pre editaciu prilohy. */
	public function renderEdit() {
		$this->template->h2 = 'Editácia údajov dokumentu:'.$this->dokument->nazov;
	}

  /** Formular pre editaciu info. o dokumente.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentDokumentEditForm() {
    $ft = new \App\AdminModule\Components\Clanky\PrilohyClanok\EditPrilohyFormFactory($this->dokumenty, $this->user);
    $form = $ft->create($this->upload_size, $this->prilohy_adresar, $this->nastavenie['prilohy_images']);
    $form->setDefaults($this->dokument);
    $form['uloz']->onClick[] = function ($button) {
      $this->flashOut(!count($button->getForm()->errors), ['Clanky:', $this->dokument->id_hlavne_menu], 'Príloha bola úspešne uložená!', 'Došlo k chybe a zmena sa neuložila. Skúste neskôr znovu...');
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('Clanky:', $this->dokument->id_hlavne_menu);
		};
		return $this->_vzhladForm($form);
	}
}