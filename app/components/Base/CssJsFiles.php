<?php
namespace PeterVojtech\Base;
use Nette;

/**
 * Komponenta pre vlozenie css a js suborov do stranky
 * Posledna zmena(last change): 03.01.2016
 * 
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> 
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.0
 */

class CssJsFilesControl extends Nette\Application\UI\Control {
  
  /** @var array */
  private $files;
  /** @var string Nazov modulu*/
  private $module;
  /** @var string Nazov:Presenter_akcia */
  private $pa;

  /**
   * @param array $files
   */
  public function __construct($files, $name, $action) {
    parent::__construct();
    $this->files = $files;
    $modul_presenter = explode(":", $name);
    $this->module = $modul_presenter[0]; //Modul
    $this->pa = $modul_presenter[1]."_".$action;//Presenter_akcia
  }

  /** Render funkcia pre vypisanie odkazu na clanok 
  * @see Nette\Application\Control#render()
  */
  public function render($t) {
    $this->template->setFile(__DIR__ . '/'.$t.'.latte');
    $this->template->render();
  }
  
  public function renderCss() {
    $css = array_merge($this->files['default']['css'], 
                      isset($this->files[$this->module][$this->pa]['css']) ? 
                            array_merge($this->files[$this->module]['css'], $this->files[$this->module][$this->pa]['css']) :
                            $this->files[$this->module]['css']
                     );
    $this->template->files = $css;
//    dump($css);die();
    $this->render('Css');
  }
  
  public function renderJs() {
    $js = array_merge($this->files['default']['js'], 
                      isset($this->files[$this->module][$this->pa]['js']) ? 
                            array_merge($this->files[$this->module]['js'], $this->files[$this->module][$this->pa]['js']) : 
                            $this->files[$this->module]['js']
                     );
    $this->template->files = $js;
    $this->render('Js');
  }
}