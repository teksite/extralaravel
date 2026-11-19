<?php

namespace Teksite\Extralaravel\Enums;

use Generator;
use Illuminate\Support\Str;
use Teksite\Extralaravel\Support\Digits;

enum MobilePatterns
{
    case IRAN;
    case USA;
    case CANADA;
    case UNITED_KINGDOM;
    case GERMANY;
    case FRANCE;
    case TURKEY;
    case UAE;
    case INDIA;
    case AUSTRALIA;
    case AFGHANISTAN;
    case IRAQ;
    case AZERBAIJAN;
    case ARMENIA;
    case TURKMENISTAN;
    case UZBEKISTAN;
    case TAJIKISTAN;
    case KYRGYZSTAN;
    case KAZAKHSTAN;
    case PAKISTAN;
    case SAUDI_ARABIA;
    case KUWAIT;
    case QATAR;
    case BAHRAIN;
    case OMAN;
    case JORDAN;
    case LEBANON;
    case SYRIA;
    case EGYPT;
    case ISRAEL;
    case GEORGIA;
    case RUSSIA;
    case UKRAINE;
    case BELARUS;
    case ITALY;
    case SPAIN;
    case PORTUGAL;
    case NETHERLANDS;
    case BELGIUM;
    case SWITZERLAND;
    case AUSTRIA;
    case SWEDEN;
    case NORWAY;
    case DENMARK;
    case FINLAND;
    case POLAND;
    case CZECH_REPUBLIC;
    case SLOVAKIA;
    case GREECE;
    case ROMANIA;
    case HUNGARY;
    case BULGARIA;
    case SERBIA;
    case CROATIA;
    case SLOVENIA;
    case LITHUANIA;
    case LATVIA;
    case ESTONIA;
    case IRELAND;
    case CHINA;
    case HONG_KONG;
    case TAIWAN;
    case JAPAN;
    case SOUTH_KOREA;
    case SINGAPORE;
    case MALAYSIA;
    case INDONESIA;
    case THAILAND;
    case VIETNAM;
    case PHILIPPINES;
    case BANGLADESH;
    case SRI_LANKA;
    case NEPAL;
    case NEW_ZEALAND;
    case SOUTH_AFRICA;
    case NIGERIA;
    case KENYA;
    case GHANA;
    case TANZANIA;
    case MOROCCO;
    case ALGERIA;
    case TUNISIA;
    case BRAZIL;
    case MEXICO;
    case CHILE;
    case COLOMBIA;
    case PERU;
    case URUGUAY;


    /**
     * Longest normalized input worth running a regular expression on: "+" + 15 digits (E.164) + room for a
     * two-digit trunk prefix. Anything longer cannot be a valid number, so it is rejected before PCRE runs.
     */
    private const int MAX_LENGTH = 20;


    /**
     * Code point of the regional indicator symbol letter "A"; two of them in a row render as a flag emoji.
     */
    private const int REGIONAL_INDICATOR_A = 0x1F1E6;

