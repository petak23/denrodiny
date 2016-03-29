<?php
namespace App\FrontModule\Presenters;

use Nette\Application\UI\Form;
use \VisualPaginator\VisualPaginator;
use DbTable, Language_support;

/**
 * Prezenter pre debatu.
 * Posledna zmena(last change): 28.10.2015
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.3a
 *
 */
class DebataPresenter extends \App\FrontModule\Presenters\BasePresenter {
	/** 
   * @inject
   * @var DbTable\Debata */
	public $debata;
  /**
   * @inject
   * @var Language_support\Debata */
  public $texty_presentera;
  /** @var int */
  private $textA_rows = 5;
  /** @var int */
  private $textA_cols = 100;

	protected function startup() {
    parent::startup();
    // Kontrola prihlasenia - ak >0 ok
    if (!$this->user->isLoggedIn()) {
      $this->flashRedirect(array('User:', array('backlink'=>$this->storeRequest())), $this->trLang('base_nie_je_opravnenie1').'<br/>'.$this->trLang('base_prihlaste_sa'), 'danger,n');
    }
    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect(($this->action == 'edit' && $this->user->isAllowed($this->name, 'default')) ? 'Debata:' : 'Homepage:', sprintf($this->trLang('base_nie_je_opravnenie'), $this->action), 'danger');
    }
    $this->template->h2 = $this->trLang('h2');
    $this->template->txt_na_zmaz = $this->trLang('txt_na_zmaz');
    $vp = new VisualPaginator($this, 'vp');
    $paginator = $vp->getPaginator();
    $paginator->itemsPerPage = $this->udaje->getUdajInt('d_riadkov');
    $paginator->itemCount = $this->debata->findAll()->count();
    $this->template->komentare = $this->debata->findAll()->order('timestamp DESC')
                                              ->limit($paginator->getLength(), $paginator->getOffset());
	}

  /**
   * Akcia - Vypis debaty a prednastavenie hodnot pre form
   */
	public function actionDefault() {
    $this["komentarForm"]->setDefaults(array("id"=>0, "id_user_profiles"=>$this->user->getId()));
  }
  
  /**
   * Akcia - Editacia polozky debaty
   * @param int $id Id polozky
   */
  public function actionEdit($id) {
    $d = $this->debata->find($id);
    $this["komentarForm"]->setDefaults(array("text"=>$d->text,
                                             "id"=>$d->id,
                                             "id_user_profiles"=>$d->id_user_profiles));
    $this->setView('default');
  }

  /**
   * Pridanie/editacia prispevku form component factory.
   * @return Nette\Application\UI\Form
   */
  protected function createComponentKomentarForm() {
    $form = new Form;
    $form->addProtection();
    $form->addTextArea('text', $this->trLang('KomentarForm_text'))
         ->setAttribute('rows', $this->textA_rows)
         ->setAttribute('cols', $this->textA_cols);
    $form->addHidden('id');
    $form->addHidden('id_user_profiles');
    $form->addSubmit('uloz', $this->trLang('KomentarForm_uloz'));
    $form->onSuccess[] = $this->onZapisKomentar;
    return $form;
  }

  public function onZapisKomentar(Form $form) {
    $values = $form->getValues(); 				//Nacitanie hodnot formulara
    $u = $this->debata->uloz(array(
        'id_user_profiles' => $values->id_user_profiles,
        'text'             => $values->text,
        ), $values->id);
    if ($u !== FALSE && $u['id']) {
      $this->flashRedirect('Debata:default', $this->trLang('save_ok'), 'success');
    } else {
      $this->flashMessage($this->trLang('save_er'), 'danger');
    }
  }

  /*********** signal processing ***********/
	function confirmedDelete($id) {
    if ($this->debata->zmaz($id) == 1) { $this->flashMessage($this->trLang('delete_ok'), 'success');}
    else {$this->flashMessage($this->trLang('delete_error'), 'danger');}
    if (!$this->isAjax()) {$this->redirect('this');}
	}
}