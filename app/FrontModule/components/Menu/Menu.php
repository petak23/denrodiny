<?php
namespace App\FrontModule\Components\Menu;
use Nette;

class Menu extends Nette\Application\UI\Control {
	var $rootNode; // = new MenuItem();
	var $separateMenuLevel;
	protected $_selected;
	var $allNodes = array();
	protected $_path = null;
	var $templatePath = array();
	public $idCounter = 0;
  protected $nastav = array(
          "level" => 0,
          "templateType" => "tree",
          "nadpis" => FALSE,
          "divClass" => FALSE,
          "avatar" => "",
          "anotacia" => FALSE,
          "text_title_image" =>"Titulný obrázok",
					"article_avatar_view_in" => 0,
          "separator" => "|",
      );

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->rootNode = new MenuNode();
		$this->rootNode->menu = $this;
		$this->rootNode->isRootNode = true;
	}

	public function setSelected($node) {
		if (is_scalar($node)) {
			if (!isset($this->allNodes[$node])) return;
			$node = $this->allNodes[$node];
		};
		$this->_path = null;
		$this->_selected = $node;
	}

	public function getSelected() {
		return $this->_selected;
	}
  
  public function setTextTitleImage($text){
    $this->nastav["text_title_image"] = $text;
  }

	public function getPath() {
    if (!$this->_path) { $this->_path = $this->makePath(); }
		return $this->_path;
	}

	function makePath() {
		$node = $this->selected;
		$path = array();
		while ($node && ($node != $this->rootNode)) {
			$path[] = $node;
			$node = $node->parent;
		};
		$path = array_reverse($path);
		return $path;
	}

	public function render($opt) {
    $nastav = array_merge($this->nastav, $opt);
    $level = (int)$nastav["level"];
    $tT = $nastav["templateType"];
		$template = $this->template;
    $template->nastav = $nastav;
		$template->path = $this->path;
		$template->selected = $this->selected;
		$template->startNode = ($level == 0) ? $this->rootNode : (isset($this->path[$level - 1]) ? $this->path[$level - 1] : null);
    if ($tT == 'tree') { $template->showAll = false; }
    elseif ($tT == 'map') {
      $nastav["templateType"] = 'tree';
      $template->showAll = true;
    } elseif ($tT == 'fixed') {
      $template->showAll = true;
      if (isset($nastav["cast"])) {
        $p = array();
        foreach ($this->rootNode->nodes as $v) { $p[] = $v->id; }
        $o = array_search(-1*$nastav["cast"], $p);
        $template->startNode = $this->rootNode->nodes[$o]; 
      } else {
        $template->startNode = $this->rootNode->nodes[0];
      }
    } elseif ($tT== 'mapa') {
      $template->startNode = $this->allNodes;
    }
    $template->startLevel = $level;
    $template->spRootNode = $this->rootNode;
		$template->setFile(dirname(__FILE__) . '/' . ucfirst(isset($nastav["templateFile"]) ? $nastav["templateFile"] : $nastav["templateType"]).'.latte');
		$template->render();
	}

	public function fromTable($data, $setupNode) {
		$usedIds = array(null);
		$newIds = array();
		$nodes = array();
		foreach($data as $row) {
			$node = new MenuNode;
			$parentId = $setupNode($node, $row);
			$nodes[$parentId][] = $node;
		}
		$this->linkNodes(null, $nodes);
	}

	protected function linkNodes($parentId, &$nodes) {
		if (isset($nodes[$parentId])) {
			foreach($nodes[$parentId] as $node) {
				if ($parentId) {
					$this->allNodes[$parentId]->add($node);
				} else {
					$this->rootNode->add($node);
				}
				$this->linkNodes($node->id, $nodes);
			}
		}
	}

	public function byId($id) {
		return $this->allNodes[$id];
	}

	public function selectByUrl($url) {
		foreach($this->allNodes as $node) {
			if ($url == $node->link) {
				$this->selected = $node;
			}
		}
	}
}

class MenuNode extends \Nette\Object {
	var $name;
	var $tooltip;
	var $avatar;
	var $anotacia;
  var $node_class;
  var $poradie_podclankov = 0;
	var $link;	//Odkaz na polozku
  var $absolutna; //Ak je absolutny odkaz
  var $novinka; //Či je položka novinkou
	var $nodes = array();
	var $parent;
	var $id;
	var $menu;
	var $isRootNode = false;

	public function Add($node) {
		if (is_array($node)) {
			$newNode = new MenuNode;
			foreach($node as $k => $v) {
				$newNode->{$k} = $v;
			}
			$node = $newNode;
		}
		$node->parent = $this;
		$node->menu = $this->menu;
		if (!$node->id) {
			$node->id = '__auto_id_'.$this->menu->idCounter++;
		}
		$this->nodes[] = $node;
		$this->menu->allNodes[$node->id] = $node;
		return $node;
	}

	public function getItemClass() {
    $out = "";
		if ($this == $this->menu->selected) {
			$out .= ' selected';
		} else if (in_array($this, $this->menu->path)) {
			$out .= ' in-path';
		}
    return $out;
	}
}