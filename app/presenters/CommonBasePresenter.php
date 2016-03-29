<?php
namespace App\Presenters;

use Nette, Nette\Utils\Strings;
use DbTable;

/**
 * Zakladny presenter pre vsetky presentery v aplikacii
 * Base presenter for all application presenters.
 * Posledna zmena(last change): 02.02.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.1.1
 */

abstract class CommonBasePresenter extends Nette\Application\UI\Presenter
{
  /** @persistent */
  public $language = 'sk';
  /** @persistent */
  public $backlink = '';

  /** 
   * @inject
   * @var DbTable\Druh */
	public $druh;
	/** 
   * @inject
   * @var DbTable\Hlavne_menu */
	public $hlavne_menu;
  /** 
   * @inject
   * @var DbTable\Hlavne_menu_lang */
  public $hlavne_menu_lang;
  /** 
   * @inject
   * @var DbTable\Registracia */
	public $registracia;
	/**
   * @inject 
   * @var DbTable\Udaje */
	public $udaje;
	/** 
   * @inject
   * @var DbTable\User_profiles */
	public $user_profiles;
	/** 
   * @inject
   * @var DbTable\Verzie */
	public $verzie;
  /** 
   * @inject
   * @var DbTable\Lang */
	public $lang;
  /**
   * @inject
   * @var Nette\Http\Request
   */
  public $httpRequest;

  /** @var string kmenovy nazov stranky pre rozne ucely typu www.neco.sk*/
  public $nazov_stranky;
  /** @var int Uroven registracie uzivatela  */
	public $id_reg;
  /** @var int Maximalna uroven registracie uzivatela */
	public $max_id_reg = 0;
  
	/** @var string Specificky nazov casti */
	public $spec_nazov;

	/** @var string Specificky nazov konkretneho clanku */
	public $spec_clanok;

	/** @var string Nadpis formulara */
	public $nadpis;

  /** @var array Pole s hlavnymi udajmi webu */
  public $udaje_webu;

  /** @var string Nazov prvku na zmazanie */
  public $zdroj_na_zmazanie;

  /** @var int */
  public $language_id = 1;
  
  /** @var array nastavenie z config-u */
  public $nastavenie;
	/** @var string - relatívna cesta pre avatar poloziek menu */
	public $avatar_path = "files/menu/";
  /** @var array Zoznam css a js suborov pre layout  */
  public $web_files = array("css"=>array(), "js"=>array());
  /** @var int Maximalna velkost suboru pre upload */
  public $upload_size = 0;
  
  protected function startup() {
    parent::startup();
    // Sprava uzivatela
    $user = $this->getUser(); //Nacitanie uzivatela
    // Kontrola prihlasenia a nacitania urovne registracie
    $this->id_reg = ($user->isLoggedIn()) ? $user->getIdentity()->id_registracia : 0;
    // Nastavenie z config-u
    $this->nastavenie = $this->context->parameters;
    $modul_presenter = explode(":", $this->name);
    $m = $modul_presenter[0]; //Modul
    if ($m == "Mapa") {//Ak mám modul "Mapa" tak ostatne neporebujem
      $this->language = 'sk';
      $this->language_id = 1;
      return; 
    }
    // Skontroluj ci je nastaveny jazyk a ci pozadovany jazyk existuje ak ano akceptuj
    if (!isset($this->language)) {//Prednastavim hodnotu jazyka
      $lang_temp = $this->lang->find(1);
      $this->language = $lang_temp->skratka; 
      $this->language_id = $lang_temp->id;
    }
    if (isset($this->params['language'])) {
      $lang_temp = $this->lang->findOneBy(array('skratka'=>$this->params['language']));
      if(isset($lang_temp->skratka) && $lang_temp->skratka == $this->params['language']) {
        $this->language = $this->params['language'];
        $this->language_id = $lang_temp->id;
      } else { //Inak nastav Slovencinu
        $this->language = 'sk';
        $this->language_id = 1;
      }
    } 
    //Nacitanie a spracovanie hlavnych udajov webu
    $this->udaje_webu = $this->udaje->findAll()->fetchPairs('nazov', 'text');
    $vysledok = array();
    //Nacitanie len tych premennych, ktore platia pre danu jazykovu mutaciu
    foreach ($this->udaje_webu as $key => $value) { 
      $kluc = explode("-", $key);
      if (count($kluc) == 2 && $kluc[1] == $this->language) { $vysledok[substr($key, 0, strlen($key)-strlen($this->language)-1)] = $value; } 
      if (count($kluc) == 1) {$vysledok[$key] = $value;}
    }
    $this->udaje_webu = $vysledok;
    // Nacitanie pomocnych premennych
    $this->udaje_webu['meno_presentera'] = strtolower($modul_presenter[1]); //Meno aktualneho presentera
    $httpR = $this->httpRequest->getUrl();
    $this->nazov_stranky = $httpR->host.$httpR->scriptPath; // Nazov stranky v tvare www.nieco.sk
    $this->nazov_stranky = substr($this->nazov_stranky, 0, strlen($this->nazov_stranky)-1);
    // Priradenie hlavnych parametrov a udajov
    $this->max_id_reg = $this->registracia->findAll()->max('id');//Najdi max. ur. reg.
    //Najdi info o druhu
    $tmp_druh = $this->druh->findBy(array("druh.presenter"=>ucfirst($this->udaje_webu['meno_presentera'])))
                           ->where("druh.modul IS NULL OR druh.modul = ?", $modul_presenter[0])->limit(1)->fetch();
    if ($tmp_druh !== FALSE) {
      if ($tmp_druh->je_spec_naz) { //Ak je spec_nazov pozadovany a mam id
        $hl_udaje = $this->hlavne_menu->hladaj_id(isset($this->params['id']) ? (int)trim($this->params['id']) : 0, $this->id_reg);
      } else {//Ak nie je spec_nazov pozadovany
        $hl_udaje = $this->hlavne_menu->findOneBy(array("id_druh"=>$tmp_druh->id));
      }
    } else { $hl_udaje = FALSE; }
    if ($hl_udaje !== FALSE) { //Ak sa hl. udaje nasli
      //Nacitanie textov hl_udaje pre dany jazyk 
      $lang_hl_udaje = $this->hlavne_menu_lang->findOneBy(array('id_lang'=>$this->language_id, 
                                                                'id_hlavne_menu'=>$hl_udaje->id));
      if ($lang_hl_udaje !== FALSE){ //Nasiel som udaje a tak aktualizujem
        $this->udaje_webu["nazov"] = $lang_hl_udaje->nazov;
        $this->udaje_webu["h1part2"] = $lang_hl_udaje->h1part2;
        $this->udaje_webu["description"] = $lang_hl_udaje->description;
      } else { //Len preto aby tam nieco bolo
        $this->udaje_webu["nazov"] = "Error nazov";
        $this->udaje_webu["h1part2"] = "Error h1part2";
        $this->udaje_webu["description"] = "Error description";
      }
      $this->udaje_webu['hl_udaje'] = $hl_udaje->toArray();
    } else { //Len preto aby tam nieco bolo
      $this->udaje_webu["description"] = "Nenájdená stránka";
      $this->udaje_webu['hl_udaje'] = FALSE;
    }
    //Vypocet max. velkosti suboru pre upload
    $ini_v = trim(ini_get("upload_max_filesize"));
    $s = array('g'=> 1<<30, 'm' => 1<<20, 'k' => 1<<10);
    $this->upload_size =  intval($ini_v) * ($s[strtolower(substr($ini_v,-1))] ?: 1);
	}

