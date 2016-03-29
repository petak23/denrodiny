<?php

namespace DbTable;
use Nette\Utils\Arrays;
/**
 * Model, ktory sa stara o tabulku clanok
 * (c) Ing. Peter VOJTECH ml.
 * Posledna zmena 07.01.2014
 */
class Clanok extends Table
{
	/** @var string */
	protected $tableName = 'clanok';

	/**
	 * Hlada zoznam clankov patriacich k polozke hlavneho menu a min. urovne registracie uzivatela
	 * Ak je posledny parameter TRUE tak hlada len aktualne clanky (podla datumu)
	 * @param int $id_hlavne_menu
	 * @param int $id_reg
	 * @param boolean $aktualne
	 * @param boolean $zobrazenie Ak TRUE zobrazi vsetky clanky aj tie co maju zobrazenie = 0
	 * @return \Nette\Database\Table\Selection
	 */
	public function hladaj_zoznam($id_hlavne_menu = 0, $id_reg = 5, $aktualne = TRUE, $zobrazenie = FALSE)
	{
		if (!$id_hlavne_menu) return FALSE; //id nie je nastavene
		$hladaj = array(
				"id_hlavne_menu =".$id_hlavne_menu,
				"podclanok < 1",
				"id_registracia <= ".$id_reg,
				"zobrazenie > ".($zobrazenie ? "-1" : "0"),
		);
		if ($aktualne) {
			return $this->findBy($hladaj)->where("datum_platnosti IS NULL OR datum_platnosti >= ?", strftime("%Y-%m-%d", time()));
		} else {
			return $this->findBy($hladaj);
		}
	}

	/**
		* Hlada zoznam podclankov patriacich k clanku(id) a min. urovne registracie uzivatela
		* @param int $id
		* @param int $id_reg
		* @return \Nette\Database\Table\Selection
		*/
	public function hladaj_zoznam_pod($id = 0, $id_reg = 5)
	{
		if (!$id) return FALSE; //Spec nazov nie je nastaveny
		return $this->findBy(array("id_hlavne_menu =".$id, "podclanok > 0", "id_registracia <= ".$id_reg));
	}
	
	/* ------------------------- */
	
	/** Funkcia pre ziskanie info o konkretnom clanku na zaklade spec_nazov, language_id 
	  * a min. urovne registracie uzivatela
		* @param string $spec_nazov - specificky nazov clanku v hl. menu
		* @param int $language_id - id jazykovej mutacie clanku. Ak = 0 tak vsetky
		* @param int $id_registracie - min. uroven registracie uzivatela. Ak nemam tak sa berie 5
		* @return array|FALSE
		*/
	public function getOneClanokSp($spec_nazov, $language_id, $id_registracia = 5) 
  {
    //Otestuj vstupy
    if (!isset($spec_nazov) || !strlen($spec_nazov)) return array("error"=>"Nie je zadaný spec_nazov!");
    
		//Najdi v tabulke hlavne_menu polozku podla spec. nazvu a urovne registracie - id clanku
    $pom = $this->connection->table('hlavne_menu')
                ->where(isset($id_registracia) ? array("spec_nazov"=>$spec_nazov, "id_registracia <= ".$id_registracia)
                                               : array("spec_nazov"=>$spec_nazov))->limit(1)->fetch();
	  if ($pom === FALSE || count($pom) == 0) return array("error"=>"Nenájdená položka v hlavnom menu!"); //Polozka sa nenasla
			
    //Pokracuj v hladani a vysledok vrat
    return $this->_hladajClanok($pom, $language_id);
  }
  
