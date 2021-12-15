<?php
declare(strict_types=1);

use Bitrix\Main\ModuleManager,
    Bitrix\Main\Application,
    Bitrix\Main\Entity\Base,
    Bitrix\Main\EventManager,
    Bitrix\Main\Loader,
    Aliado\IpInformation;

class aliado_ipinformation extends CModule
{
    public $MODULE_ID = 'aliado.ipinformation';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = 'N';
    const MESSAGE_NAME_IP_INFORMATION = 'SEND_IP_INFORMATION';

    public function __construct()
    {
        $arModuleVersion = [];

        include(__DIR__ . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = 'Информация об IP';
        $this->MODULE_DESCRIPTION = 'Модуль получает данные об IP покпателя при оформлении заказа и отправляет письмо';
    }

    public function DoInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallAgents();
        $this->InstallEvents();
        $this->InstallDB();
        return true;
    }

    public function DoUninstall()
    {
        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallEvents();
        $this->UnInstallAgents();
    }

    function InstallDB()
    {
        global $DB, $APPLICATION;
        if (!$DB->query("SELECT 'x' FROM aliado_ipinformation", true))
        {
            $this->errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/local/modules/aliado.ipinformation/install/db/' . mb_strtolower($DB->type) . '/install.sql');
        }

        if ($this->errors !== false)
        {
            $APPLICATION->throwException(implode('', $this->errors));
            return false;
        }
        return true;
    }

    public function InstallAgents()
    {
        $arrEventRes = [];
        $arrEventTypes = [
            [
                'LID' => SITE_ID,
                'EVENT_NAME' => self::MESSAGE_NAME_IP_INFORMATION,
                'NAME' => 'Отправка информации по IP адресу клиента',
                'DESCRIPTION' => '#MORE_INFO#'
            ],
        ];

        $textMessage = "
<!doctype html>
<html lang='ru'>
<head>
  <meta charset='utf-8'>
  <title>Информация по IP</title>
</head>
<body>
<p>Информация о IP покупателя</p>
<p>Номер заказа - #ORDER_ID# </p>
<p>IP покупателя #IP# </p>

#IP_INFO#

</body>
</html>";
        $arrEventTemplates = [
            self::MESSAGE_NAME_IP_INFORMATION => [
                'ACTIVE' => 'Y',
                'EVENT_NAME' => self::MESSAGE_NAME_IP_INFORMATION,
                'LID' => ['s1'],
                'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                'EMAIL_TO' => '#EMAIL_TO#',
                'SUBJECT' => 'Информация по IP',
                'BODY_TYPE' => 'html',
                'MESSAGE' => $textMessage
            ],
        ];

        $et = new CEventType;
        foreach ($arrEventTypes as $arrEventType)
        {
            $res = $et->Add($arrEventType);

            if ($res)
                $arrEventRes[$res] = $arrEventType['EVENT_NAME'];
        }

        if (is_array($arrEventRes))
        {
            $em = new CEventMessage;
            foreach ($arrEventTemplates as $arrEventTemplate)
            {
                $ress = $em->Add($arrEventTemplate);
            }
        }

        \CAgent::AddAgent(
            "\\Aliado\\IpInformation\\Agents::definitionIp();",
            'aliado.ipinformation',
            'N',
            60,
            '',
            'Y'
        );

        return true;
    }

    public function InstallEvents()
    {
        $em = EventManager::getInstance();
        $em->registerEventHandler(
            'sale' ,
            'OnSaleOrderSaved',
            $this->MODULE_ID,
            IpInformation\EventHandler::class,
            'definitionIpHandler'
        );
        $em->registerEventHandler(
            'aliado.ipinformation' ,
            'OnGetIPData',
            $this->MODULE_ID,
            IpInformation\EventHandler::class,
            'onGetIPDataHandler'
        );
        return true;
    }

    public function UnInstallDB()
    {
        global $DB, $APPLICATION;

        $this->errors = $DB->runSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/local/modules/aliado.ipinformation/install/db/' . mb_strtolower($DB->type) . '/uninstall.sql');

        if ($this->errors !== false)
        {
            $APPLICATION->throwException(implode('', $this->errors));
            return false;
        }


        return true;
    }

    public function UnInstallAgents()
    {
        $et = new CEventType;
        $et->Delete(self::MESSAGE_NAME_IP_INFORMATION);

        $by = self::MESSAGE_NAME_IP_INFORMATION;
        $order = 'desc';
        $arfilter = ['TYPE_ID' => self::MESSAGE_NAME_IP_INFORMATION];
        $rsEMessages = CEventMessage::GetList($by, $order, $arfilter);

        $em = new CEventMessage;

        while($arEMessage = $rsEMessages->GetNext())
        {
            $em->Delete($arEMessage['ID']);
        }

        \CAgent::RemoveAgent(
            "\\Aliado\\IpInformation\\Agents::definitionIp();",
            'aliado.ipinformation'
        );
        return true;
    }

    public function UnInstallEvents()
    {
        $em = EventManager::getInstance();
        $em->unregisterEventHandler(
            'sale' ,
            'OnSaleOrderSaved',
            $this->MODULE_ID,
            IpInformation\EventHandler::class,
            'definitionIpHandler'
        );
        $em->unregisterEventHandler(
            'sale' ,
            'OnGetIPData',
            $this->MODULE_ID,
            IpInformation\EventHandler::class,
            'onGetIPDataHandler'
        );
        return true;
    }

}