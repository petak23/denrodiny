<?phpnamespace App\AdminModule\Presenters\Forms\Produkt;use Nette\Application\UI\Form;class ProduktEditFormFactory {  /**   * Edit hlavne menu form component factory.   * @return Nette\Application\UI\Form   */    public function create($iko, $upload_size)  {		$form = new Form();		$form->addProtection();    $form->addHidden("id"); $form->addHidden("id_lang"); $form->addHidden("spec_nazov");    $form->addUpload('title_image', 'Titulný obrázok:')         ->setOption('description', 'Odporúčaný rozmer obrázku je: 90x70px alebo násobky tejto veľkosti. Inak môže dôjsť k deformácii alebo orezaniu obrázku pri ukladaní!' )				 ->addCondition(Form::FILLED)          ->addRule(Form::IMAGE, 'Titulný obrázok musí byť JPEG, PNG alebo GIF.')          ->addRule(Form::MAX_FILE_SIZE, 'Maximálna veľkosť súboru je 64 kB.', 64 * 1024 /* v bytoch */);    $form->addText('nazov', 'Názov produktu:', 50, 50)         ->setRequired('Názov musí byť zadaný!');    $form->addRadioList('tepkat', 'Tepelná kategória:', $iko)         ->getSeparatorPrototype()->setName(NULL);		$form->addText('vyska', 'Výška[mm]:', 5, 5)				 ->addRule(Form::RANGE, 'Výška musí byť v rozsahu od %d do %d mm!', array(0, 4000))				 ->setRequired('Výška musí byť zadaná!');    $form->addText('sirka', 'Šírka[mm]:', 5, 5)				 ->addRule(Form::RANGE, 'Šírka musí byť v rozsahu od %d do %d mm!', array(0, 2000))				 ->setRequired('Šírka musí byť zadaná!');    $form->addText('hlbka', 'Hĺbka[mm]:', 5, 5)				 ->addRule(Form::RANGE, 'Hĺbka musí byť v rozsahu od %d do %d mm!', array(0, 2000))				 ->setRequired('Hĺbka musí byť zadaná!');    $form->addText('hmotnost', 'Hmotnosť[kg]:', 5, 5)				 ->addRule(Form::RANGE, 'Hmotnosť musí byť v rozsahu od %d do %d kg!', array(0, 3500))				 ->setRequired('Hmotnosť musí byť zadaná!');    $form->addText('bottom_plinth_weight', 'Hmotnosť spodného podstavca[kg]:', 5, 5)         ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Hmotnosť spodného podstavca musí byť v rozsahu od %d do %d kg!', array(0, 2000));    $form->addCheckbox('copatible_aurum', ' Kompatibilné s Aurum Pellet Unit');    $form->addText('termal_energy', 'Tepelná energia[kWh]:', 5, 5)				 ->addRule(Form::RANGE, 'Tepelná energia musí byť v rozsahu od %d do %d kWh!', array(0, 200))				 ->setRequired('Tepelná energia musí byť zadaná!');    $form->addText('heating_time', 'Čas spaľovania[h:min]:', 5, 5)         ->addCondition(Form::FILLED)          ->addRule(Form::PATTERN, 'Čas spaľovania musí byť zapísaný v tvare napr.:02:30', '([0-9]\s*){2}:([0-9]\s*){2}');    $form->addText('heating_occasion', 'Max. množstvo dreva[kg]:', 5, 5)         ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Max. množstvo dreva musí byť v rozsahu od %d do %d kg!', array(0, 100));    $form->addText('forewood_lenght', 'Dĺžka dreva do rúry na pečenie[cm]:', 5, 5)         ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Dĺžka dreva do rúry na pečenie musí byť v rozsahu od %d do %d cm!', array(0, 100));    $form->addText('firewood_lenght', 'Dĺžka dreva[cm]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Dĺžka dreva musí byť v rozsahu od %d do %d cm!', array(0, 100));    $form->addText('nominal_heat_output', 'Nominálny tepelný výkon[kW]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Nominálny tepelný výkon musí byť v rozsahu od %d do %d kW!', array(0, 15));    $form->addText('nominal_heat_time', 'Nominálny čas[h]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Nominálny čas musí byť v rozsahu od %d do %d h!', array(0, 50));    $form->addText('heat_release_time100', 'Tepelná akumulačná kapacita, 100 percent maximálneho výkonu[h]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Tepelná akumulačná kapacita musí byť v rozsahu od %d do %d h!', array(0, 50));    $form->addText('heat_release_time50', 'Tepelná akumulačná kapacita, 50 percent maximálneho výkonu[h]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Tepelná akumulačná kapacita musí byť v rozsahu od %d do %d h!', array(0, 100));    $form->addText('heat_release_time25', 'Tepelná akumulačná kapacita, 25 percent maximálneho výkonu[h]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Tepelná akumulačná kapacita musí byť v rozsahu od %d do %d h!', array(0, 100));    $form->addText('efficiency', 'Účinnosť[%]:', 5, 5)				 ->addCondition(Form::FILLED)          ->addRule(Form::RANGE, 'Účinnosť musí byť v rozsahu od %d do %d %!', array(0, 100));    $form->addUpload('pec_pdf', 'Katalógový list vo formáte pdf:')         ->setOption('description', sprintf('Max veľkosť prílohy v bytoch %s kB', $upload_size/1024))         ->addCondition(Form::FILLED)          ->addRule(Form::MIME_TYPE, 'Katalógový list musí byť vo formáte pdf!', 'application/pdf')          ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť prílohy može byť v bytoch %d B', $upload_size);    $form->addSubmit('uloz', 'Ulož produkt');    $form->getRenderer()->wrappers['pair']['.odd'] = 'r1';		return $form;	}}