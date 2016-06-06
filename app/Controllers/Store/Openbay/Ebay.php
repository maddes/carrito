<?php

namespace Carrito\Controllers\Store\Openbay;

class Ebay extends Controller
{
    public function eventAddOrder($order_id)
    {
    }

    public function eventAddOrderHistory($order_id)
    {
        if (!empty($order_id)) {
            $this->model_openbay_ebay_order->addOrderHistory($order_id);
        }
    }
}
