<?php

namespace App\Http\Requests;

use App\Enums\AssetCategory;
use App\Enums\AssetCondition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_barang' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::enum(AssetCategory::class)],
            'jumlah' => ['required', 'integer', 'min:1'],
            'satuan' => ['required', 'string', 'max:50'],
            'kondisi' => ['required', Rule::enum(AssetCondition::class)],
            'tahun_pembelian' => ['required', 'integer', 'between:1900,'.date('Y')],
            'nilai_pembelian' => ['nullable', 'numeric', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'deskripsi' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nama_barang.required' => 'Nama barang harus diisi.',
            'kategori.required' => 'Kategori aset harus dipilih.',
            'jumlah.required' => 'Jumlah harus diisi.',
            'jumlah.min' => 'Jumlah minimal adalah 1.',
            'satuan.required' => 'Satuan harus diisi.',
            'kondisi.required' => 'Kondisi aset harus dipilih.',
            'tahun_pembelian.required' => 'Tahun pembelian harus diisi.',
            'tahun_pembelian.between' => 'Tahun pembelian tidak valid.',
            'nilai_pembelian.numeric' => 'Nilai pembelian harus berupa angka.',
            'nilai_pembelian.min' => 'Nilai pembelian tidak boleh negatif.',
        ];
    }
}
