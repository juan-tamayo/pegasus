<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConnectImapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && ! $this->user()->is_demo;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['host' => strtolower(trim((string) $this->input('host'))), 'address' => strtolower(trim((string) $this->input('address')))]);
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', Rule::in(['namecheap', 'imap'])],
            'address' => ['required', 'email', 'max:254'],
            'username' => ['required', 'string', 'max:254', 'not_regex:/[\r\n\x00]/'],
            'password' => ['required', 'string', 'max:1024', 'not_regex:/[\r\n\x00]/'],
            'host' => ['required', 'string', 'max:253', Rule::in(config('pegasus.imap_allowed_hosts'))],
            'port' => ['required', 'integer', Rule::in([993, 143])],
            'encryption' => ['required', Rule::in(['ssl', 'tls'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if (($this->input('port') == 993 && $this->input('encryption') !== 'ssl') || ($this->input('port') == 143 && $this->input('encryption') !== 'tls')) {
                $v->errors()->add('port', 'Use port 993 with TLS encryption or 143 with mandatory STARTTLS.');
            }
            if ($this->input('provider') === 'namecheap' && ($this->input('host') !== 'mail.privateemail.com' || $this->input('port') != 993 || $this->input('username') !== $this->input('address'))) {
                $v->errors()->add('host', 'Namecheap uses mail.privateemail.com:993 and your full email address as username.');
            }
        });
    }
}
