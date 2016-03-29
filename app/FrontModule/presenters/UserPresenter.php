<?php
namespace App\FrontModule\Presenters;

use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Latte;
use DbTable, Language_support;

/**
 * Prezenter pre prihlasenie, registraciu a aktiváciu uzivatela, obnovenie zabudnutého hesla a zresetovanie hesla.
 *
 * Posledna zmena(last change): 19.01.2016
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.7
 *
 */
class UserPresenter extends \App\FrontModule\Presenters\BasePresenter {
	/**
   * @inject
   * @var Language_support\User
   */
  public $texty_presentera;
  /** 
   * @inject
   * @var DbTable\Users */
	public $users;
  /** @var mix */
  private $clen;
  /** @var mix */
  private $hasser;
  /** @var array Nastavenie zobrazovania volitelnych poloziek */
  private $user_view_fields;

	protected function startup() {
    parent::startup();
    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect('Homepage:', sprintf($this->trLang('base_nie_je_opravnenie'), $this->action), 'danger');
    }
    if ($this->user->isLoggedIn()) { 
      $this->flashRedirect('Homepage:', $this->trLang('base_loged_in_bad'), 'danger');
    }
    $this->hasser = $this->user->getAuthenticator(); //Ziskanie objektu pre vytvaranie hash hesla a iných
    $this->hasser->PasswordHash(8,FALSE);            //Nastavenie
    $this->template->form_required = $this->trLang('base_form_required');
    $this->template->h2 = $this->trLang('h2_'.$this->action); //Nacitanie hlavneho nadpisu
    $this->clen = $this->user_profiles->find(1);  //Odosielatel e-mailu
    $this->user_view_fields = $this->nastavenie['user_view_fields'];
	}

  /** Render pre editovateľné zobrazenie profilu */
  public function renderDefault() {
    if ($this->id_reg) { $this->template->form_required = $this->trLang('default_user_logedIn');}
    //Zobrazenie prihlasovacieho formulára
    $this->template->view_login_form = !(boolean)$this->id_reg;
  }

  /** Akcia pre registráciu nového uzivatela */
  public function actionRegistracia() {
    if ($this->udaje->findOneBy(['nazov'=>'registracia_enabled'])->text != 1) {
      $this->flashRedirect('Homepage:', $this->trLang('registracia_not_enabled'), 'danger');
    }
  }
  
  /** Akcia pre aktivaciu registrovaneho uzivatela */
  public function actionActivateUser($id_pol, $new_password_key) {
    $users_data = $this->user_profiles->find($id_pol);
    if ($new_password_key == $users_data->users->new_password_key){ //Aktivacia prebeha v poriadku
      try {
        $this->users->uloz(['activated'=>1, 'new_password_key'=>NULL], $users_data->users->id);
        $this->user_profiles->uloz([
            'id_registracia'=>1,
            'modified' => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
            ], $users_data->id);
        $this->flashRedirect('User:', $this->trLang('activate_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->trLang('activate_err1').$e->getMessage(), 'danger,n');
      }
    } else { $this->flashMessage($this->trLang('activate_err2'), 'danger'); }//Neuspesna aktivacia
    $this->redirect('Homepage:');
  }

  /** Akcia pre zobrazenie formularu pri zabudnutom hesle */
  public function actionForgottenPassword() {
    $this->template->forgot_pass_txt = $this->trLang('forgot_pass_txt');
  }  
  
  /** Akcia pre reset hesla pri zabudnutom hesle 
   * @param int $id Id uzivatela pre reset hesla
   * @param string $new_password_key Kontrolny retazec pre reset hesla
   */
  public function actionResetPassword($id, $new_password_key) {
    if (!isset($id) OR !isset($new_password_key)) {
      $this->flashRedirect('Homepage:', $this->trLang('reset_pass_err1'), 'danger');
    } else {
      $users_data = $this->users->find($id);
      if ($new_password_key == $users_data->new_password_key){ 
        $this->template->email = sprintf($this->trLang('reset_pass_email'), $users_data->email);
        $this->template->reset_pass_txt1 = $this->trLang('reset_pass_txt1');
        $this->template->reset_pass_txt2 = $this->trLang('reset_pass_txt2');
        $this["resetPasswordForm"]->setDefaults(["id"=>$id]); //Nastav vychodzie hodnoty
      } else { 
        $this->flashRedirect('Homepage:', $this->trLang('reset_pass_err'.($users_data->new_password_key == NULL ? '2' : '3')), 'danger');
      }
    }
  }

  /** Formular pre registraciu uzivatela.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentClenRegistraciaForm() {
		$form = new Form();
		$form->addProtection();
		$form->addText('meno', $this->trLang('RegistraciaForm_meno'), 50, 50)
				 ->addRule(Form::MIN_LENGTH, $this->trLang('RegistraciaForm_meno_ar'), 2)
         ->setAttribute('autofocus', 'autofocus')
				 ->setRequired($this->trLang('RegistraciaForm_meno_sr'));
    $form->addText('priezvisko', $this->trLang('RegistraciaForm_priezvisko'), 50, 50)
				 ->addRule(Form::MIN_LENGTH, $this->trLang('RegistraciaForm_priezvisko_ar'), 3)
				 ->setRequired($this->trLang('RegistraciaForm_priezvisko_sr'));
    $form->addText('username', $this->trLang('RegistraciaForm_username'), 50, 50)
				 ->addRule(Form::MIN_LENGTH, $this->trLang('RegistraciaForm_username_ar'), 3)
				 ->setRequired($this->trLang('RegistraciaForm_username_sr'));
    $form->addText('email', $this->trLang('Form_email'), 50, 50)
         ->setType('email')
				 ->addRule(Form::EMAIL, $this->trLang('Form_email_ar'))
				 ->setRequired($this->trLang('Form_email_sr'));
    $form->addPassword('heslo', $this->trLang('RegistraciaForm_heslo'), 50, 50)
				 ->addRule(Form::MIN_LENGTH, $this->trLang('RegistraciaForm_heslo_ar'), 5)
				 ->setRequired($this->trLang('RegistraciaForm_heslo_sr'));
    $form->addPassword('heslo2', $this->trLang('RegistraciaForm_heslo2'), 50, 50)
         ->addRule(Form::EQUAL, $this->trLang('RegistraciaForm_heslo2_ar'), $form['heslo'])
				 ->setRequired($this->trLang('RegistraciaForm_heslo2_sr'));
    if ($this->user_view_fields["pohl"]) {
      $form->addSelect('pohl', $this->trLang('RegistraciaForm_pohl'),
        							 ['M'=>$this->trLang('RegistraciaForm_m'),'Z'=>$this->trLang('RegistraciaForm_z')]);
    }
		$form->addSubmit('uloz', $this->trLang('RegistraciaForm_uloz'));
		$form->onSuccess[] = $this->userRegisterFormSubmitted;
		return $this->_setupForm($form);
	}

  /** Spracovanie reistracneho formulara
   * @param Nette\Application\UI\Form $button Data formulara
   */
  public function userRegisterFormSubmitted($button) {
		// Inicializacia
    $values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara
    // Over, ci dane username uz existuje. Ak ano vypis a skonc.
    if ($this->users->testUsername($values->username)) {
      $this->flashMessage($this->trLang('registracia_username_duble'), 'danger'); return;
    }
    // Over, ci dany email uz existuje. Ak ano vypis a skonc.
    if ($this->users->testEmail($values->email)) {
      $this->flashMessage(sprintf($this->trLang('registracia_email_duble'), $values->email, $this->link('User:forgotPassword')), 'danger,n'); return;
    }
    $new_password_key = $this->hasser->HashPassword($values->heslo.StrFTime("%Y-%m-%d %H:%M:%S", Time()));
    $uloz_data_user_profiles = [ //Nastavenie vstupov pre tabulku user_profiles
      'meno'      => $values->meno,
      'priezvisko'=> $values->priezvisko,
      'pohl'      => isset($values->pohl) ? $values->pohl : 'Z',
      'modified'  => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
      'created'   => StrFTime("%Y-%m-%d %H:%M:%S", Time()),
    ];

    $uloz_data_users = [ //Nastavenie vstupov pre tabulku users
      'username'  => $values->username,
      'password'  => $this->hasser->HashPassword($values->heslo),
      'email'     => $values->email,
      'activated' => 0,
    ];
    //Uloz info do tabulky users
    if (($uloz_users = $this->users->uloz($uloz_data_users)) !== FALSE) { //Ulozenie v poriadku
      $uloz_data_user_profiles['id_users'] = $uloz_users['id']; //nacitaj id ulozeneho clena
      $uloz_user_profiles = $this->user_profiles->uloz($uloz_data_user_profiles);
    }
    if ($uloz_user_profiles !== FALSE) { //Ulozenie v poriadku
      $this->flashMessage($this->trLang('base_save_ok'), 'success');
      $templ = new Latte\Engine;
      $params = [
        "site_name" => $this->nazov_stranky,
        "nadpis"    => sprintf($this->trLang('email_activate_nadpis'),$this->nazov_stranky),
        "email_activate_txt" => $this->trLang('email_activate_txt'),
        "email_nefunkcny_odkaz" => $this->trLang('email_nefunkcny_odkaz'),
        "email_pozdrav" => $this->trLang('email_pozdrav'),
        "nazov"     => $this->trLang('register_aktivacia'),
        "odkaz" 		=> 'http://'.$this->nazov_stranky.$this->link("User:activateUser", $uloz_user_profiles['id'], $new_password_key),
      ];
      $mail = new Message;
      $mail->setFrom($this->nazov_stranky.' <'.$this->clen->users->email.'>')
           ->addTo($values->email)->setSubject($this->trLang('register_aktivacia'))
           ->setHtmlBody($templ->renderToString(__DIR__ . '/templates/User/email_activate-html.latte', $params));
      try {
        $sendmail = new SendmailMailer;
        $sendmail->send($mail);
        $this->users->find($uloz_users['id'])->update(['new_password_key'=>$new_password_key]);
        $this->flashMessage($this->trLang('register_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->trLang('send_email_err').$e->getMessage(), 'danger,n');
      }
      $this->redirect('Homepage:');
    } else { $this->flashMessage($this->trLang('register_save_err'), 'danger');}	//Ulozenie sa nepodarilo
  }

  /**
	 * Forgot password user form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentForgottenPasswordForm() {
    $form = new Form();
		$form->addProtection();
    $form->addText('email', $this->trLang('Form_email'), 50, 50)
         ->setType('email')
         ->setAttribute('placeholder', $this->trLang('Form_email_ph'))
				 ->addRule(Form::EMAIL, $this->trLang('Form_email_ar'))
				 ->setRequired($this->trLang('Form_email_sr'));
		$form->addSubmit('uloz', $this->trLang('ForgottenPasswordForm_uloz'))->setAttribute('class', 'btn btn-success')
         ->onClick[] = [$this, 'forgotPasswordFormSubmitted'];
    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')
         ->setValidationScope([])
         ->onClick[] = function () { $this->redirect('Homepage:');}; 
		return $this->_setupForm($form);
	}

  public function forgotPasswordFormSubmitted($button) {
		//Inicializacia
    $values = $button->getForm()->getValues();                 //Nacitanie hodnot formulara
    $clen = $this->users->findOneBy(['email'=>$values->email]);
    $new_password_requested = StrFTime("%Y-%m-%d %H:%M:%S", Time());
    $new_password_key = $this->hasser->HashPassword($values->email.$new_password_requested);
    if (isset($clen->email) && $clen->email == $values->email) { //Taky clen existuje
      $templ = new Latte\Engine;
      $params = [
        "site_name" => $this->nazov_stranky,
        "nadpis"    => sprintf($this->trLang('email_reset_nadpis'),$this->nazov_stranky),
        "email_reset_txt" => $this->trLang('email_reset_txt'),
        "email_nefunkcny_odkaz" => $this->trLang('email_nefunkcny_odkaz'),
        "email_pozdrav" => $this->trLang('email_pozdrav'),
        "nazov"     => $this->trLang('forgot_pass'),
        "odkaz" 		=> 'http://'.$this->nazov_stranky.$this->link("User:resetPassword", $clen->id, $new_password_key),
      ];
      $mail = new Message;
      $mail->setFrom($this->nazov_stranky.' <'.$this->clen->users->email.'>')
           ->addTo($values->email)->setSubject($this->trLang('forgot_pass'))
           ->setHtmlBody($templ->renderToString(__DIR__ . '/templates/User/forgot_password-html.latte', $params));
      try {
        $sendmail = new SendmailMailer;
        $sendmail->send($mail);
        $this->users->find($clen->id)->update(['new_password_key'=>$new_password_key, 'new_password_requested'=>$new_password_requested]);
        $this->flashMessage($this->trLang('forgot_pass_email_ok'), 'success');
      } catch (Exception $e) {
        $this->flashMessage($this->trLang('send_email_err').$e->getMessage(), 'danger,n');
      }
      $this->redirect('Homepage:');
    } else {													//Taky clen neexzistuje
      $this->flashMessage(sprintf($this->trLang('forgot_pass_user_err'),$values->email), 'danger');
    }
  }

  /**
	 * Password reset form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentResetPasswordForm() {
		$form = new Form();
		$form->addProtection();
    $form->addHidden('id');
    $form->addPassword('new_heslo', $this->trLang('ResetPasswordForm_new_heslo'), 50, 80)
				 ->setRequired($this->trLang('ResetPasswordForm_new_heslo_sr'));
		$form->addPassword('new_heslo2', $this->trLang('ResetPasswordForm_new_heslo2'), 50, 80)
         ->addRule(Form::EQUAL, $this->trLang('ResetPasswordForm_new_heslo2_ar'), $form['new_heslo'])
				 ->setRequired($this->trLang('ResetPasswordForm_new_heslo2_sr'));
		$form->addSubmit('uloz', $this->trLang('base_save'));
    $form->onSuccess[] = $this->userPasswordResetFormSubmitted;
		return $this->_setupForm($form);
	}

	public function userPasswordResetFormSubmitted($form) {
		$values = $form->getValues(); 	//Nacitanie hodnot formulara
		if ($values->new_heslo != $values->new_heslo2) {
			$this->flashRedirect('this', $this->trLang('reset_pass_hesla_err'), 'danger');
		}
		//Vygeneruj kluc pre zmenu hesla
    $new_password = $this->hasser->HashPassword($values->new_heslo);
    $values->new_heslo = 'xxxxx'; //Len pre istotu
    $values->new_heslo2= 'xxxxx'; //Len pre istotu
    try {
      $this->users->find($values->id)->update(['password'=>$new_password, 'new_password_key'=>NULL, 'new_password_requested'=>NULL]);
			$this->flashRedirect('User:', $this->trLang('reset_pass_ok'), 'success');
		} catch (Exception $e) {
			$this->flashRedirect('Homepage:', $this->trLang('reset_pass_err').$e->getMessage(), 'danger,n');
		}
	}
}