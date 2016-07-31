<?php

namespace Tests\Traits;

use Tests\Helpers\Normalizer;
use Tests\UnitTestCase;

class NormalizerTest extends UnitTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->normalizer = new Normalizer();
    }
    
    /** @test */
    public function it_removes_slashes_from_end_of_path()
    {
        $this->assertSame('foo/bar',
            $this->normalizer->runNormalizePath('foo/bar////'));
    }

    /** @test */
    public function it_removes_backslashes_from_end_of_path()
    {
        $this->assertSame('foo/bar',
            $this->normalizer->runNormalizePath('foo/bar\\\\\\'));
    }

    /** @test */
    public function it_removes_both_slashes_and_backslashes_from_end_of_path()
    {
        $this->assertSame('foo/bar',
            $this->normalizer->runNormalizePath('foo/bar\\\//\\//\\//'));
    }

    /** @test */
    public function it_returns_original_path_when_no_slashes_or_backslaehs()
    {
        $this->assertSame('foo/bar', $this->normalizer->runNormalizePath('foo/bar'));
    }
}
