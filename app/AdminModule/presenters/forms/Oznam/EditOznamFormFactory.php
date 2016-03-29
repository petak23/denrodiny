<?phpnamespace App\AdminModule\Presenters\Forms\Oznam;use Nette\Application\UI\Form;use DbTable;use Nette\Security\User;class EditOznamFormFactory {  /** @var DbTable\Oznam */	private $oznam;    /** @var array Urovne registracie */  public $urovneReg;    /** @var DbTable\Ikonka */  public $ikonka;  /**   *    * @param DbTable\Oznam $oznam   * @param DbTable\Registracia $registracia   * @param DbTable\Ikonka $ikonka   * @param User $user   */  public function __construct(DbTable\Oznam $oznam, DbTable\Registracia $registracia, DbTable\Ikonka $ikonka, User $user) {		$this->oznam = $oznam;    $this->ikonka = $ikonka;    $this->urovneReg = $registracia->hladaj_urovne(0, ($user->isLoggedIn()) ? $user->getIdentity()->id_registracia : 0)->fetchPairs('id', 'nazov');    	}    /**   * Formular pre editaciu oznamu   * @param int $oznam_ucast Povolenie potvrdenia ucasti   * @param boolean $send_e_mail_news Povolenie zasielania info e-mailov   * @param boolean $oznam_title_image_en Povolenie titulneho obrazka   * @param string $nazov_stranky Nazov stranky   * @return Form   */  public function create($oznam_ucast, $send_e_mail_news, $oznam_title_image_en, $nazov_stranky) {    $form = new Form();		$form->addProtection();    $form->addHidden("id");$form->addHidden("id_user_profiles");$form->addHidden("datum_zadania");		$form->addDatePicker('datum_platnosti', 'Dátum platnosti')				 ->addRule(Form::FILLED, 'Dátum platnosti musí byť zadaný!');		$form->addText('nazov', 'Nadpis:', 50, 80)				 ->addRule(Form::MIN_LENGTH, 'Nadpis musí mať spoň %d znakov!', 3)				 ->setRequired('Názov musí byť zadaný!');		$form->addSelect('id_registracia', 'Povolené prezeranie pre min. úroveň:', $this->urovneReg);		if ($oznam_ucast) {      $form->addCheckbox('potvrdenie', ' Potvrdenie účasti');    } else {      $form->addHidden('potvrdenie');    }    if ($send_e_mail_news) {      $form->addCheckbox('posli_news', ' Posielatie NEWS o tejto aktualite');    } else {      $form->addHidden("posli_news", FALSE);    }      if (!$oznam_title_image_en) { //$this->oznam_title_image_en      $ikonky = $this->ikonka->findAll()->fetchPairs('id', 'nazov');      $outDir = 'http://'.$nazov_stranky.'/www/ikonky/128/';      foreach ($ikonky as $key => $nazov) {        $iko[$key] = Html::el('img', ['class'=>'ikonkySmall'])->src($outDir.$nazov.'128.png');      }      $form->addRadiolist('id_ikonka', 'Ikonka pred článkom:', $iko)           ->setAttribute('class', 'ikons-set')           ->getSeparatorPrototype()->setName(NULL);    }		$form->addTextArea('text', 'Text:')				 ->setAttribute('cols', 60)->setAttribute('class', 'jquery_ckeditor');//    $form->onValidate[] = [$this, 'validateEditOznamForm'];    $form->addSubmit('uloz', 'Ulož')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editOznamFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')->setAttribute('class', 'btn btn-default')         ->setValidationScope(FALSE);		return $form;	}    /** Spracovanie vstupov z formulara   * @param Nette\Forms\Controls\SubmitButton $button Data formulara   */	public function editOznamFormSubmitted($button)	{    $values = $button->getForm()->getValues();    try {			$this->oznam->ulozOznam($values);		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}	}    /** Vlastná validácia   * @param Nette\Application\UI\Form $button   *///  public function validateEditOznamForm($button) {//    $values = $button->getForm()->getValues();//    if ($button->isSubmitted()->name == 'uloz') {//      $user = $this->users->find($values->id_users);//      // Over, ci dane username uz existuje.//      $uu = $this->users->findOneBy(['username'=>$values->username]);//      if ($uu && $user->id <> $uu->id) {//        $button->addError(sprintf('Zadané užívateľské meno %s už existuje! Zvolte prosím iné!', $values->username));//      }//      // Over, ci dany email uz existuje.//      $ue = $this->users->findOneBy(['email'=>$values->email]);//      if ($ue && $user->id <> $ue->id) {//        $button->addError(sprintf('Zadaný e-mail %s už existuje! Zvolte prosím iný!', $values->email));//      }//    }//  }}