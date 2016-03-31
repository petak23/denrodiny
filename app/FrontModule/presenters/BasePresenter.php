<?php
namespace App\FrontModule\Presenters;

use Nette\Application\UI\Form,
    Nette\Security as NS;
use Nette\Application\UI\Multiplier;
use Nette;
use DbTable;

/**
 * Zakladny presenter pre vsetky presentery vo FRONT module
 * 
 * Posledna zmena(last change): 31.03.2016
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link      http://petak23.echo-msz.eu
 * @version 1.2.0
 */

abstract class BasePresenter extends \App\Presenters\CommonBasePresenter {
  /** 
   * @inject
   * @var DbTable\Adresar */
  public $adresar;
  /** 
   * @inject
   * @var DbTable\Dokumenty */
	public $dokumenty;
  /** 
   * @inject
   * @var DbTable\Hlavne_menu_cast */
  public $hlavne_menu_cast;

  /** 
  * @inject
  * @var DbTable\Produkt_lang */
//	public $produkt_lang;
  
  /** @var mix */
  public $texty_presentera;
  
  // -- Komponenty
  /** @var \App\FrontModule\Components\Oznam\IAktualneOznamyControl @inject */
  public $aktualneOznamyControlFactory;
  /** @var \App\FrontModule\Components\Slider\ISliderControl @inject */
  public $sliderControlFactory;
  
  /** Vratenie textu pre dany kluc a jazyk
   * @param string $key - kluc daneho textu
   * @return string - hodnota pre dany text
   */
  public function trLang($key) {
    if ($this->texty_presentera == NULL) { return $key; }
    return ($this->user->isInRole("Admin")) ? $key."-".$this->texty_presentera->trText($key) : $this->texty_presentera->trText($key);
  }
  
  protected function _setupForm($form) {
    $renderer = $form->getRenderer();
    $renderer->wrappers['controls']['container'] = NULL;
    $renderer->wrappers['pair']['container'] = 'div class=form-group';
    $renderer->wrappers['pair']['.error'] = 'has-error';
    $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
    $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
    $renderer->wrappers['control']['description'] = 'span class=help-block';
    $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
    return $form;
  }

	protected function startup() {
    parent::startup();
    $this->texty_presentera->setLanguage($this->language); //Nastavenie textov podla jazyka
	}

  /** Komponenta pre vykreslenie menu
   * @return \App\FrontModule\Components\Menu\Menu
   */
  public function createComponentMenu() {
    $menu = new \App\FrontModule\Components\Menu\Menu;
    $menu->textTitleImage = $this->trLang("base_text_title_image");
    $hl_m = $this->hlavne_menu->getMenuFront($this->id_reg, $this->language_id);
    if ($hl_m !== FALSE) {
      $servise = $this;
      $menu->fromTable($hl_m, function($node, $row) use($servise) {
        $poll = ["id", "name", "tooltip", "avatar", "anotacia", "novinka", "node_class", "poradie_podclankov"];
        foreach ($poll as $v) { $node->$v = $row['node']->$v; }
        // Nasledujuca cast priradi do $node->link odkaz podla kriteria:
        // Ak $rna == NULL - vytvori link ako odkaz do aplikacie
        // Ak $rna zacina "http" - pouzije sa absolutna adresa
        // Ak $rna obsahuje text "Clanky:default 2" - vytvorí sa odkaz do aplikácie na clanok s id 2 - moze byt aj bez casti ":2" odkazu ale musí byť aj default
        // Ak $rna neobsahuje ":" tak sa použije tak ako je
        $rna = $row['node']->absolutna;
        if ($rna !== NULL) {
          $node->link = strpos($rna, 'http') !== FALSE ? $rna 
                                                       : (count($p = explode(" ", $rna)) == 2 ? $servise->link($p[0], ["id"=>$p[1]]) 
                                                                                              : (count($p = explode(":", $rna)) == 2 ? $servise->link($p[0]) : $rna)
                                                         );
        } else {
          $node->link = is_array($row['node']->link) ? $servise->link($row['node']->link[0], ["id"=>$row['node']->id]) 
                                                     : $servise->link($row['node']->link);
        }
        return $row['nadradena'] ? $row['nadradena'] : null;
      });
    }
    return $menu;
  }
    
