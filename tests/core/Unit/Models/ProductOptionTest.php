<?php

uses(\Lunar\Tests\Core\TestCase::class);

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductOptionValue;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

test('can make a product option with translations', function () {
    $productOption = ProductOption::factory()->create();

    $this->assertDatabaseHas((new ProductOption)->getTable(), [
        'id' => $productOption->id,
        'name' => json_encode($productOption->name),
        'handle' => $productOption->handle,
    ]);

    $this->assertDatabaseCount((new ProductOption)->getTable(), 1);
});

test('handle matches name default locale', function () {
    /** @var ProductOption $productOption */
    $productOption = ProductOption::factory()->create();

    expect(Str::slug($productOption->translate('name')))->toEqual($productOption->handle);
});

//test('handle if not unique throw exception', function () {
//    $productOption = ProductOption::factory()->create();
//
//    $this->expectException(QueryException::class);
//    $this->expectExceptionMessage('UNIQUE constraint failed');
//    ProductOption::factory()->create([
//        'handle' => $productOption->handle,
//    ]);
//
//    $this->assertDatabaseCount((new ProductOption)->getTable(), 1);
//
//    ProductOption::factory()->create([
//        'handle' => $productOption->handle.'-unique',
//    ]);
//
//    $this->assertDatabaseCount((new ProductOption)->getTable(), 2);
//});

test('can delete product option', function () {
    $productOption = ProductOption::factory()->create();
    $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

    $productOption->delete();
    $this->assertDatabaseCount((new ProductOption)->getTable(), 0);
});

test('can delete product option by handle', function () {
    $productOption = ProductOption::factory()->create();
    $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

    ProductOption::modelClass()::where('handle', $productOption->handle)->delete();
    $this->assertDatabaseCount((new ProductOption)->getTable(), 0);
});

test('can create option value', function () {
    $productOption = ProductOption::factory()->create();
    $this->assertDatabaseCount((new ProductOption)->getTable(), 1);

    $productOption->values()->create([
        'name' => collect([
            'en' => 'Option Value 1 (EN)',
            'fr' => 'Option Value 1 (FR)',
        ]),
    ]);

    $this->assertDatabaseCount((new ProductOptionValue)->getTable(), 1);
    expect(ProductOptionValue::modelClass()::whereRelation(
        'option',
        'product_option_id',
        $productOption->id)->get())->toHaveCount(1);
});

test('takes scout prefix into account', function () {
    $expected = config('scout.prefix').'product_options';

    expect((new ProductOption)->searchableAs())->toEqual($expected);
});