  /** Funkcia overi opravnenie na konkretnu operaciu a ak nie je redirect-ne
		* @param string $operacia
		* @param string $redirect
		*/
	protected function opravnenie($operacia, $redirect = 'Homepage:') {
		if (!$this->user->isAllowed($this->name , $operacia)) { //Ak nemam opravnenie skonc
			$this->flashMessage($this->trLang('base_nie_je_opravnenie1'), 'zle');
			$this->redirect($redirect);
		}
	}

  /** Funkcia overi opravnenie na pristup k danej polozke a vlastnictvo polozky
    * @param string $resource
    * @param string $action
    * @param int $id_user_profiles
    * @return boolean
    */
  public function overPristup($resource = NULL, $action = NULL, $id_user_profiles = 0) {
    $out = FALSE;
    $user = $this->user;
    if ($user->isLoggedIn()) {
      if ($user->isInRole('admin')) { $out = TRUE; // Admin moze vsetko
      } elseif (isset($resource) && isset($action) && $user->isAllowed($resource , $action)) { // Inak ak mam opravnenie a som vlastnik tak mozem
        $out = $id_user_profiles ? ($user->getIdentity()->id == $id_user_profiles ? TRUE : FALSE) : FALSE; //Ak som vlastník
      }
    }
    return $out;
  }

  /** Funkcia overi opravnenie vlastnictvo clanku, príspevku, ... pre editaciu a mazanie
   * @param string $co
   * @param int $id_user_profiles
   * @return boolean
   */
  public function siVlastnik($co = 'edit', $id_user_profiles = 0) {
    return $this->overPristup($this->name , $co, $id_user_profiles);
  }
  
  /** Funkcia overi vlastnictvo clanku
   * @param int $id_user_profiles
   * @return boolean
   */
  public function vlastnik($id_user_profiles = 0) {
    $user = $this->user;
    return $user->isInRole('admin') ? TRUE : $user->getIdentity()->id == $id_user_profiles;
  }
  
  public function getMenu() {
		return $this->getComponent('menu');
	}
  
