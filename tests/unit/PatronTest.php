<?php

namespace flipbox\patron\tests;

use Codeception\Test\Unit;
use flipbox\patron\Patron as PatronPlugin;
use flipbox\patron\services\Session;

class PatronTest extends Unit
{
    /**
     * @var PatronPlugin
     */
    private $module;

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
     */
    protected function _before()
    {
        $this->module = new PatronPlugin('patron');
    }

    /**
     * Test the component is set correctly
     */
    public function testSessionComponent()
    {
        $this->assertInstanceOf(
            Session::class,
            $this->module->getSession()
        );

        $this->assertInstanceOf(
            Session::class,
            $this->module->session
        );
    }
}
