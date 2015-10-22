<?php

class ControllerModulePPButton extends Controller
{
    public function index()
    {
        $status = true;

        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout')) || (!$this->customer->isLogged() && ($this->cart->hasRecurringProducts() || $this->cart->hasDownload()))) {
            $status = false;
        }

        if ($status) {
            if (preg_match('/Mobile|Android|BlackBerry|iPhone|Windows Phone/', $this->request->server['HTTP_USER_AGENT'])) {
                $data['mobile'] = true;
            } else {
                $data['mobile'] = false;
            }

            $data['payment_url'] = $this->url->link('payment/pp_express/express', '', 'SSL');

            return $this->load->view('module/pp_button', $data);
        }
    }
}