  /** Naplnenie spolocnych udajov pre sablony */
  public function beforeRender() {
    $this->menu->selectByUrl($this->link('this'));
    $this->template->udaje = $this->udaje_webu;
		$this->template->verzia = $this->verzie->posledna();
		$this->template->urovregistr = $this->id_reg;
    $this->template->maxurovregistr = $this->max_id_reg;
    $this->template->language = $this->language;
    $this->template->user_admin = $this->user_profiles->findOneBy(['id_registracia'=>$this->max_id_reg]);
    $this->template->user_spravca = $this->user_profiles->findOneBy(['id_registracia'=>$this->max_id_reg-1]);
    $this->template->nazov_stranky = $this->nazov_stranky;
		$this->template->avatar_path = $this->avatar_path;
    $this->template->text_title_image = $this->trLang("base_text_title_image");
		$this->template->article_avatar_view_in = $this->nastavenie["article_avatar_view_in"];
    $this->template->omrvinky_enabled = $this->nastavenie["omrvinky_enabled"];
    $this->template->view_log_in_link_in_header = $this->nastavenie['user_panel']["view_log_in_link_in_header"];
	}

  /** Signal pre odhlasenie sa */
	public function handleSignOut() {
		$this->getUser()->logout();
    $this->id_reg = 0;
		$this->flashMessage($this->trLang('base_log_out_mess'), 'success');
    // Kontrola ACL
    if (!$this->user->isAllowed($this->name, $this->action)) {
      $this->flashRedirect('Homepage:', sprintf($this->trLang('base_nie_je_opravnenie'), $this->action), 'danger');
    }
	}

  /** Signal prepinania jazykov
   * @param string $language skratka noveho jazyka
   */
  public function handleSetLang($language) {
    if ($this->language != $language) { //Cokolvek rob len ak sa meni
      //Najdi v DB pozadovany jazyk
      $la_tmp = $this->lang->findOneBy(['skratka'=>$language]);
      //Ak existuje tak akceptuj
      if (isset($la_tmp->skratka) && $la_tmp->skratka == $language) { $this->language = $language; }
    }
    $this->redirect('this');
	}
  
  /** Komponenta pre výpis css a js súborov
   * @return \PeterVojtech\Base\CssJsFilesControl
   */
  public function createComponentFiles() {
    return new \PeterVojtech\Base\CssJsFilesControl($this->nastavenie['web_files'], $this->name, $this->action);
  }
  
  /**
   * Vytvorenie komponenty pre menu uzivatela a zaroven panel jazykov
   * @return \App\FrontModule\Components\User\UserLangMenu
   */
  public function createComponentUserLangMenu() {
    $mnu = new \App\FrontModule\Components\User\UserLangMenu();
    $nup = $this->nastavenie['user_panel'];
		$mnu->setTexty([
			'base_SignInForm_username' 			=> $this->trLang('base_SignInForm_username'),
			'base_SignInForm_username_req' 	=> $this->trLang('base_SignInForm_username_req'),
			'base_SignInForm_password' 			=> $this->trLang('base_SignInForm_password'),
			'base_SignInForm_password_req' 	=> $this->trLang('base_SignInForm_password_req'),
			'base_SignInForm_remember' 			=> $this->trLang('base_SignInForm_remember'),
			'base_SignInForm_login' 				=> $this->trLang('base_SignInForm_login'),
      'base_AdminLink_name'           => $this->trLang('base_AdminLink_name'),
		]);
		$mnu->settings(array_merge($nup, ['view_avatar'  => $nup['view_avatar'] && $this->nastavenie['user_view_fields']['avatar']]));
		return $mnu;
  }

