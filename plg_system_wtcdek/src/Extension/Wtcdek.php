<?php

/**
 * System plugin for CDEK configuration and AJAX reference data.
 * @package       WT Cdek library package
 * @subpackage    plg_system_wtcdek
 * @author        Sergey Tolkachyov, WebTolk
 * @copyright     Copyright (c) 2024 - 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @version       __DEPLOY_VERSION__
 * @license       GNU General Public License version 3 or later.
 * @link          https://web-tolk.ru
 * @since         1.0.0
 */

namespace Joomla\Plugin\System\Wtcdek\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Document\Document;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Webtolk\Cdekapi\Cdek;
use Webtolk\Cdekapi\Service\DeliverypointsService;

/**
 *
 *
 * @since 1.3.0
 */
class Wtcdek extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since 1.3.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since 1.3.0
     */
    protected $app;

    /**
     * The document.
     *
     * @var Document
     *
     * @since 1.3.0
     */
    private $document;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since 1.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterRoute' => 'addCdekWidgetAsset',
            'onAjaxWtcdek' => 'onAjaxWtcdek',
        ];
    }

    /**
     * @since 1.3.0
     */
    public function addCdekWidgetAsset(): void
    {
        // Only trigger in frontend
        if (Factory::getApplication()->isClient('site')) {
            /** @var Joomla\CMS\WebAsset\WebAssetRegistry $wa */
            $wa = Factory::getContainer()->get(WebAssetRegistry::class);
            $wa->addRegistryFile('media/lib_wtcdek/joomla.assets.json');
        }
    }

    /**
     * Route CDEK AJAX requests in frontend and administrator forms by action.
     * Explicit GET/POST input keeps cookies out of provider parameters.
     *
     * @param   Event  $event  Joomla 4 generic or Joomla 5/6 result-aware AJAX event.
     * @return  void
     * @since   1.0.0
     */
    public function onAjaxWtcdek(Event $event): void
    {
        $app    = $this->getApplication();
        $input  = $app->getInput();
        $data   = array_replace($input->get->getArray(), $input->post->getArray());
        $action = $data['action'] ?? '';

        if ($action === '') {
            return;
        }

        switch ($action) {
            case 'offices':
            case 'calculate':
                $token       = Session::getFormToken();
                $tokenSource = $input->post->getInt($token, 0) === 1 ? 'post' : 'get';
                $hasToken    = $input->$tokenSource->getInt($token, 0) === 1
                    || $input->server->get('HTTP_X_CSRF_TOKEN', '', 'alnum') === $token;

                if (!$hasToken || !Session::checkToken($tokenSource)) {
                    $result = json_encode(['message' => 'Wrong Joomla session token']);
                    break;
                }

                $cdek   = new Cdek();
                $result = $action === 'offices'
                    ? $cdek->deliverypoints()->getDeliveryPoints($data)
                    : $cdek->calculator()->calculateTariffList($data);
                // Preserve the widget's raw JSON response through com_ajax.
                $result = json_encode($result);
                break;

            case 'deliverypoints':
                if ($input->getMethod() !== 'POST') {
                    throw new \RuntimeException('POST required.', 405);
                }

                // Require the posted token explicitly; checkToken may redirect and return true on a new session.
                if ($input->post->getInt(Session::getFormToken(), 0) !== 1 || !Session::checkToken('post')) {
                    throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
                }

                $app->getLanguage()->load('lib_webtolk_cdekapi', JPATH_SITE);
                $city       = trim($input->post->getString('city', ''));
                $postalCode = trim($input->post->getString('postal_code', ''));

                if (\strlen($city) > 200 || \strlen($postalCode) > 20) {
                    throw new \InvalidArgumentException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_ERROR'), 400);
                }

                try {
                    $filters = DeliverypointsService::normalizeFilters([
                        'type'         => $input->post->getString('type', 'ALL'),
                        'is_reception' => $input->post->getString('is_reception', ''),
                        'is_handout'   => $input->post->getString('is_handout', ''),
                    ]);
                } catch (\InvalidArgumentException $exception) {
                    throw new \InvalidArgumentException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_FILTER_INVALID'), 400);
                }

                try {
                    $fiasGuid = DeliverypointsService::normalizeFiasGuid($input->post->getString('fias_guid', ''));
                } catch (\InvalidArgumentException $exception) {
                    throw new \InvalidArgumentException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_FIAS_INVALID'), 400);
                }

                $options = [];

                if ($city !== '' || $postalCode !== '' || $fiasGuid !== '') {
                    try {
                        $options = (new DeliverypointsService())->getOptions(new Cdek(), $city, $postalCode, $filters, $fiasGuid);
                    } catch (\Throwable $exception) {
                        throw new \RuntimeException(Text::_('LIB_WTCDEK_FIELD_DELIVERYPOINTS_ERROR'), 502);
                    }
                }
                $result = ['options' => $options];
                break;

            default:
                $result = json_encode(['message' => 'Unknown action']);
                break;
        }

        if ($event instanceof ResultAwareInterface) {
            $event->addResult($result);
        } else {
            $results   = $event->getArgument('result', []);
            $results[] = $result;
            $event->setArgument('result', $results);
        }
    }
}
