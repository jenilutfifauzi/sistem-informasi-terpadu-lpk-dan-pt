<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeePTRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Personal Information
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16', Rule::unique('karyawan_pt', 'nik')->whereNull('deleted_at')],
            'email' => ['required', 'email:rfc,dns', Rule::unique('karyawan_pt', 'email')->whereNull('deleted_at')],
            'tanggal_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'alamat' => ['required', 'string', 'max:1000'],
            'telepon' => ['required', 'string', 'max:20'],

            // Employment Information
            'jabatan' => ['required', Rule::enum(\App\Enums\JabatanPT::class)],
            'divisi' => ['required', Rule::enum(\App\Enums\DivisiPT::class)],
            'status' => ['required', Rule::enum(\App\Enums\StatusKepegawaian::class)],
            'jenis_kontrak' => ['required', Rule::enum(\App\Enums\JenisKontrak::class)],
            'tanggal_bergabung' => ['required', 'date', 'after_or_equal:tanggal_lahir'],

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
