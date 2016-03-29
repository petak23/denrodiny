<?phpnamespace App\AdminModule\Presenters;use DbTable;/** * Prezenter pre administraciu hlavnych udajov webu. * Posledna zmena(last change): 18.12.2015 * *	Modul: ADMIN * * @author Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version 1.0.6 */class UdajePresenter extends \App\AdminModule\Presenters\BasePresenter {  /**    * @inject   * @var DbTable\Druh */  public $druh;  /**   * @inject    * @var DbTable\Udaje_typ */	public $udaje_typ;    /** @var array */  protected $udaje_typ_form;  /** @var Forms\Udaje\AddTypeUdajeFormFactory @inject*/	public $addTypeUdajeForm;  /** @var Forms\Udaje\EditUdajeFormFactory @inject*/	public $editUdajeForm;  /** Vychodzie nastavenia */  protected function startup() {    parent::startup();    $this->template->h2 = 'Administrácia hlavných udajov webu';    $this->template->zdroj_na_zmazanie = 'údaj';    $this->udaje_typ_form = $this->udaje_typ->findAll()->fetchPairs('id', 'nazov');	}  /** Akcia pre pridanie udaju prvy krok */	public function actionAdd() {}    /** Akcia pre pridanie udaju druhy krok   * @param int $type   */  public function actionAdd2($type) {    $this["editUdajeForm"]->setDefaults(['id_udaje_typ'  => $type]);    $this->setView('Edit');  }  /** Akcia pre editaciu udaju    * @param int $id   */	public function actionEdit($id) {    if (($pol_udaje = $this->udaje->find($id)) === FALSE) {      $this->setView('notFound');    } else {      $this["editUdajeForm"]->setDefaults($pol_udaje);      $this["editUdajeForm"]->setDefaults([        'spravca'   => $pol_udaje->id_registracia == $this->ur_reg['admin'] ? 0 : 1,        'druh_null' => $pol_udaje->id_druh == NULL ? 1 : 0,      ]);    }	}  /** Grid pre tabulku Udaje   * @return \App\AdminModule\Components\Udaje\UdajeWebuGrid   */  public function createComponentUdajeTableGrid()	{		return new \App\AdminModule\Components\Udaje\UdajeWebuGrid($this->udaje);	}    protected function createComponentAddTypeUdajeForm() {    $form = $this->addTypeUdajeForm->create($this->udaje_typ_form);      $form['uloz']->onClick[] = function ($button) {      $values = $button->getForm()->getValues();      $this->redirect('Udaje:add2', $values->id_udaje_typ);		};    $form['cancel']->onClick[] = function () {			$this->redirect('Udaje:');		};		return $this->_vzhladForm($form);  }  /** Edit udaje form component factory.	 * @return Nette\Application\UI\Form	 */	protected function createComponentEditUdajeForm()	{    $form = $this->editUdajeForm->create($this->user->isInRole("admin"), $this->druh->findAll()->fetchPairs('id', 'popis'), $this->ur_reg);      $form['uloz']->onClick[] = function ($form) {      $this->flashOut(!count($form->errors), 'Udaje:', 'Údaj bol uložený!', 'Došlo k chybe a údaj sa neuložil. Skúste neskôr znovu...');		};    $form['cancel']->onClick[] = function () {			$this->redirect('Udaje:');		};		return $this->_vzhladForm($form);	}   /** Funkcia pre spracovanie signálu vymazavania	  * @param int $id - id polozky v hlavnom menu		* @param string $nazov - nazov polozky z hl. menu - na zrusenie?		* @param string $druh - blizsia specifikacia, kde je to potrebne		*/	function confirmedDelete($id, $nazov, $druh = "")	{    if ($this->udaje->zmaz($id) == 1) { $this->flashMessage('Položka bola úspešne vymazaná!', 'success'); }     else { $this->flashMessage('Došlo k chybe a položka nebola vymazaná!', 'danger'); }    if (!$this->isAjax()) { $this->redirect('Udaje:'); }  }}