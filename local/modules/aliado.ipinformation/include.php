<?php
use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses(
    'aliado.ipinformation',
    [
        'Aliado\\IpInformation\\EventHandlers' => 'lib/EventHandlers.php',
        'Aliado\\IpInformation\\SendingEmail' => 'lib/SendingEmail.php',
        'Aliado\\IpInformation\\Agents' => 'lib/Agents.php',
        'Aliado\\Main\\Entity\\IpInformationTable' => 'lib/entity/ipinformation.php',
    ]
);