  /** Sign in form component factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm() {
		$form = new Form;
		$form->addText('username', $this->trLang('base_SignInForm_username'), 40, 20)
				 ->setRequired($this->trLang('base_SignInForm_username_req'));
		$form->addPassword('password', $this->trLang('base_SignInForm_password'), 40)
				 ->setRequired($this->trLang('base_SignInForm_password_req'));
		$form->addCheckbox('remember', $this->trLang('base_SignInForm_remember'));
		$form->addSubmit('login', $this->trLang('base_SignInForm_login'));
    $re = $form->getRenderer();
    $re->wrappers["controls"]["container"] = "dl";
    $re->wrappers["pair"]["container"] = NULL;
    $re->wrappers["label"]["container"] = "dt";
    $re->wrappers["control"]["container"] = "dd";
		$form->onSuccess[] = $this->signInFormSubmitted;
		return $form;
	}

  /** Overenie po prihlaseni
   * @param Nette\Application\UI\Form $form
   */
	public function signInFormSubmitted($form) {
    $values = $form->getValues();
    $this->prihlasMa($values->username, $values->password, $values->remember);
	}

  /** Funkcia overi prihlasenia a vykona prislusne ukony
   * @param string $name Prihlasovacie meno
   * @param string $password Prihlasovacie heslo
   * @param boolean $remember Pamatanie prihlasenia
   */
  public function prihlasMa($name, $password, $remember = FALSE) {
    try {
			$user = $this->getUser();
			if ($remember) {
				$user->setExpiration('+ 14 days', FALSE);
			} else {
				$user->setExpiration('+ 30 minutes', TRUE);
			}
			$user->login($name, $password);
			$this->restoreRequest($this->backlink);
      $this->flashRedirect('Homepage:', $this->trLang('base_login_ok'), 'success');
		} catch (NS\AuthenticationException $e) {
			$this->flashMessage(sprintf($this->trLang('base_login_error'), $e->getMessage()), 'danger');
    }
  }
  /** Vytvorenie komponenty pre potvrdzovaci dialog
   * @return Nette\Application\UI\Form
   */
  public function createComponentConfirmForm() {
    $form = new \PeterVojtech\Confirm\ConfirmationDialog($this->getSession('news'));
    $form->addConfirmer(
        'delete', // názov signálu bude confirmDelete!
        [$this, 'confirmedDelete'], // callback na funkciu pri kliknutí na YES
        [$this, 'questionDelete'] // otázka
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
     return sprintf($this->trLang('base_delete_text'),
                    isset($params['zdroj_na_zmazanie']) ? $params['zdroj_na_zmazanie'] : "položku",
                    isset($params['nazov']) ? $params['nazov'] : '');
  }
  
  /** Vytvorenie komponenty slideru
   * @return \App\FrontModule\Components\Slider\Slider
   */
	public function createComponentSlider() {
    $slider = $this->sliderControlFactory->create();
    $slider->setNastavenie($this->nastavenie["slider"]);
    return $slider;
	}
  
  /** Komponenta pre vykreslenie odkazu na clanok s anotaciou
   * @return \App\FrontModule\Components\Clanky\OdkazNaClankyControl
   */
  public function createComponentOdkazNaClanky() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
			$odkaz = New \App\FrontModule\Components\Clanky\OdkazNaClankyControl();
      $odkaz->setTextNotFoundClanok($servise->trLang('odkazNaClankyControl_not_found'));
      $odkaz->setTexts([
        "to_foto_galery"  => $this->trLang("base_to_foto_galery"),
        "to_article"      => $this->trLang("base_to_article"),
        "neplatny"        => $this->trLang("base_neplatny"),
        "platil_do"       => $this->trLang("base_platil_do"),
        "platny_do"       => $this->trLang("base_platnost_do"),
      ]);
			return $odkaz;
		});
  }
  
  /** Komponenta pre zobrazenie clanku
   * @return \App\FrontModule\Components\Clanky\ZobrazClanokControl
   */
  public function createComponentUkazClanok() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
      if (is_numeric($id)) {
        $clanok = $servise->hlavne_menu_lang->getOneArticleId($id, $servise->language_id, 0);
      } else {
        $clanok = $servise->hlavne_menu_lang->getOneArticleSp($id, $servise->language_id, 0);
      }
      $ukaz_clanok = New \App\FrontModule\Components\Clanky\ZobrazClanokControl($clanok);
      $ukaz_clanok->setTexts([
        "not_found"         =>$servise->trLang('base_not_found'),
        "platnost_do"       =>$servise->trLang('base_platnost_do'),
        "zadal"             =>$servise->trLang('base_zadal'),
        "zobrazeny"         =>$servise->trLang('base_zobrazeny'),  
        "anotacia"          =>$servise->trLang('base_anotacia'),
        "viac"              =>$servise->trLang('base_viac'),
        "text_title_image"  =>$servise->trLang("base_text_title_image"),
        ]);
      $ukaz_clanok->setClanokHlavicka($servise->udaje_webu['clanok_hlavicka']);
      return $ukaz_clanok;
    });
  }
  /** Komponenta pre zobrazenie adresy
   * @return Multiplier
   */
  public function createComponentUkazAdresu() {
    $servise = $this;
		return new Multiplier(function ($id) use ($servise) {
      return New \App\FrontModule\Components\Adresar\ZobrazAdresuControl($servise->adresar->find($id));
    });
  }
  
  /** Vytvorenie komponenty pre vypisanie aktualnych oznamov
   * @return \App\FrontModule\Components\Oznam\AktualneOznamyControl
   */
	public function createComponentAktualne() {
    $aktualne = $this->aktualneOznamyControlFactory->create();
    $aktualne->setNastavenie($this->context->parameters['oznam'])         
             ->setTexty(["h2"=>$this->trLang('base_aktualne_h2'),"viac"=>$this->trLang("base_viac"),"title"=>$this->trLang("base_title")]);
    return $aktualne;
	}
  
  /** Komponenta pre vykreslenie odkazu na produkty s anotaciou
   * @return \App\FrontModule\Components\Produkt\ProduktyZoznam
   */
