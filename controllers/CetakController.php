<?php

namespace app\controllers;

use yii\httpclient\Client;


class CetakController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionEdp()
    {
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            // 'format' => array(210, 140),
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 10,
            'margin_bottom' => 5,
            'margin_header' => 2,
            'margin_footer' => 2
        ]);
        // $mpdf->use_kwt = true;
        $mpdf->shrink_tables_to_fit = 1;
        $mpdf->SetTitle('Cetak Absensi EDP');
        $mpdf->AddPage('L');

        // return $this->render('cetak-edp');

        // Data

        $client = new Client();
        $orangEdp = $client->createRequest()
            ->setMethod('GET')
            ->setUrl('http://sip.simrs.aa/api/simpeg')
            ->setData([
                'token' => 'DataPegawaiRSUD44',
                'jenis' => 'programmer', // jaringan
                // 'jenis' => 'jaringan', // jaringan
            ])
            ->send();

        if ($orangEdp->isOk) {
            $org_edp = $orangEdp->data['result'];
        } else {
            $org_edp = null;
        }

        $bulanAbsen = '03-2021';

        $query_date = date('Y-m-d', strtotime('01-' . $bulanAbsen));
        $periode = \Yii::$app->formatter->asDate($query_date, 'php:F Y');

        // First day of the month.
        $startDate = date('Y-m-01', strtotime($query_date));
        // Last day of the month.
        $endDate = date('Y-m-t', strtotime($query_date));

        $startDay = (int) date('d', strtotime($startDate));
        $endDay = (int) date('d', strtotime($endDate));

        $berapaLembar = $endDay / 6;
        $berapaLembar = is_float($berapaLembar) ? (floor($berapaLembar) + 1) : floor($berapaLembar);

        $startDayTanggal = $startDay;
        $startDayPukul = $startDay;
        $startDayTtd = $startDay;

        $mpdf->WriteHTML($this->renderPartial('cetak-edp', [
            'org_edp' => $org_edp,
            'berapaLembar' => $berapaLembar,
            'periode' => $periode,
            'startDay' => $startDay,
            'endDay' => $endDay,
            'startDayTanggal' => $startDayTanggal,
            'startDayPukul' => $startDayPukul,
            'startDayTtd' => $startDayTtd,
            'endDate' => $endDate,
        ]));
        // $mpdf->SetJS('this.print(false);');
        // $mpdf->Output('Cetak Struk Penjualan ' . $model['no_penjualan'] . '.pdf', 'F');
        $mpdf->Output('Cetak Absensi EDP.pdf', 'I');
        exit;
    }
}
