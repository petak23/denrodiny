<?php
namespace App\FrontModule\Components\User;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Latte;

/**
 * Komponenta pre vytvorenie kontaktneho formulara a odoslanie e-mailu
 * 
 * Posledna zmena(last change): 14.09.2015
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */

class Kontakt extends Control {

  /** @var string */
  private $emails = "";
  /** @var int */
  private $textA_rows = 8;
  /** @var int */
  private $textA_cols = 60;
	/** @var array */
	private $text_form = array(
   'h4'       => 'Kontaktný formulár',
   'uvod'     => '',
	 'meno'     => 'Vaše meno:',
	 'email'    => 'Váš e-mail(aby sme mohli odpovedať...):',
	 'email_ar'	=> 'Prosím zadajte e-mail v správnom tvare. Napr. jano@hruska.com',
	 'email_sr'	=> 'Váš e-mail musí byť zadaný!',
   'text'     => 'Váš dotaz:',
   'text_sr'	=> 'Text dotazu musí byť zadaný!',
   'uloz'     => 'Pošli dotaz',
   'send_ok'  => 'Váš dotaz bol zaslaný v poriadku. Ďakujeme za zaslanie dotazu.',
   'send_er'  => 'Váš dotaz nebol zaslaný!. Došlo k chybe. Prosím skúste to neskôr znovu.<br />' 
	);
  /** @var string */
  private $nazov_stranky = "";

  /** Funkcia pre nastavenie textov formulara a sablony.
   *  Nastavitelne polia su: 'h4', 'uvod', 'meno', 'email', 'email_ar', 'email_sr', 'text', 'text_sr', 'uloz',
   *  'send_ok', 'send_er'
   * @param array $texty - pole textov
   * @param int $rows - pocet riadkov textarea
   * @param int $cols - pocet stlpcov textarea
   */
  public function setSablona($texty = array(), $rows = NULL, $cols = NULL)
  {
    if (is_array($texty) && count($texty)) { $this->text_form = array_merge($this->text_form, $texty);}
    if (isset($rows)) $this->textA_rows = $rows;
    if (isset($cols)) $this->textA_cols = $cols;
  }

  /** Funkcia pre nastavenie emailovych adries, na ktore sa odosle formular
	 * @param  string $emails - pole s emailovymi adresami
	 */
  public function setSpravca($emails) {
    if (isset($emails)) {
      $this->emails = $emails;
    }
  }
  
  /** Funkcia pre nastavenie nazvu stranky
	 * @param  string $nazov_stranky
	 */
  public function setNazovStranky($nazov_stranky) {
    $this->nazov_stranky = $nazov_stranky;
  }

  /**
   * @see Nette\Application\Control#render()
   */
  public function render() {
    $this->template->setFile(__DIR__ . '/Kontakt.latte');
		$this->template->h4 = $this->text_form['h4'];
		$this->template->uvod = $this->text_form['uvod'];
    $this->template->render();
  }

  /** Potvrdenie ucasti form component factory.
   * @return Nette\Application\UI\Form
   */
  protected function createComponentKontaktForm() {
      $form = new Form;
      $form->addProtection();
      $form->addText('meno', $this->text_form['meno'], 30, 50);
      $form->addText('email', $this->text_form['email'], 30, 50)
         ->setType('email')
				 ->addRule(Form::EMAIL, $this->text_form['email_ar'])
				 ->setRequired($this->text_form['email_sr']);
      $form->addTextArea('text', $this->text_form['text'])
           ->setAttribute('rows', $this->textA_rows)
           ->setAttribute('cols', $this->textA_cols)
           ->setRequired($this->text_form['text_sr']);
      $renderer = $form->getRenderer();
      $renderer->wrappers['controls']['container'] = 'dl';
      $renderer->wrappers['pair']['container'] = NULL;
      $renderer->wrappers['label']['container'] = 'dt';
      $renderer->wrappers['control']['container'] = 'dd';
      $form->addSubmit('uloz', $this->text_form['uloz']);
      $form->onSuccess[] = $this->onZapisDotaz;
      return $form;
  }

  /** Spracovanie formulara
   * @param \Nette\Application\UI\Form $form
   */
  public function onZapisDotaz(Form $form) {
    $values = $form->getValues(); 				//Nacitanie hodnot formulara
    $templ = new Latte\Engine;
    $params = array(
      "nadpis"      => sprintf('Správa z kontaktného formulará stránky %s', $this->nazov_stranky),
      "dotaz_meno"  => sprintf('Užívateľ s menom %s poslal nasledujúcu správu:', $values->meno),
      "dotaz_text"  => $values->text,
    );
    $mail = new Message;
    $mail->setFrom($values->meno.' <'.$values->email.'>')
         ->addTo($this->emails)
         ->setSubject(sprintf('Správa z kontaktného formulará stránky %s', $this->nazov_stranky))
         ->setHtmlBody($templ->renderToString(__DIR__ . '/Kontakt_email-html.latte', $params));
    try {
      $sendmail = new SendmailMailer;
      $sendmail->send($mail);
      $this->presenter->flashMessage($this->text_form['send_ok'], 'success');
    } catch (Exception $e) {
      $this->presenter->flashMessage($this->text_form['send_er'].$e->getMessage(), 'danger,n');
    }
    $this->redirect('this');
  }
}
