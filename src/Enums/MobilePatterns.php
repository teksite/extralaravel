<?php

namespace Teksite\Extralaravel\Enums;

use Illuminate\Support\Str;

enum MobilePatterns: string
{
    case IRAN = '/^(\\+98|0|0098)?9(0[1-5]|[1-3]\\d|4[0-9]|9[0-2])\\d{7}$/';
    case AFGHANISTAN = '/^(\\+93|0)?[7][0-9]{8}$/';
    case BAHRAIN = '/^(\\+973)?(3|6|7)\\d{7}$/';
    case BANGLADESH = '/^(\\+880|0)?1[13456789]\\d{8}$/';
    case BHUTAN = '/^(\\+975|0)?17\\d{6}$/';
    case BRUNEI = '/^(\\+673)?[8-9]\\d{6}$/';
    case CAMBODIA = '/^(\\+855|0)?1[2-9]\\d{7}$/';
    case CHINA = '/^(\\+86|0)?1[3-9]\\d{9}$/';
    case EAST_TIMOR = '/^(\\+670)?7\\d{7}$/';
    case HONG_KONG = '/^(\\+852)?[5-9]\\d{7}$/';
    case INDIA = '/^(\\+91|0)?[6-9]\\d{9}$/';
    case INDONESIA = '/^(\\+62|0)?8[1-9]\\d{7,10}$/';
    case JAPAN = '/^(\\+81|0)?[7-9]0\\d{8}$/';
    case JORDAN = '/^(\\+962|0)?7[7-9]\\d{7}$/';
    case KAZAKHSTAN = '/^(\\+7|8)?7\\d{9}$/';
    case KUWAIT = '/^(\\+965)?[569]\\d{7}$/';
    case KYRGYZSTAN = '/^(\\+996|0)?7\\d{8}$/';
    case LAOS = '/^(\\+856|0)?20[2-9]\\d{7}$/';
    case LEBANON = '/^(\\+961|0)?[37]\\d{7}$/';
    case MACAU = '/^(\\+853)?6\\d{6}$/';
    case MALAYSIA = '/^(\\+60|0)?1[0-46-9]\\d{7,8}$/';
    case MALDIVES = '/^(\\+960)?7\\d{6}$/';
    case MONGOLIA = '/^(\\+976|0)?[89]\\d{7}$/';
    case MYANMAR = '/^(\\+95|0)?9[0-9]{7,9}$/';
    case NEPAL = '/^(\\+977|0)?9[78]\\d{8}$/';
    case NORTH_KOREA = '/^(\\+850)?[1-9]\\d{7}$/';
    case OMAN = '/^(\\+968)?[79]\\d{7}$/';
    case PAKISTAN = '/^(\\+92|0)?3[0-9]{9}$/';
    case PALESTINE = '/^(\\+970|0)?5[0-9]{8}$/';
    case PHILIPPINES = '/^(\\+63|0)?9\\d{9}$/';
    case QATAR = '/^(\\+974)?[3-7]\\d{7}$/';
    case SAUDI_ARABIA = '/^(\\+966|0)?5[0-9]{8}$/';
    case SINGAPORE = '/^(\\+65)?[689]\\d{7}$/';
    case SOUTH_KOREA = '/^(\\+82|0)?1[0-9]\\d{7,8}$/';
    case SRI_LANKA = '/^(\\+94|0)?7[0-9]{8}$/';
    case SYRIA = '/^(\\+963|0)?9\\d{8}$/';
    case TAIWAN = '/^(\\+886|0)?9\\d{8}$/';
    case TAJIKISTAN = '/^(\\+992|0)?[9]\\d{8}$/';
    case THAILAND = '/^(\\+66|0)?[6-9]\\d{7,8}$/';
    case TURKEY = '/^(\\+90|0)?5[0-9]{9}$/';
    case TURKMENISTAN = '/^(\\+993|0)?6\\d{7}$/';
    case UAE = '/^(\\+971|0)?5[0-9]{8}$/';
    case UZBEKISTAN = '/^(\\+998|0)?9[0-9]{8}$/';
    case VIETNAM = '/^(\\+84|0)?(3[2-9]|5[6-9]|7[0-9]|8[1-9]|9[0-9])\\d{7}$/';
    case YEMEN = '/^(\\+967|0)?7[0-9]{8}$/';

