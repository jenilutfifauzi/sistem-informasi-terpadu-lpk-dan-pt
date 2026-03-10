<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeePTRequest extends FormRequest
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
     * Note: nik and tanggal_bergabung are NOT present — these fields are disabled
     * in the edit form and therefore not submitted.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $employeeId = $this->route('record');

        return [
            // Personal Information (NIK not editable)
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', Rule::unique('karyawan_pt', 'email')->ignore($employeeId)->whereNull('deleted_at')],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'alamat' => ['required', 'string', 'max:1000'],
            'telepon' => ['required', 'string', 'max:20'],

            // Employment Information (tanggal_bergabung not editable)
            'jabatan' => ['required', Rule::enum(\App\Enums\JabatanPT::class)],
            'divisi' => ['required', Rule::enum(\App\Enums\DivisiPT::class)],
            'status' => ['required', Rule::enum(\App\Enums\StatusKepegawaian::class)],
            'jenis_kontrak' => ['required', Rule::enum(\App\Enums\JenisKontrak::class)],

            // Compensation (Optional)
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tunjangan' => ['nullable', 'numeric', 'min:0'],

            // Document (Optional)
            'dokumen_path' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh di masa depan',
            'jabatan.required' => 'Jabatan wajib diisi',
            'divisi.required' => 'Divisi wajib diisi',
            'jenis_kontrak.required' => 'Jenis kontrak wajib diisi',
            'gaji_pokok.numeric' => 'Gaji pokok harus berupa angka',
            'gaji_pokok.min' => 'Gaji pokok tidak boleh negatif',
            'tunjangan.numeric' => 'Tunjangan harus berupa angka',
            'tunjangan.min' => 'Tunjangan tidak boleh negatif',
            'dokumen_path.file' => 'Dokumen harus berupa file',
            'dokumen_path.mimes' => 'Format dokumen harus PDF, JPG, JPEG, atau PNG',
            'dokumen_path.max' => 'Ukuran dokumen maksimal 5MB',
        ];
    }
}
