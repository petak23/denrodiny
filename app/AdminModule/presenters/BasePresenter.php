<?phpnamespace App\AdminModule\Presenters;use Nette\Security\User;use Nette\Utils\Strings;use DbTable;/** * Zakladny presenter pre vsetky presentery v module ADMIN *  * Posledna zmena(last change): 11.01.2016 * * Modul: ADMIN * * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version 1.1.5a */abstract class BasePresenter extends \App\Presenters\CommonBasePresenter {  /**    * @inject   * @var DbTable\Admin_menu */  public $admin_menu;    /** @var string Adresar pre prilohy clankov */  public $prilohy_adresar;    // -- Komponenty  /** @var \App\AdminModule\Components\Oznam\IAktualneOznamyControl @inject */  public $aktualneOznamyControlFactory;  /** @var \App\AdminModule\Components\User\IUserLastControl @inject */  public $userLastControlFactory;    /** @var array Hodnoty role=>id v DB tab registracia */  public $ur_reg = array();  /** @var array Hodnoty id=>nazov pre formulare z tabulky registracia */  public $urovneReg;	/** @var array - pole s chybami pri uploade */  public $upload_error = array(           0=>"Bez chyby. Súbor úspešne nahraný.",          1=>"Nahrávaný súbor je väčší ako systémom povolená hodnota!",          2=>"Nahrávaný súbor je väčší ako je formulárom povolená hodnota!",          3=>"Nahraný súbor bol nahraný len čiastočne...",          4=>"Žiadny súbor nebol nahraný... Pravdepodobne ste vo formuláry žiaden nezvolili!",          5=>"Upload error 5.",          6=>"Chýbajúci dočasný priečinok!",        );    /** Vychodzie nastavenia */  protected function startup() {    parent::startup();    // Sprava uzivatela    $user = $this->getUser();    // Kontrola prihlasenia    if ($this->id_reg) { //Prihlaseny uzivatel      //Kontrola ACL      if (!$user->isAllowed($this->name, $this->action)) {        $this->flashRedirect('Homepage:', 'Na požadovanú akciu nemáte dostatočné oprávnenie!', 'danger');      }    } else { //Neprihlaseny      if ($user->getLogoutReason() === User::INACTIVITY) {        $backlink = $this->getApplication()->storeRequest();        $this->flashRedirect(array(':Front:User:', array('backlink' => $backlink)),          'Boli ste príliš dlho neaktívny a preto ste boli odhlásený! Prosím, prihláste sa znovu.', 'danger');      } else {        $this->flashRedirect(':Front:User:in', 'Nemáte dostatočné oprávnenie na danú operáciu!', 'danger');      }    }    $this->prilohy_adresar = "www/files/prilohy/";    $this->ur_reg = $this->registracia->vsetky_urovne_array();//Najdi max. ur. reg.    $this->urovneReg = $this->registracia->hladaj_urovne(0, $this->id_reg)->fetchPairs('id', 'nazov'); //Hodnoty id=>nazov pre formulare z tabulky registracia  }    /** Nastevenie premennych pre vsetky sablony */  public function beforeRender()  {    if (isset($this->params["id"])) {      $this->menu->selectByUrl($this->link(ucfirst($this->udaje_webu['meno_presentera']).":", array("id"=>(int)$this->params["id"])));    } else {      $this->menu->selectByUrl($this->link(ucfirst($this->udaje_webu['meno_presentera']).":"));    }    $this->template->hl_udaje = $this->udaje_webu['hl_udaje'];    $this->template->verzia = $this->verzie->posledna();    $this->template->nazov_stranky = $this->nazov_stranky;    $this->template->udaje = $this->udaje_webu;    $this->template->urovregistr = $this->id_reg;    $this->template->lang_menu = $this->lang->findAll();    $this->template->language = $this->language;    $this->template->avatar_path = $this->avatar_path;    $this->template->admin_menu = $this->admin_menu->findAll();    $this->template->server_for_adminer = $this->hlavne_menu->getDBInfo();    $this->template->nastavenie = $this->nastavenie;  }  //  ---- Komponenty ----     /** Komponenta pre výpis css a js súborov   * @return \PeterVojtech\Base\CssJsFilesControl */  public function createComponentFiles() {    return new \PeterVojtech\Base\CssJsFilesControl($this->nastavenie['web_files'], $this->name, $this->action);  }    /** Komponenta pre vykreslenie odkazu na produkty s anotaciou   * @return \App\AdminModule\Components\Produkt\ProduktyZoznam   */  public function createComponentProduktZoznam() {    return New \App\AdminModule\Components\Produkt\ProduktyZoznamControl();  }    /** Vytvorenie komponenty pre vypisanie aktualnych oznamov   * @return \App\AdminModule\Components\Oznam\AktualneOznamyControl */	public function createComponentAktualne() {    return $this->aktualneOznamyControlFactory->create();	}    /** Vytvorenie komponenty pre posledných 25 prihlásení   * @return \App\AdminModule\Components\User\UserLastControl */	public function createComponentLast() {    return $this->userLastControlFactory->create();	}    /** Vytvorenie komponenty pre hlavne menu   * @return \App\AdminModule\Components\Menu\Menu   */  public function createComponentMenu() {    $menu = new \App\AdminModule\Components\Menu\Menu;    $hl_m = $this->hlavne_menu->getMenuAdmin($this->id_reg, $this->language_id);    if ($hl_m !== FALSE) {      $servise = $this;      $menu->fromTable($hl_m, function($node, $row) use($servise){        foreach (array("name", "tooltip", "avatar", "anotacia", "node_class", "id", "poradie_podclankov", "datum_platnosti") as $v) {           $node->$v = $row['node']->$v;         }        $node->link = is_array($row['node']->link) ? $servise->link($row['node']->link[0], array("id"=>$row['node']->id)) : $servise->link($row['node']->link);        return $row['nadradena'] ? $row['nadradena'] : null;      });    }    return $menu;  }    /**    * Komponenta Confirmation Dialog pre delete News    * @return Nette\Application\UI\Form    */  public function createComponentConfirmForm() {    $form = new \PeterVojtech\Confirm\ConfirmationDialog($this->getSession('news'));    $form->addConfirmer(        'delete', // názov signálu bude confirmDelete!        array($this, 'confirmedDelete'), // callback na funkciu pri kliknutí na YES        array($this, 'questionDelete') // otázka    );    return $form;  }    /**  * Zostavenie otázky pre ConfDialog s parametrom  * @param Nette\Utils\Html $dialog  * @param array $params  * @return string $question  */  public function questionDelete($dialog, $params) {     $dialog->getQuestionPrototype();     return sprintf("Naozaj chceš zmazať %s '%s'?",                    isset($params['zdroj_na_zmazanie']) ? $params['zdroj_na_zmazanie'] : "položku",                    isset($params['nazov']) ? $params['nazov'] : '');  }      /** Funkcia zmaze v DB specificky nazov   * @param string - spec nazov clanku na zmazanie   * @param string - nazov modelu - volitelny parameter ak nie je berie sa $this->zdrojAcl	 */	protected function _zmazSpecNazov($spec_nazov = '', $model = NULL) {    if (!strlen($spec_nazov)) { return; } //Nezadany spec. nazov		$this->{$model}->delSpecNazov($spec_nazov, $this->id_reg); //Vymaze spec. nazov clanku	}  /** Funkcia skontroluje a priradi specificky nazov clanku podla nazvu   * @param string - nazov clanku   * @param string - nazov modelu - volitelny parameter ak nie je berie sa $this->zdrojAcl	 * @return string	 */	protected function _najdiSpecNazov($nazov = 'spec-nazov', $model = NULL) {    //Prevedie na tvar pre URL s tym, ze _ akceptuje    $spec_nazov = Strings::webalize($nazov, '_');		//Pomocna premena pre koncovku		$pom = 1;		while ($pom) { //Dokial pom <> 0 rob			//Zisti ci je v DB tento spec. nazov			$zisti = $this->{$model}->hladaj_spec($spec_nazov);			//Ak nie pom=0 a skonci      if (!isset($zisti->id)) { $pom = 0;}			else { //Nazov som nasiel				if ($pom==1) { //Ak je to prvy raz pridaj na koniec nazvu "1"					$spec_nazov .= $pom;				} else { //Ak je to nasobne odober predosli index a pridaj novy					$spec_nazov = substr($spec_nazov, 0, strlen($spec_nazov)-strlen($pom-1)).$pom;				}				$pom++;			}		}		return $spec_nazov;	}	/** Vypis spravy podla podmienky    * @param boolean $if   * @param string $dobre   * @param string $zle   */  public function _ifMessage($if, $dobre, $zle) {    if ($if) { $this->flashMessage($dobre, 'success'); }    else { $this->flashMessage($zle, 'danger'); }  }    /**   * Nastavenie vzhľadu formulara   * @param \Nette\Application\UI\Form $form   * @return \Nette\Application\UI\Form   */  public function _vzhladForm($form) {    $renderer = $form->getRenderer();    $renderer->wrappers['error']['container'] = 'div class="row"';    $renderer->wrappers['error']['item'] = 'div class="col-md-6 col-md-offset-3 alert alert-danger"';    $renderer->wrappers['controls']['container'] = NULL;    $renderer->wrappers['pair']['container'] = 'div class=form-group';    $renderer->wrappers['pair']['.error'] = 'has-error';    $renderer->wrappers['control']['container'] = 'div class="col-sm-9 control-field"';    $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';    $renderer->wrappers['control']['description'] = 'span class="help-block alert alert-info"';    $renderer->wrappers['control']['errorcontainer'] = 'span class="help-block alert alert-danger"';    // make form and controls compatible with Twitter Bootstrap    $form->getElementPrototype()->class('form-horizontal');    foreach ($form->getControls() as $control) {      if ($control instanceof Controls\Button) {        $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');        $usedPrimary = TRUE;      } elseif ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {        $control->getControlPrototype()->addClass('form-control');      } elseif ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {        $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);      }    }    return $form;  }}