  public function getOneClanokId($id, $language_id, $id_registracia = 5) 
  {
    //Otestuj vstupy
    if (!isset($id) || $id == 0 ) return array("error"=>"Nie je zadané id!");
   
    //Najdi v tabulke hlavne_menu polozku podla spec. nazvu a urovne registracie - id clanku
    $pom = $this->connection->table('hlavne_menu')
                ->where(isset($id_registracia) ? array("id"=>$id, "id_registracia <= ".$id_registracia)
                                               : array("id"=>$id))->limit(1)->fetch();
	  if ($pom === FALSE || count($pom) == 0) return array("error"=>"Nenájdená položka v hlavnom menu!"); //Polozka sa nenasla

    //Pokracuj v hladani a vysledok vrat
    return $this->_hladajClanok($pom, $language_id);  
  }
  
  protected function _hladajClanok($hlm, $language_id)
  {
    //Otestuj vstupy
    if (!isset($language_id)) return array("error"=>"Nie je zadané language_id!");
    
    //Prirad tabulky, ktore nemam
    $hlavne_menu_lang = $this->connection->table('hlavne_menu_lang'); 
    $clanok_lang = $this->connection->table('clanok_lang');
    
    //Najdi v tabulke clanok polozku podla id clanku
    $najdi_clanok = $this->getTable()->get($hlm->clanok);
    if ($najdi_clanok === FALSE || count($najdi_clanok) == 0) 
      return array("error"=>"Nenájdená položka v tabulke clanok! - ".$hlm->clanok);
        
    //Najdi v tabulke hlavne_menu_lang polozku podla id_lang, id_hlavne_menu - nazov, h1part2, description
    $texty_hl_menu = $hlavne_menu_lang->where("id_hlavne_menu", $hlm->id);
    if ($texty_hl_menu === FALSE || count($texty_hl_menu) == 0) 
      return array("error"=>"Nenájdená položka v tabulke hlavne_menu_lang! - ".$hlm->id);
    
    //Najdi v tabulke clanok_lang polozku podla id
    $texty_clanku = $clanok_lang->where("id_clanok", $hlm->clanok);
    if ($texty_clanku === FALSE || count($texty_clanku) == 0) 
      return array("error"=>"Nenájdená položka v tabulke clanok_lang! - ".$hlm->clanok);
    
    //Ak mam vsetko poslam to na vystup
    $out = array(
      "hlavne_menu"   => $hlm,
      "clanok"        => $najdi_clanok,
      "lang"          => $language_id,
    );
    if ($language_id) { //Jeden jazyk
      $texty_hl_menu = $texty_hl_menu->where("id_lang", $language_id)->limit(1)->fetch();
      $texty_clanku = $texty_clanku->where("id_lang", $language_id)->limit(1)->fetch();
      $out["texty"][$language_id] = array(
            "nazov"       => $texty_hl_menu->nazov,
            "h1part2"    	=> $texty_hl_menu->h1part2,
            "description"	=> $texty_hl_menu->description,
            "text"        => $texty_clanku->text,
            "anotacia"    => $texty_clanku->anotacia,
      );
    } else { //Viac jazykov
      $pomh = array();$pomc = array();
      $ja = $this->connection->table('lang');
      foreach ($ja as $j)
        $prototip[$j->id] = array(
          "nazov"       => "",
          "h1part2"    	=> "",
          "description"	=> "",
          "text"        => "",
          "anotacia"    => "",
          "jazyk_skr"   => $j->skratka
        );
      foreach ($texty_hl_menu as $hl_txt) {
        $pomh[$hl_txt->id_lang] = array(
            "nazov"       => $hl_txt->nazov,
            "h1part2"    	=> $hl_txt->h1part2,
            "description"	=> $hl_txt->description,
            "jazyk_skr"   => $hl_txt->lang->skratka,
        );
      }
      foreach ($texty_clanku as $cl_txt) {
        $pomc[$cl_txt->id_lang] = array(
            "text"      => $cl_txt->text,
            "anotacia"  => $cl_txt->anotacia,
            "jazyk_skr" => $cl_txt->lang->skratka,
        );
      }
      $out["texty"] = Arrays::mergeTree($pomh, $pomc);
      $out["texty"] = Arrays::mergeTree($out["texty"], $prototip);
//      dump($out["texty"]);
//      die();   
    }
    return $out;
  }
}