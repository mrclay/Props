<?php
/**
 * Example of Props\Pimple based on official Pimple docs
 */

namespace {
    require __DIR__ . '/setup-autoloading.php';
}

namespace PropsExample {

    class SessionStorage {
        public function __construct($cookieName) { $this->cookieName = $cookieName; }
    }
    class Session {
        public function __construct($storage) { $this->storage = $storage; }
    }
    class Zend_Mail {
        public function setFrom($from) { $this->from = $from; }
    }

    /**
     * @property-read string     $cookie_name
     * @property-read string     $session_storage_class
     * @property-read Session   $session
     * @property-read \Closure   $random
     * @property-read Zend_Mail $mail
     */
    class MyDI2 extends \Props\Pimple {
        public function __construct() {
            parent::__construct();

            $this->cookie_name = 'SESSION_ID';

            $this->session_storage_class = 'PropsExample\\SessionStorage';

            $this->session_storage = function (MyDI2 $c) {
                $class = $c->session_storage_class;
                return new $class($c->cookie_name);
            };

            $this->session = $this->factory(function (MyDI2 $c) {
                return new Session($c->session_storage);
            });

            $this->random = $this->protect(function () { return rand(); });

            $this->mail = function (MyDI2 $c) {
                return new Zend_Mail();
            };

            $this->{'mail.default_from'} = 'foo@example.com';

            $this->extend('mail', function($mail, MyDI2 $c) {
                $mail->setFrom($c->{'mail.default_from'});
                return $mail;
            });
        }
    }

    $di = new MyDI2;

    $r1 = $di->random;
    $r2 = $di->random;

    echo (int)($r1 === $r2) . "<br>";

    echo $r1() . "<br>";

    echo get_class($di->raw('session')) . '<br>';

    echo var_export($di->session, true) . '<br>';

    echo var_export($di->mail, true) . '<br>';
}
