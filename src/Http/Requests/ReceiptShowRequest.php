<?php

namespace DuncanMcClean\SimpleCommerce\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReceiptShowRequest extends FormRequest
{
    public function authorize()
    {
        if (! $this->hasValidSignature()) {
            return false;
        }

        return true;
    }

    public function rules()
    {
        return [];
    }
}
