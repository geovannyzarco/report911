<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    /**
     * Autentica al usuario con manejo de errores personalizado.
     */
    protected function authenticate(): mixed
    {
        try {
            $credentials = $this->getCredentialsFromFormData($this->data);

            $user = Auth::getProvider()->retrieveByCredentials($credentials);

            if (! $user) {
                throw ValidationException::withMessages([
                    'data.oni' => 'El ONI o la contraseña son incorrectos.',
                ]);
            }

            if (! Auth::getProvider()->validateCredentials($user, $credentials)) {
                throw ValidationException::withMessages([
                    'data.password' => 'El ONI o la contraseña son incorrectos.',
                ]);
            }

            Auth::login($user, $this->data['remember'] ?? false);

            return $user;

        } catch (QueryException $e) {
            Notification::make()
                ->title('Error de base de datos')
                ->body('No se pudo conectar a la base de datos. Verifique la configuración.')
                ->danger()
                ->persistent()
                ->send();

            return null;
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error de inicio de sesión')
                ->body('Ocurrió un error inesperado: '.$e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            return null;
        }
    }
}