//  public function createComponentProduktZoznam() {
//		return New \App\FrontModule\Components\Produkt\ProduktyZoznamControl($this->produkt_lang->getTableColsInfo());
//  }
  
  /** Komponenta pre vypis kontaktneho formulara
   * @return \App\FrontModule\Components\User\Kontakt
   */
	public function createComponentKontakt() {
		$kontakt = New \App\FrontModule\Components\User\Kontakt();
    $kontakt->setSablona([
        'h4'        => $this->trLang('komponent_kontakt_h4'),
        'uvod'      => $this->trLang('komponent_kontakt_uvod'),
        'meno'      => $this->trLang('komponent_kontakt_meno'),
        'email'     => $this->trLang('komponent_kontakt_email'),
        'email_ar'	=> $this->trLang('komponent_kontakt_email_ar'),
        'email_sr'	=> $this->trLang('komponent_kontakt_email_sr'),
        'text'      => $this->trLang('komponent_kontakt_text'),
        'text_sr'   => $this->trLang('komponent_kontakt_text_sr'),
        'uloz'      => $this->trLang('komponent_kontakt_uloz'),
        'send_ok'   => $this->trLang('komponent_kontakt_send_ok'),
        'send_er'   => $this->trLang('komponent_kontakt_send_er') 
      ]);
    $spravca = $this->user_profiles->findOneBy(["users.username" => "spravca"]);
		$kontakt->setSpravca($spravca->users->email);
    $kontakt->setNazovStranky($this->nazov_stranky);
		return $kontakt;	
	}
  
  protected function createTemplate($class = NULL) {
    $servise = $this;
    $template = parent::createTemplate($class);
    $template->addFilter('trLang', function ($key) use($servise){
      if ($servise->texty_presentera == NULL) { return $key; }
      return ($servise->user->isInRole("Admin")) ? $key."-".$servise->texty_presentera->trText($key) : $servise->texty_presentera->trText($key);
    });
    $template->addFilter('nadpisH1', function ($key){
      $out = "";
      foreach (explode(" ", $key) as $v) {
        $out .= "<div>".$v." </div>";
      }
      return $out;
    }); 
    return $template;
	}
}
