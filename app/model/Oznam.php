<?php
namespace DbTable;
use Nette;

/**
 * Model, ktory sa stara o tabulku oznam
 * Posledna zmena 28.01.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.3
 */
class Oznam extends Table {
  /** @var string */
  protected $tableName = 'oznam';
  
  /** Vypisanie vsetkych aktualnych oznamov
   * @param boolean $usporiadanie Urcuje usporiadane podla datumu platnosti
   * @return \Nette\Database\Table\Selection
   */
  public function aktualne($usporiadanie = FALSE) {
  	return $this->findBy(["datum_platnosti >= '".StrFTime("%Y-%m-%d",strtotime("0 day"))."'"])
                ->order('datum_platnosti '.($usporiadanie ? 'ASC' : 'DESC'));
	}

  /** Vrati uz neaktualne oznamy
   * @return \Nette\Database\Table\Selection
   */
	public function neaktualne() {
  	return $this->findBy(["datum_platnosti < '".StrFTime("%Y-%m-%d",strtotime("0 day"))."'"])->order('datum_platnosti DESC');
	}
  
  /** Vypisanie vsetkych oznamov aj s priznakom aktualnosti
   * @param boolean $usporiadanie Urcuje usporiadane podla datumu platnosti
   * @return array
   */
  public function vsetky($usporiadanie = FALSE) {
  	$oznamy = $this->findAll()->order('datum_platnosti '.($usporiadanie ? 'ASC' : 'DESC'));
    $out = [];
    foreach ($oznamy as $o) {
      $temp = ["oznam" => $o, "aktualny" => $o->datum_platnosti >= StrFTime("%Y-%m-%d",strtotime("0 day"))];
      $out[] = $temp;
    }
    return $out;
	}
  
  /** Vymazanie oznamu
   * @param int $id Id oznamu
   * @return type
   * @throws Database\DriverException
   */
  public function vymazOznam($id) {
    try {
      $oznam = $this->find($id);
      if ($oznam->potvrdenie) { 
        $this->connection->table('oznam_ucast')->where(["id_oznam"=>$id])->delete(); 
      }
      $this->connection->table('oznam_komentar')->where(["id_oznam"=>$id])->delete();
      
      return $oznam->delete();
      
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    } 
  }
  
  /** Funkcia pre ulozenie oznamu
   * @param Nette\Utils\ArrayHash $values
   * @return Nette\Database\Table\ActiveRow|FALSE
   * @throws Database\DriverException
   */
  public function ulozOznam(Nette\Utils\ArrayHash $values) {
    $val = clone $values;
    $id = isset($val->id) ? $val->id : 0;
    unset($val->id, $val->posli_news);
    try {
      return $this->uloz($val, $id);
    } catch (Exception $e) {
      throw new Database\DriverException('Chyba ulozenia: '.$e->getMessage());
    }
  }
}