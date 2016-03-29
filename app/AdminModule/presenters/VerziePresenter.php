<?php
namespace App\AdminModule\Presenters;

use PeterVojtech;

/**
 * Prezenter pre spravu verzii.
 * 
 * Posledna zmena(last change): 18.12.2015
 *
 *	Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.8b
 */

class VerziePresenter extends \App\AdminModule\Presenters\BasePresenter {

  /** @var Forms\Verzie\EditVerzieFormFactory @inject*/
	public $editVerzieForm;
  
	public function renderDefault()	{
		$this->template->verzie = $this->verzie->vsetky();
	}
  /** Akcia pre pridanie verzie */
	public function actionAdd()	{
		$this->template->h2 = 'Pridanie verzie';
    $this["verzieEditForm"]->setDefaults([
                                'id'              => 0,
                                'id_user_profiles'=> $this->getUser()->getId(),
                              ]);
    $this->setView('edit');
	}

  /** Akcia pre editaciu verzie
   * @param int $id Id editovanej verzie
   */
	public function actionEdit($id)	{
    if (($verzia = $this->verzie->find($id)) === FALSE) {
      $this->setView('notFound');
    } else {
      $this->template->h2 = 'Editácia verzie: '.$verzia->cislo;
      $this["verzieEditForm"]->setDefaults($verzia);
    }
	}

	/**
	 * Edit oznam form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentVerzieEditForm() {
    $form = $this->editVerzieForm->create($this->nastavenie['send_e_mail_news']);
    $form['uloz']->onClick[] = [$this, 'verzieEditFormSubmitted'];
    $form['cancel']->onClick[] = function () {
			$this->redirect('default');
		};
		return $this->_vzhladForm($form);
	}

  /** Spracovanie vstupov z formulara
   * @param Nette\Forms\Controls\SubmitButton $button Data formulara
   */
	public function verzieEditFormSubmitted($button) {
		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    $posli_news = $values->posli_news;
    if ($this->verzie->ulozVerziu($values) !== FALSE) { //Ulozenie v poriadku
      if ($posli_news) { //Poslanie e-mailu
				$params = [ "site_name" => $this->nazov_stranky,
                    "cislo" 		=> $values->cislo,
                    "text"      => $values->text,
                    "odkaz" 		=> $this->link("Verzie:default"),
                  ];
        $send = new PeterVojtech\Email\EmailControl(__DIR__.'/templates/Verzie/verzie-html.latte', $this->user_profiles, 1, 4);
        try {
          $this->flashMessage('E-mail bol odoslany v poriadku na emaily: '.$send->send($params, 'Nová verzia stránky '.$this->nazov_stranky), 'success');
        } catch (Exception $e) {
          $this->flashMessage($e->getMessage(), 'danger');
        }
      }
      $this->flashRedirect('Verzie:', 'Verzia bola úspešne uložená!', 'success');
    } else {													//Ulozenie sa nepodarilo
      $this->flashMessage('Došlo k chybe a verzia sa neuložila. Skúste neskôr znovu...', 'danger');
    }
	}
  
    /** Funkcia pre spracovanie signálu vymazavania
	  * @param int $id Id polozky v hlavnom menu
		* @param string $nazov Text pre hlasenie
		* @param string $verzia Nazov vymazavanej verzie
		*/
	function confirmedDelete($id, $nazov, $verzia = "")	{
		if ($this->verzie->zmaz($id) == 1) { 
      $this->flashMessage('Verzia '.$verzia.' bola úspešne vymazaná!', 'success'); 
    } else { 
      $this->flashMessage('Došlo k chybe a verzia '.$verzia.' nebola vymazaná!', 'danger'); 
    }
    if (!$this->isAjax()) { $this->redirect('Verzie:'); }
  }
}