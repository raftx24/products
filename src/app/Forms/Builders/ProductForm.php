<?php

namespace LaravelEnso\Products\app\Forms\Builders;

use LaravelEnso\Forms\app\Services\Form;
use LaravelEnso\Products\app\Http\Resources\Supplier;
use LaravelEnso\Products\app\Models\Product;

class ProductForm
{
    protected const TemplatePath = __DIR__.'/../Templates/product.json';

    protected $form;

    public function __construct()
    {
        $this->form = new Form(static::TemplatePath);
    }

    public function create()
    {
        return $this->form->create();
    }

    public function edit(Product $product)
    {
        $product->suppliers->each(function ($supplier) {
            $supplier->pivot->inCents(false);
        });

        return $this->form
            ->value('suppliers', Supplier::collection($product->suppliers))
            ->value('defaultSupplierId', optional($product->defaultSupplier())->id)
            ->edit($product->inCents(false));
    }
}
