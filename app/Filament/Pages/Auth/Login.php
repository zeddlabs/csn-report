<?php

use Filament\Forms\Form;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
  public function form(Form $form): Form
  {
    return $form->schema([
      $this->getUsernameFormComponent(),
      $this->getPasswordFormComponent(),
    ])->statePath('data');
  }

  protected function getUsernameFormComponent(): Component
  {
    return TextInput::make('username')
      ->label('Username')
      ->required()
      ->autocomplete()
      ->autofocus()
      ->extraInputAttributes(['tabindex' => 1]);
  }

  /**
   * @param  array<string, mixed>  $data
   * @return array<string, mixed>
   */
  protected function getCredentialsFromFormData(array $data): array
  {
    return [
      'username' => $data['username'],
      'password' => $data['password'],
    ];
  }
}
