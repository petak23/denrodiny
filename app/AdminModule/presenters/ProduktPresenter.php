<?phpnamespace App\AdminModule\Presenters;use Nette\Application\UI\Form;use Nette\Utils\Strings;use Nette\Utils\Html;use Nette\Image;use DbTable;use ForUploads;/** * Prezenter pre spravu produktov. *  * Posledna zmena(last change): 22.02.2016 * * Modul: ADMIN * * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.4 */class ProduktPresenter extends \App\AdminModule\Presenters\BasePresenter {  /**    * @inject   * @var DbTable\Produkt_lang */	public $produkt_lang;	/**    * @inject   * @var DbTable\Produkt_prepoj */	public $produkt_prepoj;  /**    * @inject   * @var DbTable\Produkt_images */	public $produkt_images;  /**    * @inject   * @var DbTable\Clanok_komponenty */	public $clanok_komponenty;  /**   * @inject   * @var ForUploads\Images */  public $for_uploads;  // -- Formulare  /** @var Forms\Produkt\ProduktEditFormFactory @inject*/	public $formProdukt;  	/** @var \Nette\Database\Table\ActiveRow|FALSE Prednastavene hodnoty pre formular */	public $zobraz_produkt;  /** @var \Nette\Database\Table\Selection|FALSE Produkty k danej polozke hlavneho menu */	public $produkt_images_view;  /** @var \Nette\Database\Table\Selection|FALSE Kategórie v ktorých je produkt */	public $kategorie_produktu;   /** @var \Nette\Database\Table\Selection|FALSE Kategórie produktov */	public $kategorie;  /** @var int  */  public $id_hlavne_menu;  /** @var string Cesta k adresaru pre ukladanie suborov produktov */  private $produkt_path = '';  /** @var string Pre presmerovanie po editacii */  private $back_edit_link;  /** StartUp */  protected function startup() {		parent::startup();	  $this->produkt_path = $this->context->parameters['wwwDir']."/www/".$this->context->parameters['produkt_path'];    $this->template->produkt_path = "/www/".$this->context->parameters['produkt_path'];  }    /** Zakladna akcia - zobrazenie produktu.   * @param int $id Id produktu   * @param int $id_hlavne_menu Id hlavneho menu   */  public function actionDefault($id, $id_hlavne_menu) {    $this->kategorie_produktu = array_values($this->produkt_prepoj->findBy(array("id_produkt_lang"=>$id))->fetchPairs("id", "id_hlavne_menu_lang"));     $this->zobraz_produkt = $this->produkt_prepoj->findOneBy(array("id_hlavne_menu_lang"=>$id_hlavne_menu, "id_produkt_lang"=>$id));        $this->produkt_images_view = $this->produkt_images->findBy(array("id_produkt_lang"=>$id));        $this->id_hlavne_menu = (int)$id_hlavne_menu;     $kategorie = $this->clanok_komponenty->findBy(array("id_komponenty"=>2))->fetchPairs("id","id_hlavne_menu"); //Najdi všetky clanky kde je komponenta produktZoznam = 2(id)    $this->kategorie = $this->hlavne_menu_lang->findBy(array("id"=>array_values($kategorie)))->fetchPairs("id","nazov");  } 	  /** Render pre zobrazenie produktu. */  public function renderDefault() {    $this->template->p = isset($this->zobraz_produkt) ? $this->zobraz_produkt->produkt_lang : FALSE;        $this->template->pi = $this->produkt_images_view;        $this->template->back_link_id = $this->id_hlavne_menu;    $this->template->kategorie = $this->kategorie;  }  	/** Akcia pre pridanie produktu.   * @param type $id_hlavne_menu Id polozky menu ku ktorej bude patrit   */  public function actionAdd($id_hlavne_menu) {    $this->id_hlavne_menu = isset($id_hlavne_menu) ? (int)$id_hlavne_menu : 0;		$this["produktEditForm"]->setDefaults(array("id_lang" => 1));    $this->setView("edit");	}  /** Akcia pre editaciu produktu.   * @param int $id Id produktu   * @param int $id_hlavne_menu Id hlavneho menu   * @param string $back_edit_link Nazov presentera pre presmerovanie po editacii   */  public function actionEdit($id, $id_hlavne_menu, $back_edit_link) {    if (isset($id) && $id) {      $produkt = $this->produkt_lang->find($id)->toArray();      $produkt["heating_time"] = ($produkt["heating_time"]->h<10 ? "0" : "").$produkt["heating_time"]->h.":".($produkt["heating_time"]->i<10 ? "0" : "").$produkt["heating_time"]->i;      $this["produktEditForm"]->setDefaults($produkt);      $this->id_hlavne_menu = isset($id_hlavne_menu) ? (int)$id_hlavne_menu : 0;      if (isset($back_edit_link)) { $this->back_edit_link = $back_edit_link;}    }  }  	/** Formular pre editaciu hodnot produktu.	 * @return Nette\Application\UI\Form	 */	public function createComponentProduktEditForm() {    $outDir = 'http://'.$this->nazov_stranky.'/www/images/icon_';    for ($i = 1; $i <= 3; $i++) {      $iko[$i] = Html::el('img', array('class'=>'ikonkySmall'))->src($outDir.$i.'_s.jpg');    }    $form = $this->formProdukt->create($iko, $this->upload_size);		$form->onSuccess[] = $this->produktEditFormSubmitted;		return $form;	}  /** Spracovanie formulara pre editaciu hodnot produktu.   * @param Nette\Application\UI\Form $form Hodnoty formulara   */	public function produktEditFormSubmitted($form) {		$values = $form->getValues();             //Nacitanie hodnot formulara    $id_pol = (int)$values->id;// Ak je == 0 tak sa pridava    unset($values->id);    if ($id_pol == 0) { //Ak sa pridava       if ($this->produkt_lang->findOneBy(array("nazov"=>$values->nazov)) !== FALSE) { //kontroluj existenciu názvu				$this->flashMessage('Zadali ste už existujúci názov produktu! Prosím, zvolte iný!', 'danger');				return;			} else {        $values->spec_nazov = $this->_najdiSpecNazov($values->nazov, 'produkt_lang'); //tak nastav specificky nazov      }		}    //Uprav vystupy pre ulozenie    //1. pre int a tiny int    foreach (array("bottom_plinth_weight", "forewood_lenght", "firewood_lenght", "efficiency") as $k) {      if (isset($values->$k) && (int)$values->$k == 0) { $values->$k = NULL;}    }    //2. pre time    if (isset($values->heating_time) && $values->heating_time == "00:00") {$values->heating_time = NULL;}    //3. pre float    foreach (array("heating_occasion", "nominal_heat_output", "nominal_heat_time", "heat_release_time100", "heat_release_time50", "heat_release_time25") as $k) {      if (isset($values->$k)) {        $values->$k = (float)$values->$k == 0 ? NULL : str_replace(",",".",$values->$k);      }    }    if ($values->title_image && $values->title_image->name != "") {      if ($values->title_image->isImage()) {        $pi = pathinfo($values->title_image->getSanitizedName());        $image_name = $this->produkt_path."/thumbs/".$values->spec_nazov.".".$pi['extension'];        $values->title_image->move($image_name);        $image = Image::fromFile($image_name);        $image->resize(90, 70, Image::SHRINK_ONLY | Image::EXACT);        $image->save($image_name, 80);      } else {        $this->flashMessage('Pre titulný obrázok nebol použitý obrázok a tak nebol uložený!', 'danger');      }      unset($values->title_image);    } else { 			unset($values->title_image);		}    if (isset($values->pec_pdf)) {      if ($values->pec_pdf->name === NULL) {         unset($values->pec_pdf);      } else {        $pdf_name = Strings::webalize($values->nazov, '_').".pdf";        $values->pec_pdf->move($this->produkt_path."/pdf/".$pdf_name);        $values->pec_pdf = $pdf_name;      }    }    $uloz = $this->produkt_lang->uloz($values, $id_pol);    if (isset($uloz['id'])) { //Ulozenie v poriadku      $this->flashMessage('Produkt bol úspešne uložený!', 'success');      if ($id_pol == 0) { //Ak pridavam musim pridat aj prepojenie        $this->produkt_prepoj->uloz(array("id_hlavne_menu_lang"=>$this->id_hlavne_menu, "id_produkt_lang"=>$uloz['id']));      }      if (isset($this->back_edit_link)) {        $this->redirect($this->back_edit_link.":default", $uloz['id'], $this->id_hlavne_menu);      } else {        $this->redirect('Clanky:default', $this->id_hlavne_menu);      }    }	}    /** Formular pre pridanie obrazku k produktu.	 * @return Nette\Application\UI\Form	 */  public function createComponentAddImageForm() {		$form = new Form();		$form->addProtection();    $form->addHidden('id_produkt_lang', $this->zobraz_produkt->id_produkt_lang);		$form->addUpload('subor', 'Pridaj obrázok')         ->setOption('description', sprintf('Max veľkosť obrázku v bytoch %s kB', $this->upload_size/1024))         ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť obrázka v bytoch %d B', $this->upload_size)         ->addRule(Form::IMAGE, 'Obrázok musí byť JPEG, PNG alebo GIF.');		$form->addSubmit('uloz', 'Pridaj');		$form->onSuccess[] = $this->addImageFormSubmitted;		$form->getRenderer()->wrappers['pair']['.odd'] = 'r1';		return $form;	}   /** Spracovanie formulara pre pridanie obrazku k produktu.   * @param Nette\Application\UI\Form $form Hodnoty formulara   */  public function addImageFormSubmitted($form)	{		$values = $form->getValues(); 			//Nacitanie hodnot formulara    $produkt_dir = $this->produkt_path."/images/";    if ($values->subor && $values->subor->name != "" && $values->subor->isImage()) {      $image_name = $this->for_uploads->getImageNameIndex($produkt_dir,                                                           $values->subor->getSanitizedName(),                                                           $this->zobraz_produkt->produkt_lang->spec_nazov."_");      $values->subor->move($produkt_dir.$image_name);			$values->subor = $image_name;      //Uloženie zmien      $this->produkt_images->uloz($values);      $this->flashMessage('Položka bola uložená!', 'success');    } else {      $this->flashMessage('Pre položku nebol použitý obrázok alebo nebol vybratý žiaden súbor a tak nebol uložený!', 'danger');    }    $this->redirect('this');  }    /** Formular pre editaciu kategorie produktu.	 * @return Nette\Application\UI\Form	 */  public function createComponentKategorieEditForm() {		$form = new Form();		$form->addProtection();    $form->addHidden('id_produkt_lang', $this->zobraz_produkt->id_produkt_lang);    $form->addCheckboxList('kategorie', NULL, $this->kategorie);    $form->setDefaults(array('kategorie'=>$this->kategorie_produktu));		$form->addSubmit('uloz', 'Ulož');		$form->onSuccess[] = $this->kategorieEditFormSubmitted;		return $form;	}   /** Spracovanie formulara pre editaciu kategorie produktu.   * @param Nette\Application\UI\Form $form Hodnoty formulara   */  public function kategorieEditFormSubmitted($form)	{		$values = $form->getValues(); 			//Nacitanie hodnot formulara    $kategoria_pridana = array_diff($values->kategorie, $this->kategorie_produktu);    $kategoria_odobrata = array_diff($this->kategorie_produktu, $values->kategorie);    //Uloženie zmien    if (count($kategoria_pridana)) {      foreach ($kategoria_pridana as $k => $v) {        $this->produkt_prepoj->uloz(array("id_hlavne_menu_lang"=>$v, "id_produkt_lang"=>$values->id_produkt_lang));      }    }    if (count($kategoria_odobrata)) {      foreach ($kategoria_odobrata as $k => $v) {        if ($v != $this->id_hlavne_menu) {          $this->produkt_prepoj->findAll()->where(array("id_hlavne_menu_lang"=>$v, "id_produkt_lang"=>$values->id_produkt_lang))->delete();        } else {          $this->flashMessage('Nie je možné zrušiť prvok v aktuálnej kategórii!', 'danger');        }      }    }    $this->flashRedirect('this', 'Položka bola uložená!', 'success');  }    /** Doplnenie filtru pre sablonu. */  protected function createTemplate($class = NULL) {    $template = parent::createTemplate($class);    $template->addFilter('ciarka', function ($s){      return str_replace(".",",",$s);    });    return $template;	}    /** Funkcia pre spracovanie signálu vymazavania.	 * @param int $id - id produktu   * @param int $back_link_id Id clanku, na ktory sa ma presmerovat po zmazani	 */	function confirmedDelete($id = 0, $back_link_id = 0)	{    if (!$id) { $this->error("Id položky nie je nastavené!"); }    if (!$back_link_id) { $this->error("Nie je nastavené id článku!"); }    $produkt_images = $this->produkt_images->findBy(array("id_produkt_lang"=>$id));    foreach ($produkt_images as $p) {      if (is_file("www/".$this->context->parameters['produkt_path']."/images/".$p->subor)) {        unlink($this->produkt_path."/images/".$p->subor);       }    }    if ($produkt_images->delete() && $this->produkt_prepoj->findBy(array("id_produkt_lang"=>$id))->delete()) {      $pr = $this->produkt_lang->find($id);      if (isset($pr->pec_pdf)) {        $ppo = $this->produkt_lang->findBy(array("pec_pdf"=>$pr->pec_pdf));        if (count($ppo) == 1 && is_file("www/".$this->context->parameters['produkt_path']."/pdf/".$pr->pec_pdf)) {          unlink($this->produkt_path."/pdf/".$pr->pec_pdf);        }      }      if (is_file("www/".$this->context->parameters['produkt_path']."/thumbs/".$pr->spec_nazov.".jpg")) {        unlink($this->produkt_path."/thumbs/".$pr->spec_nazov.".jpg");      }      $out = $pr->delete();    } else { $out = FALSE; }    $this->_ifMessage($out, "Produkt bol zmazaný!", "Došlo k chybe a produkt sa nezmazal!");    $this->redirect("Clanky:default", $back_link_id);    }}