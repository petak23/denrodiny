<?php
namespace App\AdminModule\Components\Udaje;

use NiftyGrid\Grid;

class UdajeWebuGrid extends Grid {
	protected $articles;

	public function __construct($articles) {
		parent::__construct();
		$this->articles = $articles;
	}

	protected function configure($presenter) {
		$source = new \NiftyGrid\NDataSource($this->articles->findBy(array('id_registracia<='.$presenter->id_reg))
              ->select('udaje.id AS id, udaje.nazov AS nazovU, text, id_udaje_typ, udaje.comment AS commentU, druh.presenter AS druhP, registracia.role AS registraciaR, udaje_typ.nazov AS inputT')
              ->order('id ASC'));

		$this->setDataSource($source);
    $this->addColumn('commentU', 'Komentár', '30%');
    $this->addColumn('nazovU', 'Názov', '15%');
    $this->addColumn('text', 'Hodnota', '30%')
         ->setRenderer(function($row)/* use ($presenter)*/{
           if ($row["id_udaje_typ"] == 3) {
             return (int)$row["text"] == 1 ? "Povolene" : "Zakázané";
           } else {
             return $row["text"];
           }
         })
         ->setCellRenderer(function($row){
           return $row["id_udaje_typ"] == 3 ? ("background-color:".((int)$row["text"] == 1 ? "rgba(35, 113, 35, .4)" : "rgba(217, 83, 79,.4)")) : NULL;
         });
         
		//Pridava a maze len admin
    if ($presenter->user->isInRole('admin')) {
			$this->addColumn('registraciaR', 'Prístup', '5%');
			$this->addColumn('druhP', 'Druh', '5%');
			$this->addColumn('inputT', 'Typ', '5%');
		}
    $this->addButton("edit", "Editovať")
			->setClass("edit")
			->setLink(function($row) use ($presenter){
          return $presenter->link("Udaje:edit", $row['id']);
        })
			->setAjax(FALSE);
    //Pridava a maze len admin
    if ($presenter->user->isInRole('admin')) {
      $this->addButton("delete", "Zmazať")
        ->setClass("vymaz")
        ->setLink(function($row) use ($presenter){
            return $presenter->link("confirmForm:confirmDelete!", array("id"=>$row['id'], "nazov"=>$row['nazovU'], "druh"=>"udaj", "zdroj_na_zmazanie"=>$presenter->zdroj_na_zmazanie));  
          })
        ->setAjax(FALSE);
    }
    $this->paginate = FALSE;
    $this->enableSorting = FALSE;
  }
}