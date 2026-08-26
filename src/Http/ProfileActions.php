<?php

declare(strict_types=1);

namespace App\Http;

use App\Models\Image;
use App\Models\User;
use App\Models\Video;
use App\Services\Storage;
use App\Services\UploadValidator;
use App\Support\Auth;
use App\Support\Config;
use App\Support\Validation;

/**
 * As quatro ações do formulário de perfil.
 *
 * O controller original resolvia isso num `if/else if` de 130 linhas dentro de
 * um único `try`, onde qualquer exceção - de validação a falha de rede - caía no
 * mesmo `catch` e virava a mesma mensagem. Aqui cada ação é um método com o seu
 * próprio caminho de erro.
 */
final class ProfileActions
{
    public function __construct(private readonly int $userId)
    {
    }

    /**
     * Despacha a ação enviada pelo formulário.
     */
    public function handle(string $action): void
    {
        match ($action) {
            'update_info' => $this->updateInfo(),
            'change_password' => $this->changePassword(),
            'delete_account' => $this->deleteAccount(),
            'update_avatar' => $this->updateAvatar(),
            default => flash()->put('error', 'Ação desconhecida.'),
        };
    }

    /**
     * Atualiza nome, e-mail e elo.
     */
    private function updateInfo(): void
    {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $elo = strtolower(trim((string) ($_POST['elo'] ?? '')));

        $validation = Validation::validate([
            'nome' => ['required', 'min:2', 'max:60'],
            'email' => ['required', 'email', 'max:255'],
            'elo' => ['required', 'in:' . implode(',', User::ELOS)],
        ], ['nome' => $name, 'email' => $email, 'elo' => $elo]);

        // A regra `unique` não serve aqui: o e-mail atual da própria pessoa está
        // na tabela. A checagem precisa ignorar a própria linha.
        $existing = User::findByEmail($email);
        if ($existing !== null && (int) $existing->id !== $this->userId) {
            $validation->addError('email', 'Este e-mail já está em uso por outra conta.');
        }

        if ($validation->fails()) {
            $this->flashValidationErrors($validation);
            flash()->put('formData', ['name' => $name, 'email' => $email, 'elo' => $elo]);

            return;
        }

        $updated = User::updateProfile($this->userId, [
            'name' => $name,
            'email' => $email,
            'elo' => $elo,
        ]);

        if ($updated === null) {
            flash()->put('error', 'Não foi possível atualizar o perfil.');

            return;
        }

        Auth::refresh([
            'name' => $updated->name,
            'email' => $updated->email,
            'elo' => $updated->elo,
        ]);

        flash()->put('message', 'Perfil atualizado com sucesso.');
    }

    /**
     * Troca a senha, exigindo a senha atual.
     */
    private function changePassword(): void
    {
        $current = (string) ($_POST['senha_atual'] ?? '');
        $new = (string) ($_POST['nova_senha'] ?? '');
        $confirmation = (string) ($_POST['confirmar_senha'] ?? '');

        $user = User::find($this->userId);

        $validation = Validation::validate([
            'nova_senha' => ['required', 'min:8', 'max:72', 'strong'],
        ], ['nova_senha' => $new]);

        if ($new !== $confirmation) {
            $validation->addError('confirmar_senha', 'A confirmação não confere com a nova senha.');
        }

        if ($user === null || !$user->verifyPassword($current)) {
            $validation->addError('senha', 'A senha atual está incorreta.');
        }

        if ($validation->fails()) {
            $this->flashValidationErrors($validation);

            return;
        }

        User::updatePassword($this->userId, $new);

        // Trocar a senha deve encerrar as sessões abertas em outros dispositivos:
        // é exatamente o que a pessoa espera ao desconfiar que a senha vazou.
        Auth::revokeAllTokensFor($this->userId);

        flash()->put('message', 'Senha alterada. As sessões em outros dispositivos foram encerradas.');
    }

