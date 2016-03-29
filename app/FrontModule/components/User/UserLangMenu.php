<?php
namespace App\FrontModule\Components\User;

use Nette\Application\UI\Control;
use Nette\Utils\Html;
use Nette\Application\UI\Form;
/**
 * Plugin pre zobrazenie ponuky o užívateľovi a jazykoch
 * Posledna zmena(last change): 27.10.2015
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2013 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.4
 */

class UserLangMenu extends Control {
  /** @var array - defaultne texty pre prihlasovaci form */
	protected $texty = array(
		'base_SignInForm_username' 			=> "Meno alebo e-mail",
		'base_SignInForm_username_req' 	=> "Meno je povinné! Prosím, zadajte ho...",
		'base_SignInForm_password' 			=> "Heslo",
		'base_SignInForm_password_req' 	=> "Heslo je povinné! Prosím, zadajte ho...",
		'base_SignInForm_remember' 			=> "Zapamätaj prihlásenie",
		'base_SignInForm_login' 				=> "Prihlás",
    'base_AdminLink_name'           => "Administrácia",
	);
	
  /** @var array - lokalne nastavenia */
	private $nastavenie = array(
		'view_avatar'                 => FALSE,
		'view_log_in_link_in_header' 	=> 1,
    'admin_link'                  => 1,
	);
	
  /** Nastavenie textov pre prihlasovaci formular
   * @param array $texty
   */
	public function setTexty($texty) {
		if (is_array($texty)) {
			$this->texty = array_merge($this->texty, $texty);
		}
	}
	/** Nastaveneie nastaveni
   * @param array $nastavenie
   */
	public function settings($nastavenie) {
		if (is_array($nastavenie)) {
			$this->nastavenie = array_merge($this->nastavenie, $nastavenie);
		}
	}
  
  /** Panel neprihlaseneho uzivatela
   * @param array $udaje_webu
   * @param int $vlnh
   * @return \App\FrontModule\Components\User\MenuItem
   */
  private function _panelNeprihlaseny($udaje_webu, $vlnh) {
    $menu_user = array();
    $menu_user[] = new MenuItem(array(
        'odkaz'=>'User:default#prihlas', 
        'class'=>'log-in'.(($vlnh) ? "" : " prazdny fa fa-lock"),
        'title'=>$udaje_webu['log_in'].$vlnh,
        'nazov'=>($vlnh & 1) ? $udaje_webu['log_in'] : ($vlnh ? NULL : ""),
        'ikonka'=>($vlnh & 2) ? "sign-in" : NULL,
                        ));
    if ($vlnh > 0) {//Ak je >0 zobraz link
      $menu_user[] = new MenuItem(array(
          'odkaz'=>'User:forgottenPassword',
          'title'=>$udaje_webu['forgot_password'],
          'ikonka'=>($this->nastavenie['view_log_in_link_in_header'] & 2) ? "frown-o" : NULL,
          'nazov'=>($this->nastavenie['view_log_in_link_in_header'] & 1) ? $udaje_webu['forgot_password'] : NULL,
                          ));
    } else {//Ak je 0 nezobraz link
      $this->template->fl = new MenuItem(array(
        'odkaz'=>'User:forgottenPassword',
        'title'=>$udaje_webu['forgot_password'],
        'class'=>'fl',
        'ikonka'=>"frown-o",
        'nazov'=>$udaje_webu['forgot_password'],
                        ));
    }
    if (isset($udaje_webu['registracia_enabled']) && $udaje_webu['registracia_enabled']) {
      $menu_user[] = new MenuItem(array(
          'odkaz'=>'User:registracia', 
          'nazov'=>$udaje_webu['register']
                          ));
    }
    return $menu_user;
  }

