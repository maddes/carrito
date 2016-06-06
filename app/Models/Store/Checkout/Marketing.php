<?php

namespace Carrito\Models\Store\Checkout;

class Marketing extends Model
{
    public function getMarketingByCode($code)
    {
        $query = $this->db->query('SELECT * FROM '.DB_PREFIX."marketing WHERE code = '".$this->db->escape($code)."'");

        return $query->row;
    }
}
