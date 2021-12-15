<?php
declare(strict_types=1);

namespace Aliado\IpInformation;

use Aliado\Main\Entity\IpInformationTable;

/**
 *
 */
class Agents
{
    /**
     *
     */
    const URL_IP_INFO = 'https://rest.db.ripe.net/search.json?query-string=';

    /**
     * @return string
     */
    public static function definitionIp()
    {
        $newIPs = IpInformationTable::getList(
            [
                'filter' =>
                    [
                        'DATA' => NULL
                    ]
            ]
        )->fetchAll();
        if (!empty($newIPs)) {
            $httpClient  = new \Bitrix\Main\Web\HttpClient();
            foreach ($newIPs as $item) {
                $response = $httpClient->get(self::URL_IP_INFO . $item['IP']);;
                if ($response) {
                    $arData = json_decode($response, true);
                    $ipInfo = $arData['objects']['object'];

                    IpInformationTable::update($item['ID'], [
                        'DATA' => serialize($ipInfo)
                    ]);

                    $eventData = [
                        'IP_INFO' => $ipInfo,
                        'IP' => $item['IP'],
                        'ORDER_ID' => $item['ORDER_ID']
                    ];
                    $event = new \Bitrix\Main\Event('aliado.ipinformation', 'OnGetIPData', [$eventData]);
                    $event->send();
                }
            }
        }

        return "\\Aliado\\IpInformation\\Agents::definitionIp();";
    }
}