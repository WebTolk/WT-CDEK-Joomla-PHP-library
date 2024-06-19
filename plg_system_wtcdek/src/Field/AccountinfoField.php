<?php
/**
 * Library to connect to CDEK service.
 * @package     Webtolk\Cdek
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (C) Sergey Tolkachyov, 2024. All rights reserved.
 * @version     1.0.0
 * @license     GNU General Public License version 3 or later. Only for *.php files!
 * @link        https://api-docs.cdek.ru/29923741.html
 */

namespace Joomla\Plugin\System\Wtcdek\Field;
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Webtolk\Cdekapi\Cdek;

class AccountinfoField extends FormField
{

	protected $type = 'Accountinfo';

	protected function getInput(): string
	{

		$html = '';
		$data = $this->form->getData();
		$plugin_params = new Registry($data->get('params'));

		if(empty($plugin_params->get('client_id','')) && empty($plugin_params->get('client_secret','')))
		{
			return $html;
		}

		$cdek = new Cdek();
		if($cdek->canDoRequest())
		{
			$response = $cdek->getLocationCities(['city' => 'Саратов','size' => 1]);
			if($response[0]['code'])
			{
				$element = 	$data->get('element');
				$html =  '</div>
						<div class="card container shadow-sm w-100 p-0 border-1 border-success">
							<div class="row g-0">
								<div class="col-12 col-md-2 d-flex justify-content-center align-items-center">
									<a href="https://cdek.ru" target="_blank">
											<svg width="73" height="20" viewBox="0 0 145 41" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M22.7199 30.4706H16.4674C6.71351 30.4706 13.7997 9.75397 22.0113 9.75397H31.8903C33.4742 9.75397 36.267 10.0458 37.6426 6.12752L39.7684 0.041748H26.2213C18.8851 0.041748 13.1744 2.62612 9.04778 6.96119C1.91992 14.3808 -0.49771 25.9688 1.21131 31.5127C2.83696 36.6397 7.2554 40.0161 14.1748 40.0995L19.552 40.1411H26.1796L27.8053 35.2642C29.0141 31.8045 26.3047 30.4706 22.7199 30.4706ZM98.8338 21.8838L100.876 15.2562H79.0758C75.4494 15.2562 73.8237 16.2566 73.1985 18.3407L71.156 24.9684H92.9564C96.5828 24.9684 98.2085 23.968 98.8338 21.8838ZM67.9047 33.5552L65.8622 40.1828H87.6626C91.2474 40.1828 92.9147 39.1824 93.54 37.0983L95.5825 30.4706H73.7821C70.1973 30.4706 68.5716 31.471 67.9047 33.5552ZM103.919 6.71109L105.962 0.0834308H84.1612C80.5348 0.0834308 78.9091 1.08383 78.2839 3.168L76.2414 9.79566H98.0418C101.627 9.79566 103.252 8.79526 103.919 6.71109ZM70.9893 8.04496C70.0722 1.87582 66.7792 0.0834308 58.776 0.0834308H44.1869L35.6835 24.9684H41.0606C44.2702 24.9684 45.8959 25.0101 47.6049 20.4249L51.148 9.75397H56.5668C61.1937 9.75397 60.1516 15.548 57.8173 21.1752C55.7332 26.1355 52.1067 30.5123 47.7299 30.5123H38.6847C35.0582 30.5123 33.3909 31.5127 32.7239 33.5969L30.473 40.2245H37.1007L43.6033 40.1828C49.3556 40.1411 54.0658 39.7243 59.568 34.764C65.4037 29.4702 72.1564 15.8814 70.9893 8.04496ZM144.269 0.041748H131.639L119.759 12.6718C118.383 14.1307 116.966 15.5896 115.59 17.2987H115.465L121.384 0.041748H111.089L97.2081 40.1828H107.504L111.922 27.5528L116.507 23.6762L120.134 35.0975C121.259 38.6405 122.427 40.1828 124.928 40.1828H132.806L124.719 17.7572L144.269 0.041748Z" fill="#00B33C"/>
											</svg>
								</a>
								</div>
								<div class="col-12 col-md-10">
									<div class="card-body">
										' . Text::_("PLG_" . strtoupper($element) . "_SUCCESSFULLY_CONNECTED") . '
									</div>
								</div>
							</div>
						</div><div>
						';
			}
		}

		return $html;
	}
}