    private const array DATA = [
        'IRAN'           => ['iso' => 'IR', 'code' => '98', 'trunk' => '0', 'label' => 'Iran', 'example' => '9123456789', 'pattern' => '/^(\+98|0098|0)?9\d{9}$/'],
        'USA'            => ['iso' => 'US', 'code' => '1', 'trunk' => '', 'label' => 'United States', 'example' => '2015550123', 'pattern' => '/^(\+1)?[2-9]\d{9}$/'],
        'CANADA'         => ['iso' => 'CA', 'code' => '1', 'trunk' => '', 'label' => 'Canada', 'example' => '5062345678', 'pattern' => '/^(\+1)?[2-9]\d{9}$/'],
        'UNITED_KINGDOM' => ['iso' => 'GB', 'code' => '44', 'trunk' => '0', 'label' => 'United Kingdom', 'example' => '7400123456', 'pattern' => '/^(\+44|0)?7\d{9}$/'],
        'GERMANY'        => ['iso' => 'DE', 'code' => '49', 'trunk' => '0', 'label' => 'Germany', 'example' => '15123456789', 'pattern' => '/^(\+49|0)?1[5-7]\d{8,10}$/'],
        'FRANCE'         => ['iso' => 'FR', 'code' => '33', 'trunk' => '0', 'label' => 'France', 'example' => '612345678', 'pattern' => '/^(\+33|0)?[67]\d{8}$/'],
        'TURKEY'         => ['iso' => 'TR', 'code' => '90', 'trunk' => '0', 'label' => 'Turkey', 'example' => '5012345678', 'pattern' => '/^(\+90|0)?5\d{9}$/'],
        'UAE'            => ['iso' => 'AE', 'code' => '971', 'trunk' => '0', 'label' => 'United Arab Emirates', 'example' => '501234567', 'pattern' => '/^(\+971|0)?5\d{8}$/'],
        'INDIA'          => ['iso' => 'IN', 'code' => '91', 'trunk' => '0', 'label' => 'India', 'example' => '8123456789', 'pattern' => '/^(\+91|0)?[6-9]\d{9}$/'],
        'AUSTRALIA'      => ['iso' => 'AU', 'code' => '61', 'trunk' => '0', 'label' => 'Australia', 'example' => '412345678', 'pattern' => '/^(\+61|0)?4\d{8}$/'],
        'AFGHANISTAN'    => ['iso' => 'AF', 'code' => '93', 'trunk' => '0', 'label' => 'Afghanistan', 'example' => '701234567', 'pattern' => '/^(?:\+93|0)?(?:7\d{8})$/'],
        'IRAQ'           => ['iso' => 'IQ', 'code' => '964', 'trunk' => '0', 'label' => 'Iraq', 'example' => '7912345678', 'pattern' => '/^(?:\+964|0)?(?:7[3-9]\d{8})$/'],
        'AZERBAIJAN'     => ['iso' => 'AZ', 'code' => '994', 'trunk' => '0', 'label' => 'Azerbaijan', 'example' => '401234567', 'pattern' => '/^(?:\+994|0)?(?:36554\d{4}|(?:[16]0|4[04]|5[015]|7[07]|99)\d{7})$/'],
        'ARMENIA'        => ['iso' => 'AM', 'code' => '374', 'trunk' => '0', 'label' => 'Armenia', 'example' => '77123456', 'pattern' => '/^(?:\+374|0)?(?:(?:33|4[1349]|55|77|88|9[13-9])\d{6})$/'],
        'TURKMENISTAN'   => ['iso' => 'TM', 'code' => '993', 'trunk' => '8', 'label' => 'Turkmenistan', 'example' => '66123456', 'pattern' => '/^(?:\+993|8)?(?:(?:6\d|7[12])\d{6})$/'],
        'UZBEKISTAN'     => ['iso' => 'UZ', 'code' => '998', 'trunk' => '', 'label' => 'Uzbekistan', 'example' => '912345678', 'pattern' => '/^(?:\+998)?(?:(?:(?:[25]0|33|8[078]|9[0-57-9])\d{3}|6(?:1(?:2(?:2[01]|98)|35[0-4]|50\d|61[23]|7(?:[01][017]|4\d|55|9[5-9]))|2(?:(?:11|7\d)\d|2(?:[12]1|9[01379])|5(?:[126]\d|3[0-4]))|5(?:19[01]|2(?:27|9[26])|(?:30|59|7\d)\d)|6(?:2(?:1[5-9]|2[0367]|38|41|52|60)|(?:3[79]|9[0-3])\d|4(?:56|83)|7(?:[07]\d|1[017]|3[07]|4[047]|5[057]|67|8[0178]|9[79]))|7(?:2(?:24|3[237]|4[5-9]|7[15-8])|5(?:7[12]|8[0589])|7(?:0\d|[39][07])|9(?:0\d|7[079])))|7(?:[07]\d{3}|2(?:2(?:2[79]|95)|3(?:2[5-9]|6[0-6])|57\d|7(?:0\d|1[17]|2[27]|3[37]|44|5[057]|66|88))|3(?:2(?:1[0-6]|21|3[469]|7[159])|(?:33|9[4-6])\d|5(?:0[0-4]|5[579]|9\d)|7(?:[0-3579]\d|4[0467]|6[67]|8[078]))|4(?:2(?:29|5[0257]|6[0-7]|7[1-57])|5(?:1[0-4]|8\d|9[5-9])|7(?:0\d|1[024589]|2[0-27]|3[0137]|[46][07]|5[01]|7[5-9]|9[079])|9(?:7[015-9]|[89]\d))|5(?:112|2(?:0\d|2[29]|[49]4)|3[1568]\d|52[6-9]|7(?:0[01578]|1[017]|[23]7|4[047]|[5-7]\d|8[78]|9[079]))|9(?:22[128]|3(?:2[0-4]|7\d)|57[02569]|7(?:2[05-9]|3[37]|4\d|60|7[2579]|87|9[07]))))\d{4})$/'],
        'TAJIKISTAN'     => ['iso' => 'TJ', 'code' => '992', 'trunk' => '', 'label' => 'Tajikistan', 'example' => '917123456', 'pattern' => '/^(?:\+992)?(?:(?:33[03-9]|4(?:1[18]|4[02-479])|81[1-9])\d{6}|(?:[09]\d|1[0-27-9]|2[0-27]|3[08]|40|5[05]|66|7[0157-9]|8[07-9])\d{7})$/'],
        'KYRGYZSTAN'     => ['iso' => 'KG', 'code' => '996', 'trunk' => '0', 'label' => 'Kyrgyzstan', 'example' => '700123456', 'pattern' => '/^(?:\+996|0)?(?:312(?:58\d|973)\d{3}|(?:2(?:0[0-35]|2\d)|5[0-24-7]\d|600|7(?:[07]\d|55)|88[08]|9(?:12|9[05-9]))\d{6})$/'],
        'KAZAKHSTAN'     => ['iso' => 'KZ', 'code' => '7', 'trunk' => '8', 'label' => 'Kazakhstan', 'example' => '7710009998', 'pattern' => '/^(?:\+7|8)?(?:7(?:0[0-25-8]|47|6[0-4]|7[15-8]|85)\d{7})$/'],
        'PAKISTAN'       => ['iso' => 'PK', 'code' => '92', 'trunk' => '0', 'label' => 'Pakistan', 'example' => '3012345678', 'pattern' => '/^(?:\+92|0)?(?:3(?:[0-2479]\d|3[0-79]|55|64)\d{7})$/'],
        'SAUDI_ARABIA'   => ['iso' => 'SA', 'code' => '966', 'trunk' => '0', 'label' => 'Saudi Arabia', 'example' => '512345678', 'pattern' => '/^(?:\+966|0)?(?:579[0-8]\d{5}|5(?:[013-689]\d|7[0-8])\d{6})$/'],
        'KUWAIT'         => ['iso' => 'KW', 'code' => '965', 'trunk' => '', 'label' => 'Kuwait', 'example' => '50012345', 'pattern' => '/^(?:\+965)?(?:(?:41\d\d|5(?:(?:[05]\d|1[0-7]|6[56])\d|2(?:22|5[25])|7(?:55|77)|88[58]|999)|6(?:(?:0[034679]|5[015-9]|6\d)\d|1(?:00|11|6[16])|2[26]2|3[36]3|4[46]4|7(?:0[013-9]|[67]\d)|8[68]8|9(?:[069]\d|3[039]))|9(?:(?:[04679]\d|8[057-9])\d|1(?:00|1[01]|99)|2(?:00|2\d)|3(?:00|3[03])|5(?:00|5\d)))\d{4})$/'],
        'QATAR'          => ['iso' => 'QA', 'code' => '974', 'trunk' => '', 'label' => 'Qatar', 'example' => '33123456', 'pattern' => '/^(?:\+974)?(?:[35-7]\d{7})$/'],
        'BAHRAIN'        => ['iso' => 'BH', 'code' => '973', 'trunk' => '', 'label' => 'Bahrain', 'example' => '36001234', 'pattern' => '/^(?:\+973)?(?:(?:3(?:[0-79]\d|8[0-57-9])\d|6(?:3(?:00|33|6[16])|441|6(?:3[03-9]|[69]\d|7[0-689])))\d{4})$/'],
        'OMAN'           => ['iso' => 'OM', 'code' => '968', 'trunk' => '', 'label' => 'Oman', 'example' => '92123456', 'pattern' => '/^(?:\+968)?(?:(?:1505|90[1-9]\d)\d{4}|(?:7[124-9]|9[1-9])\d{6})$/'],
        'JORDAN'         => ['iso' => 'JO', 'code' => '962', 'trunk' => '0', 'label' => 'Jordan', 'example' => '790123456', 'pattern' => '/^(?:\+962|0)?(?:(?:427|7(?:11|[29]\d|[78][0-25-9]))\d{6})$/'],
        'LEBANON'        => ['iso' => 'LB', 'code' => '961', 'trunk' => '0', 'label' => 'Lebanon', 'example' => '71123456', 'pattern' => '/^(?:\+961|0)?(?:(?:(?:3|81)\d|7(?:[01]\d|6[013-9]|8[7-9]|9[0-4]))\d{5})$/'],
        'SYRIA'          => ['iso' => 'SY', 'code' => '963', 'trunk' => '0', 'label' => 'Syria', 'example' => '944567890', 'pattern' => '/^(?:\+963|0)?(?:(?:50|9[03-689])\d{7})$/'],
        'EGYPT'          => ['iso' => 'EG', 'code' => '20', 'trunk' => '0', 'label' => 'Egypt', 'example' => '1001234567', 'pattern' => '/^(?:\+20|0)?(?:1[0-25]\d{8})$/'],
        'ISRAEL'         => ['iso' => 'IL', 'code' => '972', 'trunk' => '0', 'label' => 'Israel', 'example' => '502345678', 'pattern' => '/^(?:\+972|0)?(?:55(?:4(?:0[0-4]|10|6[015-9])|57[0-289]|8[06][0-2])\d{4}|5(?:(?:[0-2][02-9]|[36]\d|[49][2-9]|8[3-7])\d|5(?:01|2\d|3[0-3]|4[3-5]|5[0-25689]|6[6-8]|7[0-267]|8[7-9]|9[1-9]))\d{5})$/'],
        'GEORGIA'        => ['iso' => 'GE', 'code' => '995', 'trunk' => '0', 'label' => 'Georgia', 'example' => '555123456', 'pattern' => '/^(?:\+995|0)?(?:5(?:(?:(?:0555|1(?:[17]77|555))[5-9]|757(?:7[7-9]|8[01]))\d|22252[0-4])\d\d|5(?:0(?:0(?:1[09]|70)|505)|1(?:0[01]0|1(?:07|33|51))|2(?:0[02]0|2[25]2)|3(?:0[03]0|3[35]3)|4(?:0[04]0|411)|5222|9000)[0-4]\d{3}|(?:5(?:0(?:0(?:0\d|1[12]|2[02]|3[0-6]|4[04]|5[05]|77|88|9[09])|(?:[14]\d|77)\d|22[02]|888)|1(?:1(?:[03][01]|[124]\d|5[02-6]|7[0-6])|4\d\d)|2(?:228|555)|3555|4(?:4(?:[02-9]\d|14)|555)|5(?:[0157-9]\d\d|200|333|4(?:44|55))|6[89]\d\d|7(?:(?:[0147-9]\d|22)\d|5(?:00|[57]5))|8(?:0(?:[018]\d|2[0-4])|5(?:55|8[89])|8(?:55|88))|9(?:090|[1-35-9]\d\d))|790\d\d)\d{4})$/'],
        'RUSSIA'         => ['iso' => 'RU', 'code' => '7', 'trunk' => '8', 'label' => 'Russia', 'example' => '9123456789', 'pattern' => '/^(?:\+7|8)?(?:9\d{9})$/'],
        'UKRAINE'        => ['iso' => 'UA', 'code' => '380', 'trunk' => '0', 'label' => 'Ukraine', 'example' => '501234567', 'pattern' => '/^(?:\+380|0)?(?:790\d{6}|(?:39|50|6[36-8]|7[1-357]|9[1-9])\d{7})$/'],
        'BELARUS'        => ['iso' => 'BY', 'code' => '375', 'trunk' => '8', 'label' => 'Belarus', 'example' => '294911911', 'pattern' => '/^(?:\+375|8)?(?:(?:2(?:5[5-79]|9[1-9])|(?:33|44)\d)\d{6})$/'],
        'ITALY'          => ['iso' => 'IT', 'code' => '39', 'trunk' => '', 'label' => 'Italy', 'example' => '3123456789', 'pattern' => '/^(?:\+39)?(?:3[2-9]\d{7,8}|(?:31|43)\d{8})$/'],
        'SPAIN'          => ['iso' => 'ES', 'code' => '34', 'trunk' => '', 'label' => 'Spain', 'example' => '612345678', 'pattern' => '/^(?:\+34)?(?:9(?:6906(?:09|10)|7390\d\d)\d\d|(?:6\d|7[1-48])\d{7})$/'],
        'PORTUGAL'       => ['iso' => 'PT', 'code' => '351', 'trunk' => '', 'label' => 'Portugal', 'example' => '912345678', 'pattern' => '/^(?:\+351)?(?:6(?:[06]92(?:30|9\d)|[35]92(?:[049]\d|3[034]))\d{3}|(?:(?:16|6[0356])93|9(?:[1-36]\d\d|480))\d{5})$/'],
        'NETHERLANDS'    => ['iso' => 'NL', 'code' => '31', 'trunk' => '0', 'label' => 'Netherlands', 'example' => '612345678', 'pattern' => '/^(?:\+31|0)?(?:(?:6[1-58]|970\d)\d{7})$/'],
        'BELGIUM'        => ['iso' => 'BE', 'code' => '32', 'trunk' => '0', 'label' => 'Belgium', 'example' => '450001234', 'pattern' => '/^(?:\+32|0)?(?:4[5-9]\d{7})$/'],
        'SWITZERLAND'    => ['iso' => 'CH', 'code' => '41', 'trunk' => '0', 'label' => 'Switzerland', 'example' => '781234567', 'pattern' => '/^(?:\+41|0)?(?:(?:6[89]|7[235-9])\d{7})$/'],
        'AUSTRIA'        => ['iso' => 'AT', 'code' => '43', 'trunk' => '0', 'label' => 'Austria', 'example' => '664123456', 'pattern' => '/^(?:\+43|0)?(?:6(?:485|(?:5[0-3579]|6[013-9]|[7-9]\d)\d)\d{3,9})$/'],
        'SWEDEN'         => ['iso' => 'SE', 'code' => '46', 'trunk' => '0', 'label' => 'Sweden', 'example' => '701234567', 'pattern' => '/^(?:\+46|0)?(?:7[023689]\d{7})$/'],
        'NORWAY'         => ['iso' => 'NO', 'code' => '47', 'trunk' => '', 'label' => 'Norway', 'example' => '40612345', 'pattern' => '/^(?:\+47)?(?:45(?:[0-24-9]\d|3[0-57-9])\d{4}|(?:4[016-8]|9\d)\d{6})$/'],
        'DENMARK'        => ['iso' => 'DK', 'code' => '45', 'trunk' => '', 'label' => 'Denmark', 'example' => '34412345', 'pattern' => '/^(?:\+45)?(?:(?:2[6-8]|6[78]|96)\d{6}|(?:2[0-59]|3[0-689]|[457]\d|6[0-69]|8[126-9]|9[1-47-9])[1-9]\d{5})$/'],
        'FINLAND'        => ['iso' => 'FI', 'code' => '358', 'trunk' => '0', 'label' => 'Finland', 'example' => '412345678', 'pattern' => '/^(?:\+358|0)?(?:(?:4[0-8]|50)\d{4,8})$/'],
        'POLAND'         => ['iso' => 'PL', 'code' => '48', 'trunk' => '', 'label' => 'Poland', 'example' => '512345678', 'pattern' => '/^(?:\+48)?(?:21(?:1[013-5]|2\d|3[1-9])\d{5}|(?:45|5[0137]|6[069]|7[2389]|88)\d{7})$/'],
        'CZECH_REPUBLIC' => ['iso' => 'CZ', 'code' => '420', 'trunk' => '', 'label' => 'Czech Republic', 'example' => '601123456', 'pattern' => '/^(?:\+420)?(?:7060\d{5}|(?:60[1-8]|7(?:0[2-5]|19|[2379]\d))\d{6})$/'],
        'SLOVAKIA'       => ['iso' => 'SK', 'code' => '421', 'trunk' => '0', 'label' => 'Slovakia', 'example' => '912123456', 'pattern' => '/^(?:\+421|0)?(?:909[1-9]\d{5}|9(?:0[1-8]|1[0-24-9]|4[03-57-9]|5\d)\d{6})$/'],
        'GREECE'         => ['iso' => 'GR', 'code' => '30', 'trunk' => '', 'label' => 'Greece', 'example' => '6912345678', 'pattern' => '/^(?:\+30)?(?:68[57-9]\d{7}|(?:69|94)\d{8})$/'],
        'ROMANIA'        => ['iso' => 'RO', 'code' => '40', 'trunk' => '0', 'label' => 'Romania', 'example' => '712034567', 'pattern' => '/^(?:\+40|0)?(?:7020\d{5}|7(?:0[013-9]|1[0-3]|[2-7]\d|8[03-8]|9[0-39])\d{6})$/'],
        'HUNGARY'        => ['iso' => 'HU', 'code' => '36', 'trunk' => '06', 'label' => 'Hungary', 'example' => '201234567', 'pattern' => '/^(?:\+36|06)?(?:(?:[257]0|3[01])\d{7})$/'],
        'BULGARIA'       => ['iso' => 'BG', 'code' => '359', 'trunk' => '0', 'label' => 'Bulgaria', 'example' => '43012345', 'pattern' => '/^(?:\+359|0)?(?:(?:43[07-9]|99[69]\d)\d{5}|(?:8[7-9]|98)\d{7})$/'],
        'SERBIA'         => ['iso' => 'RS', 'code' => '381', 'trunk' => '0', 'label' => 'Serbia', 'example' => '601234567', 'pattern' => '/^(?:\+381|0)?(?:6(?:[0-689]|7\d)\d{6,7})$/'],
        'CROATIA'        => ['iso' => 'HR', 'code' => '385', 'trunk' => '0', 'label' => 'Croatia', 'example' => '921234567', 'pattern' => '/^(?:\+385|0)?(?:9(?:(?:0[1-9]|[12589]\d)\d\d|7(?:[0679]\d\d|5(?:[01]\d|44|55|77|9[5-79])))\d{4}|98\d{6})$/'],
        'SLOVENIA'       => ['iso' => 'SI', 'code' => '386', 'trunk' => '0', 'label' => 'Slovenia', 'example' => '31234567', 'pattern' => '/^(?:\+386|0)?(?:65(?:[178]\d|5[56]|6[01])\d{4}|(?:[37][01]|4[013]|51|6[489])\d{6})$/'],
        'LITHUANIA'      => ['iso' => 'LT', 'code' => '370', 'trunk' => '0', 'label' => 'Lithuania', 'example' => '61234567', 'pattern' => '/^(?:\+370|0)?(?:6\d{7})$/'],
        'LATVIA'         => ['iso' => 'LV', 'code' => '371', 'trunk' => '', 'label' => 'Latvia', 'example' => '21234567', 'pattern' => '/^(?:\+371)?(?:2333[0-8]\d{3}|2(?:[0-24-9]\d\d|3(?:0[07]|[14-9]\d|2[02-9]|3[0-24-9]))\d{4})$/'],
        'ESTONIA'        => ['iso' => 'EE', 'code' => '372', 'trunk' => '', 'label' => 'Estonia', 'example' => '51234567', 'pattern' => '/^(?:\+372)?(?:(?:5\d{5}|8(?:1(?:0(?:0(?:00|[178]\d)|[3-9]\d\d)|(?:1(?:0[2-6]|1\d)|[2-79]\d\d)\d)|2(?:0(?:0(?:00|4\d)|[1-7]\d\d)|(?:(?:[124-69]\d|3[5-9])\d|7(?:[0-79]\d|8[013-9])|8(?:[2-6]\d|7[01]))\d)|[349]\d{4}))\d\d|5(?:(?:[02]\d|5[0-478])\d|1(?:[0-8]\d|95)|4[67]0|6(?:4[0-4]|5[1-589]))\d{3})$/'],
        'IRELAND'        => ['iso' => 'IE', 'code' => '353', 'trunk' => '0', 'label' => 'Ireland', 'example' => '850123456', 'pattern' => '/^(?:\+353|0)?(?:8(?:22|[35-9]\d)\d{6})$/'],
        'CHINA'          => ['iso' => 'CN', 'code' => '86', 'trunk' => '0', 'label' => 'China', 'example' => '13123456789', 'pattern' => '/^(?:\+86|0)?(?:1(?:610\d|740[0-5])\d{6}|1(?:[38]\d|4[57]|[59][0-35-9]|6[25-7]|7[0-35-8])\d{8})$/'],
        'HONG_KONG'      => ['iso' => 'HK', 'code' => '852', 'trunk' => '', 'label' => 'Hong Kong', 'example' => '51234567', 'pattern' => '/^(?:\+852)?(?:(?:4(?:0(?:6[0-3]|9[3-6])|24[3-6]|44[0-35-9]|6(?:4[0-57-9]|6[0-6])|7(?:4[0-48]|6[0-5]))|5(?:25[3-7]|35[4-8]|73[0-6]|95[0-8])|6(?:26[013-8]|(?:66|78)[0-5])|7(?:0(?:7[1-8]|8[0-8])|10[1-4])|84(?:4[0-2]|5[01]|8[0-35-9])|9(?:29[013-9]|39[014-9]|59[0-467]|899))\d{4}|(?:4(?:4[0-35-9]|6[0-357-9]|7[0-35])|5(?:[1-59][0-46-9]|6[0-4689]|7[0-246-9])|6(?:0[1-9]|[13-59]\d|[268][0-57-9]|7[0-79])|70[1-59]|84[0-39]|9(?:0[1-9]|1[02-9]|[2358][0-8]|[467]\d))\d{5})$/'],
        'TAIWAN'         => ['iso' => 'TW', 'code' => '886', 'trunk' => '0', 'label' => 'Taiwan', 'example' => '912345678', 'pattern' => '/^(?:\+886|0)?(?:(?:40001[0-2]|9[0-8]\d{4})\d{3})$/'],
        'JAPAN'          => ['iso' => 'JP', 'code' => '81', 'trunk' => '0', 'label' => 'Japan', 'example' => '9012345678', 'pattern' => '/^(?:\+81|0)?(?:[6-9]0[1-9]\d{7})$/'],
        'SOUTH_KOREA'    => ['iso' => 'KR', 'code' => '82', 'trunk' => '0', 'label' => 'South Korea', 'example' => '1020000000', 'pattern' => '/^(?:\+82|0)?(?:1(?:05(?:[0-8]\d|9[0-6])|22[13]\d)\d{4,5}|1(?:0[0-46-9]|[16-9]\d|2[013-9])\d{6,7})$/'],
        'SINGAPORE'      => ['iso' => 'SG', 'code' => '65', 'trunk' => '', 'label' => 'Singapore', 'example' => '81234567', 'pattern' => '/^(?:\+65)?(?:80[1-9]\d{5}|(?:8[1-9]|9[0-8])\d{6})$/'],
        'MALAYSIA'       => ['iso' => 'MY', 'code' => '60', 'trunk' => '0', 'label' => 'Malaysia', 'example' => '123456789', 'pattern' => '/^(?:\+60|0)?(?:1(?:(?:1888[689]|4400|8(?:47|8[27])[0-4])\d{4}|9\d{7,8})|1(?:0(?:[23568]\d|4[0-6]|7[016-9]|9[0-8])|1(?:[1-5]\d\d|6(?:0[5-9]|[1-9]\d)|7(?:[0-4]\d|5[0-79]|6[02-4]|8[02-5]))|(?:[26]\d|[37][1-9]|4[235-9])\d|5(?:31|9\d\d)|8(?:1[23]|[236]\d|4[06]|5(?:46|[7-9])|7[016-9]|8[01]|9[0-8]))\d{5})$/'],
        'INDONESIA'      => ['iso' => 'ID', 'code' => '62', 'trunk' => '0', 'label' => 'Indonesia', 'example' => '812345678', 'pattern' => '/^(?:\+62|0)?(?:8[1-35-9]\d{7,10})$/'],
        'THAILAND'       => ['iso' => 'TH', 'code' => '66', 'trunk' => '0', 'label' => 'Thailand', 'example' => '812345678', 'pattern' => '/^(?:\+66|0)?(?:(?:(?:14|[89]\d)\d\d|6(?:[1-6]\d\d|7(?:1[0-8]|2[4-7]|3[1-6])))\d{5})$/'],
        'VIETNAM'        => ['iso' => 'VN', 'code' => '84', 'trunk' => '0', 'label' => 'Vietnam', 'example' => '912345678', 'pattern' => '/^(?:\+84|0)?(?:121[0-3]\d{5}|(?:1[46]0|(?:3\d|7[06-9])\d|5(?:[1689]\d|2[238]|59)|8(?:[1-8]\d|9[6-9])|9(?:[0-8]\d|9[013-9]))\d{6})$/'],
        'PHILIPPINES'    => ['iso' => 'PH', 'code' => '63', 'trunk' => '0', 'label' => 'Philippines', 'example' => '9051234567', 'pattern' => '/^(?:\+63|0)?(?:(?:8(?:1[37]|9[5-8])|9(?:0[5-9]|1[0-24-9]|[235-7]\d|4[2-9]|8[135-9]|9[1-9]))\d{7})$/'],
        'BANGLADESH'     => ['iso' => 'BD', 'code' => '880', 'trunk' => '0', 'label' => 'Bangladesh', 'example' => '1812345678', 'pattern' => '/^(?:\+880|0)?(?:(?:1[13-9]|64)\d{8}|(?:3[78]|44|66)[02-9]\d{7})$/'],
        'SRI_LANKA'      => ['iso' => 'LK', 'code' => '94', 'trunk' => '0', 'label' => 'Sri Lanka', 'example' => '712345678', 'pattern' => '/^(?:\+94|0)?(?:7(?:[0-25-8]\d|4[0-4])\d{6})$/'],
        'NEPAL'          => ['iso' => 'NP', 'code' => '977', 'trunk' => '0', 'label' => 'Nepal', 'example' => '9841234567', 'pattern' => '/^(?:\+977|0)?(?:9(?:00|6[0-3]|7[0-24-6]|8[0-24-68])\d{7})$/'],
        'NEW_ZEALAND'    => ['iso' => 'NZ', 'code' => '64', 'trunk' => '0', 'label' => 'New Zealand', 'example' => '211234567', 'pattern' => '/^(?:\+64|0)?(?:2(?:[0-27-9]\d|6)\d{6,7}|2(?:1\d|75)\d{5})$/'],
        'SOUTH_AFRICA'   => ['iso' => 'ZA', 'code' => '27', 'trunk' => '0', 'label' => 'South Africa', 'example' => '711234567', 'pattern' => '/^(?:\+27|0)?(?:(?:1(?:3492[0-25]|4495[0235]|549(?:20|5[01]))|4[34]492[01])\d{3}|8[1-4]\d{3,7}|(?:2[27]|47|54)4950\d{3}|(?:1(?:049[2-4]|9[12]\d\d)|(?:50[0-2]|[67]\d\d)\d\d|8(?:5\d{3}|7(?:08[67]|158|28[5-9]|310)))\d{4}|(?:1[6-8]|28|3[2-69]|4[025689]|5[36-8])4920\d{3}|(?:12|[2-5]1)492\d{4})$/'],
        'NIGERIA'        => ['iso' => 'NG', 'code' => '234', 'trunk' => '0', 'label' => 'Nigeria', 'example' => '8021234567', 'pattern' => '/^(?:\+234|0)?(?:(?:702[0-24-9]|819[01])\d{6}|(?:7(?:0[13-9]|[12]\d)|8(?:0[1-9]|1[0-8])|9(?:0[1-9]|1[0-6]))\d{7})$/'],
        'KENYA'          => ['iso' => 'KE', 'code' => '254', 'trunk' => '0', 'label' => 'Kenya', 'example' => '712123456', 'pattern' => '/^(?:\+254|0)?(?:(?:1(?:0[0-8]|[18]\d|2[014]|3[05-9]|4[0-6])|7\d\d)\d{6})$/'],
        'GHANA'          => ['iso' => 'GH', 'code' => '233', 'trunk' => '0', 'label' => 'Ghana', 'example' => '231234567', 'pattern' => '/^(?:\+233|0)?(?:(?:2(?:[0346-9]\d|5[67])|5(?:[03-7]\d|9[1-9]))\d{6})$/'],
        'TANZANIA'       => ['iso' => 'TZ', 'code' => '255', 'trunk' => '0', 'label' => 'Tanzania', 'example' => '621234567', 'pattern' => '/^(?:\+255|0)?(?:(?:6[0-35-9]|7\d)\d{7})$/'],
        'MOROCCO'        => ['iso' => 'MA', 'code' => '212', 'trunk' => '0', 'label' => 'Morocco', 'example' => '650123456', 'pattern' => '/^(?:\+212|0)?(?:(?:6(?:[0-79]\d|8[0-247-9])|7(?:[016-8]\d|2[0-8]|3[01]|5[0-5]))\d{6})$/'],
        'ALGERIA'        => ['iso' => 'DZ', 'code' => '213', 'trunk' => '0', 'label' => 'Algeria', 'example' => '551234567', 'pattern' => '/^(?:\+213|0)?(?:5(?:4[0-29]|6[0-5])\d{6}|(?:55|6\d|7[7-9])\d{7})$/'],
        'TUNISIA'        => ['iso' => 'TN', 'code' => '216', 'trunk' => '', 'label' => 'Tunisia', 'example' => '20123456', 'pattern' => '/^(?:\+216)?(?:3(?:001|[12]40)\d{4}|(?:(?:[259]\d|4[0-8])\d|3(?:1[1-35]|6[0-4]|91))\d{5})$/'],
        'BRAZIL'         => ['iso' => 'BR', 'code' => '55', 'trunk' => '0', 'label' => 'Brazil', 'example' => '11961234567', 'pattern' => '/^(?:\+55|0)?(?:(?:[14689][1-9]|2[12478]|3[1-578]|5[13-5]|7[13-579])(?:7|9\d)\d{7})$/'],
        'MEXICO'         => ['iso' => 'MX', 'code' => '52', 'trunk' => '', 'label' => 'Mexico', 'example' => '2221234567', 'pattern' => '/^(?:\+52)?(?:(?:2(?:2\d|3[1-35-8]|4[13-9]|7[1-689]|8[1-578]|9[467])|3(?:1[1-79]|[2458][1-9]|3\d|7[1-8]|9[1-5])|4(?:1[1-57-9]|[267][1-9]|3[1-8]|[45]\d|8[1-35-9]|9[2-689])|5(?:[56]\d|88|9[1-79])|6(?:1[2-68]|[2-4][1-9]|5[1-36-9]|6[0-57-9]|7[1-7]|8[67]|9[4-8])|7(?:[1346][1-9]|[27]\d|5[13-9]|8[1-69]|9[17])|8(?:1\d|2[13-689]|3[1-6]|4[124-6]|6[1246-9]|7[0-378]|9[12479])|9(?:1[346-9]|2[1-4]|3[2-46-8]|5[1348]|[69]\d|7[12]|8[1-8]))\d{7})$/'],
        'CHILE'          => ['iso' => 'CL', 'code' => '56', 'trunk' => '', 'label' => 'Chile', 'example' => '221234567', 'pattern' => '/^(?:\+56)?(?:2(?:1982[0-6]|3314[05-9])\d{3}|(?:2(?:1(?:160|962)|3(?:(?:[24]\d|50)\d|3(?:[0346-9]\d|1[0-35-9]|2[1-9]|5[0-24-9])|600)|646[59])|80[1-8]\d\d|9(?:(?:10[0-2]|7[1-9]\d)\d|3(?:[0-57-9]\d\d|6(?:0[02-9]|[1-9]\d))|6(?:[0-8]\d\d|9(?:[02-79]\d|1[05-9]))|9(?:[03-9]\d\d|1(?:[0235-9]\d|4[0-24-9])|2(?:[0-79]\d|8[0-46-9]))))\d{4}|(?:22|3[2-5]|[47][1-35]|5[1-3578]|6[13-57]|8[1-9]|9[2458])\d{7})$/'],
        'COLOMBIA'       => ['iso' => 'CO', 'code' => '57', 'trunk' => '0', 'label' => 'Colombia', 'example' => '3211234567', 'pattern' => '/^(?:\+57|0)?(?:333301[0-5]\d{3}|3333(?:00|2[5-9]|[3-9]\d)\d{4}|(?:3(?:24[1-9]|3(?:00|3[0-24-9]))|9408)\d{6}|3(?:0[0-5]|1\d|2[0-3]|5[01]|70)\d{7})$/'],
        'PERU'           => ['iso' => 'PE', 'code' => '51', 'trunk' => '0', 'label' => 'Peru', 'example' => '912345678', 'pattern' => '/^(?:\+51|0)?(?:9\d{8})$/'],
        'URUGUAY'        => ['iso' => 'UY', 'code' => '598', 'trunk' => '0', 'label' => 'Uruguay', 'example' => '94231234', 'pattern' => '/^(?:\+598|0)?(?:9[1-9]\d{6})$/'],
    ];

