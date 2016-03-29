<?phpnamespace App\AdminModule\Components\Article\TitleImage;use Nette\Application\UI\Form;use Nette\Utils\Strings;use Nette\Utils\Image;use DbTable;/** * Formular a jeho spracovanie pre pridanie a editaciu titulneho obrazku polozky. * Posledna zmena 04.03.2016 *  * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com> * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml. * @license * @link       http://petak23.echo-msz.eu * @version    1.0.1a */class EditTitleImageFormFactory {  /** @var DbTable\Hlavne_menu */	private $hlavne_menu;  /** @var string */  private $avatar_path;  /** @var string */  private $www_dir;  /**   * @param DbTable\Hlavne_menu $hlavne_menu */  public function __construct(DbTable\Hlavne_menu $hlavne_menu) {		$this->hlavne_menu = $hlavne_menu;	}    /**   * Formular pre pridanie a editaciu titulneho obrazku polozky.   * @return Nette\Application\UI\Form */    public function create($avatar_path, $www_dir)  {    $this->avatar_path = $avatar_path;    $this->www_dir = $www_dir;    $form = new Form();		$form->addProtection();    $form->addHidden("id");    $form->addHidden("old_avatar");		$form->addUpload('avatar', 'Titulný obrázok')         ->setOption('description', sprintf('Max veľkosť obrázka v bytoch %d kB', 300 * 1024/1000 /* v bytoch */))         ->addRule(Form::MAX_FILE_SIZE, 'Max veľkosť obrázka v bytoch %d B', 300 * 1024 /* v bytoch */)           ->addRule(Form::IMAGE, 'Titulný obrázok musí byť JPEG, PNG alebo GIF.');    $form->addSubmit('uloz', 'Zmeň')         ->setAttribute('class', 'btn btn-success')         ->onClick[] = [$this, 'editTitleImageFormSubmitted'];    $form->addSubmit('cancel', 'Cancel')         ->setAttribute('class', 'btn btn-default')         ->setAttribute('data-dismiss', 'modal')         ->setAttribute('aria-label', 'Close')         ->setValidationScope(FALSE);		return $form;	}    /**    * Spracovanie formulara pre zmenu vlastnika clanku.   * @param Nette\Forms\Controls\SubmitButton $button Data formulara    * @throws Database\DriverException   */  public function editTitleImageFormSubmitted($button) {		$values = $button->getForm()->getValues(); 	//Nacitanie hodnot formulara    try {      if (!$values->avatar->error) {        if ($values->avatar->isImage()){           $values->avatar = $this->_uploadTitleImage($values->avatar);          if (is_file("www/".$this->avatar_path.$values->old_avatar)) { unlink($this->www_dir."/www/".$this->avatar_path.$values->old_avatar);}          $this->hlavne_menu->uloz(["avatar"=>$values->avatar], $values->id);        } else {          throw new Database\DriverException('Pre titulný obrázok nebol použitý obrázok a tak nebol uložený!'.$e->getMessage());        }      } else {         throw new Database\DriverException('Pri pokuse o uloženie došlo k chybe! Pravdepodobná príčina je:'.$this->presenter->upload_error[$values->avatar->error].$e->getMessage());      }		} catch (Database\DriverException $e) {			$button->addError($e->getMessage());		}  }    /**   * @param \Nette\Http\FileUpload $avatar   * @param boolean $random   * @return string */  private function _uploadTitleImage(\Nette\Http\FileUpload $avatar) {    $path = $this->www_dir."/www/".$this->avatar_path;    $pi = pathinfo($avatar->getSanitizedName());    $ext = $pi['extension'];    $avatar_name = Strings::random(15).".".$ext;    $avatar->move($path.$avatar_name);    $image = Image::fromFile($path.$avatar_name);    $image->save($path.$avatar_name, 75);    return $avatar_name;  }}