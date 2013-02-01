<?php

require __DIR__ . '/setup-autoloading.php';

class AAA {}
class BBB {}
class CCC {
    public function __construct(BBB $bbb) {}
    public function setBbb(BBB $bbb) {}
    public $aaa;
}
class DDD {}
function get_a_bbb() { return new BBB; }

/**
 * @property-read AAA $aaa
 * @property-read BBB $bbb1
 * @property-read BBB $bbb2
 * @property-read BBB $bbb3
 * @property-read CCC $ccc
 * @property-read DDD $ddd
 *
 * @method AAA new_aaa()
 */
class MyDI extends \Props\Container {
    public function __construct() {
        // store plain old values
        $this->ddd = new DDD;
        $this->{'bbb.class'} = 'BBB';

        // set a factory, which will construct an object on demand
        $this->aaa = new \Props\Factory('AAA');

        // alternative factory syntax, and using a reference to specify the class name
        $this->setFactory('bbb1', $this->ref('bbb.class'));

        // fetch with a callback
        $this->bbb2 = new \Props\Invoker('get_a_bbb');

        // Closures get auto-wrapped with Invoker
        $this->bbb3 = function ($di) {
            return $di->bbb2;
        };

        // more advanced factory
        $cccArgs = array($this->ref('new_bbb1()'));
        $this->setFactory('ccc', 'CCC', $cccArgs)
            ->addMethodCall('setBbb', $this->ref('bbb2'))
            ->addPropertySet('aaa', $this->ref('aaa'));

        // at this point no user objects created, because refs & closures were used
    }
}

$di = new MyDI;

$di->aaa; // factory builds a AAA
$di->aaa; // the same AAA
$di->new_aaa(); // always a freshly-built AAA

$di->bbb1; // factory resolves bar.class, builds a BBB
$di->bbb2; // invoker calls get_a_bbb()
$di->bbb3; // invoker executes anon func, returning the already-cached $di->bbb2 instance

$di->ccc; // factory creates CCC, passing a new BBB object,
          // calls setBbb(), passing in $di->bbb2,
          // and sets the aaa property to $di->aaa
