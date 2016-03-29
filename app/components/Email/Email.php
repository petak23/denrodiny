<?php
namespace PeterVojtech\Email;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Application\UI;
use Latte;

/**
 * Komponenta pre zjedndusenie odoslania emailu
 * Posledna zmena(last change): 18.12.2015
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.2
 */

class EmailControl extends UI\Control {
  
  /** @var Nette\Mail\Message */
  private $mail;
  /** @var string */
  private $email_list;
  /** @var string */
  private $template;
  /** @var string */
  private $from;

  /** @param string $template Kompletná cesta k súboru template */
  public function __construct($template, $user_profiles, $from, $id_registracia) {
    parent::__construct();
    $this->mail = new Message;
    $this->template = $template;
    $uf = $user_profiles->find($from);
    $this->from = $uf->users->email;
    $this->email_list = $user_profiles->emailUsersListStr($id_registracia);
    foreach (explode(",", $this->email_list) as $c) {
      $this->mail->addTo(trim($c));
    }
  }
  
  /** Funkcia pre odoslanie emailu
   * @param array $params Parametre správy
   * @param string $subjekt Subjekt emailu
   * @return string Zoznam komu bol odoslany email
   * @throws SendException
   */
  public function send($params, $subjekt) {
    $templ = new Latte\Engine;
    $this->mail->setFrom($params["site_name"].' <'.  $this->from.'>');
    $this->mail->setSubject($subjekt)
         ->setHtmlBody($templ->renderToString($this->template, $params));
    try {
      $sendmail = new SendmailMailer;
      $sendmail->send($this->mail);
      return $this->email_list;
    } catch (Exception $e) {
      throw new SendException('Došlo k chybe pri odosielaní e-mailu. Skúste neskôr znovu...'.$e->getMessage());
    }
  }
}