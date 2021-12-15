<?php
declare(strict_types=1);

namespace Aliado\Main\Entity;

use Bitrix\Main\Entity\DataManager,
    Bitrix\Main\Entity\IntegerField,
    Bitrix\Main\Entity\StringField,
    Bitrix\Main\Entity\TextField;

/**
 *
 */
class IpInformationTable extends DataManager
{

    /**
     * @return string
     */
    public static function getTableName():string
    {
        return 'aliado_ipinformation';
    }

    /**
     * @return array
     */
    public static function getMap():array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),
            (new TextField('IP')),
            (new IntegerField('ORDER_ID')),
            (new TextField('DATA')),
        ];
    }
}