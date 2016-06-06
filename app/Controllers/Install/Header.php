<?php

namespace Carrito\Controllers\Install;

class Header extends Controller
{
    public function index()
    {
        $data['title'] = $this->document->getTitle();
        $data['description'] = $this->document->getDescription();
        $data['links'] = $this->document->getLinks();
        $data['styles'] = $this->document->getStyles();
        $data['scripts'] = $this->document->getScripts();

        if ($this->request->server['HTTPS']) {
            $data['base'] = $this->config->get('config_ssl');
        } else {
            $data['base'] = $this->config->get('config_url');
        }

        return $this->load->view('header', $data);
    }
}