    /**
     * International dial code without "+" (e.g. "98"). The USA and Canada share "1", Russia and Kazakhstan "7".
     */
    public function code(): string
    {
        return self::DATA[$this->name]['code'];
    }

    /**
     * ISO 3166-1 alpha-2 code (e.g. "IR").
     */
    public function iso(): string
    {
        return self::DATA[$this->name]['iso'];
    }

    public function pattern(): string
    {
        return self::DATA[$this->name]['pattern'];
    }

    /**
     * Country name.
     */
    public function label(): string
    {
        return self::DATA[$this->name]['label'];
    }

    /**
     * Trunk prefix dialed before a national number (e.g. "0"), or an empty string when the country has none.
     */
    public function nationalPrefix(): string
    {
        return self::DATA[$this->name]['trunk'];
    }


    /**
     * Flag emoji built from the ISO code (e.g. "🇮🇷").
     *
     * Some platforms (notably Windows) render the two letters instead of a flag.
     */
    public function flag(): string
    {
        $iso = $this->iso();

        return $this->regionalIndicator($iso[0]).$this->regionalIndicator($iso[1]);
    }


    /**
     * A valid sample mobile number, useful for placeholders and documentation.
     *
     * @param bool $international Return "+CC..." instead of the national form (trunk prefix + number).
     */
    public function example(bool $international = false): string
    {
        $national = self::DATA[$this->name]['example'];

        return $international ? '+'.$this->code().$national : $this->nationalPrefix().$national;
    }

