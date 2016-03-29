<?php
namespace App\AdminModule\Components\Oznam;

use Nette;
use DbTable;
/**
 * Komponenta pre zobrazenie konkretneho clanku
 * Posledna zmena(last change): 26.01.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2
 *
 */
class PotvrdUcastControl extends Nette\Application\UI\Control {
  /** @var \DbTable\Oznam_ucast */
  private $oznam_ucast;
  /** @var int */
  private $id_oznam = 0;
  /** @var boolean */
  private $allowed_delete_ucast = FALSE;

  public function __construct(DbTable\Oznam_ucast $ucast) {
    parent::__construct();
    $this->oznam_ucast = $ucast;
  }

  /** Nastavenie parametrov
   * @param int $id_oznam Id oznamu, ku ktoremu je potvrdenie
   * @param boolean $allowed_delete_ucast Povolenie mazania ucasti
   */
  public function setParametre($id_oznam = 0, $allowed_delete_ucast = FALSE) {
    $this->id_oznam = $id_oznam;
    $this->allowed_delete_ucast = $allowed_delete_ucast;
  }
  
  /** Signál pre vymazanie ucasti */
  public function handleVymazUcast() {
    if ($this->allowed_delete_ucast) {
      $this->oznam_ucast->vymazUcast($this->id_oznam);
    }
    if (!$this->presenter->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('ucast');
    }
  }

  public function render() {
    $this->template->ucastnici = $this->oznam_ucast->findBy(["id_oznam"=>$this->id_oznam]);
    $this->template->allowed_delete_ucast = $this->allowed_delete_ucast;
    $this->template->setFile(__DIR__ . '/Potvrd_ucast.latte');
    $this->template->render();
  }
}

interface IPotvrdUcastControl {
  /** @return PotvrdUcastControl */
  function create();
}