  /** Panel prihlaseneho uzivatela
   * @param type $user
   * @param string $baseUrl
   * @param array $hl_m_db_info
   * @param string $log_out
   * @return \App\FrontModule\Components\User\MenuItem
   */
  private function _panepPrihlaseny($user, $baseUrl, $hl_m_db_info, $log_out) {
    $menu_user = array();
    $udata = $user->getIdentity();
    if ($this->nastavenie['view_avatar']) {
      $obb = Html::el('img class="avatar"');
      if ($udata->avatar_25 && is_file('www/'.$udata->avatar_25)) {
        $obb = $obb->src($baseUrl.'/www/'.$udata->avatar_25)->alt('avatar');
      } else {
        $obb = $obb->src($baseUrl.'/www/ikonky/64/figurky_64.png')->alt('bez avatara');
      }
    } else {$obb = "";}
    $menu_user[] = new MenuItem(array(
          'odkaz'=>'UserLog:', 
          'nazov'=>$obb." ".$udata->meno.' '.$udata->priezvisko,
          'title'=>$udata->meno.' '.$udata->priezvisko));
    if ($user->isAllowed('admin', 'enter')) {
      $menu_user[] = new MenuItem(array(
        'odkaz'=>':Admin:Homepage:',
        'title'=>'Administrácia',
        'ikonka'  => ($this->nastavenie['admin_link'] & 1) ? 'pencil' : '',
        'nazov'=>($this->nastavenie['admin_link'] & 2) ? $this->texty['base_AdminLink_name'] : '',
      ));
    }
    if ($user->isInRole('admin')) {
      $menu_user[] = new MenuItem(array(
        'abs_link'=>$baseUrl."/www/adminer/?server=".$hl_m_db_info['host']."&db=".$hl_m_db_info['dbname'], 
        'title'=>'Adminer',
        'target'=>'_blank',
        'nazov'=>Html::el('img')->src($baseUrl.'/www/ikonky/16/graf_16.png')->alt('Adminer')
                          ));
    }
    $menu_user[] = new MenuItem(array(
        'odkaz'=>'signOut!',
        'ikonka'=>"sign-out",
        'nazov'=>$log_out,
                        ));
    return $menu_user;
  }

  /** Vykreslenie komponenty */
  public function render() {
		//Inicializacia
		$pthis = $this->presenter;
		$baseUrl = $this->template->baseUrl;

		if ($pthis->user->isLoggedIn()) { 
      //Panel prihlaseneho uzivatela
      $menu_user = $this->_panepPrihlaseny($pthis->user, $baseUrl, $pthis->hlavne_menu->getDBInfo(), $pthis->udaje_webu['log_out']);
		} elseif (($vlnh = $this->nastavenie['view_log_in_link_in_header']) >= 0) { 
      //Panel neprihlaseneho uzivatela
      $menu_user = $this->_panelNeprihlaseny($pthis->udaje_webu, $vlnh);
		}
		$lang_temp = $pthis->lang->findBy(array('prijaty'=>1));
		if ($lang_temp !== FALSE && count($lang_temp)>1) {
			foreach($lang_temp as $lm) {
				$menu_user[] = new MenuItem(array(
						'odkaz'=>array('setLang!', $lm->skratka),
						'title'=>$lm->nazov.", ".$lm->nazov_en,
						'class'=>($lm->skratka == $pthis->language) ? "lang actual" : "lang",
            'nazov'=>Html::el('img')->src($baseUrl.'/www/ikonky/flags/'.$lm->skratka.'.png')->alt('Adminer')
				));
			}
		}
		$this->template->menu_user = isset($menu_user) ? $menu_user : array();
		$this->template->language = $pthis->language;
		$this->template->setFile(__DIR__ . '/UserLangMenu.latte');
		$this->template->render();
	}
		
	/** Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm() {
		$form = new Form;
		$form->addText('username', $this->texty['base_SignInForm_username'].":", 20, 30)
				 ->setRequired($this->texty['base_SignInForm_username_req'])
         ->setAttribute('placeholder', $this->texty['base_SignInForm_username']);
		$form->addPassword('password', $this->texty['base_SignInForm_password'].":", 20, 20)
				 ->setRequired($this->texty['base_SignInForm_password_req'])
         ->setAttribute('placeholder', $this->texty['base_SignInForm_password']);
		$form->addCheckbox('remember', $this->texty['base_SignInForm_remember']);
		$form->addSubmit('login', $this->texty['base_SignInForm_login']);
		$form->onSuccess[] = $this->signInFormSubmitted;
		return $form;
	}
	
	/** Overenie po prihlaseni
   * @param Nette\Application\UI\Form $form
   */
	public function signInFormSubmitted($form) {
    $values = $form->getValues();
    $this->presenter->backlink = $this->presenter->storeRequest();
    $this->presenter->prihlasMa($values->username, $values->password, $values->remember);
	}
  
  public function signInFormStorno(/*$form*/) {
    $this->presenter->flashRedirect('this', 'Prihlásenie bolo stornované!', 'warning');
  }
}

class MenuItem extends \Nette\Object {
  public $odkaz;
  public $abs_link;
  public $nazov = "";
  public $class = "";
  public $title = "";
  public $target = "";
  public $ikonka;
  
  function __construct(array $params) {
    foreach ($params as $k => $v) { $this->$k = $v;}
    $this->title = $this->title == "" ? $this->nazov : $this->title;
  }
}
