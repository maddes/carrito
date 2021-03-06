<?php

final class Front
{
    private $registry;
    private $action;
    private $pre_action = [
        'admin' => [
            'common/sass',
            'common/login/check',
            'error/permission/check',
        ],
        'catalog' => [
            'common/maintenance',
            'common/seo_url',
        ]
    ];
    private $error = 'error/not_found';

    public function __construct($registry)
    {
        $this->registry = $registry;

        $this->action = $registry->get('request')->get('route', false);

        if (!$this->action) {
            switch (APP) {
                case 'catalog': $this->action = 'common/home'; break;
                case 'admin':   $this->action = 'common/dashboard'; break;
                case 'install': $this->action = 'step_1'; break;
                default: $this->action = $this->error; break;
            }
        }
    }

    public function addPreAction($pre_action)
    {
        $this->pre_action[APP][] = $pre_action;
    }

    public function dispatch()
    {
        if (isset($this->pre_action[APP])) {
            foreach ($this->pre_action[APP] as $pre_action) {
                $result = $this->execute(new Action($pre_action));

                if ($result) {
                    $this->action = $result;

                    break;
                }
            }
        }

        while ($this->action) {
            $this->action = $this->execute(new Action($this->action));
        }
    }

    private function execute($action)
    {
        $result = $action->execute($this->registry);

        if (is_string($result)) {
            $action = $result;
        } elseif ($result === false) {
            $action = $this->error;
            $this->error = '';
        } else {
            $action = false;
        }

        return $action;
    }
}
