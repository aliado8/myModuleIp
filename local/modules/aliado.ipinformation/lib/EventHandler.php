<?php
declare(strict_types=1);

namespace Aliado\IpInformation;

use Aliado\Main\Entity\IpInformationTable;
use Aliado\IpInformation\SendingEmail;

/**
 *
 */
class EventHandler
{
    /**
     * @param \Bitrix\Main\Event $event
     */
    public function definitionIpHandler(\Bitrix\Main\Event $event)
    {
        $order = $event->getParameter("ENTITY");
        $isNew = $event->getParameter("IS_NEW");

        if ($isNew)
        {
            $ip = \Bitrix\Main\Service\GeoIp\Manager::getRealIp();
            IpInformationTable::add([
                'IP' => $ip,
                'ORDER_ID' => $order->getId()
            ]);
        }

    }

    /**
     * @param $data
     */
    public function onGetIPDataHandler($data)
    {
        SendingEmail::sendEmailAboutIP($data);
    }

}