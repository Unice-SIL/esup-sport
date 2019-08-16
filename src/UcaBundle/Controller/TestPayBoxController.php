<?php

namespace UcaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Annotation\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\FormatSimple;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Datatables\FormatActiviteDatatable;
use UcaBundle\Datatables\CreneauDatatable;
use UcaBundle\Form\FormatActiviteType;
use UcaBundle\Entity\IntervalleDate;
use Symfony\Component\HttpFoundation\Response;


class TestPayBoxController extends Controller
{
    /**
     * @Route("TestPayBox", name="TestPayBox")
     */
    public function testPayBoxAction(Request $request)
    {
        $data['PBX_SITE'] = "1999888";
        $data['PBX_RANG'] = "43";
        $data['PBX_IDENTIFIANT'] = "107975626";
        $data['PBX_TOTAL'] = 9990;
        $data['PBX_DEVISE'] = 978;
        $data['PBX_CMD'] = md5(microtime() . rand());
        $data['PBX_PORTEUR'] = "davy.gueudre@acatus.fr";
        $data['PBX_RETOUR'] = "Mt:M;Ref:R;Auto:A;Erreur:E;Sign:K";
        $data['PBX_HASH'] = "SHA512";
        $data['PBX_TIME'] = date("c");
        $data['PBX_TYPEPAIEMENT'] = 'CARTE';
        $data['PBX_TYPECARTE'] = 'CB';
        $data['PBX_EFFECTUE'] = urlencode("http://localhost/Uca/web/app_dev.php/fr/TestPayBoxRetour");
        $data['PBX_REFUSE'] = $data['PBX_EFFECTUE'];
        $data['PBX_ANNULE'] = $data['PBX_EFFECTUE'];
        $data['PBX_ATTENTE'] = $data['PBX_EFFECTUE'];
        $data['PBX_RUF1'] = 'POST';
        $data['PBX_REPONDRE_A'] = urlencode("http://paybox.epizy.com?time=" . time());
        // $data['PBX_ ERRORCODETEST'] = '??';

        $msg = implode('&', array_map(function ($k, $v) {
            return $k . '=' . $v;
        }, array_keys($data), $data));

        $secretKey = "0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF";
        $binKey = pack("H*", $secretKey);
        $data['PBX_HMAC'] = strtoupper(hash_hmac('sha512', $msg, $binKey));

        $twigConfig = [
            'payboxServer' => "preprod-tpeweb.paybox.com",
            'postData' => $data
        ];
        return $this->render('@Uca/Test/PayBox.html.twig', $twigConfig);
    }

    /**
     * @Route("TestPayBoxRetour", name="TestPayBoxRetour")
     */
    public function testPayBoxRetourAction(Request $request)
    {
        // $logger = $this->get('logger');
        // $logger->info('paiement: ' . json_encode($_GET));
        // return new Response();

        dump(json_encode($_GET));
        die;
        /* 
        https://preprod-admin.paybox.com/
        199988832 / 1999888I
        */

        /* Banque Française (CB)
        DESCRIPTION	CARTE	VALIDITE*	CVV*
        Numéro de carte de test Paybox	1111 2222 3333 4444	12/20	123
        Carte participant au programme 3-D Secure (enrôlée)	4012 0010 3714 1112	12/20	123
        Carte hors programme 3-D Secure (non enrôlée)	4012 0010 3844 3335	12/20	123 
        */

        /* Atos WorldLine (Belgique)
        DESCRIPTION	CARTE	VALIDITE*	CVV*
        Carte Visa belge	4236 8615 8842 3130	12/20	123
        Carte Mastercard belge	5476 8520 5684 3079	12/20	123
        Carte Maestro belge	6703 1111 2222 3334	12/20	N/A
        */

        // PBX_EFFECTUE: http://localhost/Uca/web/app_dev.php/fr/TestPayBoxRetour?Mt=999&Ref=56644834204bf964e340a550e154bfc8&Auto=XXXXXX&Erreur=00000
        // PBX_ANNULE: http://localhost/Uca/web/app_dev.php/fr/TestPayBoxRetour?Ref=a787517cba58d797f65926bfcb13b423&Erreur=00001
    }

    /**
     * @Route("TestPayBoxRetour2", name="TestPayBoxRetour2")
     */
    public function testPayBoxRetour2Action(Request $request)
    {
        $logger = $this->get('logger');
        // $logger->info('paiement.');
        $logger->info('paiement: ' . json_encode($_GET));
        return new Response();
    }
}