  /** Funkcia pre zjednodusenie vypisu flash spravy a presmerovania
   * @param array|string $redirect Adresa presmerovania
   * @param string $text Text pre vypis hlasenia
   * @param string $druh - druh hlasenia
   */
  public function flashRedirect($redirect, $text = "", $druh = "info") {
		$this->flashMessage($text, $druh);
    if (is_array($redirect)) {
      if (count($redirect) > 1) {
        $this->redirect($redirect[0], $redirect[1]);
      } elseif (count($redirect) == 1) { $this->redirect($redirect[0]);}
    } else { $this->redirect($redirect); }
	}
  /**
   * Funkcia pre zjednodusenie vypisu flash spravy a presmerovania aj pre chybovy stav
   * @param boolean $ok Podmienka
   * @param array|string $redirect Adresa presmerovania
   * @param string $textOk Text pre vypis hlasenia ak je podmienka splnena
   * @param string $textEr Text pre vypis hlasenia ak NIE je podmienka splnena
   */
  public function flashOut($ok, $redirect, $textOk = "", $textEr = "") {
    if ($ok) {
      $this->flashRedirect($redirect, $textOk, "success");
    } else {
      $this->flashMessage($textEr, 'danger');
    }
  }
  
  /** Funkcia pre zjednodusenie testu, ci je dotaz do DB v poriadku a 
	 *  pre zjednoduseny vypis flash spravy a presmerovania
	 * @param \Nette\Database\Table\Selection|\Nette\Database\Table\ActiveRow|FALSE $db - dotaz z DB na test
   * @param array|string|NULL $redirect - adresa presmerovania
   * @param string $text - Text pre vypis hlasenia
   * @param string $druh - druh hlasenia
   */
	public function testDB($db, $redirect = NULL, $text = "", $druh = "info") {
		if ($db === FALSE OR count($db) == 0) {
      if ($redirect !== NULL) { $this->flashRedirect($redirect, $text, $druh);}
      else { return FALSE;}
    } else { return $db;}
	}
	
	/** Funkcia vymaze subor ak exzistuje
	 * @param string $subor Nazov suboru aj srelativnou cestou
	 * @return int Ak zmaze alebo neexistuje(nie je co mazat) tak 1 inak 0
	 */
	public function vymazSubor($subor) {
		return (is_file($subor)) ? unlink($this->context->parameters["wwwDir"]."/".$subor) : -1;
	}
	
  /**
   * Vytvorenie spolocnych helperov pre sablony
   * @param type $class
   * @return type
   */
  protected function createTemplate($class = NULL) {
    $servise = $this;
    $template = parent::createTemplate($class);
    $template->addFilter('hlmenuclass', function ($id, $id_registracia, $hl_udaje) {
    	$polozka_class = $id_registracia>2 ? 'adminPol' : '';
      //TODO $classPol .= ' zvyrazni';
      if ($id == $hl_udaje) { $polozka_class .= ' active'; }
      return $polozka_class;
    });
    $template->addFilter('nahodne', function ($max) { //Generuje nahodne cislo do template v rozsahu od 0 do max
      return (int)rand(0, $max);
    });
    $template->addFilter('uprav_email', function ($email) { //Upravi email aby sa nedal pouzit ako nema

      return Strings::replace($email, array(
          '~@~' => '[@]',
          '~\.~' => '[dot]',
      ));
    });
    $template->addFilter('textreg', function ($text, $id_registracia, $max_id_reg) {
      for ($i = $max_id_reg; $i>=0; $i--) {
        $z_zac = "#REG".$i."#"; //Pociatocna znacka
        $z_alt = "#REG-A".$i."#"; //Alternativna znacka
        $z_kon = "#/REG".$i."#";//Koncova znacka
        if (($p_zac = strpos($text, $z_zac)) !== FALSE && ($p_kon = strpos($text, $z_kon)) !== FALSE && $p_zac < $p_kon) { //Ak som našiel začiatok a koniec a sú v správnom poradí
          $text = substr($text, 0, $p_zac) //Po zaciatocnu zancku
                  .(($p_alt = strpos($text, $z_alt)) === FALSE ? // Je alternativa
                   ($i < $id_registracia ? substr($text, $p_zac+strlen($z_zac), $p_kon-$p_zac-strlen($z_zac)) : '') : // Bez alternativy
                   ($i < $id_registracia ? substr($text, $p_zac+strlen($z_zac), $p_alt-$p_zac-strlen($z_zac)) : substr($text, $p_alt+strlen($z_alt), $p_kon-$p_alt-strlen($z_alt))))// S alternativou
                  .substr($text, $p_kon+strlen($z_kon)); //Od koncovej znacky
			  } 
      }
      return $text;
    });
    $template->addFilter('vytvor_odkaz', function ($row) use($servise){
      return isset($row->absolutna) ? $row->absolutna :
                          (isset($row->spec_nazov) ? $servise->link($row->druh->presenter.':default',$row->spec_nazov)
                                                   : $servise->link($row->druh->presenter.':default'));
    });
    $template->addFilter('menu_mutacia_nazov', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(array('id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id));
      return $pom !== FALSE ? $pom->nazov : $id;
    });
    $template->addFilter('menu_mutacia_title', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(array('id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id));
      return $pom !== FALSE ? ((isset($pom->description) && strlen ($pom->description)) ? $pom->description : $pom->nazov) : $id;
    });
    $template->addFilter('menu_mutacia_h1part2', function ($id) use($servise){
      $pom = $servise->hlavne_menu_lang->findOneBy(array('id_hlavne_menu'=>$id, 'id_lang'=>$servise->language_id));
      return $pom !== FALSE ? $pom->h1part2 : $id;
    });
    return $template;
	}
}