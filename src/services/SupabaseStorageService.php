<?php

class SupabaseStorageService
{
    private $supabaseUrl;
    private $supabaseKey;
    private $bucketName;

    public function __construct()
    {
        $this->supabaseUrl = $_ENV['SUPABASE_URL'] ?? 'https://YOUR_PROJECT_REF.supabase.co';
        // Usar service key para uploads (tem permissões administrativas para contornar RLS)
        $this->supabaseKey = $_ENV['SUPABASE_SERVICE_KEY'] ?? $_ENV['SUPABASE_ANON_KEY'] ?? 'YOUR_ANON_PUBLIC_KEY';
        $this->bucketName = 'strategy-covers';
    }

    /**
     * Faz upload de uma imagem para o Supabase Storage
     */
    public function uploadImage($file, $userId)
    {
        try {
            // Validar arquivo
            if (!$this->validateFile($file)) {
                throw new Exception('Arquivo inválido');
            }

            // Gerar nome único para o arquivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filePath = "user_{$userId}/{$filename}";

            // Preparar dados para upload
            $fileContent = file_get_contents($file['tmp_name']);
            $mimeType = $file['type'];

            // Fazer upload via cURL
            $uploadResult = $this->uploadToSupabase($filePath, $fileContent, $mimeType);

            if (!$uploadResult) {
                throw new Exception('Falha no upload para o Supabase');
            }

            // Salvar informações da imagem no banco
            $image = new Image();
            $image->filename = $filename;
            $image->original_name = $file['name'];
            $image->file_path = $filePath;
            $image->file_size = $file['size'];
            $image->mime_type = $mimeType;
            $image->user_id = $userId;

            $result = $image->save();

            if (!$result) {
                throw new Exception('Falha ao salvar informações da imagem no banco');
            }

            return $image;

        } catch (Exception $e) {
            error_log("Erro no upload da imagem: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida o arquivo de upload
     */
    private function validateFile($file)
    {
        // Verificar se o arquivo foi enviado
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Verificar tamanho (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        // Verificar tipo MIME
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        // Verificar extensão
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * Faz upload do arquivo para o Supabase Storage
     */
    private function uploadToSupabase($filePath, $fileContent, $mimeType)
    {
        $url = "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/{$filePath}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->supabaseKey,
            'apikey: ' . $this->supabaseKey,
            'Content-Type: ' . $mimeType,
            'x-upsert: true'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300 || $curlError) {
            error_log("Erro no upload para Supabase: HTTP $httpCode" . ($curlError ? ", cURL: $curlError" : ""));
        }

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Deleta uma imagem do Supabase Storage
     */
    public function deleteImage($filePath)
    {
        $url = "{$this->supabaseUrl}/storage/v1/object/{$this->bucketName}/{$filePath}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->supabaseKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Gera URL pública para uma imagem
     */
    public function getPublicUrl($filePath)
    {
        return "{$this->supabaseUrl}/storage/v1/object/public/{$this->bucketName}/{$filePath}";
    }
}
