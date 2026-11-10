<?php

namespace App\Http\Requests\CNS;

use Illuminate\Foundation\Http\FormRequest;

class StuffingContainerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'id' => 'nullable|integer',
            'Order_No' => 'nullable|string|max:150|exists:vessel_schedules,Order_No',
            'Date' => 'required|date',
            'Time_From' => 'nullable|date_format:H:i',
            'Time_To' => 'nullable|date_format:H:i',
            'Police_No' => 'nullable|string|max:150',
            'Container' => 'required|string|max:150',
            'Seal' => 'nullable|string|max:150',
            'Tare' => 'nullable|numeric|min:0',
            'Netto_KG' => 'nullable|numeric|min:0',
            'Note' => 'nullable|string|max:255',
            'Weighbridge_Note' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:8192',
            'Flexibag_Photos' => 'nullable|array|max:7',
            'Flexibag_Photos.*' => 'image|mimes:jpg,jpeg,png|max:8192',
            'Loading_Start_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
            'Loading_Done_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
            'Seal_Customs_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
            'Seal_Shipping_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
            'Seal_Surveyor_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
            'Road_Letter_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
            'Weighbridge_Photo' => 'nullable|image|mimes:jpg,jpeg,png|max:8192',
        ];
    }
}
