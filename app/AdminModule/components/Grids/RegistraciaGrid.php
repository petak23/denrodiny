<?php
namespace PeterVojtech\Grids;

use \NiftyGrid\Grid;

class RegistraciaGrid extends Grid {
	protected $articles;

	public function __construct($articles) {
		parent::__construct();
		$this->articles = $articles;
	}

	protected function configure($presenter) {
		$source = new \NiftyGrid\NDataSource($this->articles->findAll()->select('registracia.id AS id, role, nazov'));

		$this->setDataSource($source);
    $this->addColumn('id', 'ID', '10%');
    $this->addColumn('role', 'Role', '30%');
    $this->addColumn('nazov', 'Názov', '40%');
    $this->addButton("edit", "Editovať")
			->setClass("edit")
			->setLink(function($row) use ($presenter){
          return $presenter->link("Registracia:edit", $row['id']);
        })
			->setAjax(FALSE);
    //Pridava a maze len admin
    if ($presenter->user->isInRole('admin')) {
      $this->addButton("delete", "Zmazať")
        ->setClass("vymaz")
        ->setLink(function($row) use ($presenter){
            return $presenter->link("nonajaxForm:confirmDelete!", array("id"=>$row['id'],"nazov"=>$row['nazov'],"zdroj_na_zmazanie"=>$presenter->zdroj_na_zmazanie));  
          });
      $this->setNewLink($presenter->link("Registracia:add"));
    }
    $this->paginate = FALSE;
    $this->enableSorting = FALSE;
  }
}