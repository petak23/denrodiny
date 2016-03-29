<?php
namespace App\MapaModule\Components\Menu;
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
          "nadpis" => FALSE,
          "divClass" => FALSE,
          "avatar" => "",
          "anotacia" => FALSE,
          "text_title_image" =>"Titulný obrázok",
      );

	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->templatePath = array(
      'urllist' => dirname(__FILE__) . '/Urllist.latte',// sablona pro Urllist.txt
			'sitemap' => dirname(__FILE__) . '/Sitemap.latte',// sablona pro Urllist.txt
		);
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

	public function render($part, $templateType) {
		$template = $this->template;
		$template->path = $this->path;
		$template->selected = $this->selected;
		$level = (int)$part;
		$template->startNode = ($level == 0) ? $this->rootNode : 
                                           (isset($this->path[$level - 1]) ? $this->path[$level - 1] : null);
//    if ($templateType == 'urllist' OR $templateType == 'sitemap') { 
			$template->showAll = true;
//		}  
      
    $template->startLevel = $level;
		$template->templateType = $templateType;
    $template->spRootNode = $this->rootNode;
		$template->setFile($this->templatePath[$templateType]);
		$template->render();
	}

  public function renderUrllist($level = 0) {	$this->template->nastav = $this->nastav; $this->render($level, 'urllist');	}
	public function renderSitemap($level = 0) {	$this->template->nastav = $this->nastav; $this->render($level, 'sitemap');	}

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
	var $link;	//Odkaz na polozku
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
}