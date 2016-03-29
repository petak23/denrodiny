<?php

namespace DbTable;

/**
 * Model, ktory sa stara o tabulku user_profiles
 * Posledna zmena 19.01.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.4
 */
class User_profiles extends Table {
  /** @var string */
  protected $tableName = 'user_profiles';

  /**
   * Funkcia zisti max a sum prihlásenia
   * @return array max a sum pocet prihlaseni
   */
  function getPocetPr() {
		return ['max'=>$this->findAll()->max('pocet_pr'), 'sum'=>(int)$this->findAll()->sum('pocet_pr')];
	}
  
  /**
   * Funkcia na zostavenie pola emailov podla urovne registracie pre odoslanie info mailu. 
   * @param int $id_registracia Minimalna uroven registracie
   * @return array Pole emailou s dvoma castami "komu" - retazec emilov oddelených ciarkami; "clen" - arra emailov
   */
  public function emailUsersList($id_registracia = 5) {
    $cl = $this->findBy(['id_registracia >='.$id_registracia, 'news'=>'A']);
    $out = ["clen"=>[],"komu"=>""];
    $sum = count($cl); $iter = 0;
    foreach ($cl as $c) {
      $iter++;
      if ($c->users->email != '---') {
        $out["komu"] .= $sum == $iter ? $c->users->email : $c->users->email.', '; 
        $out["clen"][] = $c->users->email;
      }
    }
    return $out;
  }

  /**
   * Funkcia na zostavenie ratazca emailov podla urovne registracie pre odoslanie info mailu. 
   * @param int $id_registracia Minimalna uroven registracie
   * @return sring Retazec emailov oddelených ciarkami
   */
  public function emailUsersListStr($id_registracia = 5) {
    $cl = $this->findBy(['id_registracia >='.$id_registracia, 'news'=>'A']);
    $out = "";
    $sum = count($cl); $iter = 0;
    foreach ($cl as $c) {
      $iter++;
      if ($c->users->email != '---') {
        $out .= $sum == $iter ? $c->users->email : $c->users->email.', '; 
      }
    }
    return $out;
  }
  
  /**
   * Funkcia pre formulár na zostavenie zoznamu všetkých užívateľov
   * @return array Pole uzivatelov vo formate: id => "meno priezvisko"
   */
  public function uzivateliaForm() {
    $u = $this->findAll();
    $out = [];
    foreach ($u as $v) {
      $out[$v->id] = $v->meno." ".$v->priezvisko;
    }
    return $out;
  }
  
  /** Pre zmazanie aktivít uzivatela
   * @param int $clen_id_up
   * @throws Database\DriverException
   */
  public function delUser($clen_id_up) {
    try {
      $this->connection->table('clanok_komentar')->where(['id_user_profiles'=>$clen_id_up])->delete();
      $this->connection->table('oznam_komentar')->where(['id_user_profiles'=>$clen_id_up])->delete();
      $this->connection->table('user_prihlasenie')->where(['id_user_profiles'=>$clen_id_up])->delete();
      $this->connection->table('clanok')->where(['id_user_profiles'=>$clen_id_up])->update(['id_user_profiles'=>1]);
      $this->connection->table('dokumenty')->where(['id_user_profiles'=>$clen_id_up])->update(['id_user_profiles'=>1]);
      $this->connection->table('oznam')->where(['id_user_profiles'=>$clen_id_up])->update(['id_user_profiles'=>1]);
      $this->connection->table('verzie')->where(['id_user_profiles'=>$clen_id_up])->update(['id_user_profiles'=>1]);
    } catch (Exception $e) {
      throw new Database\DriverException('DB error: '.$e->getMessage());
    }
  }
}