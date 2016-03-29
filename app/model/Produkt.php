<?php

namespace DbTable;
use Nette;

/**
 * Model, ktory sa stara o tabulku produkt
 * (c) Ing. Peter VOJTECH ml.
 * Posledna zmena 03.02.2014
 */
class Produkt extends Table
{
  /** @var string */
  protected $tableName = 'produkt'; //Pravdepodobne uz len ako artefakt
	/** @var Nette\Database\Table\Selection|FALSE */
	protected $produkt;
	/** @var Nette\Database\Table\Selection|FALSE */
	protected $hlavne_menu_lang;
	
	public function __construct(Nette\Database\Connection $db)
  {
    parent::__construct($db);
		$this->produkt = $this->connection->table("produkt");
		$this->hlavne_menu_lang = $this->connection->table("hlavne_menu_lang");
	}
  
  /**
   * 
   * @param int $id
   * @param int $id_lang
   * @return array|FALSE
   */
	public function getProdukt($id, $id_lang = 1)
	{
		$hlavne_menu_lang = $this->hlavne_menu_lang->where(array("id_hlavne_menu"=>$id, "id_lang"=>$id_lang));
		if ($hlavne_menu_lang === FALSE) return FALSE;
		
		$produkt = $this->produkt->get($hlavne_menu_lang->hlavne_menu->clanok);
		if ($produkt === FALSE) return FALSE;
		
		return array("hlavne_menu_lang"=>$hlavne_menu_lang, "produkt"=>$produkt);
	}
}