<?php

namespace Language_support;


use Nette;

/**
 * Hlavna trieda pre podporu jazykov lang_supp_main pre presentre vo FrontModule.
 * Posledna zmena(last change): 04.02.2016
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.6
 */

abstract class lang_supp_main extends Nette\Object {
  /** @var string Skratka jazyka */
  protected $jazyk = 'sk';
  /** @var array Samotne texty podla jazykov */
  protected $texty;
  /** @var array Konkretny jazyk pre vystup */
  protected $out_texty;
  /** @var array Vychodzie texty pre BasePresenter */
  private $texty_base = array(
      'sk'=>array( //Slovencina
        'base_lang_not_def'           => 'Jazyk, ktorý požadujete, nie je pre tento web definovaný!<br />Language you require is not defined for this site!',
        'base_nie_je_opravnenie'      => 'Na požadovanú akciu %s nemáte dostatočné oprávnenie!',
        'base_nie_je_opravnenie1'     => 'Nemáte dostatočné oprávnenie na danú operáciu!',
        'base_loged_in_bad'           => 'Daná akcia je určená pre neprihláseného užívateľa!',  
        'base_internal_error'         => 'Je nám ľúto, ale došlo k vnútornej chybe! Prosím skúste úpredošlú operáciu neskôr znovu...',
        'base_log_out_mess'           => 'Boli ste odhlásený...',
        'base_delete_text'            => 'Naozaj chceš zmazať %s: %s ?',
        'base_SignInForm_username'    => 'Meno alebo e-mail',
        'base_SignInForm_username_req'=> 'Uveďte, prosím, užívateľské meno alebo email.',
        'base_SignInForm_password'    => 'Heslo',
        'base_SignInForm_password_req'=> 'Uveďte, prosím, heslo.',
        'base_SignInForm_remember'    => 'Pamätať si ma',
        'base_SignInForm_login'       => 'Prihlásiť sa...',
        'base_AdminLink_name'         => "Administrácia",
        'base_login_ok'               =>   'Úspešne ste sa prihlásili!',
        'base_login_error'            =>  'Pri prihlásení došlo k chybe: %s',
        'base_prihlaste_sa'           => 'Prosím, prihláste sa!',
        'base_save'                   => 'Ulož',
        'base_save_ok'                => 'Údaje boli uložené v poriadku!',
        'base_form_required'          => '<span class="form_required">Červeno</span> označené položky sú povinné!',
        "base_not_found"              => "Položka sa žiaľ nenašla! Možná príčina: %s",
        "base_platnost_do"            => "Platnosť do: ",
        "base_platil_do"              => "Platil do: ",
        "base_neplatny"               => "Článok už nie je platný!",
        "base_zadal"                  => "Zadal: ",
        "base_zobrazeny"              => "Zobrazený: ",  
        "base_anotacia"               => "Anotácia:",
				"base_aktualny_projekt"       => "Aktuálny projekt",
        "base_aktualne_clanky"        => "Aktuálne",
        "base_viac"                   => "viac",
        "base_title"                  => "Zobrazenie celého obsahu.",
        "base_text_title_image"       => "Titulný obrázok",
        "base_error_bad_link"         => "Pokúšate sa dostať na neexzistujúcu stránku!",
        "base_template_not_found"     => "Chyba zobrazenia! Nesprávny názov šablóny[%s]...",
        'komponent_kontakt_h4'        => 'Kontaktný formulár',
        'komponent_kontakt_uvod'      => '',
        'komponent_kontakt_meno'      => 'Vaše meno:',
        'komponent_kontakt_email'     => 'Váš e-mail(aby sme mohli odpovedať...):',
        'komponent_kontakt_email_ar'	=> 'Prosím, zadajte e-mail v správnom tvare. Napr. jano@hruska.com',
        'komponent_kontakt_email_sr'  => 'Váš e-mail musí byť zadaný!',
        'komponent_kontakt_text'      => 'Váš dotaz:',
        'komponent_kontakt_text_sr'   => 'Text dotazu musí byť zadaný!',
        'komponent_kontakt_uloz'      => 'Pošli dotaz',
        'komponent_kontakt_send_ok'   => 'Váš dotaz bol zaslaný v poriadku. Ďakujeme za zaslanie dotazu.',
        'komponent_kontakt_send_er'   => 'Váš dotaz nebol zaslaný!. Došlo k chybe. Prosím skúste to neskôr znovu.<br />',
        'komponent_nahodna_notFound'  => 'Žiadna fotka sa nenašla!',
        'komponent_nahodna_h4'        => 'niečo z galérie ...',
        'base_kontakt_h2'             => 'Kontakt',
        'base_aktualne_h2'            => 'AKTUÁLNE',
        'base_jedalny_listok'         => 'Pozrieť jedálny lístok',
        "base_to_foto_galery"         => "Odkaz na fotogalériu:",
        "base_to_article"             => "Odkaz na článok:",
      ),
      'en'=>array( //Anglictina
        'base_lang_not_def'  => 'Language you require is not defined for this site!',
        'base_nie_je_opravnenie1' => 'You do not have enough privileges for this operation!',
        'base_prihlaste_sa' => 'Please log in!',
        'base_SignInForm_username'      => 'Name or e-mail',
        'base_SignInForm_username_req'  => 'Please indicate username or email.',
        'base_SignInForm_password'      => 'Password',
        'base_SignInForm_password_req'  => 'Please indicate the password.',
        'base_SignInForm_remember'      => 'Remember Me',
        'base_SignInForm_login'         => 'Login ...',
        'base_AdminLink_name'           => "Administration",
        'base_save' => 'Save',
        "base_not_found"   =>"Article not found! msg: %s",
        "base_platnost_do" =>"Expiration date: ",
        "base_zadal"       =>"Entered: ",
        "base_zobrazeny"   =>"Displayed: ",  
        "base_anotacia"    =>"Annotation:",
        "base_viac"        =>"more",
        "base_title"       =>"Show more",
        "base_text_title_image" => "Title image",
        "base_template_not_found"  => "Display error! Incorect name of template[%s]...",
        'komponent_kontakt_h4'        => 'Contact form',
        'komponent_kontakt_uvod'      => '',
        'komponent_kontakt_meno'      => 'Your name:',
        'komponent_kontakt_email'     => 'Your e-mail',
        'komponent_kontakt_email_ar'	=> 'Please, write e-mail in correct form. For example: jano@hruska.com',
        'komponent_kontakt_email_sr'  => 'Your e-mail must be specified!',
        'komponent_kontakt_text'      => 'Your query:',
        'komponent_kontakt_text_sr'   => 'Text your query must be specified!',
        'komponent_kontakt_uloz'      => 'Send query',
        'komponent_kontakt_send_ok'   => 'Your query has been sent okay. Thank you for posting your query.',
        'komponent_kontakt_send_er'   => 'Your query has been sent!. An error has occurred. Please try again later.<br />',
        'komponent_nahodna_notFound'  => 'No photo could not be found!',
        'komponent_nahodna_h4'        => 'something from the gallery ...',
        'base_kontakt_h2'             => 'Contact',
        'base_aktualne_h2'            => 'NEWS',
      ),
    );

