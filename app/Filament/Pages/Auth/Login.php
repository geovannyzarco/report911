<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class Login extends BaseLogin
{
    /**
     * Personaliza el formulario de login para usar ONI en lugar de email.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('oni')
                    ->label('ONI')
                    ->required()
                    ->maxLength(255)
                    ->autofocus(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ]);
    }

    /**
     * Extrae las credenciales del formulario usando ONI en lugar de email.
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'oni' => $data['oni'],
            'password' => $data['password'],
        ];
    }
}
