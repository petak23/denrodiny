<?php

namespace DbTable;
use Nette;

/**
 * Model, ktory sa stara o tabulku produkt_lang
 * Posledna zmena 29.05.2015
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.0
 */
class Produkt_lang extends Table {
  /** @var string */
  protected $tableName = 'produkt_lang';
	/** @var Nette\Database\Table\Selection|FALSE */
	protected $hlavne_menu_lang;
	
	public function __construct(Nette\Database\Context $db) {
    parent::__construct($db);
		$this->hlavne_menu_lang = $this->connection->table("hlavne_menu_lang");
	}
  
  /** Nacitanie udajov o produkte
   * @param int $id Id produktu
   * @param int $id_lang Id jazyka
   * @return array|FALSE
   */
	public function getProdukt($id, $id_lang = 1)
	{
		$hlavne_menu_lang = $this->hlavne_menu_lang->where(array("id_hlavne_menu"=>$id, "id_lang"=>$id_lang));
    if ($hlavne_menu_lang === FALSE) { return FALSE;}
		
		$produkt = $this->find($hlavne_menu_lang->hlavne_menu->clanok);
    if ($produkt === FALSE) { return FALSE;}
		
		return array("hlavne_menu_lang"=>$hlavne_menu_lang, "produkt"=>$produkt);
	}
}