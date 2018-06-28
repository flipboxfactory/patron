<?php

namespace flipbox\patron\tests;

use Codeception\Test\Unit;
use flipbox\patron\Patron as PatronPlugin;
use flipbox\patron\services\Providers;
use flipbox\patron\services\Tokens;
use flipbox\patron\services\ManageProviders;
use flipbox\patron\services\ManageTokens;
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
    public function testProvidersComponent()
    {
        $this->assertInstanceOf(
            Providers::class,
            $this->module->getProviders()
        );

        $this->assertInstanceOf(
            Providers::class,
            $this->module->providers
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testTokensComponent()
    {
        $this->assertInstanceOf(
            Tokens::class,
            $this->module->getTokens()
        );

        $this->assertInstanceOf(
            Tokens::class,
            $this->module->tokens
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testManageProvidersComponent()
    {
        $this->assertInstanceOf(
            ManageProviders::class,
            $this->module->manageProviders()
        );

        $this->assertInstanceOf(
            ManageProviders::class,
            $this->module->manageProviders
        );
    }

    /**
     * Test the component is set correctly
     */
    public function testManageTokensComponent()
    {
        $this->assertInstanceOf(
            ManageTokens::class,
            $this->module->manageTokens()
        );

        $this->assertInstanceOf(
            ManageTokens::class,
            $this->module->manageTokens
        );
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
