<?php
namespace App\FrontModule\Components\Oznam;

use Nette;
use DbTable;

/**
 * Komponenta pre pridavanie komentarov k oznamom
 * Component to add comments to various objects (eg, articles, announcements...)
 * Posledna zmena(last change): 22.01.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 */

class KomentarControl extends Nette\Application\UI\Control {
  
  /** @var DbTable\Oznam_komentar */
  public $oznam_komentar;
  
  /** @var int Id oznamu*/
  private $id_oznam;
  /** @var int */
  private $id_user_profiles;
  /** @var int Pocet riadkov textoveho pola*/
  private $textA_rows;

  /**
   * @param DbTable\Oznam_komentar $oznam_komentar
   * @param Nette\Security\User $user
   */
  public function __construct(DbTable\Oznam_komentar $oznam_komentar, Nette\Security\User $user) {
    parent::__construct();
    $this->oznam_komentar = $oznam_komentar; 
    $this->id_user_profiles = $user->getId();
  }
  
  /** Nastavenie parametrov
	 * @param  int $id_oznam Id polozky, ku ktorej sa vztahuje komentar
   * @param  int $rows Pocet riadkov textoveho pola
   * @return \App\FrontModule\Components\Oznam\KomentarControl
   */
  public function setParametre($id_oznam, $rows = 2) {
    $this->id_oznam = $id_oznam;
    $this->textA_rows = $rows;
    return $this;
  }

  /** 
   * @see Nette\Application\Control#render()
   */
  public function render() {
    $this->template->setFile(__DIR__ . '/Komentar.latte');
    $this->template->komentare = $this->oznam_komentar->najdiKomentar($this->id_oznam);
    $this->template->render();
  }

  /**
   * Formular pre pridanie komentara.
   * @return Nette\Application\UI\Form
   */
  protected function createComponentKomentarForm() {
      $form = new Nette\Application\UI\Form;
      $form->addHidden('id_oznam', $this->id_oznam);
      $form->addHidden('id_user_profiles', $this->id_user_profiles);
      $form->addTextArea('text')->setAttribute('class', 'form-control')
           ->setAttribute('rows', $this->textA_rows);//->setDefaultValue('');
      $form->addSubmit('uloz', 'Pridaj komentár')->setAttribute('class', 'btn btn-success');
      $form->onSuccess[] = $this->onZapisKomentar;
      $form->getElementPrototype()->class('ajax');
      $renderer = $form->getRenderer();
      $renderer->wrappers['controls']['container'] = NULL;
      $renderer->wrappers['pair']['container'] = 'div class=form-group';
      $renderer->wrappers['pair']['.error'] = 'has-error';
      $renderer->wrappers['control']['container'] = 'div class=col-sm-12';
      $renderer->wrappers['label']['container'] = 'div style="display:none"';
      $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
      return $form;
  }

  /**
   * Spracovanie formulara pre pridanie komentara
   * @param Nette\Application\UI\Form $form
   */
  public function onZapisKomentar(Nette\Application\UI\Form $form) {
    $this->oznam_komentar->uloz($form->getValues());
    if (!$this->presenter->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('koment');
    }
  }
}

interface IKomentarControl {
  /** @return KomentarControl */
  function create();
}