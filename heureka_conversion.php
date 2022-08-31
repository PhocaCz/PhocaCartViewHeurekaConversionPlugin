<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

defined('_JEXEC') or die;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.filesystem.file');
jimport( 'joomla.html.parameter' );


JLoader::registerPrefix('Phocacart', JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/phocacart');

class plgPCVHeureka_conversion extends CMSPlugin
{
	function __construct(& $subject, $config) {
		parent :: __construct($subject, $config);
		$this->loadLanguage();
	}

	public function onPCVonInfoViewDisplayContent($context, &$infoData, &$infoAction, $eventData) {


		$p = [];
		$p['heureka_key'] = $this->params->get('heureka_key', '');

		if (!isset($infoData['user_id'])) { $infoData['user_id'] = 0;}

		if (isset($infoData['order_id']) && (int)$infoData['order_id'] > 0 && isset($infoData['order_token']) && $infoData['order_token'] != '') {
			$order = PhocacartOrder::getOrder($infoData['order_id'], $infoData['order_token'], $infoData['user_id']);

			if (isset($order['id']) && (int)$order['id'] > 0 && $infoAction != 5) {
				$orderProducts = PhocacartOrder::getOrderProducts($order['id']);

				if (!empty($orderProducts)) {

					$price = new PhocacartPrice();


					$s   = [];
					$s[] = 'var _hrq = _hrq || [];';
					$s[] = '_hrq.push([\'setKey\', \'' . addslashes(trim(strip_tags($p['heureka_key']))) . '\']);';
					$s[] = '_hrq.push([\'setOrderId\', \'' . (int)$order['id'] . '\']);';

					foreach ($orderProducts as $k => $v) {
						$productPrice = $price->getPriceFormatRaw($v['brutto'], 0, 0, 0, 2, '.', '');

						$s[] = '_hrq.push([\'addProduct\', \'' . addslashes($v['title']) . '\',\'' . $productPrice . '\',\'' . (int)$v['quantity'] . '\',\'' . (int)$v['id'] . '\']);';
					}

					$s[] = '_hrq.push([\'trackOrder\']);';

					$s[] = '(function() {';
					$s[] = '	var ho = document.createElement(\'script\');';
					$s[] = '	ho.type = \'text/javascript\'; ho.async = true;';
					$s[] = '	ho.src = \'https://im9.cz/js/ext/1-roi-async.js\';';
					$s[] = '	var s = document.getElementsByTagName(\'script\')[0];';
					$s[] = '	s.parentNode.insertBefore(ho, s);';
					$s[] = '})();';

					Factory::getDocument()->addScriptDeclaration(implode("\n", $s));

				}
			}
		}

		/*
		$output = array();
		$output['content'] = '';

		return $output;
		*/
	}

}
?>
