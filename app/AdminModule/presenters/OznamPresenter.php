<?php
namespace App\AdminModule\Presenters;

use Nette\Forms\Container;
use Nette\Application\UI\Multiplier;
use DbTable;
use PeterVojtech;

/**
 * Prezenter pre spravu oznamov.
 * 
 * Posledna zmena(last change): 10.02.2016
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.1.1a
 */

Container::extensionMethod('addDatePicker', function (Container $container, $name, $label = NULL) {
    return $container[$name] = new \JanTvrdik\Components\DatePicker($label);
});

class OznamPresenter extends \App\AdminModule\Presenters\BasePresenter {
	/** 
   * @inject
   * @var DbTable\Oznam */
	public $oznam;
  
  // -- Komponenty
  /** @var \App\AdminModule\Components\Oznam\IPotvrdUcastControl @inject */
  public $potvrdUcastControlFactory;
  /** @var \App\AdminModule\Components\Oznam\IKomentarControl @inject */
  public $komentarControlControlFactory;
  
  // -- Formulare
  /** @var Forms\Oznam\EditOznamFormFactory @inject*/
	public $editOznamForm;
  
	/** @var boolean|FALSE */
	private $oznam_usporiadanie = FALSE;

  /** Vychodzie nastavenia */
	protected function startup() {
    parent::startup();
    //Z DB zisti ako budu oznamy usporiadane
    if (($pomocna = $this->udaje->getKluc("oznam_usporiadanie")) !== FALSE) {
      $this->oznam_usporiadanie = (boolean)$pomocna->text;
    }
    if (($pomocna = $this->udaje->getKluc("oznam_komentare")) !== FALSE) {
      $oznam_komentare = (boolean)$pomocna->text;
    } else {$oznam_komentare = FALSE; }
    $this->template->oznam_komentare = $oznam_komentare;
    $this->template->oznam_title_image_en = (boolean)$this->udaje->getUdajInt("oznam_title_image_en");
	}

  /** Render pre vypis oznamov */
  public function renderDefault() {
		$this->template->oznamy = $this->oznam->vsetky($this->oznam_usporiadanie);
    $ponuka = [];
    $ponuka[] = [ "odkaz" => $this->oznam_usporiadanie ? "oznZoradenieDesc!" : "oznZoradenieAsc!",
                  "text"  => "Zmeň na prvý je ".($this->oznam_usporiadanie ? "najneskôr" : "najskôr"),
                  "class" => $this->oznam_usporiadanie ? "fa-sort-numeric-asc" : "fa-sort-numeric-desc",
                  "button_type" => "info-link"
                ];
    if ($this->user->isAllowed('Admin:Oznam' , 'add')) {
      $ponuka[] = [ "odkaz" => "Oznam:add",
                    "text"  => "Pridaj nový oznam",
                    "class" => "fa-plus",
                    "button_type" => "edit-link"
                  ];
    }
    $this->template->ponuka = $ponuka;
  }

  /** Akcia pre pridanie oznamu */
	public function actionAdd() {
    $this["oznamEditForm"]->setDefaults([ //Nastav vychodzie hodnoty
      'id'							=> 0,
      'id_user_profiles'=> $this->getUser()->getId(),
      'id_registracia'	=> 0,
      'id_ikonka'				=> 0,
      'datum_platnosti'	=> StrFTime("%Y-%m-%d", Time()),
      'datum_zadania'   => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
      'potvrdenie'			=> 0,  
      'posli_news'			=> 0,
    ]);
    $this->setView('edit');
    $this->template->h2 = 'Pridanie oznamu';
	}

  /** Akcia pre editaciu oznamu
   * @param int $id Id oznamu
   */
	public function actionEdit($id) {
    if (($oznam = $this->oznam->hladaj_id($id, $this->id_reg)) === FALSE) {
      $this->setView('notFound');
    } else { //Ak je vsetko OK priprav premenne
      $this["oznamEditForm"]->setDefaults($oznam);
      $this["oznamEditForm"]->setDefaults([ //Nastav vychodzie hodnoty
        'datum_zadania'   => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
        'posli_news'			=> 0,
      ]);
      $this->template->h2 = sprintf('Editácia oznamu: %s', $oznam->nazov);
    }
    
	}
  