    // اروپا
    case ALBANIA = '/^(\\+355|0)?6[789]\\d{7}$/';
    case ANDORRA = '/^(\\+376)?[346-9]\\d{5}$/';
    case ARMENIA = '/^(\\+374|0)?[77|9]\\d{7}$/';
    case AUSTRIA = '/^(\\+43|0)?6[3-9]\\d{7,10}$/';
    case AZERBAIJAN = '/^(\\+994|0)?[5-7]\\d{7}$/';
    case BELARUS = '/^(\\+375|0)?(25|29|33|44)\\d{7}$/';
    case BELGIUM = '/^(\\+32|0)?4[67]\\d{7}$/';
    case BOSNIA = '/^(\\+387|0)?6[1-3]\\d{6}$/';
    case BULGARIA = '/^(\\+359|0)?8[7-9]\\d{7}$/';
    case CROATIA = '/^(\\+385|0)?9[1-9]\\d{7}$/';
    case CYPRUS = '/^(\\+357|0)?9[6-9]\\d{6}$/';
    case CZECHIA = '/^(\\+420)?[67]\\d{8}$/';
    case DENMARK = '/^(\\+45)?[2-9]\\d{7}$/';
    case ESTONIA = '/^(\\+372)?5\\d{7}$/';
    case FINLAND = '/^(\\+358|0)?4[0-9]\\d{7}$/';
    case FRANCE = '/^(\\+33|0)?[67]\\d{8}$/';
    case GEORGIA = '/^(\\+995)?5\\d{8}$/';
    case GERMANY = '/^(\\+49|0)?1[5-7]\\d{8}$/';
    case GREECE = '/^(\\+30|0)?69\\d{8}$/';
    case HUNGARY = '/^(\\+36|0)?7\\d{8}$/';
    case ICELAND = '/^(\\+354)?[6-8]\\d{6}$/';
    case IRELAND = '/^(\\+353|0)?8[35679]\\d{7}$/';
    case ITALY = '/^(\\+39)?3\\d{9}$/';
    case KOSOVO = '/^(\\+383)?4[3-9]\\d{6}$/';
    case LATVIA = '/^(\\+371)?2\\d{7}$/';
    case LIECHTENSTEIN = '/^(\\+423)?7[0-9]\\d{6}$/';
    case LITHUANIA = '/^(\\+370|0)?6\\d{7}$/';
    case LUXEMBOURG = '/^(\\+352)?6[691]\\d{6}$/';
    case MALTA = '/^(\\+356)?(77|99|79)\\d{6}$/';
    case MOLDOVA = '/^(\\+373|0)?6\\d{7}$/';
    case MONACO = '/^(\\+377)?[346-9]\\d{6}$/';
    case MONTENEGRO = '/^(\\+382)?6[0-9]\\d{6}$/';
    case NETHERLANDS = '/^(\\+31|0)?6\\d{8}$/';
    case NORTH_MACEDONIA = '/^(\\+389|0)?7[0-9]\\d{6}$/';
    case NORWAY = '/^(\\+47)?4[0-9]\\d{6}$/';
    case POLAND = '/^(\\+48|0)?[45]\\d{8}$/';
    case PORTUGAL = '/^(\\+351)?9[1236]\\d{7}$/';
    case ROMANIA = '/^(\\+40|0)?7[0-9]\\d{8}$/';
    case RUSSIA = '/^(\\+7|8)?9[0-9]{9}$/';
    case SAN_MARINO = '/^(\\+378)?6[0-9]\\d{7}$/';
    case SERBIA = '/^(\\+381|0)?6[0-9]\\d{6,7}$/';
    case SLOVAKIA = '/^(\\+421|0)?9[0-9]\\d{7}$/';
    case SLOVENIA = '/^(\\+386|0)?3[1-9]\\d{7}$/';
    case SPAIN = '/^(\\+34)?[67]\\d{8}$/';
    case SWEDEN = '/^(\\+46|0)?7[0-9]\\d{7}$/';
    case SWITZERLAND = '/^(\\+41|0)?7[0-9]\\d{7}$/';
    case UKRAINE = '/^(\\+380|0)?[67]\\d{8}$/';
    case UNITED_KINGDOM = '/^(\\+44|0)?7[1-9]\\d{8}$/';
    case VATICAN = '/^(\\+39)?06\\d{8}$/';

    case CANADA = '/^\\+1[2-9]\\d{2}[2-9]\\d{6}$/';
    case USA = '/^\\+1(2[0-9]{2}|3[0-9]{2}|4[0-9]{2}|5[0-9]{2}|6[0-9]{2}|7[0-9]{2}|8[0-9]{2}|9[0-9]{2})[2-9]\\d{6}$/';
    case MEXICO = '/^(\\+52|0)?1\\d{10}$/';
    case GREENLAND = '/^(\\+299)?[2-9]\\d{5}$/';

