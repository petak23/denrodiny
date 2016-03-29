<?php
namespace App\FrontModule\Components\Oznam;

use Nette;
use DbTable;
/**
 * Komponenta pre zobrazenie potvrdenia ucasti pre oznam pre FRONT modul
 * Posledna zmena(last change): 26.01.2016
 *
 * @author Ing. Peter VOJTECH ml <petak23@gmail.com>
 * @copyright Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link http://petak23.echo-msz.eu
 * @version 1.0.2a
 */

class PotvrdUcastControl extends Nette\Application\UI\Control {
  /** @var DbTable\Oznam_ucast */
	public $oznam_ucast;
  /** @var DbTable\Oznam_volba */
	public $oznam_volba;
  /** @var int */
  private $id_oznam = 0;
  /** @var int */
  private $id_user_profiles;
  /** @var array */
  private $texty_pu = ["h4"=>"Potvrdenie účasti", 
                       "ucast_uz_potvrdili"=>"Účasť už potvrdili:",
                       "este_nikto"=>"Ešte nikto..."];

  public function __construct(DbTable\Oznam_ucast $oznam_ucast, DbTable\Oznam_volba $oznam_volba, Nette\Security\User $user) {
    parent::__construct();
    $this->oznam_ucast = $oznam_ucast;
    $this->oznam_volba = $oznam_volba;
    $this->id_user_profiles = $user->getId();
  }
  /** Nastavenie parametrov
   * @param int $id_oznam Id oznamu, ku ktoremu je potvrdenie
   * @param array $texty_pu Texty pre viacjazycnu podporu
   */
  public function setParametre($id_oznam = 0, $texty_pu = []) {
    $this->id_oznam = $id_oznam;
    if (is_array($texty_pu)) {
      $this->texty_pu = array_merge($this->texty_pu, $texty_pu);
    }
  }
  
  /** Signal pre potvrdenie ucasti
   * @param int $id_volba Id volby ucasti
   */
  public function handlePotvrd($id_volba) {
    $this->oznam_ucast->zapisUcast($this->id_user_profiles, $this->id_oznam, $id_volba);
    if (!$this->presenter->isAjax()) {
      $this->presenter->redirect('this');
    } else {
      $this->redrawControl('potvrdenieUcasti');
    }
  }
  
  /** Render */
  public function render() {
    $this->template->volby = $this->oznam_volba->volby();
    $this->template->ucast = $this->oznam_ucast->mojaUcast($this->id_oznam, $this->id_user_profiles);
    $this->template->ucastnici = $this->oznam_ucast->findBy(["id_oznam"=>$this->id_oznam]);
    $this->template->texty_pu = $this->texty_pu;
    
    $this->template->setFile(__DIR__ . '/Potvrd_ucast.latte');
    $this->template->render();
  }
}

interface IPotvrdUcastControl {
  /** @return PotvrdUcastControl */
  function create();
}
