<?php

namespace Tests\Traits;

use Mnabialek\LaravelModular\Models\Module;
use Tests\Helpers\Application;
use Tests\Helpers\Replacer;
use Tests\UnitTestCase;
use Mockery as m;

class ReplacerTest extends UnitTestCase
{
    protected $moduleName = 'Car';
    protected $string = '{module} {class} {moduleNamespace} {namespace} {plural|lower} {foo} {bar}';
    protected $modulesNamespace = 'SampleModules\\\\';

    protected $module;
    protected $replacer;
    protected $app;

    protected function setUp()
    {
        parent::setUp();
        $this->replacer = m::mock(Replacer::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->app = m::mock(Application::class);
        $this->config = m::mock(Config::class);

        $this->replacer->setLaravel($this->app);
        $this->module = m::mock(Module::Class);
        $this->module->shouldReceive('name')->times(4)
            ->andReturn($this->moduleName);

        $this->config->shouldReceive('modulesNamespace')->once()
            ->andReturn($this->modulesNamespace);
    }

    /** @test */
    public function it_returns_valid_string_without_user_replacements()
    {
        $this->app->shouldReceive('offsetGet')->with('config')->times(11)
            ->andReturn($this->config);
        $this->config->shouldReceive('startSeparator')->times(5)
            ->andReturn('{');
        $this->config->shouldReceive('endSeparator')->times(5)
            ->andReturn('}');

        $this->assertSame($this->moduleName . ' ' . $this->moduleName . ' ' .
            $this->moduleName . ' ' . 'SampleModules' . ' ' . 'cars' . ' ' .
            '{foo} {bar}',
            $this->replacer->replace($this->string, $this->module, []));
    }

    /** @test */
    public function it_returns_valid_string_with_user_replacements()
    {
        $this->app->shouldReceive('offsetGet')->with('config')->times(15)
            ->andReturn($this->config);
        $this->config->shouldReceive('startSeparator')->times(7)
            ->andReturn('{');
        $this->config->shouldReceive('endSeparator')->times(7)
            ->andReturn('}');

        $this->assertSame($this->moduleName . ' ' . $this->moduleName . ' ' .
            $this->moduleName . ' ' . 'SampleModules' . ' ' . 'cars' . ' ' .
            'baz foo', $this->replacer->replace($this->string, $this->module,
            ['foo' => 'baz', 'bar' => 'foo']));
    }
}