    /**
     * Normalize the raw input and test it against this country's pattern.
     */
    public function validate(string $phone): bool
    {
        return $this->matches(self::normalize($phone));
    }

    /**
     * Test an already normalized number against this country's pattern.
     *
     * Use this when checking several countries so the input is normalized only once.
     */
    public function matches(string $normalizedPhone): bool
    {
        return strlen($normalizedPhone) <= self::MAX_LENGTH
            && preg_match($this->pattern(), $normalizedPhone) === 1;
    }

    /**
     * The number without international or trunk prefix (e.g. "09121234567" => "9121234567"), or null when
     * the number does not match this country.
     */
    public function nationalNumber(string $phone): ?string
    {
        $phone = self::normalize($phone);

        if (!$this->matches($phone))  return null;


        if (str_starts_with($phone, '+')) {
            return substr($phone, strlen($this->code()) + 1);
        }

        return $this->withoutTrunkPrefix($phone);
    }

    /**
     * The number in national dialing form: trunk prefix + national number (e.g. "09121234567").
     */
    public function toNational(string $phone): ?string
    {
        $national = $this->nationalNumber($phone);

        return $national === null ? null : $this->nationalPrefix().$national;
    }

    /**
     * The number in E.164 form with a leading "+" (e.g. "+989121234567"), or null when it does not match.
     */
    public function toE164(string $phone): ?string
    {
        $national = $this->nationalNumber($phone);

        return $national === null ? null : '+'.$this->code().$national;
    }