  /** Funkcia na pridanie textu do pola
   * @param string $key Nazov kluca
   * @param string $text Hodnota kluca
   */
  public function addTexty($key, $text) {
     if (isset($this->texty[$this->language][$key])) {
       $this->texty[$this->language][$key.'a'] = $text;
     } else {
       $this->texty[$this->language][$key] = $text;
     }
  }

  /** Nastavenie aktualneho jazyka
   * @param string $language Skratka jazyka
   */
  public function setLanguage($language) {
    $this->jazyk = $language;
    $this->out_texty = array_merge($this->texty_base[$this->jazyk], $this->texty[$this->jazyk]);
  }

  /**
   * Vratenie textu pre dany kluc a jazyk
   * @param string $key Kluc daneho textu
   * @return string 
   */
  public function trText($key) {
    return isset($this->out_texty[$key]) ? $this->out_texty[$key]
                                         : (isset($this->texty_base['sk'][$key]) ?
                                           $this->texty_base['sk'][$key] : $key);
  }

  /**
   * Vrati pole textov pre prislusny jazyk
   * @param string $language Skratka jazyka
   * @return array|FALSE
   */
  public function getLanguageArray($language = 'sk') {
    return (isset($this->texty_base[$language]) && isset($this->texty[$language])) ?
              array_merge($this->texty_base[$language], $this->texty[$language]) : FALSE;
  }
}
