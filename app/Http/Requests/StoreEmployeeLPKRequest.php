<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeLPKRequest extends FormRequest
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
        return [
            // Personal Information
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16', Rule::unique('karyawan_lpk', 'nik')->whereNull('deleted_at')],
            'email' => ['required', 'email:rfc,dns', Rule::unique('karyawan_lpk', 'email')->whereNull('deleted_at')],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'alamat' => ['required', 'string', 'max:1000'],
            'telepon' => ['required', 'string', 'max:20'],

            // Employment Information
            'jabatan' => ['required', 'in:Instruktur,Admin LPK,Staff'],
            'status' => ['required', 'in:Aktif,Cuti,Resign'],
            'tanggal_bergabung' => ['required', 'date', 'after_or_equal:tanggal_lahir'],

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
            'nik.required' => 'NIK wajib diisi',
            'nik.digits' => 'NIK harus berupa 16 digit angka',
            'nik.unique' => 'NIK sudah terdaftar',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
            'tanggal_lahir.before' => 'Tanggal lahir tidak boleh di masa depan',
            'tanggal_bergabung.required' => 'Tanggal bergabung wajib diisi',
            'tanggal_bergabung.after_or_equal' => 'Tanggal bergabung tidak boleh sebelum tanggal lahir',
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
