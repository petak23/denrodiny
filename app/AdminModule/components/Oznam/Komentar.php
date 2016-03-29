<?php
namespace App\AdminModule\Components\Oznam;

use Nette;
use DbTable;

/**
 * Komponenta pre zobrazenie a vymazanie komentarov k oznamom
 * Posledna zmena(last change): 01.02.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.0
 */

class KomentarControl extends Nette\Application\UI\Control {
  
  /** @var DbTable\Oznam_komentar */
  public $oznam_komentar;
  /** @var int Id oznamu*/
  private $id_oznam;
  /** @var int */
  private $id_user_profiles;
  /** @var boolean */
  private $allowed_delete_komentare = FALSE;

  /**
   * @param DbTable\Oznam_komentar $oznam_komentar
   * @param Nette\Security\User $user
   */
  public function __construct(DbTable\Oznam_komentar $oznam_komentar, Nette\Security\User $user) {
    parent::__construct();
    $this->oznam_komentar = $oznam_komentar; 
    $this->id_user_profiles = $user->getId();
  }
  
  /** Nastavenie parametrov
	 * @param  int $id_oznam Id polozky, ku ktorej sa vztahuje komentar
   * @return \App\FrontModule\Components\Oznam\KomentarControl
   */
  public function setParametre($id_oznam, $allowed_delete_komentare) {
    $this->id_oznam = $id_oznam;
    $this->allowed_delete_komentare = $allowed_delete_komentare;
    return $this;
  }

  /** 
   * @see Nette\Application\Control#render()
   */
  public function render() {
    $this->template->setFile(__DIR__ . '/Komentar.latte');
    $this->template->komentare = $this->oznam_komentar->najdiKomentar($this->id_oznam);
    $this->template->id_oznam = $this->id_oznam;
    $this->template->allowed_delete_komentare = $this->allowed_delete_komentare;
    $this->template->render();
  }
  
  /** Signál pre vymazanie komentarov */
  public function handleVymazKomentare() {
    if ($this->allowed_delete_komentare) {
      $this->oznam_komentar->vymazKomentar($this->id_oznam);
    }
    if (!$this->presenter->isAjax()) {
      $this->redirect('this');
    } else {
      $this->redrawControl('koment');
    }
  }
  
}

interface IKomentarControl {
  /** @return KomentarControl */
  function create();
}