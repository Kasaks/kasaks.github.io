<?php

$GLOBALS['title_tail'] = 'Чеховский завод Машиностроитель';

class UserController {

	public function show_home () {

		$page_title = $GLOBALS['title_tail'];

		$page_description = '';

		include_once($_SERVER['DOCUMENT_ROOT'].'/views/user/home.php');

		return true;

	}

	public function show_certificates () {

		$page_title = 'Сертификаты | ' . $GLOBALS['title_tail'];

		$page_description = '';

		include_once($_SERVER['DOCUMENT_ROOT'].'/views/user/certificates.php');

		return true;

	}

	public function show_registry () {

		$page_title = 'Реестр | ' . $GLOBALS['title_tail'];

		$page_description = '';

		include_once($_SERVER['DOCUMENT_ROOT'].'/views/user/registry.php');

		return true;

	}

	public function show_404 () {

		$page_title = 'Страница не найдена' . $GLOBALS['title_tail'];

		// $page_description = 'Сайт автосервиса квалифицирующегося на ремонте КПП! У нас отличная репутация к нам на ремонт едут со всей центральной России и более отдаленных регионов';

		include_once($_SERVER['DOCUMENT_ROOT'].'/views/error/404.php');

		return true;

	}

};

	