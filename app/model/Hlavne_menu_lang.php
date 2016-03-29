<?php

namespace DbTable;
use Nette;

/**
 * Model starajuci sa o tabulku hlavne_menu_lang
 * Posledna zmena 08.06.2015
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class Hlavne_menu_lang extends Table {
  /** @var string */
  protected $tableName = 'hlavne_menu_lang';
    
  /** Funkcia pre ziskanie info o konkretnom clanku na zaklade spec_nazov, language_id 
	  * a min. urovne registracie uzivatela
		* @param string $spec_nazov - specificky nazov clanku v hl. menu
		* @param int $language_id - id jazykovej mutacie clanku. Ak nemam tak 1 - sk
		* @param int $id_registracia - min. uroven registracie uzivatela. Ak nemam tak sa berie 5
		* @return array|FALSE
		*/
	public function getOneArticleSp($spec_nazov, $language_id = 1, $id_registracia = 5) {
    $articles = clone $this;
		//Najdi v tabulke hlavne_menu polozku podla spec. nazvu a urovne registracie
    return $articles->getTable()->where("hlavne_menu.spec_nazov", $spec_nazov)
                                ->where("id_lang", $language_id)
                                ->where("hlavne_menu.id_registracia <= ?", $id_registracia)
                                ->fetch();
  }
  
  /** Funkcia pre ziskanie info o konkretnom clanku na zaklade id, language_id 
	  * a min. urovne registracie uzivatela
		* @param int $id - id polozky v hl. menu
		* @param int $language_id - id jazykovej mutacie clanku. Ak nemam tak 1 - sk
		* @param int $id_registracia - min. uroven registracie uzivatela. Ak nemam tak sa berie 5 - admin
		* @return Nette\Database\Table\ActiveRow|array
		*/
  public function getOneArticleId($id, $language_id = 1, $id_registracia = 5) {
    $articles = clone $this;
    //Najdi v tabulke hlavne_menu polozku podla id a urovne registracie
    return $articles->getTable()->where("id_hlavne_menu", $id)
                                ->where("id_lang", $language_id)
                                ->where("hlavne_menu.id_registracia <= ?", $id_registracia)
                                ->fetch();
  }
  
  public function ulozTextClanku($values, $action, $id_hlavne_menu) {
    $uloz_txt = [];
    foreach ($values as $k => $v) {
      $a = explode("_", $k, 2);
      $uloz_txt[$a[0]][$a[1]] = $v;
    }
    $ulozenie = 1;
		if (($utc = count($uloz_txt))) {
			foreach($uloz_txt as $ke => $ut){
        $cid = ($action == "edit2") ? (isset($ut["id"]) ? $ut["id"] : 0) : 0;
				$uloz_t = $this->ulozClanokLang($ut, $cid);
        if ($uloz_t !== FALSE && $uloz_t['id']) { //Ulozenie v poriadku
          $this->_prepojHlavneMenuLeng($cid, $id_hlavne_menu, $uloz_t['id'], $ut["id_lang"]);
          $ulozenie++;
        }
			}
			if ($ulozenie != $utc+1) { //Nieco sa neulozilo v poriadku
				//TODO!!! Zmazanie toho co sa uz ulozilo
				$ulozenie = 0; 
			}
    } else { $ulozenie = ($this->action == "add2") ? 0 : 1;} //Ak pri pridani nemam texty je to chyba!
    return $ulozenie;
  }
  
  /** Funkcia pridava alebo aktualizuje v DB tabulke 'clanok_lang' podla toho, ci je zadané ID
   * @param array $data
   * @param int $id
   * @return \Nette\Database\Table\ActiveRow|FALSE
   */
  public function ulozClanokLang($data, $id = 0) {
    $clanok_lang = $this->connection->table('clanok_lang');
    return $id ? ($clanok_lang->where(array('id'=>$id))->update($data) !== FALSE ? $clanok_lang->get($id) : FALSE): $clanok_lang->insert($data);
  }
  /** Ak pridavam tak vytvorim zavislost na hlavne_menu_lang
   * @param int $cid
   * @param int $id_hlavne_menu
   * @param int $id_clanok_lang
   * @param int $id_lang
   */
  public function _prepojHlavneMenuLeng($cid, $id_hlavne_menu, $id_clanok_lang, $id_lang) {
    if ($cid == 0) { //
      $pol = $this->findOneBy(["id_hlavne_menu"=>$id_hlavne_menu, "id_lang"=>$id_lang]);
      $this->uloz(["id_clanok_lang"=>$id_clanok_lang], $pol->id);
    } 
  }
}
