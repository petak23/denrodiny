<?php
namespace App\FrontModule\Presenters;

use Tracy\Debugger,
	  \Nette\Application as NA;
use Language_support;
/**
 * Prezenter pre smerovanie na chybove stranky.
 * Posledna zmena(last change): 10.08.2015
 *
 *	Modul: FRONT
 *
 * @author Ing. Peter VOJTECH ml. <petak23@gmail.com>
 * @copyright  Copyright (c) 2012 - 2015 Ing. Peter VOJTECH ml.
 * @license
 * @link       http://petak23.echo-msz.eu
 * @version 1.0.3
 *
 */
class ErrorPresenter extends \App\FrontModule\Presenters\BasePresenter {
  /**
   * @inject
   * @var Language_support\Error
   */
  public $texty_presentera;
	/**
	 * @param  Exception
	 * @return void
	 */
	public function renderDefault($exception)
	{
		if ($this->isAjax()) { // AJAX request? Just note this error in payload.
			$this->payload->error = TRUE;
			$this->terminate();

		} elseif ($exception instanceof NA\BadRequestException) {
			$code = $exception->getCode();
      $code_a = in_array($code, array(403, 404, 405, 410, 500)) ? $code : '4xx';
      $this->template->h2 = $this->trLang('err_'.$code_a.'_h2');
      $this->template->text = $this->trLang('err_'.$code_a.'_text');
      $this->template->err_code = "error ".$code_a;
			$this->setView($code==500 ? "500" : "400");
			// log to access.log
			Debugger::log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

		} else {
			$this->setView('500'); // load template 500.latte
			Debugger::log($exception, Debugger::ERROR); // and log exception
		}
	}

}