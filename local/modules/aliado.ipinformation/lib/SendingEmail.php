<?php
declare(strict_types=1);

namespace Aliado\IpInformation;


/**
 *
 */
class SendingEmail
{
    /**
     *
     */
    const MESSAGE_NAME_IP_INFORMATION = 'SEND_IP_INFORMATION';

    /**
     * @param $data
     */
    public static function sendEmailAboutIP($data)
    {
        $data = $data->getParameters();

        $dataForEmail = self::transformInformation($data[0]['IP_INFO']);

        $rsSites = \Bitrix\Main\SiteTable::getList(
            [
                'select' => ['LID'],
                'filter' => ['ACTIVE' => 'Y']
            ]
        );
        $arSites = $rsSites->fetchAll();
        $sitesList = [];

        foreach ($arSites as $site) {
            $sitesList[] = $site['LID'];
        }

        \Bitrix\Main\Mail\Event::send([
            'EVENT_NAME' => MESSAGE_NAME_IP_INFORMATION,
            'LID' => $sitesList,
            'C_FIELDS' => [
                'ORDER_ID' => $data[0]['ORDER_ID'],
                'IP' => $data[0]['IP'],
                'IP_INFO' => $dataForEmail
            ]
        ]);
    }

    /**
     * @param $data
     * @return string
     */
    private static function transformInformation($data) {
        $html = '<table>';
        $html .= '<tr>';
        $html .= '<th>Название параметра</th>';
        $html .= '<th>Значение параметра</th>';
        $html .= '</tr>';
        foreach ($data as $item) {
            $html .= '<tr>';
            $html .= '<td colspan="2" style="text-align: center"><b>' . $item['type'] . '</b></td>';
            $html .= '</tr>';
            foreach ($item['attributes']['attribute'] as $attribute) {
                $html .= '<tr>';
                $html .= '<td>' . $attribute['name'] . '</td>';
                $html .= '<td>' . $attribute['value'] . '</td>';
                $html .= '</tr>';
            }
        }
        $html .= '</table>';

        return $html;
    }
}