<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeLPKRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $employeeId = $this->route('record');

        return [
            // Personal Information (NIK not editable)
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc,dns', Rule::unique('karyawan_lpk', 'email')->ignore($employeeId)->whereNull('deleted_at')],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'alamat' => ['required', 'string', 'max:1000'],
            'telepon' => ['required', 'string', 'max:20'],

            // Employment Information (tanggal_bergabung not editable)
            'jabatan' => ['required', 'in:Instruktur,Admin LPK,Staff'],
            'status' => ['required', 'in:Aktif,Cuti,Resign'],

            // Compensation (Optional)
            'honor_pokok' => ['nullable', 'numeric', 'min:0'],
            'honor_per_jam' => ['nullable', 'numeric', 'min:0'],

            // Certificate (Optional)
            'sertifikat_path' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh di masa depan',
            'honor_pokok.numeric' => 'Honor pokok harus berupa angka',
            'honor_pokok.min' => 'Honor pokok tidak boleh negatif',
            'honor_per_jam.numeric' => 'Honor per jam harus berupa angka',
            'honor_per_jam.min' => 'Honor per jam tidak boleh negatif',
            'sertifikat_path.file' => 'Sertifikat harus berupa file',
            'sertifikat_path.mimes' => 'Format sertifikat harus PDF, JPG, JPEG, atau PNG',
            'sertifikat_path.max' => 'Ukuran sertifikat maksimal 5MB',
        ];
    }
}
