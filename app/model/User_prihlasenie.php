<?php

namespace DbTable;
use Nette;

/**
 * Model staraj[cí sa o tabulku user_prihlasenie
 */
class User_prihlasenie extends Table
{
  /** @var string */
  protected $tableName = 'user_prihlasenie';

  function getLastPr($pocet = 25)
	{
		return $this->findAll()->order('prihlasenie_datum DESC')->limit($pocet);
	}

}