    case ANTIGUA = '/^\\+1(268)\\d{7}$/';
    case BAHAMAS = '/^\\+1(242)\\d{7}$/';
    case BARBADOS = '/^\\+1(246)\\d{7}$/';
    case BELIZE = '/^(\\+501)?(6|7|9)\\d{6}$/';
    case COSTA_RICA = '/^(\\+506)?[67]\\d{7}$/';
    case CUBA = '/^(\\+53)?5\\d{7}$/';
    case DOMINICA = '/^\\+1(767)\\d{7}$/';
    case DOMINICAN_REPUBLIC = '/^\\+1(809|829|849)\\d{7}$/';
    case EL_SALVADOR = '/^(\\+503)?7[0-9]{7}$/';
    case GRENADA = '/^\\+1(473)\\d{7}$/';
    case GUATEMALA = '/^(\\+502)?[3-7]\\d{7}$/';
    case HAITI = '/^(\\+509)?[3-4]\\d{7}$/';
    case HONDURAS = '/^(\\+504)?[9-8]\\d{7}$/';
    case JAMAICA = '/^\\+1(876|658)\\d{7}$/';
    case NICARAGUA = '/^(\\+505)?[5-8]\\d{7}$/';
    case PANAMA = '/^(\\+507)?[6-8]\\d{7}$/';
    case PUERTO_RICO = '/^\\+1(787|939)\\d{7}$/';
    case SAINT_KITTS = '/^\\+1(869)\\d{7}$/';
    case SAINT_LUCIA = '/^\\+1(758)\\d{7}$/';
    case TRINIDAD = '/^\\+1(868)\\d{7}$/';

    case ARGENTINA = '/^(\\+54|0)?9?1[1-9]\\d{8}$/';
    case BOLIVIA = '/^(\\+591|0)?(6|7)\\d{7}$/';
    case BRAZIL = '/^(\\+55|0)?([1-9][1-9]?)9?\\d{8}$/';
    case CHILE = '/^(\\+56|0)?9\\d{8}$/';
    case COLOMBIA = '/^(\\+57|0)?3\\d{9}$/';
    case ECUADOR = '/^(\\+593|0)?9[2-9]\\d{7}$/';
    case GUYANA = '/^(\\+592)?6\\d{6}$/';
    case PARAGUAY = '/^(\\+595|0)?9[1-9]\\d{7}$/';
    case PERU = '/^(\\+51|0)?9[0-9]{8}$/';
    case SURINAME = '/^(\\+597)?7\\d{6}$/';
    case URUGUAY = '/^(\\+598|0)?9[0-9]{7}$/';
    case VENEZUELA = '/^(\\+58|0)?4[1-4]\\d{8}$/';

