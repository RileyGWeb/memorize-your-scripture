<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\MemorizeLater;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MemorizeLaterModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_fillable_attributes()
    {
        $model = new MemorizeLater();
        
        $expected = [
            'user_id',
            'book',
            'chapter', 
            'verses',
            'note',
            'added_at',
        ];

        $this->assertEquals($expected, $model->getFillable());
    }

    public function test_table_name()
    {
        $model = new MemorizeLater();
        $this->assertEquals('memorize_later', $model->getTable());
    }

    public function test_casts()
    {
        $model = new MemorizeLater();
        
        $this->assertEquals('array', $model->getCasts()['verses']);
        $this->assertEquals('datetime', $model->getCasts()['added_at']);
    }

    public function test_has_factory()
    {
        $this->assertTrue(method_exists(MemorizeLater::class, 'factory'));
    }
}