    /**
     * Convert digits to English, drop every character except digits and "+", and turn a leading "00" into "+".
     */
    public static function normalize(string $phone): string
    {
        $phone = preg_replace('/[^\d+]/', '', Digits::toEnglish(trim($phone))) ?? '';

        if (str_starts_with($phone, '00')) return '+'.substr($phone, 2);

        return $phone;
    }

    /**
     * Find a country by case name ("UNITED_KINGDOM"), English label ("United Kingdom"), ISO code ("GB")
     * or the common alias "UK". Case, spaces and hyphens are ignored.
     */
    public static function country(string $country): ?self
    {
        $needle = strtoupper(str_replace([' ', '-'], '_', trim($country)));

        if ($needle === 'UK') {
            $needle = 'GB';
        }

        foreach (self::cases() as $case) {
            if ($case->name === $needle || $case->iso() === $needle || self::slug($case->label()) === $needle) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Find a country by ISO 3166-1 alpha-2 code. "UK" is accepted as an alias of "GB".
     */
    public static function fromIso(string $iso): ?self
    {
        $iso = strtoupper(trim($iso));

        if ($iso === 'UK') {
            $iso = 'GB';
        }

        foreach (self::cases() as $case) {
            if ($case->iso() === $iso) {
                return $case;
            }
        }

        return null;
    }

    /**
     * All countries that use a dial code ("98", "+98" and "0098" are equivalent).
     *
     * A list is returned because several countries share a code (+1: USA and Canada, +7: Russia and Kazakhstan).
     *
     * @return list<self>
     */
    public static function fromDialCode(string|int $dialCode): array
    {
        $digits = preg_replace('/\D/', '', (string)$dialCode) ?? '';

        if (str_starts_with((string)$dialCode, '00')) {
            $digits = substr($digits, 2);
        }

        if ($digits === '') {
            return [];
        }

        return array_values(array_filter(
            self::cases(),
            static fn(self $case): bool => $case->code() === $digits,
        ));
    }

    public static function validateWithCountry(string $phone, string|self $country): bool
    {
        $country = is_string($country) ? self::country($country) : $country;

        return $country?->validate($phone) ?? false;
    }

    /**
     * Check a number against several countries (or against every supported country when $countries is null).
     *
     * @param string        $phone
     * @param iterable|null $countries Case names, ISO codes or cases; unknown names are ignored.
     * @return bool
     */
    public static function validateAny(string $phone, ?iterable $countries = null): bool
    {
        $phone = self::normalize($phone);

        if ($countries === null) {
            foreach (self::matching($phone) as $_) {
                return true;
            }

            return false;
        }

        foreach ($countries as $country) {
            $country = is_string($country) ? self::country($country) : $country;

            if ($country?->matches($phone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect the first country whose pattern matches the number.
     *
     * Numbers without an international prefix can match several countries, and the USA and Canada
     * share one pattern, so the first matching case (declaration order) wins. Use {@see self::detectCountries()}
     * when the ambiguity matters.
     */
    public static function detectCountry(string $phone): ?self
    {
        foreach (self::matching(self::normalize($phone)) as $country) {
            return $country;
        }

        return null;
    }

    /**
     * Every country whose pattern matches the number, in declaration order.
     *
     * @return list<self>
     */
    public static function detectCountries(string $phone): array
    {
        return iterator_to_array(self::matching(self::normalize($phone)), false);
    }

    /**
     * Convert a number to its digits-only international form (e.g. "09121234567" => "989121234567").
     *
     * Numbers that already carry an international prefix are validated and returned without the "+".
     * National numbers require $country to know which prefix to add.
     */
    public static function canonical(string $phone, string|self|null $country = null): ?string
    {
        $phone = self::normalize($phone);

        if (str_starts_with($phone, '+')) {
            foreach (self::matching($phone) as $_) {
                return substr($phone, 1);
            }

            return null;
        }

        $country = is_string($country) ? self::country($country) : $country;

        if ($country === null || !$country->matches($phone)) {
            return null;
        }

        return $country->code().$country->withoutTrunkPrefix($phone);
    }

    /**
     * Same as {@see self::canonical()} but with a leading "+" (e.g. "+989121234567").
     */
    public static function e164(string $phone, string|self|null $country = null): ?string
    {
        $canonical = self::canonical($phone, $country);

        return $canonical === null ? null : '+'.$canonical;
    }

    /**
     * Whether two numbers are the same subscriber, however they are written ("0912..." and "+98912...").
     *
     * Numbers without an international prefix need $country, otherwise they never compare equal.
     */
    public static function areSame(string $first, string $second, string|self|null $country = null): bool
    {
        $first = self::canonical($first, $country);

        return $first !== null && $first === self::canonical($second, $country);
    }

    /**
     * ISO code => country name pairs, handy for select boxes.
     *
     * @return array<string, string>
     */
    public static function options(bool $sortByLabel = false): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->iso()] = $case->label();
        }

        if ($sortByLabel)  asort($options);


        return $options;
    }

    /**
     * Hide the middle of a number so it can be logged or shown without exposing it (e.g. "+989******567").
     *
     * Works on the normalized number and does not need to know the country. Numbers too short to keep both
     * ends visible are masked completely.
     */
    public static function mask(string $phone, int $visibleStart = 4, int $visibleEnd = 3): string
    {
        $phone = self::normalize($phone);
        $length = strlen($phone);
        $visibleStart = max(0, $visibleStart);
        $visibleEnd = max(0, $visibleEnd);

        if ($length <= $visibleStart + $visibleEnd) return str_repeat('*', $length);


        return substr($phone, 0, $visibleStart)
            .str_repeat('*', $length - $visibleStart - $visibleEnd)
            .($visibleEnd > 0 ? substr($phone, -$visibleEnd) : '');
    }

    /**
     * Lazily yield every country that matches a normalized number.
     *
     * For numbers with an international prefix, countries with another dial code are skipped without running
     * their regular expression, because their pattern cannot match anyway.
     *
     * @return Generator<int, self>
     */
    private static function matching(string $normalizedPhone): Generator
    {
        $international = str_starts_with($normalizedPhone, '+');

        foreach (self::cases() as $case) {
            if ($international && !str_starts_with($normalizedPhone, '+'.$case->code())) {
                continue;
            }

            if ($case->matches($normalizedPhone)) {
                yield $case;
            }
        }
    }

    private function withoutTrunkPrefix(string $normalizedPhone): string
    {
        $trunk = $this->nationalPrefix();

        if ($trunk !== '' && str_starts_with($normalizedPhone, $trunk)) return substr($normalizedPhone, strlen($trunk));

        return $normalizedPhone;
    }

    private function regionalIndicator(string $letter): string
    {
        return (string)mb_chr(self::REGIONAL_INDICATOR_A + ord($letter) - ord('A'), 'UTF-8');
    }

    private static function slug(string $label): string
    {
        return strtoupper(str_replace([' ', '-'], '_', $label));
    }
}