    /**
     * Apaga a conta, exigindo confirmação por senha.
     */
    private function deleteAccount(): void
    {
        $current = (string) ($_POST['senha_atual'] ?? '');
        $user = User::find($this->userId);

        if ($user === null || !$user->verifyPassword($current)) {
            flash()->put('error', 'Informe a senha atual para apagar a conta.');

            return;
        }

        // Apagados antes do usuário, e não depois: o `ON DELETE CASCADE` do
        // banco remove as linhas de `images`/`videos` automaticamente, mas não
        // sabe nada sobre o Supabase Storage. Sem este passo, o arquivo
        // continua público e acessível indefinidamente - órfão, sem nenhuma
        // linha no banco que aponte para ele.
        $this->deleteOwnedMediaFiles();

        User::delete($this->userId);
        Auth::logout();

        flash()->put('message', 'Sua conta foi apagada.');
        redirect('/login');
    }

    /**
     * Remove do Supabase Storage toda mídia pertencente a este usuário.
     *
     * Melhor esforço: uma falha de rede ao apagar um arquivo não pode impedir
     * a exclusão da conta em si - a pessoa pediu para sair, e um objeto órfão
     * no bucket é um problema bem menor do que uma conta presa sem poder ser
     * apagada.
     */
    private function deleteOwnedMediaFiles(): void
    {
        $storage = Storage::disk();

        foreach (Image::forUser($this->userId) as $image) {
            try {
                $storage->deleteImage((string) $image->file_path);
            } catch (\Throwable $e) {
                error_log('[strathub] falha ao apagar imagem órfã na exclusão de conta: ' . $e->getMessage());
            }
        }

        foreach (Video::forUser($this->userId) as $video) {
            try {
                $storage->deleteVideo((string) $video->file_path);
            } catch (\Throwable $e) {
                error_log('[strathub] falha ao apagar vídeo órfão na exclusão de conta: ' . $e->getMessage());
            }
        }
    }

    /**
     * Apaga do Storage o avatar anterior, se ele for um arquivo nosso.
     *
     * `avatarDefault.png` e uma string vazia (conta sem avatar ainda) não
     * apontam para nada no bucket - nesses casos não há o que apagar.
     */
    private function deletePreviousAvatar(string $previousAvatarUrl): void
    {
        if ($previousAvatarUrl === '' || $previousAvatarUrl === 'avatarDefault.png') {
            return;
        }

        $prefix = (string) Config::get('storage.image_prefix', '');

        if ($prefix === '' || !str_starts_with($previousAvatarUrl, $prefix)) {
            return;
        }

        $filePath = substr($previousAvatarUrl, strlen($prefix));

        try {
            Storage::disk()->deleteImage($filePath);
        } catch (\Throwable $e) {
            error_log('[strathub] falha ao apagar avatar anterior: ' . $e->getMessage());
        }
    }

    /**
     * Substitui a foto de perfil.
     */
    private function updateAvatar(): void
    {
        $file = $_FILES['avatar'] ?? null;

        if (!UploadValidator::wasUploaded($file)) {
            flash()->put('error', 'Selecione uma imagem para alterar seu avatar.');

            return;
        }

        if (!UploadValidator::isSuccessful($file)) {
            flash()->put('error', UploadValidator::describeError((int) $file['error']));

            return;
        }

        // Guardado antes do upload: é o que permite apagar o arquivo antigo do
        // Storage depois de confirmado que o novo já está no lugar. Sem isso,
        // cada troca de avatar deixa o arquivo anterior órfão no bucket - o
        // registro em `users.avatar` é sobrescrito, mas o objeto em si nunca
        // é removido.
        $previousAvatar = (string) ($_SESSION['auth']->avatar ?? '');

        $result = Storage::disk()->uploadImage((array) $file, $this->userId);

        if (!$result->ok || $result->file === null) {
            flash()->put('error', (string) $result->error);

            return;
        }

        $updated = User::updateProfile($this->userId, ['avatar' => $result->file->publicUrl()]);

        if ($updated === null) {
            flash()->put('error', 'A imagem subiu, mas não foi possível salvar no perfil.');

            return;
        }

        $this->deletePreviousAvatar($previousAvatar);

        Auth::refresh(['avatar' => $updated->avatar]);

        flash()->put('message', 'Avatar atualizado.');
    }

    /**
     * O formulário de perfil não desenha erros campo a campo, então as
     * mensagens são reunidas num único toast.
     */
    private function flashValidationErrors(Validation $validation): void
    {
        $messages = array_merge(...array_values($validation->errors()));

        flash()->put('error', implode(' ', $messages));
    }
}
