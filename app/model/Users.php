<?php

namespace DbTable;
use Nette;
/**
 * Model, ktory sa stara o tabulku users
 * Posledna zmena 19.01.2016
 * 
 * @author     Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2016 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version    1.0.2
 */
class Users extends Table {
  /** @var string */
  protected $tableName = 'users';

  /** Hladanie uzivatela podla username
   * @param string $username
   * @return type
   */
  public function findByUsername($username) {
    return $this->findAll()->where('username', $username)->fetch();
  }
  
  /** Test existencie username
   * @param string $username
   * @return boolean
   */
  public function testUsername($username) {
    return $this->findBy(['username'=>$username])->count() > 0 ? TRUE : FALSE;
  }
  /** Test existencie emailu
   * @param string $email
   * @return boolean
   */
  public function testEmail($email) {
    return $this->findBy(['email'=>$email])->count() > 0 ? TRUE : FALSE;
  }
}