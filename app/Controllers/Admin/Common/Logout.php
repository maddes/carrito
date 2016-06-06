<?php

namespace Carrito\Controllers\Admin\Common;

class Logout extends Controller
{
    public function index()
    {
        $this->user->logout();

        unset($this->session->data['token']);

        $this->response->redirect($this->url->link('common/login', '', 'SSL'));
    }
}
