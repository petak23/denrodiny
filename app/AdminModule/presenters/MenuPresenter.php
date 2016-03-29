<?php
namespace App\AdminModule\Presenters;

/**
 * Prezenter pre administraciu hlavneho menu.
 * Posledna zmena(last change): 01.03.2016
 *
 * Modul: ADMIN
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.7
 */
class MenuPresenter extends \App\AdminModule\Presenters\ArticlePresenter {
  
  /** Render pre defaultnu akciu */
  public function renderDefault() {
    parent::renderDefault();
		$this->template->uroven = $this->zobraz_clanok->hlavne_menu->uroven+2;
    $this->template->obsahuje = $this->hlavne_menu->findBy(array("id_nadradenej"=>$this->zobraz_clanok->hlavne_menu->id))->count();
  }
  
  /** Akcia pre 1. krok pridania polozky - udaje pre hl. menu.
   * @param int $id - id nadradenej polozky
   * @param int $uroven - uroven menu
   */
  public function actionAdd($id, $uroven) {
		$this->menuformuloz = ["text"=>"Ulož","redirect"=>"Menu:default"];
    parent::actionAdd($id, $uroven);
	}
	
  /** Akcia pre 1. krok editovania clanku - udaje pre hl. menu.
   * @param int $id - id editovanej polozky
   */
  public function actionEdit($id) {
    $this->menuformuloz = ["text"=>"Ulož","redirect"=>"Menu:default", "edit"=>TRUE];
    parent::actionEdit($id);
	}
}