    case ALGERIA = '/^(\\+213|0)?[5-7]\\d{8}$/';
    case ANGOLA = '/^(\\+244|0)?9[1-4]\\d{7}$/';
    case BENIN = '/^(\\+229)?9\\d{7}$/';
    case BOTSWANA = '/^(\\+267)?7[1-9]\\d{6}$/';
    case BURKINA_FASO = '/^(\\+226)?7[0-9]\\d{7}$/';
    case BURUNDI = '/^(\\+257)?7[1-9]\\d{6}$/';
    case CAMEROON = '/^(\\+237)?6[5-9]\\d{7}$/';
    case CAPE_VERDE = '/^(\\+238)?9[1-9]\\d{6}$/';
    case CENTRAL_AFRICAN = '/^(\\+236)?7[0-9]\\d{6}$/';
    case CHAD = '/^(\\+235)?[6-7]\\d{7}$/';
    case COMOROS = '/^(\\+269)?3[34]\\d{5}$/';
    case CONGO = '/^(\\+242)?0?\\d{9}$/';
    case DRC = '/^(\\+243)?[89]\\d{8}$/';
    case DJIBOUTI = '/^(\\+253)?77\\d{6}$/';
    case EGYPT = '/^(\\+20|0)?1[0-9]{9}$/';
    case EQUATORIAL_GUINEA = '/^(\\+240)?[56]\\d{7}$/';
    case ERITREA = '/^(\\+291)?7\\d{6}$/';
    case ESWATINI = '/^(\\+268)?7[0-9]\\d{6}$/';
    case ETHIOPIA = '/^(\\+251)?9\\d{8}$/';
    case GABON = '/^(\\+241)?0?\\d{7,8}$/';
    case GAMBIA = '/^(\\+220)?[5-9]\\d{6}$/';
    case GHANA = '/^(\\+233)?[23]\\d{8}$/';
    case GUINEA = '/^(\\+224)?6\\d{8}$/';
    case GUINEA_BISSAU = '/^(\\+245)?9\\d{6}$/';
    case IVORY_COAST = '/^(\\+225)?0[1-9]\\d{7}$/';
    case KENYA = '/^(\\+254|0)?7[0-9]\\d{8}$/';
    case LESOTHO = '/^(\\+266)?[5-8]\\d{7}$/';
    case LIBERIA = '/^(\\+231)?(88|77)\\d{7}$/';
    case LIBYA = '/^(\\+218|0)?91\\d{7}$/';
    case MADAGASCAR = '/^(\\+261)?3[2-4]\\d{7}$/';
    case MALAWI = '/^(\\+265)?(88|99|98)\\d{7}$/';
    case MALI = '/^(\\+223)?6[0-9]\\d{7}$/';
    case MAURITANIA = '/^(\\+222)?2[0-9]\\d{6}$/';
    case MAURITIUS = '/^(\\+230)?5[0-9]\\d{6}$/';
    case MOROCCO = '/^(\\+212|0)?[67]\\d{8}$/';
    case MOZAMBIQUE = '/^(\\+258)?8[45]\\d{7}$/';
    case NAMIBIA = '/^(\\+264)?81\\d{7}$/';
    case NIGER = '/^(\\+227)?9[0-9]\\d{7}$/';
    case NIGERIA = '/^(\\+234|0)?[789]\\d{9}$/';
    case RWANDA = '/^(\\+250)?7[0-9]\\d{7}$/';
    case SAO_TOME = '/^(\\+239)?9\\d{6}$/';
    case SENEGAL = '/^(\\+221)?7[0-9]\\d{7}$/';
    case SEYCHELLES = '/^(\\+248)?2[0-9]\\d{5}$/';
    case SIERRA_LEONE = '/^(\\+232)?[78]\\d{7}$/';
    case SOMALIA = '/^(\\+252)?[67]\\d{7}$/';
    case SOUTH_AFRICA = '/^(\\+27|0)?[6-8]\\d{8}$/';
    case SOUTH_SUDAN = '/^(\\+211)?9\\d{8}$/';
    case SUDAN = '/^(\\+249)?9\\d{8}$/';
    case TANZANIA = '/^(\\+255|0)?7[0-9]\\d{8}$/';
    case TOGO = '/^(\\+228)?9[0-9]\\d{7}$/';
    case TUNISIA = '/^(\\+216)?[2-9]\\d{7}$/';
    case UGANDA = '/^(\\+256|0)?7[0-9]\\d{8}$/';
    case ZAMBIA = '/^(\\+260)?9[7-9]\\d{7}$/';
    case ZIMBABWE = '/^(\\+263|0)?7[0-9]\\d{7}$/';

    case AUSTRALIA = '/^(\\+61|0)?4\\d{8}$/';
    case FIJI = '/^(\\+679)?[7-9]\\d{6}$/';
    case KIRIBATI = '/^(\\+686)?[6-9]\\d{4}$/';
    case MARSHALL_ISLANDS = '/^(\\+692)?[3-6]\\d{5}$/';
    case MICRONESIA = '/^(\\+691)?[3-8]\\d{5}$/';
    case NAURU = '/^(\\+674)?[5-8]\\d{4}$/';
    case NEW_ZEALAND = '/^(\\+64|0)?2\\d{7,9}$/';
    case PALAU = '/^(\\+680)?[5-8]\\d{5}$/';
    case PAPUA_NG = '/^(\\+675)?7\\d{7}$/';
    case SAMOA = '/^(\\+685)?[5-9]\\d{5}$/';
    case SOLOMON_ISLANDS = '/^(\\+677)?[7-8]\\d{5}$/';
    case TONGA = '/^(\\+676)?[5-8]\\d{5}$/';
    case TUVALU = '/^(\\+688)?[6-9]\\d{4}$/';
    case VANUATU = '/^(\\+678)?[5-8]\\d{5}$/';


    public function getPattern(): string
    {
        return $this->value;
    }

    public function validate(string $phone): bool
    {
        return (bool) preg_match($this->value, $phone);
    }

    public static function validateWithCountry(string $phone, string $country): bool
    {
        $country=Str::snake(strtolower($country));
        $enum = self::tryFrom($country);
        return $enum && $enum->validate($phone);
    }

    public static function detectCountry(string $phone): ?self
    {

        foreach (self::cases() as $case) {
            if ($case->validate($phone)) {
                return $case;
            }
        }
        return null;
    }
}