  /** Render pre pridanie a editaciu oznamu */
  public function renderEdit() {
    $this->template->bezp_kod	= '4RanoS5689q6-498'; //Bezp. kod pre CKEditor$
    $this->template->CKtoolbar= "OznamToolbar".(($this->user->isInRole('admin')) ? "" : "_Spravca");
  }
  
	/** Formular pre editaciu a pridanie oznamu
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentOznamEditForm() {
    $form = $this->editOznamForm->create($this->udaje_webu['oznam_ucast'], $this->nastavenie['send_e_mail_news'], $this->template->oznam_title_image_en, $this->nazov_stranky); 
    $form['uloz']->onClick[] = function ($button) { 
      $form_val = $button->getForm();
      if (!count($form_val->errors) && $form_val->getValues()->posli_news) { $this->_sendOznamyEmail($form_val->getValues());}
      $this->flashOut(!count($form_val->errors), 'Oznam:', 'Oznam bol uložený!', 'Došlo k chybe a oznam sa neuložil. Skúste neskôr znovu...');
      
		};
    $form['cancel']->onClick[] = function () {
			$this->redirect('Oznam:');
		};
		return $this->_vzhladForm($form);
	}
  
  /** Odoslanie info e-mailu 
   * @param Nette\Utils\ArrayHash $values
   */
	public function _sendOznamyEmail($values) {
    $params = [ "site_name" => $this->nazov_stranky,
                "nazov" 		=> $values->nazov,
                "text"      => $values->text,
                "odkaz" 		=> $this->link(":Front:Oznam:default"),
                "datum_platnosti" => $values->datum_platnosti,
              ];
    $send = new PeterVojtech\Email\EmailControl(__DIR__.'/templates/Oznam/email_oznamy_html.latte', $this->user_profiles, 1, $values->id_registracia);
    try {
      $this->flashMessage('E-mail bol odoslany v poriadku na emaily: '.$send->send($params, 'Nový oznam na stránke '.$this->nazov_stranky), 'success');
    } catch (Exception $e) {
      $this->flashMessage($e->getMessage(), 'danger');
    }
	}
  
  /** Signal pre zmenu zoradenia oznamov podla datumu platnosti od najmladsich */
  public function handleOznZoradenieDesc() {
    $this->udaje->opravKluc('oznam_usporiadanie', "0");
		$this->redirect('this');
	}
  
  /** Signal pre zmenu zoradenia oznamov podla datumu platnosti od najstarsich */
  public function handleOznZoradenieAsc() {
    $this->udaje->opravKluc('oznam_usporiadanie', "1");
		$this->redirect('this');
	}
  
  /** Komponenta na obsluhu potvrdenia ucasti */
	public function createComponentPotvrdUcast() {
		return new Multiplier(function ($id_oznam) {
      $potvrd = $this->potvrdUcastControlFactory->create();
      $potvrd->setParametre($id_oznam, $this->user->isAllowed('Admin:Oznam', 'ucast'));
			return $potvrd;
		});
	}
  
  /** Obsluha komentara
   * @return Multiplier */
	public function createComponentKomentar() {
		return new Multiplier(function ($id_oznam) {
      $komentar = $this->komentarControlControlFactory->create();
      $komentar->setParametre($id_oznam, $this->user->isInRole('admin'));
			return $komentar;
		});
	}
  
  /** Funkcia pre spracovanie signálu vymazavania
	  * @param int $id Id oznamu */
	function confirmedDelete($id)	{
    if ($this->oznam->vymazOznam($id) == 1) { $this->flashMessage('Aktualita(oznam) bola úspešne vymazaná!', 'success'); } 
    else { $this->flashMessage('Došlo k chybe a aktualita(oznam) nebola vymazaná!', 'danger'); }
    if (!$this->isAjax()) { $this->redirect('Oznam:'); }
  }
  
}
