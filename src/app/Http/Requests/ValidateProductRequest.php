<?php

namespace LaravelEnso\Products\app\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use LaravelEnso\Products\app\Models\Product;

class ValidateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'manufacturer_id' => 'nullable|integer|exists:companies,id',
            'suppliers' => 'array',
            'suppliers.id' => 'exists:companies,id',
            'defaultSupplierId' => 'nullable|exists:companies,id|required_with:suppliers',
            'name' => 'required|string|max:255',
            'part_number' => 'required|string',
            'internal_code' => 'nullable|string|max:255',
            'measurement_unit_id' => 'required|exists:measurement_units,id',
            'package_quantity' => 'nullable|integer',
            'list_price' => 'required|numeric|min:0.01',
            'vat_percent' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'link' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->product()->exists()) {
                collect(['part_number', 'manufacturer_id'])->each(function ($attribute) use ($validator) {
                    $validator->errors()->add($attribute, __(
                        'A product with the specified part number and manufacturer already exists!'
                    ));
                });
            }

            $suppliers = collect($this->get('suppliers'));

            if ($suppliers->isEmpty()) {
                return;
            }

            if (! $suppliers->pluck('id')->contains($this->get('defaultSupplierId'))) {
                $validator->errors()->add('defaultSupplierId', __(
                    'This supplier must be within selected suppliers'
                ));
            }

            if ($this->hasInvalidSuppliers($suppliers)) {
                $validator->errors()->add('suppliers', __(
                    'Part number and acquisition price are mandatory for each supplier'
                ));
            }

            if ($this->hasInvalidDefaultSupplier($suppliers)) {
                $validator->errors()->add('defaultSupplierId', __(
                    'The default supplier does not have the minimum acquisition price'
                ));
            }
        });
    }

    protected function product()
    {
        return Product::where('part_number', $this->get('part_number'))
            ->where('manufacturer_id', $this->get('manufacturer_id'))
            ->where('id', '<>', optional($this->route('product'))->id);
    }

    private function hasInvalidSuppliers($suppliers)
    {
        return $suppliers->some(function ($supplier) {
            return ! is_numeric($supplier['pivot']['acquisition_price'])
                || $supplier['pivot']['acquisition_price'] <= 0
                || ! $supplier['pivot']['part_number'];
        });
    }

    private function hasInvalidDefaultSupplier(Collection $suppliers)
    {
        $defaultSupplier = $suppliers->first(function ($supplier) {
            return $supplier['id'] === $this->get('defaultSupplierId');
        });

        return $suppliers
            ->reject(function ($supplier) use ($defaultSupplier) {
                return $supplier['id'] === $defaultSupplier['id'];
            })
            ->contains(function ($supplier) use ($defaultSupplier) {
                return $supplier['pivot']['acquisition_price'] < $defaultSupplier['pivot']['acquisition_price'];
            });
    }
}
