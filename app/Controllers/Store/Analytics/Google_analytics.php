<?php

namespace Carrito\Controllers\Store\Analytics;

class Google_analytics extends Controller
{
    public function index()
    {
        return html_entity_decode($this->config->get('google_analytics_code'), ENT_QUOTES, 'UTF-8');
    }
}
