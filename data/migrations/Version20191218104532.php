<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 *
 */
final class Version20191218104532 extends AbstractMigration
{
    public function getDescription() : string {
        return  'Provides organisation site schema';
    }

    public function up(Schema $schema) : void
    {
        // country
        $table = $schema->createTable('country');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $table->addColumn('code', 'string', ['notnull' => true, 'length' => 3]);
        $table->addColumn('name', 'string', ['notnull' => true, 'length' => 64]);
        $table->addUniqueIndex(['name'], 'name_index');
        $table->addUniqueIndex(['code'], 'code_index');
        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');

        $table = $schema->createTable('organisation_site');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement'=>true]);
        $table->addColumn('uuid', 'uuid');
        $table->addColumn('organisation_id', 'integer', ['notnull' => true, 'unsigned' => true]);
        $table->addColumn('name', 'string', ['notnull' => false]);
        $table->addColumn('street_address', 'string', ['notnull' => true]);
        $table->addColumn('street_address2', 'string', ['notnull' => false]);
        $table->addColumn('town', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('city', 'string', ['notnull' => true, 'length' => 64]);
        $table->addColumn('county', 'string', ['notnull' => false, 'length' => 64]);
        $table->addColumn('country_id', 'integer', ['unsigned' => true, 'notnull' => true]);
        $table->addColumn('telephone', 'string', ['length' => 20, 'notnull' => false]);
        $table->addColumn('postal_code', 'string',['notnull' => true, 'length' => 15]);
        $table->addUniqueIndex(['postal_code'], 'postal_code_index');
        $table->addForeignKeyConstraint('organisation',['organisation_id'], ['id'],
            ['onDelete'=>'RESTRICT', 'onUpdate'=>'CASCADE'], 'organisation_site_organisation_id_fk');
        $table->addForeignKeyConstraint('country',['country_id'], ['id'],
            ['onDelete'=>'RESTRICT', 'onUpdate'=>'CASCADE'], 'organisation_site_country_id_fk');
        $table->setPrimaryKey(['id']);
        $table->addOption('engine', 'InnoDB');
    }

    public function setUpCountryTable() : void
    {
        $this->connection->insert('country', ['code' => 'AF', 'name' => 'Afghanistan']);
        $this->connection->insert('country', ['code' => 'AL', 'name' => 'Albania']);
        $this->connection->insert('country', ['code' => 'DZ', 'name' => 'Algeria']);
        $this->connection->insert('country', ['code' => 'DS', 'name' => 'American Samoa']);
        $this->connection->insert('country', ['code' => 'AD', 'name' => 'Andorra']);
        $this->connection->insert('country', ['code' => 'AO', 'name' => 'Angola']);
        $this->connection->insert('country', ['code' => 'AI', 'name' => 'Anguilla']);
        $this->connection->insert('country', ['code' => 'AQ', 'name' => 'Antarctica']);
        $this->connection->insert('country', ['code' => 'AG', 'name' => 'Antigua and Barbuda']);
        $this->connection->insert('country', ['code' => 'AR', 'name' => 'Argentina']);
        $this->connection->insert('country', ['code' => 'AM', 'name' => 'Armenia']);
        $this->connection->insert('country', ['code' => 'AW', 'name' => 'Aruba']);
        $this->connection->insert('country', ['code' => 'AU', 'name' => 'Australia']);
        $this->connection->insert('country', ['code' => 'AT', 'name' => 'Austria']);
        $this->connection->insert('country', ['code' => 'AZ', 'name' => 'Azerbaijan']);
        $this->connection->insert('country', ['code' => 'BS', 'name' => 'Bahamas']);
        $this->connection->insert('country', ['code' => 'BH', 'name' => 'Bahrain']);
        $this->connection->insert('country', ['code' => 'BD', 'name' => 'Bangladesh']);
        $this->connection->insert('country', ['code' => 'BB', 'name' => 'Barbados']);
        $this->connection->insert('country', ['code' => 'BY', 'name' => 'Belarus']);
        $this->connection->insert('country', ['code' => 'BE', 'name' => 'Belgium']);
        $this->connection->insert('country', ['code' => 'BZ', 'name' => 'Belize']);
        $this->connection->insert('country', ['code' => 'BJ', 'name' => 'Benin']);
        $this->connection->insert('country', ['code' => 'BM', 'name' => 'Bermuda']);
        $this->connection->insert('country', ['code' => 'BT', 'name' => 'Bhutan']);
        $this->connection->insert('country', ['code' => 'BO', 'name' => 'Bolivia']);
        $this->connection->insert('country', ['code' => 'BA', 'name' => 'Bosnia and Herzegovina']);
        $this->connection->insert('country', ['code' => 'BW', 'name' => 'Botswana']);
        $this->connection->insert('country', ['code' => 'BV', 'name' => 'Bouvet Island']);
        $this->connection->insert('country', ['code' => 'BR', 'name' => 'Brazil']);
        $this->connection->insert('country', ['code' => 'IO', 'name' => 'British Indian Ocean Territory']);
        $this->connection->insert('country', ['code' => 'BN', 'name' => 'Brunei Darussalam']);
        $this->connection->insert('country', ['code' => 'BG', 'name' => 'Bulgaria']);
        $this->connection->insert('country', ['code' => 'BF', 'name' => 'Burkina Faso']);
        $this->connection->insert('country', ['code' => 'BI', 'name' => 'Burundi']);
        $this->connection->insert('country', ['code' => 'KH', 'name' => 'Cambodia']);
        $this->connection->insert('country', ['code' => 'CM', 'name' => 'Cameroon']);
        $this->connection->insert('country', ['code' => 'CA', 'name' => 'Canada']);
        $this->connection->insert('country', ['code' => 'CV', 'name' => 'Cape Verde']);
        $this->connection->insert('country', ['code' => 'KY', 'name' => 'Cayman Islands']);
        $this->connection->insert('country', ['code' => 'CF', 'name' => 'Central African Republic']);
        $this->connection->insert('country', ['code' => 'TD', 'name' => 'Chad']);
        $this->connection->insert('country', ['code' => 'CL', 'name' => 'Chile']);
        $this->connection->insert('country', ['code' => 'CN', 'name' => 'China']);
        $this->connection->insert('country', ['code' => 'CX', 'name' => 'Christmas Island']);
        $this->connection->insert('country', ['code' => 'CC', 'name' => 'Cocos (Keeling) Islands']);
        $this->connection->insert('country', ['code' => 'CO', 'name' => 'Colombia']);
        $this->connection->insert('country', ['code' => 'KM', 'name' => 'Comoros']);
        $this->connection->insert('country', ['code' => 'CG', 'name' => 'Congo']);
        $this->connection->insert('country', ['code' => 'CK', 'name' => 'Cook Islands']);
        $this->connection->insert('country', ['code' => 'CR', 'name' => 'Costa Rica']);
        $this->connection->insert('country', ['code' => 'HR', 'name' => 'Croatia (Hrvatska)']);
        $this->connection->insert('country', ['code' => 'CU', 'name' => 'Cuba']);
        $this->connection->insert('country', ['code' => 'CY', 'name' => 'Cyprus']);
        $this->connection->insert('country', ['code' => 'CZ', 'name' => 'Czech Republic']);
        $this->connection->insert('country', ['code' => 'DK', 'name' => 'Denmark']);
        $this->connection->insert('country', ['code' => 'DJ', 'name' => 'Djibouti']);
        $this->connection->insert('country', ['code' => 'DM', 'name' => 'Dominica']);
        $this->connection->insert('country', ['code' => 'DO', 'name' => 'Dominican Republic']);
        $this->connection->insert('country', ['code' => 'TP', 'name' => 'East Timor']);
        $this->connection->insert('country', ['code' => 'EC', 'name' => 'Ecuador']);
        $this->connection->insert('country', ['code' => 'EG', 'name' => 'Egypt']);
        $this->connection->insert('country', ['code' => 'SV', 'name' => 'El Salvador']);
        $this->connection->insert('country', ['code' => 'GQ', 'name' => 'Equatorial Guinea']);
        $this->connection->insert('country', ['code' => 'ER', 'name' => 'Eritrea']);
        $this->connection->insert('country', ['code' => 'EE', 'name' => 'Estonia']);
        $this->connection->insert('country', ['code' => 'ET', 'name' => 'Ethiopia']);
        $this->connection->insert('country', ['code' => 'FK', 'name' => 'Falkland Islands (Malvinas)']);
        $this->connection->insert('country', ['code' => 'FO', 'name' => 'Faroe Islands']);
        $this->connection->insert('country', ['code' => 'FJ', 'name' => 'Fiji']);
        $this->connection->insert('country', ['code' => 'FI', 'name' => 'Finland']);
        $this->connection->insert('country', ['code' => 'FR', 'name' => 'France']);
        $this->connection->insert('country', ['code' => 'FX', 'name' => 'France, Metropolitan']);
        $this->connection->insert('country', ['code' => 'GF', 'name' => 'French Guiana']);
        $this->connection->insert('country', ['code' => 'PF', 'name' => 'French Polynesia']);
        $this->connection->insert('country', ['code' => 'TF', 'name' => 'French Southern Territories']);
        $this->connection->insert('country', ['code' => 'GA', 'name' => 'Gabon']);
        $this->connection->insert('country', ['code' => 'GM', 'name' => 'Gambia']);
        $this->connection->insert('country', ['code' => 'GE', 'name' => 'Georgia']);
        $this->connection->insert('country', ['code' => 'DE', 'name' => 'Germany']);
        $this->connection->insert('country', ['code' => 'GH', 'name' => 'Ghana']);
        $this->connection->insert('country', ['code' => 'GI', 'name' => 'Gibraltar']);
        $this->connection->insert('country', ['code' => 'GK', 'name' => 'Guernsey']);
        $this->connection->insert('country', ['code' => 'GR', 'name' => 'Greece']);
        $this->connection->insert('country', ['code' => 'GL', 'name' => 'Greenland']);
        $this->connection->insert('country', ['code' => 'GD', 'name' => 'Grenada']);
        $this->connection->insert('country', ['code' => 'GP', 'name' => 'Guadeloupe']);
        $this->connection->insert('country', ['code' => 'GU', 'name' => 'Guam']);
        $this->connection->insert('country', ['code' => 'GT', 'name' => 'Guatemala']);
        $this->connection->insert('country', ['code' => 'GN', 'name' => 'Guinea']);
        $this->connection->insert('country', ['code' => 'GW', 'name' => 'Guinea-Bissau']);
        $this->connection->insert('country', ['code' => 'GY', 'name' => 'Guyana']);
        $this->connection->insert('country', ['code' => 'HT', 'name' => 'Haiti']);
        $this->connection->insert('country', ['code' => 'HM', 'name' => 'Heard and Mc Donald Islands']);
        $this->connection->insert('country', ['code' => 'HN', 'name' => 'Honduras']);
        $this->connection->insert('country', ['code' => 'HK', 'name' => 'Hong Kong']);
        $this->connection->insert('country', ['code' => 'HU', 'name' => 'Hungary']);
        $this->connection->insert('country', ['code' => 'IS', 'name' => 'Iceland']);
        $this->connection->insert('country', ['code' => 'IN', 'name' => 'India']);
        $this->connection->insert('country', ['code' => 'IM', 'name' => 'Isle of Man']);
        $this->connection->insert('country', ['code' => 'ID', 'name' => 'Indonesia']);
        $this->connection->insert('country', ['code' => 'IR', 'name' => 'Iran (Islamic Republic of)']);
        $this->connection->insert('country', ['code' => 'IQ', 'name' => 'Iraq']);
        $this->connection->insert('country', ['code' => 'IE', 'name' => 'Ireland']);
        $this->connection->insert('country', ['code' => 'IL', 'name' => 'Israel']);
        $this->connection->insert('country', ['code' => 'IT', 'name' => 'Italy']);
        $this->connection->insert('country', ['code' => 'CI', 'name' => 'Ivory Coast']);
        $this->connection->insert('country', ['code' => 'JE', 'name' => 'Jersey']);
        $this->connection->insert('country', ['code' => 'JM', 'name' => 'Jamaica']);
        $this->connection->insert('country', ['code' => 'JP', 'name' => 'Japan']);
        $this->connection->insert('country', ['code' => 'JO', 'name' => 'Jordan']);
        $this->connection->insert('country', ['code' => 'KZ', 'name' => 'Kazakhstan']);
        $this->connection->insert('country', ['code' => 'KE', 'name' => 'Kenya']);
        $this->connection->insert('country', ['code' => 'KI', 'name' => 'Kiribati']);
        $this->connection->insert('country', ['code' => 'KP', 'name' => 'Korea, Democratic People\'s Republic of']);
        $this->connection->insert('country', ['code' => 'KR', 'name' => 'Korea, Republic of']);
        $this->connection->insert('country', ['code' => 'XK', 'name' => 'Kosovo']);
        $this->connection->insert('country', ['code' => 'KW', 'name' => 'Kuwait']);
        $this->connection->insert('country', ['code' => 'KG', 'name' => 'Kyrgyzstan']);
        $this->connection->insert('country', ['code' => 'LA', 'name' => 'Lao People\'s Democratic Republic']);
        $this->connection->insert('country', ['code' => 'LV', 'name' => 'Latvia']);
        $this->connection->insert('country', ['code' => 'LB', 'name' => 'Lebanon']);
        $this->connection->insert('country', ['code' => 'LS', 'name' => 'Lesotho']);
        $this->connection->insert('country', ['code' => 'LR', 'name' => 'Liberia']);
        $this->connection->insert('country', ['code' => 'LY', 'name' => 'Libyan Arab Jamahiriya']);
        $this->connection->insert('country', ['code' => 'LI', 'name' => 'Liechtenstein']);
        $this->connection->insert('country', ['code' => 'LT', 'name' => 'Lithuania']);
        $this->connection->insert('country', ['code' => 'LU', 'name' => 'Luxembourg']);
        $this->connection->insert('country', ['code' => 'MO', 'name' => 'Macau']);
        $this->connection->insert('country', ['code' => 'MK', 'name' => 'Macedonia']);
        $this->connection->insert('country', ['code' => 'MG', 'name' => 'Madagascar']);
        $this->connection->insert('country', ['code' => 'MW', 'name' => 'Malawi']);
        $this->connection->insert('country', ['code' => 'MY', 'name' => 'Malaysia']);
        $this->connection->insert('country', ['code' => 'MV', 'name' => 'Maldives']);
        $this->connection->insert('country', ['code' => 'ML', 'name' => 'Mali']);
        $this->connection->insert('country', ['code' => 'MT', 'name' => 'Malta']);
        $this->connection->insert('country', ['code' => 'MH', 'name' => 'Marshall Islands']);
        $this->connection->insert('country', ['code' => 'MQ', 'name' => 'Martinique']);
        $this->connection->insert('country', ['code' => 'MR', 'name' => 'Mauritania']);
        $this->connection->insert('country', ['code' => 'MU', 'name' => 'Mauritius']);
        $this->connection->insert('country', ['code' => 'TY', 'name' => 'Mayotte']);
        $this->connection->insert('country', ['code' => 'MX', 'name' => 'Mexico']);
        $this->connection->insert('country', ['code' => 'FM', 'name' => 'Micronesia, Federated States of']);
        $this->connection->insert('country', ['code' => 'MD', 'name' => 'Moldova, Republic of']);
        $this->connection->insert('country', ['code' => 'MC', 'name' => 'Monaco']);
        $this->connection->insert('country', ['code' => 'MN', 'name' => 'Mongolia']);
        $this->connection->insert('country', ['code' => 'ME', 'name' => 'Montenegro']);
        $this->connection->insert('country', ['code' => 'MS', 'name' => 'Montserrat']);
        $this->connection->insert('country', ['code' => 'MA', 'name' => 'Morocco']);
        $this->connection->insert('country', ['code' => 'MZ', 'name' => 'Mozambique']);
        $this->connection->insert('country', ['code' => 'MM', 'name' => 'Myanmar']);
        $this->connection->insert('country', ['code' => 'NA', 'name' => 'Namibia']);
        $this->connection->insert('country', ['code' => 'NR', 'name' => 'Nauru']);
        $this->connection->insert('country', ['code' => 'NP', 'name' => 'Nepal']);
        $this->connection->insert('country', ['code' => 'NL', 'name' => 'Netherlands']);
        $this->connection->insert('country', ['code' => 'AN', 'name' => 'Netherlands Antilles']);
        $this->connection->insert('country', ['code' => 'NC', 'name' => 'New Caledonia']);
        $this->connection->insert('country', ['code' => 'NZ', 'name' => 'New Zealand']);
        $this->connection->insert('country', ['code' => 'NI', 'name' => 'Nicaragua']);
        $this->connection->insert('country', ['code' => 'NE', 'name' => 'Niger']);
        $this->connection->insert('country', ['code' => 'NG', 'name' => 'Nigeria']);
        $this->connection->insert('country', ['code' => 'NU', 'name' => 'Niue']);
        $this->connection->insert('country', ['code' => 'NF', 'name' => 'Norfolk Island']);
        $this->connection->insert('country', ['code' => 'MP', 'name' => 'Northern Mariana Islands']);
        $this->connection->insert('country', ['code' => 'NO', 'name' => 'Norway']);
        $this->connection->insert('country', ['code' => 'OM', 'name' => 'Oman']);
        $this->connection->insert('country', ['code' => 'PK', 'name' => 'Pakistan']);
        $this->connection->insert('country', ['code' => 'PW', 'name' => 'Palau']);
        $this->connection->insert('country', ['code' => 'PS', 'name' => 'Palestine']);
        $this->connection->insert('country', ['code' => 'PA', 'name' => 'Panama']);
        $this->connection->insert('country', ['code' => 'PG', 'name' => 'Papua New Guinea']);
        $this->connection->insert('country', ['code' => 'PY', 'name' => 'Paraguay']);
        $this->connection->insert('country', ['code' => 'PE', 'name' => 'Peru']);
        $this->connection->insert('country', ['code' => 'PH', 'name' => 'Philippines']);
        $this->connection->insert('country', ['code' => 'PN', 'name' => 'Pitcairn']);
        $this->connection->insert('country', ['code' => 'PL', 'name' => 'Poland']);
        $this->connection->insert('country', ['code' => 'PT', 'name' => 'Portugal']);
        $this->connection->insert('country', ['code' => 'PR', 'name' => 'Puerto Rico']);
        $this->connection->insert('country', ['code' => 'QA', 'name' => 'Qatar']);
        $this->connection->insert('country', ['code' => 'RE', 'name' => 'Reunion']);
        $this->connection->insert('country', ['code' => 'RO', 'name' => 'Romania']);
        $this->connection->insert('country', ['code' => 'RU', 'name' => 'Russian Federation']);
        $this->connection->insert('country', ['code' => 'RW', 'name' => 'Rwanda']);
        $this->connection->insert('country', ['code' => 'KN', 'name' => 'Saint Kitts and Nevis']);
        $this->connection->insert('country', ['code' => 'LC', 'name' => 'Saint Lucia']);
        $this->connection->insert('country', ['code' => 'VC', 'name' => 'Saint Vincent and the Grenadines']);
        $this->connection->insert('country', ['code' => 'WS', 'name' => 'Samoa']);
        $this->connection->insert('country', ['code' => 'SM', 'name' => 'San Marino']);
        $this->connection->insert('country', ['code' => 'ST', 'name' => 'Sao Tome and Principe']);
        $this->connection->insert('country', ['code' => 'SA', 'name' => 'Saudi Arabia']);
        $this->connection->insert('country', ['code' => 'SN', 'name' => 'Senegal']);
        $this->connection->insert('country', ['code' => 'RS', 'name' => 'Serbia']);
        $this->connection->insert('country', ['code' => 'SC', 'name' => 'Seychelles']);
        $this->connection->insert('country', ['code' => 'SL', 'name' => 'Sierra Leone']);
        $this->connection->insert('country', ['code' => 'SG', 'name' => 'Singapore']);
        $this->connection->insert('country', ['code' => 'SK', 'name' => 'Slovakia']);
        $this->connection->insert('country', ['code' => 'SI', 'name' => 'Slovenia']);
        $this->connection->insert('country', ['code' => 'SB', 'name' => 'Solomon Islands']);
        $this->connection->insert('country', ['code' => 'SO', 'name' => 'Somalia']);
        $this->connection->insert('country', ['code' => 'ZA', 'name' => 'South Africa']);
        $this->connection->insert('country', ['code' => 'GS', 'name' => 'South Georgia South Sandwich Islands']);
        $this->connection->insert('country', ['code' => 'SS', 'name' => 'South Sudan']);
        $this->connection->insert('country', ['code' => 'ES', 'name' => 'Spain']);
        $this->connection->insert('country', ['code' => 'LK', 'name' => 'Sri Lanka']);
        $this->connection->insert('country', ['code' => 'SH', 'name' => 'St. Helena']);
        $this->connection->insert('country', ['code' => 'PM', 'name' => 'St. Pierre and Miquelon']);
        $this->connection->insert('country', ['code' => 'SD', 'name' => 'Sudan']);
        $this->connection->insert('country', ['code' => 'SR', 'name' => 'Suriname']);
        $this->connection->insert('country', ['code' => 'SJ', 'name' => 'Svalbard and Jan Mayen Islands']);
        $this->connection->insert('country', ['code' => 'SZ', 'name' => 'Swaziland']);
        $this->connection->insert('country', ['code' => 'SE', 'name' => 'Sweden']);
        $this->connection->insert('country', ['code' => 'CH', 'name' => 'Switzerland']);
        $this->connection->insert('country', ['code' => 'SY', 'name' => 'Syrian Arab Republic']);
        $this->connection->insert('country', ['code' => 'TW', 'name' => 'Taiwan']);
        $this->connection->insert('country', ['code' => 'TJ', 'name' => 'Tajikistan']);
        $this->connection->insert('country', ['code' => 'TZ', 'name' => 'Tanzania, United Republic of']);
        $this->connection->insert('country', ['code' => 'TH', 'name' => 'Thailand']);
        $this->connection->insert('country', ['code' => 'TG', 'name' => 'Togo']);
        $this->connection->insert('country', ['code' => 'TK', 'name' => 'Tokelau']);
        $this->connection->insert('country', ['code' => 'TO', 'name' => 'Tonga']);
        $this->connection->insert('country', ['code' => 'TT', 'name' => 'Trinidad and Tobago']);
        $this->connection->insert('country', ['code' => 'TN', 'name' => 'Tunisia']);
        $this->connection->insert('country', ['code' => 'TR', 'name' => 'Turkey']);
        $this->connection->insert('country', ['code' => 'TM', 'name' => 'Turkmenistan']);
        $this->connection->insert('country', ['code' => 'TC', 'name' => 'Turks and Caicos Islands']);
        $this->connection->insert('country', ['code' => 'TV', 'name' => 'Tuvalu']);
        $this->connection->insert('country', ['code' => 'UG', 'name' => 'Uganda']);
        $this->connection->insert('country', ['code' => 'UA', 'name' => 'Ukraine']);
        $this->connection->insert('country', ['code' => 'AE', 'name' => 'United Arab Emirates']);
        $this->connection->insert('country', ['code' => 'GB', 'name' => 'United Kingdom']);
        $this->connection->insert('country', ['code' => 'US', 'name' => 'United States']);
        $this->connection->insert('country', ['code' => 'UM', 'name' => 'United States minor outlying islands']);
        $this->connection->insert('country', ['code' => 'UY', 'name' => 'Uruguay']);
        $this->connection->insert('country', ['code' => 'UZ', 'name' => 'Uzbekistan']);
        $this->connection->insert('country', ['code' => 'VU', 'name' => 'Vanuatu']);
        $this->connection->insert('country', ['code' => 'VA', 'name' => 'Vatican City State']);
        $this->connection->insert('country', ['code' => 'VE', 'name' => 'Venezuela']);
        $this->connection->insert('country', ['code' => 'VN', 'name' => 'Vietnam']);
        $this->connection->insert('country', ['code' => 'VG', 'name' => 'Virgin Islands (British)']);
        $this->connection->insert('country', ['code' => 'VI', 'name' => 'Virgin Islands (U.S.)']);
        $this->connection->insert('country', ['code' => 'WF', 'name' => 'Wallis and Futuna Islands']);
        $this->connection->insert('country', ['code' => 'EH', 'name' => 'Western Sahara']);
        $this->connection->insert('country', ['code' => 'YE', 'name' => 'Yemen']);
        $this->connection->insert('country', ['code' => 'ZR', 'name' => 'Zaire']);
        $this->connection->insert('country', ['code' => 'ZM', 'name' => 'Zambia']);
        $this->connection->insert('country', ['code' => 'ZW', 'name' => 'Zimbabwe']);
    }

    public function postUp(Schema $schema) : void {
        $this->setUpCountryTable();
    }

    public function down(Schema $schema) : void
    {
        $schema->dropTable('organisation_site');
        $schema->dropTable('country');
    }

}
