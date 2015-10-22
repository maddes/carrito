<?php
/**
 * Session object handler.
 *
 * It's hardcoded to use cookies.
 */
class Session
{
    public $data = array();

    public function __construct($registry)
    {
        // Default values
        $session_id = '';
        $key = 'default';

        // Resume API Session
        if (APP === 'catalog'
            and isset($registry->get('request')->get['token'])
            and isset($registry->get('request')->get['route'])
            and substr($registry->get('request')->get['route'], 0, 4) == 'api/'
        ) {
            // Delete old API sessions
            $registry->get('db')->query('DELETE FROM `'.DB_PREFIX.'api_session` WHERE TIMESTAMPADD(HOUR, 1, date_modified) < NOW()');

            // Get session for token+ip of user
            $query = $registry->get('db')->query('SELECT DISTINCT * FROM `'.DB_PREFIX.'api` `a` LEFT JOIN `'.DB_PREFIX.'api_session` `as` ON (a.api_id = as.api_id) LEFT JOIN '.DB_PREFIX."api_ip `ai` ON (as.api_id = ai.api_id) WHERE a.status = '1' AND as.token = '".$registry->get('db')->escape($registry->get('request')->get['token'])."' AND ai.ip = '".$registry->get('db')->escape($request->server['REMOTE_ADDR'])."'");

            if ($query->num_rows) {
                $session_id = $query->row['session_id'];
                $key = $query->row['session_name'];

                // keep the session alive
                $registry->get('db')->query('UPDATE `'.DB_PREFIX."api_session` SET date_modified = NOW() WHERE api_session_id = '".$query->row['api_session_id']."'");
            }
        }

        // Start a new session
        if (!session_id()) {
            // .ini values
            ini_set('session.use_only_cookies', 'Off');
            ini_set('session.use_cookies', 'On');
            ini_set('session.use_trans_sid', 'Off');
            ini_set('session.cookie_httponly', 'On');

            // Abort if the session cookie has bad characters
            if (array_key_exists(session_name(), $registry->get('request')->cookie)
                and !preg_match('/^[a-zA-Z0-9,\-]{22,40}$/', $registry->get('request')->cookie[session_name()])
            ) {
                exit();
            }

            // Replace session ID (in cas of API session)
            if ($session_id) {
                session_id($session_id);
            }

            // Aply .ini values for the new session
            session_set_cookie_params(0, '/');

            // Start the session
            session_start();
        }

        // Set the data placeholder in the session tree
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = array();
        }

        // Link data property to actual session storage
        $this->data = &$_SESSION[$key];
    }

    public function getId()
    {
        return session_id();
    }

    public function start()
    {
        return session_start();
    }

    public function destroy()
    {
        return session_destroy();
    }
}
