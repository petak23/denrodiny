<?php
namespace App\FrontModule\Components\Clanky;

use Nette;
use DbTable;

/**
 * Komponenta pre zobrazenie príloh clanku pre FRONT modul
 * Posledna zmena(last change): 17.03.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.1
 *
 */
class PrilohyClanokControl extends Nette\Application\UI\Control {

  /** @var DbTable\Dokumenty */
  private $prilohy;
  /** @var array - texty pre sablonu v inom jazyku */
  private $texty = [
    "not_found"   =>"Položka sa žiaľ nenašla! msg: %s",
    "h3" 					=>"Prílohy",
    "nie_je"      =>"Zatiaľ nie je žiadna príloha.", 
  ];
  /** @var int */
  private $id_article;
  /** @var string */
  private $avatar_path;

  /**
   * @param DbTable\Dokumenty $dokumenty */
  public function __construct(DbTable\Dokumenty $dokumenty) {
    parent::__construct();
    $this->prilohy = $dokumenty;
  }
	
	/** Nastavenie textov pre sablonu v inom jazyku
   * @param array $text texty 
   * @return \App\FrontModule\Components\Clanky\PrilohyClanokControl */
  public function setTexts($text) {
    $this->texty = array_merge ($this->texty, $text);
    return $this;
  }
  
  /** Nastavenie id polozky, ku ktorej patria prilohy
   * @param int $id
   * @return \App\FrontModule\Components\Clanky\PrilohyClanokControl  */
  public function setNastav($id_article, $avatar_path) {
    $this->id_article = $id_article;
    $this->avatar_path = $avatar_path;
    return $this;
  }
  
  
  /** Render funkcia pre vypisanie odkazu na clanok 
   * @see Nette\Application\Control#render()
   */
  public function render() { 
    $this->template->setFile(__DIR__ . "/PrilohyClanok.latte");
    $this->template->prilohy = $this->prilohy->getViditelnePrilohy($this->id_article);
    $this->template->texty = $this->texty;
    $this->template->avatar_path = $this->avatar_path;
    $this->template->render();
  }
	
	protected function createTemplate($class = NULL) {
    $servise = $this;
    $template = parent::createTemplate($class);
    $template->addFilter('odkazdo', function ($id) use($servise){
      $serv = $servise->presenter->link("Dokumenty:default", array("id"=>$id));
      return $serv;
    });
    
    return $template;
	}
}

interface IPrilohyClanokControl {
  /** @return PrilohyClanokControl */
  function create();
}