<?php
/**
 * فلوی پرداخت زرین‌پال (پورت apps/payments/views.py)
 */
final class PaymentController
{
    private function ctx(array $appt): array
    {
        return [
            'appt' => $appt,
            'date_fa' => Jalali::pretty($appt['date']),
            'start_fa' => fa_digits(substr($appt['start_time'], 0, 5)),
        ];
    }

    public function page(Request $req, string $code): Response
    {
        $appt = Models::appointmentByCode($code);
        if (!$appt) return not_found();
        if ($appt['is_paid']) return redirect(url("/payment/$code/result/"));
        return html(render('payments/payment_page', $this->ctx($appt) + [
            'error' => Session::flash('pay_error'), 'sandbox_note' => (new ZarinPal())->isSandbox(),
        ]));
    }

    public function start(Request $req, string $code): Response
    {
        $appt = Models::appointmentByCode($code);
        if (!$appt) return not_found();
        if ($appt['is_paid']) return redirect(url("/payment/$code/result/"));

        $zp = new ZarinPal();
        try {
            $result = $zp->request(
                (int) $appt['price'],
                'رزرو نوبت ' . $appt['service']['title'] . ' — کد ' . $code,
                absolute_url("/payment/$code/callback/"),
                $appt['customer']['mobile'] ?? '',
            );
        } catch (RuntimeException $e) {
            Session::flash('pay_error', 'خطا در اتصال به درگاه پرداخت: ' . $e->getMessage());
            return redirect(url("/payment/$code/"));
        }
        DB::update('appointments', ['authority' => $result['authority'], 'status' => 'waiting_payment'], 'id = ?', [$appt['id']]);
        return redirect($result['pay_url']);
    }

    public function callback(Request $req, string $code): Response
    {
        $appt = Models::appointmentByCode($code);
        if (!$appt) return not_found();
        if ($appt['is_paid']) return redirect(url("/payment/$code/result/"));

        $status = $req->q('Status', '');
        $authority = $req->q('Authority', '');
        if ($status !== 'OK' || !$authority) {
            DB::update('appointments', ['status' => 'payment_failed'], 'id = ?', [$appt['id']]);
            return redirect(url("/payment/$code/result/"));
        }
        try {
            $verify = (new ZarinPal())->verify((int) $appt['price'], $authority);
        } catch (RuntimeException $e) {
            Session::flash('pay_error', 'خطا در تأیید پرداخت: ' . $e->getMessage());
            return redirect(url("/payment/$code/result/"));
        }
        if ($verify['ok']) {
            DB::update('appointments', ['status' => 'paid', 'ref_id' => (string) ($verify['ref_id'] ?? ''), 'paid_at' => gmdate('Y-m-d H:i:s')], 'id = ?', [$appt['id']]);
        } else {
            DB::update('appointments', ['status' => 'payment_failed'], 'id = ?', [$appt['id']]);
        }
        return redirect(url("/payment/$code/result/"));
    }

    public function result(Request $req, string $code): Response
    {
        $appt = Models::appointmentByCode($code);
        if (!$appt) return not_found();
        return html(render('payments/payment_result', $this->ctx($appt) + ['success' => $appt['is_paid'], 'error' => Session::flash('pay_error')]));
    }
}
