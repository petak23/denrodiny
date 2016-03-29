<?php
namespace App\AdminModule\Components\User;

use Nette;
//use Nette\Application\UI\Form;
//use Nette\Mail\Message;
//use Nette\Mail\SendmailMailer;

/**
 * Komponenta pre vytvorenie kontaktneho formulara a odoslanie e-mailu adminovi
 * Component for contact form and e-mail send for admin
 * Posledna zmena(last change): 12.09.2015
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.sk
 * @version 1.0.1
 */

class Kontakt extends Nette\Application\UI\Control {

  /** @var array */
//  private $emails = array();
//  /** @var int */
//  private $textA_rows = 5;
//  /** @var int */
//  private $textA_cols = 30;
	/** @var array */
//	private $text_form = array(
//	 'meno'     => 'Vaše meno:',
//	 'email'    => 'Váš e-mail(aby sme mohli odpovedať...):',
//	 'email_ar'	=> 'Prosím zadajte e-mail v správnom tvare. Napr. jano@hruska.com',
//	 'email_sr'	=> 'Váš e-mail musí byť zadaný!',
//   'text'     => 'Váš dotaz:',
//   'text_sr'	=> 'Text dotazu musí byť zadaný!',
//   'uloz'     => 'Pošli dotaz',
//   'send_ok'  => 'Váš dotaz bol zaslaný v poriadku. Ďakujeme za zaslanie dotazu.',
//   'send_er'  => 'Váš dotaz nebol zaslaný!. Došlo k chybe. Prosím skúste to neskôr znovu.<br />' 
//	);
  /** @var string */
//  private $nazov_stranky = "";

  /** Funkcia pre nastavenie textov formulara a sablony.
   *  Nastavitelne polia su: 'h4', 'uvod', 'meno', 'email', 'email_ar', 'email_sr', 'text', 'text_sr', 'uloz',
   *  'send_ok', 'send_er'
   * @param array $texty - pole textov
   * @param int $rows - pocet riadkov textarea
   * @param int $cols - pocet stlpcov textarea
   */
//  public function setSablona(/*$texty = array(), */$rows = NULL, $cols = NULL) {
////    if (is_array($texty) && count($texty)) { $this->text_form = array_merge($this->text_form, $texty);}
//    if (isset($rows)) { $this->textA_rows = $rows; }
//    if (isset($cols)) { $this->textA_cols = $cols; }
//  }

  /** Funkcia pre nastavenie emailovych adries, na ktore sa odosle formular
	 * @param  string|array $emails - pole s emailovymi adresami
	 */
//  public function setSpravca($emails) {
//    if (isset($emails)) {
//      if (!is_array($emails)) {
//        $this->emails[] = $emails;
//      } else {
//        $this->emails = $emails;
//      }
//    }
//  }
  
  /** Funkcia pre nastavenie nazvu stranky
	 * @param  string $nazov_stranky
	 */
//  public function setNazovStranky($nazov_stranky) {
//    $this->nazov_stranky = $nazov_stranky;
//  }

  /**
   * @see Nette\Application\Control#render()
   */
  public function render() {
    $this->template->setFile(__DIR__ . '/Kontakt.latte');
    $this->template->render();
  }

  /** Potvrdenie ucasti form component factory.
   * @return Nette\Application\UI\Form
   */
//  protected function createComponentKontaktForm() {
//      $form = new Form;
//      $form->addProtection();
//      $form->addTextArea('text', 'Správa:')
//           ->setAttribute('rows', $this->textA_rows)
//           ->setAttribute('cols', $this->textA_cols)
//           ->setRequired('Text dotazu musí byť zadaný!');
////      $renderer = $form->getRenderer();
////      $renderer->wrappers['controls']['container'] = 'dl';
////      $renderer->wrappers['pair']['container'] = NULL;
////      $renderer->wrappers['label']['container'] = 'dt';
////      $renderer->wrappers['control']['container'] = 'dd';
//      $form->addSubmit('uloz', 'Pošli');
//      $form->onSuccess[] = $this->onZapisDotaz;
//      return $form;
//  }

  /** Spracovanie formulara
   * @param \Nette\Application\UI\Form $form
   */
//  public function onZapisDotaz(Form $form) {
//    $values = $form->getValues(); 				//Nacitanie hodnot formulara
//    $templ = new \Nette\Latte\Engine;
//    $params = array(
//      "nadpis"      => sprintf('Dopit z kontaktneho formulará stránky %s', $this->nazov_stranky),
//      "dotaz_meno"  => sprintf('Užívateľ s menom %s poslal nasledujúci dopit:', $values->meno),
//      "dotaz_text"  => $values->text,
//      "odkaz"       => 'http://'.$this->nazov_stranky.$this->link("this"),
//    );
//    $clen_to = $this->presenter->user_profiles->find(1);
//    $clen_from = $this->presenter->user_profiles->find(1);
//    $mail = new Message;
//    $mail->setFrom($clen_from->meno.' '.$clen_from->priezvisko.' <'.$clen_from->users->email.'>');
//    $mail->addTo($clen_to->users->email);
//    $mail->setSubject($nadpis)
//         ->setHtmlBody($templ->renderToString(__DIR__ . '/Kontakt_email-html.latte', $params));
//    try {
//      $sendmail = new SendmailMailer;
//      $sendmail->send($mail);
//      $this->flashMessage('Vaša pripomienka bola odoslaná!', 'dobre');
//    } catch (Exception $e) {
//      $this->flashMessage('Vaša pripomienka nebola odoslaná!. Došlo k chybe. Prosím, skúste to neskôr znovu.<br />'.$e->getMessage(), 'zle,n');
//    }
//    $this->redirect('this');
//  }
}