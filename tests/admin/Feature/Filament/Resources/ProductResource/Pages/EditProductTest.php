<?php

use Illuminate\Support\Str;

uses(\Lunar\Tests\Admin\Feature\Filament\TestCase::class)
    ->group('resource.product');

it('can render edit page', function (string $fieldType, string $lunarFieldType, array $params = []) {
    /** @var \Lunar\Tests\Admin\Feature\Filament\TestCase::class $this */
    $attributeGroup = \Lunar\Models\AttributeGroup::factory()
        ->create([
            'attributable_type' => 'product',
        ]);

    $attributesToCreate = [
        \Lunar\FieldTypes\TranslatedText::class => [
            'handle' => 'name',
        ],
        $fieldType => $params,
    ];

    $attributes = [];

    foreach ($attributesToCreate as $type => $params) {
        $handle = $params['handle'] ?? Str::snake(class_basename($type));

        $attributes[] = \Lunar\Models\Attribute::factory()
            ->create([
                'attribute_group_id' => $attributeGroup->id,
                'type' => $type,
                'attribute_type' => 'product',
                'handle' => $handle,
                'name' => ['en' => $params['name'] ?? Str::ucfirst($handle)],
                'description' => ['en' => $params['description'] ?? Str::ucfirst($handle)],
            ]);
    }

    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $language = \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $productType = \Lunar\Models\ProductType::factory()->create();

    $productType->mappedAttributes()->attach($attributes);

    $product = \Lunar\Models\Product::factory()->create([
        'attribute_data' => collect([
            'name' => new \Lunar\FieldTypes\TranslatedText([$language->code => 'Test Product']),
        ]),
        'product_type_id' => $productType->id,
    ]);

    $this
        ->asStaff(admin: true)
        ->get(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::getUrl([
            'record' => $product->getRouteKey(),
        ]))
        ->assertSuccessful();

    /** @var Livewire\Features\SupportTesting\Testable $page */
    $page = \Livewire\Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class, [
            'record' => $product->getRouteKey(),
        ]);

    foreach ($attributes as $attribute) {
        $page
            ->assertFormFieldExists("attribute_data.{$attribute->handle}");
    }

    $page
        ->assertFormFieldExists('product_type_id')
        ->assertHasNoErrors();

})->with([
    [\Lunar\FieldTypes\Dropdown::class, \Lunar\Admin\Support\FieldTypes\Dropdown::class],
    [\Lunar\FieldTypes\ListField::class, \Lunar\Admin\Support\FieldTypes\ListField::class],
    [\Lunar\FieldTypes\Text::class, \Lunar\Admin\Support\FieldTypes\TextField::class],
    [\Lunar\FieldTypes\TranslatedText::class, \Lunar\Admin\Support\FieldTypes\TranslatedText::class],
    [\Lunar\FieldTypes\Toggle::class, \Lunar\Admin\Support\FieldTypes\Toggle::class],
    [\Lunar\FieldTypes\YouTube::class, \Lunar\Admin\Support\FieldTypes\YouTube::class],
    [\Lunar\FieldTypes\Vimeo::class, \Lunar\Admin\Support\FieldTypes\Vimeo::class],
    [\Lunar\FieldTypes\Number::class, \Lunar\Admin\Support\FieldTypes\Number::class],
    [\Lunar\FieldTypes\File::class, \Lunar\Admin\Support\FieldTypes\File::class],
]);

it('can save product', function () {
    /** @var \Lunar\Tests\Admin\Feature\Filament\TestCase::class $this */
    $attributeGroup = \Lunar\Models\AttributeGroup::factory()
        ->create([
            'attributable_type' => 'product',
        ]);

    $attribute = \Lunar\Models\Attribute::factory()
        ->create([
            'attribute_group_id' => $attributeGroup->id,
            'type' => \Lunar\FieldTypes\TranslatedText::class,
            'attribute_type' => 'product',
            'handle' => 'name',
            'name' => [
                'en' => 'Name',
            ],
            'description' => [
                'en' => 'Description',
            ],
        ]);

    \Lunar\Models\TaxClass::factory()->create([
        'default' => true,
    ]);

    \Lunar\Models\Currency::factory()->create([
        'default' => true,
        'decimal_places' => 2,
    ]);

    $language = \Lunar\Models\Language::factory()->create([
        'default' => true,
    ]);

    $brand = \Lunar\Models\Brand::factory()->create();

    $productType = \Lunar\Models\ProductType::factory()->create();

    $productType->mappedAttributes()->attach($attribute);

    $product = \Lunar\Models\Product::factory()->create([
        'attribute_data' => collect([
            'name' => new \Lunar\FieldTypes\TranslatedText([$language->code => 'Test Product']),
        ]),
        'product_type_id' => $productType->id,
    ]);

    /** @var Livewire\Features\SupportTesting\Testable $page */
    $page = \Livewire\Livewire::actingAs($this->makeStaff(admin: true), 'staff')
        ->test(\Lunar\Admin\Filament\Resources\ProductResource\Pages\EditProduct::class, [
            'record' => $product->getRouteKey(),
        ])
        ->fillForm([
            'brand_id' => $brand->id,
            'product_type_id' => $productType->id,
            'attribute_data.name' => [$language->code => 'Foo Bar'],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    \Pest\Laravel\assertDatabaseHas((new \Lunar\Models\Product)->getTable(), [
        'product_type_id' => $productType->id,
        'brand_id' => $brand->id,
        'status' => 'published',
        'attribute_data' => json_encode([
            'name' => [
                'field_type' => \Lunar\FieldTypes\TranslatedText::class,
                'value' => [
                    $language->code => 'Foo Bar',
                ],
            ],
        ]),
    ]);
});
