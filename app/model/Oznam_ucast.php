<?php
namespace DbTable;

/**
 * Model, ktory sa stara o tabulku oznam_ucast
 * Posledna zmena 26.01.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.1
 */
class Oznam_ucast extends Table {
  /** @var string */
  protected $tableName = 'oznam_ucast';
  
  /** Zapise ucast
   * @param int $id_user_profiles
   * @param int $id_oznam
   * @param int $id_oznam_volba
   */
  public function zapisUcast($id_user_profiles, $id_oznam, $id_oznam_volba) {
    $ucast = $this->findOneBy(['id_oznam'=>$id_oznam, 'id_user_profiles'=>$id_user_profiles]);
    if ($ucast !== FALSE) { //Nasiel som potvrdenie
      $ucast->update(['id_oznam_volba'=>$id_oznam_volba]);
    } else {
      $this->pridaj(['id_user_profiles'=>$id_user_profiles, 'id_oznam'=>$id_oznam, 'id_oznam_volba'=>$id_oznam_volba]);
    }
  } 
  
  /**
   * Zisti moju ucast. Ak ano vrati jej id inak 0.
   * @param int $id_oznam
   * @param int $id_user_profiles
   * @return int
   */
  public function mojaUcast($id_oznam, $id_user_profiles) {
    $ucast = $this->findOneBy(['id_oznam'=>$id_oznam, 'id_user_profiles'=>$id_user_profiles]);
    return ($ucast !== FALSE) ? $ucast->id_oznam_volba : 0;
  }
  /**
   * 
   * @param int $id_oznam
   * @return int
   */
  public function vymazUcast($id_oznam) {
    return $this->findBy(['id_oznam'=>$id_oznam])->delete();
  }
  
}