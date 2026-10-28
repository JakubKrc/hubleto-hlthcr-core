<?php

namespace Hubleto\App\External\WaiBlue\HlthcrCore\Seeds;

use Hubleto\App\External\WaiBlue\HlthcrCore\Models\Hazard;

/**
 * Starting hazard catalogue (Slovak legal categories, from the legacy catalogue).
 * Installed once, when the hazard table is empty. Exam intervals are left for the administrator.
 */
class HazardCatalogue
{
  public static function getHazardNamesByType(): array
  {
    return [
      Hazard::TYPE_FACTOR => [
        'Psychická pracovná záťaž',
        'Fyzická záťaž',
        'Chemické faktory',
        'Karcinogénne a mutagénne faktory',
        'Biologické faktory',
        'Záťaž teplom',
        'Záťaž chladom',
        'Umelé optické žiarenie - ultrafialové',
        'Umelé optické žiarenie - infračervené žiarenie',
        'Umelé optické žiarenie - lasery',
        'Umelé optické žiarenie - intenzívne pulzné svetlo',
        'Ionizujúce žiarenie',
        'Zvýšený tlak vzduchu',
        'Vibrácie',
        'Elektromagnetické pole',
        'Hluk',
      ],
      Hazard::TYPE_SPECIAL_REGULATION => [
        'Práca so zobrazovacími jednotkami',
        'Práca vo výškach',
        'Práca s bremenami',
        'Nočná práca',
        'Vedenie motorových vozidiel',
      ],
      Hazard::TYPE_PROFESSIONAL_ACTIVITY => [
        'Činnosť revízneho technika vyhradených technických zariadení tlakových.',
        'Obsluha kotla I. až V. triedy.',
        'Oprava kotla I. až V. triedy a potrubného vedenia na rozvod nebezpečnej kvapaliny s najvyšším dovoleným tlakom nad 1 MPa',
        'Činnosť revízneho technika vyhradených technických zariadení zdvíhacích',
        'Obsluha mobilného žeriava výložníkového typu a vežového žeriava výložníkového typu',
        'Obsluha pohyblivej pracovnej plošiny na podvozku s motorovým pohonom, ktorá je určená na prevádzku na pozemných komunikáciach s výškou zdvihu nad 1,5 m',
        'Oprava osobného výťahu a nákladného výťahu s povolenou dopravou osôb',
        'Viazanie bremien',
        'Činnosť revízneho technika vyhradených technických zariadení plynových',
        'Obsluha vyhradených technických zariadení plynových s vysokou mierou ohrozenia (skupina A) pracujúcich s nebezpečnými plynmi okrem zariadení určených na zásobovanie plynom z kovových tlakových nádob stabilných alebo z kovových tlakových nádob na dopravu plynov (tlakové stanice) a zariadení určených na rozvod plynov',
        'Oprava vyhradených technických zariadení plynových s vysokou mierou ohrozenia (skupina A) pracujúcich s nebezpečnými plynmi',
        'Práca vo výške 1,5m a viac nad povrchom zeme pomocou špeciálnej horolezeckej a speleologickej techniky',
        'Činnosť elektrotechnika, samostatného elektrotechnika a elektrotechnika na riadenie činností alebo na riadenie prevádzky na vyhradených technických zariadeniach elektrických',
        'Činnosti revízneho technika na vyhradených technických elektrických',
        'Obsluha motorových vozíkov',
        'Montáž a demontáž lešenia (lešenár)',
        'Obsluha vybraných stavebných strojov a zariadení',
        'Obsluha vybraných poľnohospodárskych strojov a zariadení',
        'Obsluha vybraných lesníckych strojov',
        'Obsluha ručnej motorovej reťazovej píly pri ťažbe dreva a ručnej motorovej reťazovej píly pri inej činnosti',
        'Práca s azbestom',
        'Obsluha VTZ zdvíhacích',
        'Hasič',
        'Potápač',
        'Používateľ dýchacej techniky',
        'Vedenie vozidla s právom prednosti jazdy',
        'Nedeštruktívne skúšanie materiálov',
        'Obsluha ručného elektrického, pneumatického a motorového náradia',
        'Činnosti vedúce k ožiareniu - pracovník kategórie A',
        'Výkon pracovných činností na jadrovom zariadení',
        'Práca s toxickými a veľmi toxickými látkami a zmesami',
        'Vedenie motorového vozidla, pre potreby plnenia pracovných úloh (ostatný vodič)',
        'Obsluha VTZ tlakových',
        'Obsluha zvislej posuvnej brány',
      ],
      Hazard::TYPE_EXAMINATION => [
        'Všeobecný / Pracovný:',
        'EKG',
        'Laboratórne vyšetrenia',
        'Audiometria',
        'Oftalmológ',
        'Spirometria',
        'Pletyzmografia',
        'Iné vyšetrenie/očkovanie:',
      ],
    ];
  }
}
