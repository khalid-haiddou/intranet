<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            // Account type validation
            'account_type' => ['required', 'in:individual,company'],
            
            // Common contact fields
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            
            // Membership details - all plans can now use all billing cycles
            'membership_plan' => ['required', 'in:hot-desk,bureau-dedie,bureau-prive'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'billing_cycle' => ['required', 'in:daily,weekly,biweekly,monthly'],
            
            // Authentication
            'password' => ['required', 'confirmed', Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
            'password_confirmation' => ['required'],
            
            // Terms and preferences
            'terms_accepted' => ['required', 'accepted'],
            'newsletter' => ['boolean'],
            
            // Role (optional, defaults to 'user')
            'role' => ['sometimes', 'in:admin,user'],
        ];

        // Conditional validation based on account type
        if ($this->input('account_type') === 'individual') {
            $rules = array_merge($rules, [
                'prenom' => ['required', 'string', 'max:100'],
                'nom' => ['required', 'string', 'max:100'],
                'cin' => ['required', 'string', 'max:20', 'unique:users,cin'],
                
                // Ensure company fields are not provided
                'company_name' => ['prohibited'],
                'rc' => ['prohibited'],
                'ice' => ['prohibited'],
                'legal_representative' => ['prohibited'],
            ]);
        } elseif ($this->input('account_type') === 'company') {
            $rules = array_merge($rules, [
                'company_name' => ['required', 'string', 'max:255'],
                'rc' => ['required', 'string', 'max:50', 'unique:users,rc'],
                'ice' => ['required', 'string', 'max:50'],
                'legal_representative' => ['required', 'string', 'max:255'],
                
                // Ensure individual fields are not provided
                'prenom' => ['prohibited'],
                'nom' => ['prohibited'],
                'cin' => ['prohibited'],
            ]);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // Account type messages
            'account_type.required' => 'Veuillez sélectionner un type de compte.',
            'account_type.in' => 'Le type de compte sélectionné est invalide.',
            
            // Individual validation messages
            'prenom.required' => 'Le prénom est obligatoire.',
            'nom.required' => 'Le nom est obligatoire.',
            'cin.required' => 'Le numéro CIN est obligatoire.',
            'cin.unique' => 'Ce numéro CIN est déjà enregistré.',
            
            // Company validation messages
            'company_name.required' => 'Le nom de l\'entreprise est obligatoire.',
            'rc.required' => 'Le registre de commerce est obligatoire.',
            'rc.unique' => 'Ce registre de commerce est déjà enregistré.',
            'ice.required' => 'Le numéro ICE est obligatoire.',
            'legal_representative.required' => 'Le nom du représentant légal est obligatoire.',
            
            // Contact validation messages
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà enregistrée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            
            // Membership validation messages
            'membership_plan.required' => 'Veuillez sélectionner un plan d\'abonnement.',
            'membership_plan.in' => 'Le plan d\'abonnement sélectionné est invalide.',
            'price.required' => 'Le prix est obligatoire.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix ne peut pas être négatif.',
            'billing_cycle.required' => 'Veuillez sélectionner un cycle de facturation.',
            'billing_cycle.in' => 'Le cycle de facturation sélectionné est invalide. Pour Hot Desk, seul le cycle journalier est disponible.',
            
            // Password validation messages
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password_confirmation.required' => 'Veuillez confirmer votre mot de passe.',
            
            // Terms validation messages
            'terms_accepted.required' => 'Vous devez accepter les conditions d\'utilisation.',
            'terms_accepted.accepted' => 'Vous devez accepter les conditions d\'utilisation.',
            
            // Role validation messages
            'role.in' => 'Le rôle sélectionné est invalide.',
        ];
    }

    public function attributes(): array
    {
        return [
            'prenom' => 'prénom',
            'nom' => 'nom',
            'cin' => 'CIN',
            'company_name' => 'nom de l\'entreprise',
            'rc' => 'registre de commerce',
            'ice' => 'ICE',
            'legal_representative' => 'représentant légal',
            'email' => 'email',
            'phone' => 'téléphone',
            'address' => 'adresse',
            'membership_plan' => 'plan d\'abonnement',
            'price' => 'prix',
            'billing_cycle' => 'cycle de facturation',
            'password' => 'mot de passe',
            'password_confirmation' => 'confirmation du mot de passe',
            'terms_accepted' => 'conditions d\'utilisation',
            'newsletter' => 'newsletter',
            'role' => 'rôle',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        if ($this->expectsJson()) {
            parent::failedValidation($validator);
        }

        // For web requests, redirect back with errors
        throw new \Illuminate\Validation\ValidationException($validator, response()->redirectTo($this->getRedirectUrl())
            ->withInput($this->except('password', 'password_confirmation'))
            ->withErrors($validator, 'default')
        );
    }

    /**
     * Get the validation rules that apply to the request based on account type.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        
        // Set default role if not provided
        if (!isset($validated['role'])) {
            $validated['role'] = 'user';
        }
        
        // Set default newsletter preference if not provided
        if (!isset($validated['newsletter'])) {
            $validated['newsletter'] = false;
        }
        
        return $validated;
    }
}